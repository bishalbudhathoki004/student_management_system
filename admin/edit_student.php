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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentData = sanitize_post(['gender', 'faculty', 'dob', 'address', 'phone', 'email']);
    $errors = validate_student_data($studentData, false);

    if ($errors) {
        $error = $errors[0];
        $student = array_merge($student, $studentData);
    } else {
        $upload = student_upload_photo($_FILES['photo'] ?? [], $id);

        if ($upload['error']) {
            $error = $upload['error'];
            $student = array_merge($student, $studentData);
        } else {
            if ($upload['name']) {
                $studentData['photo'] = $upload['name'];
            }

            if (student_update_profile($conn, $id, $studentData)) {
                set_flash('success', 'Student profile updated successfully.');
                redirect_to('view_student.php?id=' . $id);
            }

            $error = 'Could not update student.';
        }
    }
}

$pageTitle = 'Edit Student';
$activePage = 'manage_students';
require_once '../templates/admin/layout_header.php';
?>

<div class="card">
    <h1 class="page-title">Edit Student</h1>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if (!empty($student['photo'])): ?>
            <div class="form-group">
                <img src="../assets/uploads/<?php echo e($student['photo']); ?>" alt="Student Photo" class="photo-preview">
            </div>
        <?php endif; ?>

        <?php
        $studentData = $student;
        $isEdit = true;
        $showPhoto = true;
        $submitLabel = 'Save Changes';
        $cancelUrl = 'view_student.php?id=' . $id;
        require '../templates/students/form_fields.php';
        ?>
    </form>
</div>

<?php require_once '../templates/admin/layout_footer.php'; ?>
