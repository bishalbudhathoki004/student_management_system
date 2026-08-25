<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

$message = '';
$error = '';

$studentData = [
    'name' => '',
    'gender' => '',
    'faculty' => '',
    'dob' => '',
    'address' => '',
    'phone' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentData = sanitize_post(array_keys($studentData));
    $errors = validate_student_data($studentData, true);

    if ($errors) {
        $error = $errors[0];
    } else {
        try {
            $_SESSION['new_student'] = student_create_with_account($conn, $studentData);
            $message = 'Student added successfully.';
            $studentData = array_fill_keys(array_keys($studentData), '');
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$newStudent = $_SESSION['new_student'] ?? null;
if ($message !== '') {
    unset($_SESSION['new_student']);
}

$pageTitle = 'Add Student';
$activePage = 'add_student';
require_once '../templates/admin/layout_header.php';
?>

<div class="card">
    <h1 class="page-title">Add Student</h1>

    <?php if ($message !== ''): ?>
        <div class="message"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($newStudent): ?>
        <div class="credentials">
            <strong>Student Login Credentials</strong>
            <p><strong>ID:</strong> <?php echo (int) ($newStudent['student_id'] ?? 0); ?></p>
            <p><strong>Name:</strong> <?php echo e($newStudent['name'] ?? ''); ?></p>
            <p><strong>Username:</strong> <?php echo e($newStudent['username'] ?? ''); ?></p>
            <p><strong>Password:</strong> <?php echo e($newStudent['password'] ?? ''); ?></p>
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.print()">Print Credentials</button>
                <a href="add_student.php" class="btn btn-secondary">Add Another Student</a>
                <a href="manage_students.php" class="btn btn-secondary">Manage Students</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($message === ''): ?>
        <form method="POST" class="card" style="border-style:dashed;">
            <?php
            $isEdit = false;
            $submitLabel = 'Add Student';
            $cancelUrl = 'manage_students.php';
            require '../templates/students/form_fields.php';
            ?>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../templates/admin/layout_footer.php'; ?>
