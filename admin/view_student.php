<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

$id = get_request_int($_GET, 'id');
if ($id <= 0) {
    set_flash('error', 'Invalid student.');
    redirect_to('manage_students.php');
}

$student = student_find_by_id($conn, $id);
if (!$student) {
    set_flash('error', 'Student not found.');
    redirect_to('manage_students.php');
}

$successMessage = get_flash('success');

$pageTitle = 'View Student';
$activePage = 'manage_students';
require_once '../templates/admin/layout_header.php';
?>

<div class="card">
    <h1 class="page-title">View Student</h1>

    <?php if ($successMessage): ?>
        <div class="flash-success"><?php echo e($successMessage); ?></div>
    <?php endif; ?>

    <div class="actions" style="margin-bottom:12px;">
        <a href="manage_students.php" class="btn btn-secondary">Back</a>
        <a href="edit_student.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-primary">Edit Student</a>
    </div>

    <?php if (!empty($student['photo'])): ?>
        <div class="form-group">
            <img src="../assets/uploads/<?php echo e($student['photo']); ?>" alt="Student Photo" class="photo-preview">
        </div>
    <?php endif; ?>

    <h2 class="student-name"><?php echo e($student['name'] ?? '-'); ?></h2>

    <div class="student-details">
        <div class="detail-item"><span class="detail-label">Gender</span><div class="detail-value"><?php echo e($student['gender'] ?? '-'); ?></div></div>
        <div class="detail-item"><span class="detail-label">Date of Birth</span><div class="detail-value"><?php echo e($student['dob'] ?? '-'); ?></div></div>
        <div class="detail-item"><span class="detail-label">Faculty</span><div class="detail-value"><?php echo e($student['faculty'] ?? '-'); ?></div></div>
        <div class="detail-item"><span class="detail-label">Phone</span><div class="detail-value"><?php echo e($student['phone'] ?? '-'); ?></div></div>
        <div class="detail-item"><span class="detail-label">Email</span><div class="detail-value"><?php echo e($student['email'] ?? '-'); ?></div></div>
        <div class="detail-item"><span class="detail-label">Address</span><div class="detail-value"><?php echo e($student['address'] ?? '-'); ?></div></div>
    </div>
</div>

<?php require_once '../templates/admin/layout_footer.php'; ?>
