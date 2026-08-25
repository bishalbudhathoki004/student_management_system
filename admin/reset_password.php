<?php
require_once '../includes/bootstrap.php';

require_admin_auth();

$studentId = get_request_int($_GET, 'id');
if ($studentId <= 0) {
    set_flash('error', 'Invalid student.');
    redirect_to('manage_students.php');
}

try {
    $_SESSION['password_reset'] = student_reset_password($conn, $studentId);
    set_flash('success', 'Password reset successfully.');
} catch (Throwable $e) {
    set_flash('error', $e->getMessage());
}

redirect_to('manage_students.php');
