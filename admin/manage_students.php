<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

$search = sanitize_input($_GET['search'] ?? '');
$students = student_find_all($conn, $search);

$successMessage = get_flash('success');
$errorMessage = get_flash('error');
$passwordReset = $_SESSION['password_reset'] ?? null;
unset($_SESSION['password_reset']);

$pageTitle = 'Manage Students';
$activePage = 'manage_students';
require_once '../templates/admin/layout_header.php';
?>

<div class="card">
    <h1 class="page-title">Manage Students</h1>

    <?php if ($successMessage): ?>
        <div class="flash-success"><?php echo e($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="flash-error"><?php echo e($errorMessage); ?></div>
    <?php endif; ?>

    <?php if ($passwordReset): ?>
        <div class="credentials">
            <strong>New Login Credentials</strong>
            <p><strong>Name:</strong> <?php echo e($passwordReset['name'] ?? ''); ?></p>
            <p><strong>Username:</strong> <?php echo e($passwordReset['username'] ?? ''); ?></p>
            <p><strong>Password:</strong> <?php echo e($passwordReset['password'] ?? ''); ?></p>
        </div>
    <?php endif; ?>

    <form method="GET" class="search-form">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search by name, faculty, phone, email, username">
        <button type="submit">Search</button>
        <a href="manage_students.php" class="btn btn-secondary">Clear</a>
    </form>

    <?php require '../templates/students/table.php'; ?>
</div>

<?php require_once '../templates/admin/layout_footer.php'; ?>
