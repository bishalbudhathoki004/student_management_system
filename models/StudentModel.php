<?php

function student_find_all(mysqli $conn, string $search = ''): array
{
    $baseSql = "
        SELECT
            students.id,
            students.name,
            students.gender,
            students.faculty,
            students.phone,
            students.email,
            users.username
        FROM students
        LEFT JOIN users
            ON users.student_id = students.id
            AND users.role = 'student'
    ";

    if ($search !== '') {
        $searchValue = '%' . $search . '%';
        $sql = $baseSql . "
            WHERE
                students.name LIKE ?
                OR students.faculty LIKE ?
                OR students.phone LIKE ?
                OR students.email LIKE ?
                OR users.username LIKE ?
            ORDER BY students.name ASC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('sssss', $searchValue, $searchValue, $searchValue, $searchValue, $searchValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    $sql = $baseSql . ' ORDER BY students.name ASC';
    $result = $conn->query($sql);

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function student_find_by_id(mysqli $conn, int $id): ?array
{
    $sql = "
        SELECT
            id,
            user_id,
            name,
            gender,
            dob,
            address,
            phone,
            email,
            faculty,
            photo,
            profile_completed
        FROM students
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $student ?: null;
}

function student_create_with_account(mysqli $conn, array $data): array
{
    $conn->begin_transaction();

    try {
        $sql = "
            INSERT INTO students (
                name,
                gender,
                faculty,
                dob,
                address,
                phone,
                email,
                profile_completed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Could not prepare student query.');
        }

        $stmt->bind_param(
            'sssssss',
            $data['name'],
            $data['gender'],
            $data['faculty'],
            $data['dob'],
            $data['address'],
            $data['phone'],
            $data['email']
        );

        if (!$stmt->execute()) {
            throw new RuntimeException('Could not add student.');
        }

        $studentId = (int) $conn->insert_id;
        $stmt->close();

        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']));
        if ($baseUsername === '') {
            $baseUsername = 'student';
        }

        do {
            $username = $baseUsername . random_int(100, 999);
            $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            if (!$checkStmt) {
                throw new RuntimeException('Could not check username.');
            }
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();
        } while ($exists);

        $plainPassword = generate_password(8);
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        if ($hashedPassword === false) {
            throw new RuntimeException('Could not generate password hash.');
        }

        $userStmt = $conn->prepare(
            "INSERT INTO users (username, password, role, student_id) VALUES (?, ?, 'student', ?)"
        );

        if (!$userStmt) {
            throw new RuntimeException('Could not prepare user query.');
        }

        $userStmt->bind_param('ssi', $username, $hashedPassword, $studentId);

        if (!$userStmt->execute()) {
            throw new RuntimeException('Could not create student login.');
        }

        $userId = (int) $conn->insert_id;
        $userStmt->close();

        $updateStmt = $conn->prepare('UPDATE students SET user_id = ? WHERE id = ?');

        if (!$updateStmt) {
            throw new RuntimeException('Could not connect student account.');
        }

        $updateStmt->bind_param('ii', $userId, $studentId);

        if (!$updateStmt->execute()) {
            throw new RuntimeException('Could not connect student account.');
        }

        $updateStmt->close();

        $conn->commit();

        return [
            'student_id' => $studentId,
            'name' => $data['name'],
            'username' => $username,
            'password' => $plainPassword,
        ];
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

function student_upload_photo(array $file, int $studentId): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['name' => null, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['name' => null, 'error' => 'Could not upload photo.'];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return ['name' => null, 'error' => 'Photo size must be less than 2 MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return ['name' => null, 'error' => 'Unable to verify photo.'];
    }

    $fileType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensionMap[$fileType])) {
        return ['name' => null, 'error' => 'Invalid photo format. Use JPG, PNG or WEBP.'];
    }

    $newPhotoName = 'student_' . $studentId . '_' . time() . '.' . $extensionMap[$fileType];
    $uploadPath = __DIR__ . '/../assets/uploads/' . $newPhotoName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['name' => null, 'error' => 'Could not save photo file.'];
    }

    return ['name' => $newPhotoName, 'error' => null];
}

function student_update_profile(mysqli $conn, int $id, array $data): bool
{
    $student = student_find_by_id($conn, $id);
    if (!$student) {
        return false;
    }

    $photo = $data['photo'] ?? $student['photo'];

    $sql = 'UPDATE students SET gender=?, dob=?, address=?, phone=?, email=?, faculty=?, photo=? WHERE id=?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'sssssssi',
        $data['gender'],
        $data['dob'],
        $data['address'],
        $data['phone'],
        $data['email'],
        $data['faculty'],
        $photo,
        $id
    );

    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function student_delete(mysqli $conn, int $id): bool
{
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare('DELETE FROM users WHERE student_id = ?');
        if (!$stmt) {
            throw new RuntimeException('Could not prepare user deletion.');
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not delete student login.');
        }
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('Could not prepare student deletion.');
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('Could not delete student.');
        }

        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        $conn->commit();

        return $deleted;
    } catch (Throwable $e) {
        $conn->rollback();
        return false;
    }
}

function student_reset_password(mysqli $conn, int $studentId): array
{
    $sql = "
        SELECT
            students.id,
            students.name,
            users.id AS user_id,
            users.username
        FROM students
        LEFT JOIN users
            ON users.student_id = students.id
            AND users.role = 'student'
        WHERE students.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Database error.');
    }

    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        throw new RuntimeException('Student not found.');
    }

    if (empty($student['user_id']) || empty($student['username'])) {
        throw new RuntimeException('Student login account not found.');
    }

    $newPassword = generate_password(8);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    if ($hashedPassword === false) {
        throw new RuntimeException('Unable to create password.');
    }

    $userId = (int) $student['user_id'];

    $stmt = $conn->prepare(
        "UPDATE users SET password = ? WHERE id = ? AND student_id = ? AND role = 'student'"
    );

    if (!$stmt) {
        throw new RuntimeException('Database error.');
    }

    $stmt->bind_param('sii', $hashedPassword, $userId, $studentId);

    if (!$stmt->execute() || $stmt->affected_rows === 0) {
        $stmt->close();
        throw new RuntimeException('Password reset failed.');
    }

    $stmt->close();

    return [
        'name' => $student['name'],
        'username' => $student['username'],
        'password' => $newPassword,
    ];
}
