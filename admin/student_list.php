<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

$students = student_find_all($conn);

$pageTitle = 'Student List';
$activePage = 'student_list';
$search = '';
require_once '../templates/admin/layout_header.php';
?>

<div class="card">
    <h1 class="page-title">Student List</h1>
    <?php require '../templates/students/table.php'; ?>
</div>

<?php require_once '../templates/admin/layout_footer.php'; ?>
