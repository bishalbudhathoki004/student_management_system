<?php

session_start();

require_once "../config/database.php";


// ==========================================
// CHECK ADMIN LOGIN
// ==========================================

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../login.php");
    exit();
}


// ==========================================
// TOTAL STUDENTS
// ==========================================

$sql = "
    SELECT COUNT(*) AS total_students
    FROM students
";

$result = $conn->query($sql);

$row = $result->fetch_assoc();

$total_students = (int) $row["total_students"];


// ==========================================
// FACULTY COUNTS
// ==========================================

$sql = "
    SELECT faculty, COUNT(*) AS total
    FROM students
    GROUP BY faculty
";

$result = $conn->query($sql);


$bca = 0;
$bit = 0;
$bsccsit = 0;


while ($row = $result->fetch_assoc()) {

    if ($row["faculty"] === "BCA") {

        $bca = (int) $row["total"];

    } elseif ($row["faculty"] === "BIT") {

        $bit = (int) $row["total"];

    } elseif ($row["faculty"] === "BSc CSIT") {

        $bsccsit = (int) $row["total"];

    }
}


// ==========================================
// PERCENTAGES
// ==========================================

$bca_percentage = $total_students > 0
    ? ($bca / $total_students) * 100
    : 0;

$bit_percentage = $total_students > 0
    ? ($bit / $total_students) * 100
    : 0;

$bsccsit_percentage = $total_students > 0
    ? ($bsccsit / $total_students) * 100
    : 0;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard</title>


    <style>

        /* =========================================
           RESET
        ========================================= */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f3f7f6;

            color: #243331;

        }


        a {
            text-decoration: none;
        }



        /* =========================================
           TOP BAR
        ========================================= */

        .topbar {

            position: sticky;

            top: 0;

            z-index: 1000;

            width: 100%;

            height: 68px;

            display: flex;

            align-items: center;

            padding: 0 28px;

            background: #ffffff;

            border-bottom: 1px solid #dfe9e6;

            box-shadow:
                0 2px 12px rgba(30, 60, 55, 0.05);

        }



        /* =========================================
           MENU BUTTON
        ========================================= */

        .menu-button {

            width: 40px;

            height: 40px;

            padding: 0;

            border: 1px solid #d5e2df;

            border-radius: 9px;

            background: #ffffff;

            cursor: pointer;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            gap: 4px;

            flex-shrink: 0;

            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;

        }


        .menu-button:hover {

            background: #eef7f4;

            border-color: #b8d4ce;

        }


        .menu-button:active {

            transform: scale(0.96);

        }


        .menu-button span {

            width: 18px;

            height: 2px;

            background: #31534d;

            border-radius: 5px;

        }



        /* =========================================
           BRAND
        ========================================= */

        .brand {

            margin-left: 15px;

            color: #24584f;

            font-size: 18px;

            font-weight: 700;

            white-space: nowrap;

        }



        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {

            position: fixed;

            top: 68px;

            left: -270px;

            width: 250px;

            height: calc(100vh - 68px);

            padding: 24px 18px;

            background: #ffffff;

            border-right: 1px solid #dfe9e6;

            box-shadow:
                8px 0 25px rgba(30, 60, 55, 0.07);

            z-index: 1100;

            transition:
                left 0.28s ease;

        }


        .sidebar.open {

            left: 0;

        }


        .sidebar-link {

            display: block;

            width: 100%;

            padding: 13px 14px;

            margin-bottom: 6px;

            border-radius: 8px;

            color: #50625e;

            font-size: 14px;

            font-weight: 600;

            transition:
                background 0.2s ease,
                color 0.2s ease;

        }


        .sidebar-link:hover {

            background: #edf7f4;

            color: #28675c;

        }


        .sidebar-link.active {

            background: #e2f1ed;

            color: #28675c;

        }


        /* =========================================
           LOGOUT
        ========================================= */

        .sidebar-bottom {

            position: absolute;

            left: 18px;

            right: 18px;

            bottom: 22px;

        }


        .sidebar-logout {

            display: block;

            padding: 13px 14px;

            border-radius: 8px;

            color: #a34c4c;

            font-size: 14px;

            font-weight: 600;

            transition:
                background 0.2s ease;

        }


        .sidebar-logout:hover {

            background: #fff3f3;

        }



        /* =========================================
           OVERLAY
        ========================================= */

        .sidebar-overlay {

            position: fixed;

            inset: 0;

            background:
                rgba(25, 45, 42, 0.20);

            z-index: 1050;

            opacity: 0;

            visibility: hidden;

            transition:
                opacity 0.25s ease,
                visibility 0.25s ease;

        }


        .sidebar-overlay.show {

            opacity: 1;

            visibility: visible;

        }



        /* =========================================
           MAIN
        ========================================= */

        .dashboard-container {

            width: 100%;

            max-width: 1350px;

            margin: 0 auto;

            padding: 38px 34px 60px;

        }



        /* =========================================
           DASHBOARD TITLE
        ========================================= */

        .dashboard-heading {

            margin-bottom: 28px;

        }


        .dashboard-heading h1 {

            margin: 0;

            font-size: 29px;

            color: #203d38;

            font-weight: 700;

        }


        .dashboard-heading p {

            margin: 7px 0 0;

            color: #758681;

            font-size: 14px;

        }



        /* =========================================
           STATISTICS
        ========================================= */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 17px;

            margin-bottom: 22px;

        }


        .stat-card {

            position: relative;

            background: #ffffff;

            border: 1px solid #dfe9e6;

            border-radius: 13px;

            padding: 22px;

            min-height: 145px;

            box-shadow:
                0 4px 14px rgba(35, 65, 60, 0.045);

            overflow: hidden;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .stat-card::before {

            content: "";

            position: absolute;

            left: 0;

            top: 0;

            width: 100%;

            height: 3px;

            background: #6aa99d;

        }


        .stat-card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 9px 24px rgba(35, 65, 60, 0.08);

        }


        .stat-label {

            color: #73837f;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 14px;

        }


        .stat-number {

            color: #28675c;

            font-size: 34px;

            line-height: 1;

            font-weight: 700;

            margin-bottom: 9px;

        }


        .stat-description {

            color: #98a6a2;

            font-size: 12px;

        }



        /* =========================================
           LOWER CONTENT
        ========================================= */

        .content-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1.5fr)
                minmax(280px, 0.85fr);

            gap: 20px;

        }


        .panel {

            background: #ffffff;

            border: 1px solid #dfe9e6;

            border-radius: 13px;

            padding: 26px;

            box-shadow:
                0 4px 14px rgba(35, 65, 60, 0.045);

        }


        .panel-header {

            margin-bottom: 25px;

        }


        .panel-header h2 {

            margin: 0;

            font-size: 19px;

            color: #29423e;

        }


        .panel-header p {

            margin: 6px 0 0;

            color: #879591;

            font-size: 13px;

        }



        /* =========================================
           FACULTY
        ========================================= */

        .faculty-list {

            display: flex;

            flex-direction: column;

            gap: 20px;

        }


        .faculty-row {

            display: grid;

            grid-template-columns:
                95px
                1fr
                45px;

            align-items: center;

            gap: 14px;

        }


        .faculty-name {

            color: #4e625e;

            font-size: 13px;

            font-weight: 600;

        }


        .faculty-progress {

            height: 9px;

            background: #edf3f1;

            border-radius: 20px;

            overflow: hidden;

        }


        .faculty-progress span {

            display: block;

            height: 100%;

            background: #6aa99d;

            border-radius: 20px;

            min-width: 3px;

            transition:
                width 0.5s ease;

        }


        .faculty-number {

            color: #315b53;

            font-size: 13px;

            font-weight: 700;

            text-align: right;

        }



        /* =========================================
           TOTAL BOX
        ========================================= */

        .total-box {

            margin-top: 25px;

            padding: 18px;

            border-radius: 10px;

            background: #f2f8f6;

            border: 1px solid #dcebe7;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

        }


        .total-box span {

            color: #70827d;

            font-size: 13px;

            font-weight: 600;

        }


        .total-box strong {

            color: #28675c;

            font-size: 24px;

        }



        /* =========================================
           SYSTEM PANEL
        ========================================= */

        .system-panel {

            display: flex;

            flex-direction: column;

        }


        .system-title {

            margin: 0;

            font-size: 19px;

            color: #29423e;

        }


        .system-subtitle {

            margin: 7px 0 22px;

            color: #879591;

            font-size: 13px;

            line-height: 1.5;

        }


        .system-row {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 15px 0;

            border-bottom: 1px solid #edf2f0;

        }


        .system-row:last-child {

            border-bottom: none;

        }


        .system-row span:first-child {

            color: #61726e;

            font-size: 13px;

        }


        .system-row strong {

            color: #315b53;

            font-size: 13px;

        }



        /* =========================================
           TABLET
        ========================================= */

        @media (max-width: 1050px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }


            .content-grid {

                grid-template-columns: 1fr;

            }

        }



        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 700px) {

            .topbar {

                height: 62px;

                padding: 0 15px;

            }


            .brand {

                margin-left: 12px;

                font-size: 15px;

                overflow: hidden;

                text-overflow: ellipsis;

            }


            .menu-button {

                width: 39px;

                height: 39px;

            }


            .sidebar {

                top: 62px;

                height: calc(100vh - 62px);

                width: min(270px, 82vw);

            }


            .dashboard-container {

                padding:
                    28px 16px 40px;

            }


            .dashboard-heading {

                margin-bottom: 22px;

            }


            .dashboard-heading h1 {

                font-size: 25px;

            }


            .dashboard-heading p {

                font-size: 13px;

            }


            .stats-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 12px;

            }


            .stat-card {

                min-height: 125px;

                padding: 18px;

            }


            .stat-number {

                font-size: 28px;

            }


            .stat-label {

                font-size: 12px;

            }


            .stat-description {

                font-size: 11px;

            }


            .content-grid {

                gap: 14px;

            }


            .panel {

                padding: 20px;

            }


            .panel-header h2,
            .system-title {

                font-size: 17px;

            }


            .faculty-row {

                grid-template-columns:
                    75px
                    1fr
                    35px;

                gap: 9px;

            }

        }



        /* =========================================
           SMALL MOBILE
        ========================================= */

        @media (max-width: 430px) {

            .brand {

                font-size: 14px;

            }


            .dashboard-container {

                padding:
                    24px 13px 35px;

            }


            .dashboard-heading h1 {

                font-size: 23px;

            }


            .dashboard-heading p {

                display: none;

            }


            .stats-grid {

                grid-template-columns: 1fr;

                gap: 11px;

            }


            .stat-card {

                min-height: auto;

                padding: 19px;

            }


            .stat-number {

                font-size: 30px;

            }


            .faculty-row {

                grid-template-columns:
                    65px
                    1fr
                    30px;

                gap: 8px;

            }


            .faculty-name,
            .faculty-number {

                font-size: 12px;

            }


            .total-box {

                padding: 15px;

            }


            .total-box strong {

                font-size: 21px;

            }

        }



        /* =========================================
           VERY SMALL MOBILE
        ========================================= */

        @media (max-width: 350px) {

            .brand {

                max-width: 205px;

            }


            .panel {

                padding: 17px;

            }


            .faculty-row {

                grid-template-columns:
                    55px
                    1fr
                    28px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     TOP BAR
========================================= -->

<header class="topbar">


    <!-- THREE LINE MENU -->

    <button
        type="button"
        class="menu-button"
        id="menuButton"
        aria-label="Open navigation menu"
        aria-expanded="false"
    >

        <span></span>
        <span></span>
        <span></span>

    </button>


    <!-- SYSTEM NAME -->

    <div class="brand">

        Student Management System

    </div>


</header>



<!-- =========================================
     SIDEBAR
========================================= -->

<aside
    class="sidebar"
    id="sidebar"
>


    <a
        href="dashboard.php"
        class="sidebar-link active"
    >
        Dashboard
    </a>


    <a
        href="add_student.php"
        class="sidebar-link"
    >
        Add Student
    </a>


    <a
        href="manage_students.php"
        class="sidebar-link"
    >
        Manage Students
    </a>


    <a
        href="student_list.php"
        class="sidebar-link"
    >
        Student List
    </a>


    <div class="sidebar-bottom">

        <a
            href="../logout.php"
            class="sidebar-logout"
        >
            Logout
        </a>

    </div>


</aside>



<!-- =========================================
     OVERLAY
========================================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>



<!-- =========================================
     MAIN DASHBOARD
========================================= -->

<main class="dashboard-container">


    <!-- =====================================
         HEADING
    ====================================== -->

    <section class="dashboard-heading">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Student overview
        </p>

    </section>



    <!-- =====================================
         STAT CARDS
    ====================================== -->

    <section class="stats-grid">


        <!-- TOTAL -->

        <div class="stat-card">

            <div class="stat-label">
                Total Students
            </div>


            <div class="stat-number">

                <?php echo $total_students; ?>

            </div>


            <div class="stat-description">
                Registered students
            </div>

        </div>



        <!-- BCA -->

        <div class="stat-card">

            <div class="stat-label">
                BCA
            </div>


            <div class="stat-number">

                <?php echo $bca; ?>

            </div>


            <div class="stat-description">
                BCA students
            </div>

        </div>



        <!-- BIT -->

        <div class="stat-card">

            <div class="stat-label">
                BIT
            </div>


            <div class="stat-number">

                <?php echo $bit; ?>

            </div>


            <div class="stat-description">
                BIT students
            </div>

        </div>



        <!-- BSC CSIT -->

        <div class="stat-card">

            <div class="stat-label">
                BSc CSIT
            </div>


            <div class="stat-number">

                <?php echo $bsccsit; ?>

            </div>


            <div class="stat-description">
                BSc CSIT students
            </div>

        </div>


    </section>



    <!-- =====================================
         LOWER CONTENT
    ====================================== -->

    <section class="content-grid">


        <!-- =================================
             FACULTY OVERVIEW
        ================================== -->

        <div class="panel">


            <div class="panel-header">

                <h2>
                    Faculty Overview
                </h2>


                <p>
                    Student distribution
                </p>

            </div>


            <div class="faculty-list">


                <!-- BCA -->

                <div class="faculty-row">

                    <div class="faculty-name">
                        BCA
                    </div>


                    <div class="faculty-progress">

                        <span
                            style="width: <?php echo $bca_percentage; ?>%;"
                        ></span>

                    </div>


                    <div class="faculty-number">
                        <?php echo $bca; ?>
                    </div>

                </div>



                <!-- BIT -->

                <div class="faculty-row">

                    <div class="faculty-name">
                        BIT
                    </div>


                    <div class="faculty-progress">

                        <span
                            style="width: <?php echo $bit_percentage; ?>%;"
                        ></span>

                    </div>


                    <div class="faculty-number">
                        <?php echo $bit; ?>
                    </div>

                </div>



                <!-- BSC CSIT -->

                <div class="faculty-row">

                    <div class="faculty-name">
                        BSc CSIT
                    </div>


                    <div class="faculty-progress">

                        <span
                            style="width: <?php echo $bsccsit_percentage; ?>%;"
                        ></span>

                    </div>


                    <div class="faculty-number">
                        <?php echo $bsccsit; ?>
                    </div>

                </div>


            </div>



            <!-- TOTAL -->

            <div class="total-box">

                <span>
                    Total registered students
                </span>


                <strong>
                    <?php echo $total_students; ?>
                </strong>

            </div>


        </div>



        <!-- =================================
             SYSTEM OVERVIEW
        ================================== -->

        <div class="panel system-panel">


            <h2 class="system-title">
                System Overview
            </h2>


            <p class="system-subtitle">
                Current student management status
            </p>


            <div class="system-row">

                <span>
                    Total students
                </span>

                <strong>
                    <?php echo $total_students; ?>
                </strong>

            </div>


            <div class="system-row">

                <span>
                    BCA students
                </span>

                <strong>
                    <?php echo $bca; ?>
                </strong>

            </div>


            <div class="system-row">

                <span>
                    BIT students
                </span>

                <strong>
                    <?php echo $bit; ?>
                </strong>

            </div>


            <div class="system-row">

                <span>
                    BSc CSIT students
                </span>

                <strong>
                    <?php echo $bsccsit; ?>
                </strong>

            </div>


        </div>


    </section>


</main>



<!-- =========================================
     JAVASCRIPT
========================================= -->

<script>

    const menuButton =
        document.getElementById("menuButton");

    const sidebar =
        document.getElementById("sidebar");

    const overlay =
        document.getElementById("sidebarOverlay");


    // =========================================
    // OPEN SIDEBAR
    // =========================================

    function openSidebar() {

        sidebar.classList.add("open");

        overlay.classList.add("show");

        menuButton.setAttribute(
            "aria-expanded",
            "true"
        );

    }


    // =========================================
    // CLOSE SIDEBAR
    // =========================================

    function closeSidebar() {

        sidebar.classList.remove("open");

        overlay.classList.remove("show");

        menuButton.setAttribute(
            "aria-expanded",
            "false"
        );

    }


    // =========================================
    // TOGGLE SIDEBAR
    // =========================================

    function toggleSidebar() {

        if (
            sidebar.classList.contains("open")
        ) {

            closeSidebar();

        } else {

            openSidebar();

        }

    }


    // =========================================
    // MENU BUTTON
    // =========================================

    menuButton.addEventListener(
        "click",
        toggleSidebar
    );


    // =========================================
    // CLICK OUTSIDE
    // =========================================

    overlay.addEventListener(
        "click",
        closeSidebar
    );


    // =========================================
    // ESC KEY
    // =========================================

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeSidebar();

            }

        }
    );


    // =========================================
    // CLOSE SIDEBAR AFTER NAVIGATION
    // =========================================

    document
        .querySelectorAll(".sidebar-link")
        .forEach(function (link) {

            link.addEventListener(
                "click",
                function () {

                    closeSidebar();

                }
            );

        });


    // =========================================
    // PREVENT SIDEBAR CLICK FROM CLOSING
    // =========================================

    sidebar.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

        }
    );

</script>


</body>

</html>