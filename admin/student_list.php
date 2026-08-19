<?php

session_start();

require_once "../config/database.php";


// ==================================================
// ADMIN LOGIN CHECK
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
// GET STUDENTS
// ==================================================

$sql = "
    SELECT
        id,
        name,
        gender,
        faculty,
        dob,
        address,
        phone,
        email
    FROM students
    ORDER BY id ASC
";


$result = $conn->query($sql);


if (!$result) {

    die("Unable to load student records.");

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Student List</title>


<style>

/* ==================================================
   RESET
================================================== */

* {

    box-sizing: border-box;

}


html,
body {

    margin: 0;

    padding: 0;

}


body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f6f9;

    color: #172033;

    min-height: 100vh;

}


/* ==================================================
   TOP BAR
   SAME AS MANAGE STUDENTS
================================================== */

.topbar {

    position: fixed;

    top: 0;
    left: 0;
    right: 0;

    height: 68px;

    background: #ffffff;

    border-bottom:
        1px solid #e4e8ef;

    display: flex;

    align-items: center;

    padding: 0 28px;

    z-index: 1000;

}


/* ==================================================
   MENU BUTTON
   SAME AS MANAGE STUDENTS
================================================== */

.menu-button {

    width: 40px;

    height: 40px;

    border:
        1px solid #dce2ea;

    background: #ffffff;

    border-radius: 8px;

    cursor: pointer;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    gap: 4px;

    flex-shrink: 0;

}


.menu-button:hover {

    background: #f5f7fa;

}


.menu-button span {

    width: 18px;

    height: 2px;

    background: #29458a;

    border-radius: 2px;

}


/* ==================================================
   BRAND
================================================== */

.brand {

    margin-left: 14px;

    font-size: 18px;

    font-weight: 700;

    color: #29458a;

    white-space: nowrap;

}


/* ==================================================
   SIDEBAR
   SAME AS MANAGE STUDENTS
================================================== */

.sidebar {

    position: fixed;

    top: 68px;

    left: 0;

    width: 260px;

    height:
        calc(100vh - 68px);

    background: #ffffff;

    border-right:
        1px solid #e3e7ed;

    box-shadow:
        4px 0 18px rgba(
            15,
            23,
            42,
            0.08
        );

    z-index: 1100;

    padding:
        22px 14px;

    transform:
        translateX(-100%);

    transition:
        transform 0.25s ease;

    display: flex;

    flex-direction: column;

}


.sidebar.open {

    transform:
        translateX(0);

}


/* ==================================================
   SIDEBAR LINKS
================================================== */

.sidebar-link {

    display: block;

    padding:
        13px 15px;

    margin-bottom: 5px;

    border-radius: 7px;

    color: #374151;

    text-decoration: none;

    font-size: 14px;

    transition:
        background 0.2s ease,
        color 0.2s ease;

}


.sidebar-link:hover {

    background: #eef3fb;

    color: #29458a;

}


.sidebar-link.active {

    background: #e8eef9;

    color: #29458a;

    font-weight: 600;

}


/* ==================================================
   SIDEBAR LOGOUT
================================================== */

.sidebar-bottom {

    margin-top: auto;

    padding-top: 15px;

    border-top:
        1px solid #e5e7eb;

}


.sidebar-logout {

    display: block;

    padding:
        13px 15px;

    color: #b42318;

    text-decoration: none;

    font-size: 14px;

    border-radius: 7px;

}


.sidebar-logout:hover {

    background: #fff1f0;

}


/* ==================================================
   OVERLAY
================================================== */

.sidebar-overlay {

    position: fixed;

    inset: 0;

    background:
        rgba(
            15,
            23,
            42,
            0.25
        );

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


/* ==================================================
   MAIN CONTAINER
   SAME AS MANAGE STUDENTS
================================================== */

.container {

    width: 100%;

    max-width: 1450px;

    margin: 0 auto;

    padding:
        100px 40px 50px;

}


/* ==================================================
   TABLE CARD
================================================== */

.table-card {

    width: 100%;

    background: #ffffff;

    border:
        1px solid #dce1e8;

    border-radius: 10px;

    overflow: hidden;

    box-shadow:
        0 5px 18px rgba(
            15,
            23,
            42,
            0.04
        );

}


/* ==================================================
   TABLE HEADER
================================================== */

.table-header {

    height: 52px;

    padding:
        0 15px;

    border-bottom:
        1px solid #e0e5eb;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.table-header h2 {

    margin: 0;

    font-size: 14px;

    font-weight: 600;

    color: #26364d;

}


.table-header span {

    font-size: 12px;

    color: #8a96a6;

}


/* ==================================================
   TABLE WRAPPER
================================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

    -webkit-overflow-scrolling: touch;

}


/* ==================================================
   TABLE
   SAME FONT SIZE AS MANAGE STUDENTS
================================================== */

table {

    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

}


th {

    background: #f5f7fa;

    color: #4b5563;

    font-size: 12px;

    font-weight: 600;

    text-align: left;

    padding: 15px;

    border-bottom:
        1px solid #e0e5eb;

    white-space: nowrap;

}


td {

    padding: 15px;

    border-bottom:
        1px solid #edf0f3;

    font-size: 13px;

    color: #374151;

    vertical-align: middle;

}


tbody tr:last-child td {

    border-bottom: none;

}


tbody tr:hover {

    background: #fafbfc;

}


/* ==================================================
   STUDENT NAME
   SAME AS MANAGE STUDENTS
================================================== */

.student-name {

    font-weight: 600;

    color: #26364d;

}


/* ==================================================
   FACULTY
   SAME AS MANAGE STUDENTS
================================================== */

.faculty {

    display: inline-block;

    padding:
        5px 9px;

    background: #eef3fb;

    color: #29458a;

    border-radius: 5px;

    font-size: 11px;

    font-weight: 600;

}


/* ==================================================
   EMAIL
================================================== */

.email {

    word-break: break-word;

}


/* ==================================================
   EMPTY
   SAME AS MANAGE STUDENTS
================================================== */

.no-students {

    padding:
        60px 20px;

    text-align: center;

    color: #7b8796;

    font-size: 14px;

}


/* ==================================================
   PRINT AREA
   OUTSIDE TABLE / BOTTOM RIGHT
================================================== */

.print-area {

    width: 100%;

    margin-top: 18px;

    display: flex;

    justify-content: flex-end;

    align-items: center;

}


/* ==================================================
   PRINT BUTTON
================================================== */

.print-button {

    height: 42px;

    padding:
        0 17px;

    border:
        1px solid #29458a;

    border-radius: 7px;

    background: #29458a;

    color: #ffffff;

    font-size: 13px;

    cursor: pointer;

    white-space: nowrap;

    transition:
        background 0.2s ease,
        border-color 0.2s ease;

}


.print-button:hover {

    background: #20386f;

    border-color: #20386f;

}


/* ==================================================
   TABLET
================================================== */

@media (max-width: 900px) {

    .container {

        padding:
            95px 24px 45px;

    }

}


/* ==================================================
   MOBILE
================================================== */

@media (max-width: 700px) {

    .topbar {

        height: 62px;

        padding:
            0 15px;

    }


    .brand {

        margin-left: 12px;

        font-size: 15px;

    }


    .sidebar {

        top: 62px;

        width:
            min(
                270px,
                82vw
            );

        height:
            calc(100vh - 62px);

    }


    .container {

        padding:
            84px 15px 35px;

    }


    .table-header {

        height: auto;

        min-height: 52px;

        padding:
            13px 12px;

    }


    .table-header h2 {

        font-size: 14px;

    }


    .table-header span {

        font-size: 11px;

    }


    th,
    td {

        padding: 12px;

    }


    table {

        min-width: 900px;

    }


    .print-area {

        margin-top: 14px;

    }


    .print-button {

        width: 100%;

        height: 40px;

    }

}


/* ==================================================
   VERY SMALL MOBILE
================================================== */

@media (max-width: 400px) {

    .topbar {

        padding:
            0 12px;

    }


    .brand {

        font-size: 14px;

    }


    .container {

        padding-left: 11px;

        padding-right: 11px;

    }


    .menu-button {

        width: 38px;

        height: 38px;

    }

}


/* ==================================================
   PRINT / A4
================================================== */

@media print {

    @page {

        size: A4 portrait;

        margin: 10mm;

    }


    html,
    body {

        width: 100%;

        margin: 0;

        padding: 0;

        background: #ffffff;

    }


    .topbar,
    .sidebar,
    .sidebar-overlay,
    .print-area {

        display: none !important;

    }


    .container {

        width: 100% !important;

        max-width: none !important;

        margin: 0 !important;

        padding: 0 !important;

    }


    .table-card {

        width: 100% !important;

        border: none !important;

        border-radius: 0 !important;

        box-shadow: none !important;

        overflow: visible !important;

    }


    .table-header {

        height: auto !important;

        padding:
            0 0 10px 0 !important;

        border: none !important;

    }


    .table-header h2 {

        font-size: 14px !important;

        color: #111111 !important;

    }


    .table-header span {

        font-size: 11px !important;

        color: #333333 !important;

    }


    .table-wrapper {

        overflow: visible !important;

    }


    table {

        width: 100% !important;

        min-width: 0 !important;

        table-layout: fixed !important;

        border-collapse: collapse !important;

    }


    th {

        background: #f1f1f1 !important;

        color: #111111 !important;

        font-size: 9px !important;

        padding:
            6px 4px !important;

        border:
            1px solid #cccccc !important;

        white-space: normal !important;

    }


    td {

        color: #111111 !important;

        font-size: 9px !important;

        padding:
            6px 4px !important;

        border:
            1px solid #cccccc !important;

        word-wrap: break-word !important;

        overflow-wrap: break-word !important;

        white-space: normal !important;

    }


    .student-name {

        color: #111111 !important;

    }


    .faculty {

        background: none !important;

        color: #111111 !important;

        padding: 0 !important;

        font-size: 9px !important;

    }

}

</style>

</head>


<body>


<!-- ==================================================
     TOP BAR
================================================== -->

<header class="topbar">


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


    <div class="brand">

        Student Management System

    </div>


</header>



<!-- ==================================================
     SIDEBAR
================================================== -->

<aside
    class="sidebar"
    id="sidebar"
>


    <a
        href="dashboard.php"
        class="sidebar-link"
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
        class="sidebar-link active"
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



<!-- ==================================================
     SIDEBAR OVERLAY
================================================== -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>



<!-- ==================================================
     MAIN
================================================== -->

<main class="container">


    <!-- ==================================================
         TABLE CARD
    ================================================== -->

    <section class="table-card">


        <div class="table-header">


            <h2>

                Registered Students

            </h2>


            <span>

                <?php echo $result->num_rows; ?>

                students

            </span>


        </div>



        <div class="table-wrapper">


            <?php if ($result->num_rows > 0): ?>


                <table>


                    <thead>

                        <tr>

                            <th>
                                Name
                            </th>

                            <th>
                                Faculty
                            </th>

                            <th>
                                Gender
                            </th>

                            <th>
                                Date of Birth
                            </th>

                            <th>
                                Address
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Email
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($student = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- NAME -->

                                <td>

                                    <div class="student-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $student["name"] ?? "-",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </div>

                                </td>



                                <!-- FACULTY -->

                                <td>

                                    <span class="faculty">

                                        <?php

                                        echo htmlspecialchars(
                                            $student["faculty"] ?? "-",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </span>

                                </td>



                                <!-- GENDER -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student["gender"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>



                                <!-- DATE OF BIRTH -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student["dob"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>



                                <!-- ADDRESS -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student["address"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>



                                <!-- PHONE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student["phone"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>



                                <!-- EMAIL -->

                                <td class="email">

                                    <?php

                                    echo htmlspecialchars(
                                        $student["email"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="no-students">

                    No students found.

                </div>


            <?php endif; ?>


        </div>


    </section>



    <!-- ==================================================
         PRINT BUTTON
         OUTSIDE TABLE / BOTTOM RIGHT
    ================================================== -->

    <div class="print-area">


        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >

            Print Student List

        </button>


    </div>


</main>



<!-- ==================================================
     JAVASCRIPT
================================================== -->

<script>

const menuButton =
    document.getElementById("menuButton");

const sidebar =
    document.getElementById("sidebar");

const sidebarOverlay =
    document.getElementById("sidebarOverlay");


// ==================================================
// OPEN SIDEBAR
// ==================================================

function openSidebar() {

    sidebar.classList.add("open");

    sidebarOverlay.classList.add("show");

    menuButton.setAttribute(
        "aria-expanded",
        "true"
    );

}


// ==================================================
// CLOSE SIDEBAR
// ==================================================

function closeSidebar() {

    sidebar.classList.remove("open");

    sidebarOverlay.classList.remove("show");

    menuButton.setAttribute(
        "aria-expanded",
        "false"
    );

}


// ==================================================
// MENU BUTTON
// ==================================================

menuButton.addEventListener(
    "click",
    function () {

        if (
            sidebar.classList.contains("open")
        ) {

            closeSidebar();

        } else {

            openSidebar();

        }

    }
);


// ==================================================
// OVERLAY
// ==================================================

sidebarOverlay.addEventListener(
    "click",
    closeSidebar
);


// ==================================================
// ESC KEY
// ==================================================

document.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Escape") {

            closeSidebar();

        }

    }
);

</script>


</body>

</html>