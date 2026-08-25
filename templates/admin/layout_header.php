<?php
$pageTitle = $pageTitle ?? 'Admin';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<header class="topbar">
    <button type="button" class="menu-button" id="menuButton" aria-expanded="false" aria-controls="sidebar">
        <span></span><span></span><span></span>
    </button>
    <div class="brand">Student Management System</div>
</header>
<aside class="sidebar" id="sidebar">
    <a class="sidebar-link <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
    <a class="sidebar-link <?php echo $activePage === 'add_student' ? 'active' : ''; ?>" href="add_student.php">Add Student</a>
    <a class="sidebar-link <?php echo $activePage === 'manage_students' ? 'active' : ''; ?>" href="manage_students.php">Manage Students</a>
    <a class="sidebar-link <?php echo $activePage === 'student_list' ? 'active' : ''; ?>" href="student_list.php">Student List</a>
    <a class="sidebar-link sidebar-logout" href="../logout.php">Logout</a>
</aside>
<div class="overlay" id="sidebarOverlay"></div>
<main class="main">
