<?php

session_start();

require_once "../config/database.php";

// ==================================================
// ADMIN CHECK
// ==================================================

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../login.php");
    exit();
}

// ==================================================
// GET STUDENT ID
// ==================================================

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    $_SESSION["reset_error"] = "Invalid student.";
    header("Location: manage_students.php");
    exit();
}

$student_id = (int) $_GET["id"];

if ($student_id <= 0) {
    $_SESSION["reset_error"] = "Invalid student.";
    header("Location: manage_students.php");
    exit();
}

// ==================================================
// FIND STUDENT LOGIN
// ==================================================

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
    $_SESSION["reset_error"] = "Database error.";
    header("Location: manage_students.php");
    exit();
}

$stmt->bind_param("i", $student_id);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

$stmt->close();

// ==================================================
// CHECK STUDENT
// ==================================================

if (!$student) {
    $_SESSION["reset_error"] = "Student not found.";
    header("Location: manage_students.php");
    exit();
}

// ==================================================
// CHECK USER ACCOUNT
// ==================================================

if (
    empty($student["user_id"]) ||
    empty($student["username"])
) {
    $_SESSION["reset_error"] =
        "Student login account not found.";

    header("Location: manage_students.php");
    exit();
}

// ==================================================
// GENERATE PASSWORD
// ==================================================

$characters =
    "ABCDEFGHJKLMNPQRSTUVWXYZ" .
    "abcdefghijkmnopqrstuvwxyz" .
    "23456789";

$new_password = "";

for ($i = 0; $i < 8; $i++) {

    $new_password .= $characters[
        random_int(
            0,
            strlen($characters) - 1
        )
    ];
}

// ==================================================
// HASH PASSWORD
// ==================================================

$hashed_password = password_hash(
    $new_password,
    PASSWORD_DEFAULT
);

if (!$hashed_password) {

    $_SESSION["reset_error"] =
        "Unable to create password.";

    header("Location: manage_students.php");
    exit();
}

// ==================================================
// UPDATE PASSWORD
// ==================================================

$user_id = (int) $student["user_id"];

$sql = "
    UPDATE users
    SET password = ?
    WHERE id = ?
      AND student_id = ?
      AND role = 'student'
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["reset_error"] =
        "Database error.";

    header("Location: manage_students.php");
    exit();
}

$stmt->bind_param(
    "sii",
    $hashed_password,
    $user_id,
    $student_id
);

if (!$stmt->execute()) {

    $stmt->close();

    $_SESSION["reset_error"] =
        "Password reset failed.";

    header("Location: manage_students.php");
    exit();
}

if ($stmt->affected_rows === 0) {

    $stmt->close();

    $_SESSION["reset_error"] =
        "Password was not changed.";

    header("Location: manage_students.php");
    exit();
}

$stmt->close();

// ==================================================
// SAVE RESET DETAILS
// ==================================================

$_SESSION["password_reset"] = [

    "name" =>
        $student["name"],

    "username" =>
        $student["username"],

    "password" =>
        $new_password

];

$_SESSION["reset_success"] =
    "Password reset successfully.";

// ==================================================
// RETURN TO MANAGE STUDENTS
// ==================================================

header("Location: manage_students.php");

exit();

?>