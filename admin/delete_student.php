<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('manage_students.php');
}

$id = get_request_int($_POST, 'id');
if ($id <= 0) {
    set_flash('error', 'Invalid student.');
    redirect_to('manage_students.php');
}

if (student_delete($conn, $id)) {
    set_flash('success', 'Student deleted successfully.');
} else {
    set_flash('error', 'Unable to delete student.');
}

redirect_to('manage_students.php');
