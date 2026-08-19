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
// CHECK STUDENT ID
// ==================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_students.php");
    exit();
}

$id = (int) $_GET["id"];

if ($id <= 0) {
    header("Location: manage_students.php");
    exit();
}

// ==================================================
// GET STUDENT
// ==================================================

$sql = "
    SELECT
        id,
        name,
        gender,
        dob,
        address,
        phone,
        email,
        faculty,
        photo
    FROM students
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    header("Location: manage_students.php");
    exit();
}

$student = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>View Student</title>

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
    min-height: 100%;
}

body {
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f6fb;
    color: #172033;
}


/* ==================================================
   HEADER
   SAME STYLE AS MANAGE STUDENTS
================================================== */

.header {

    height: 54px;

    background: #f1f2f4;

    color: #173b76;

    display: flex;

    align-items: center;

    padding: 0 22px;

    position: fixed;

    top: 0;
    left: 0;
    right: 0;

    z-index: 1000;

    border-bottom: 1px solid #d9dde5;
}


/* ==================================================
   MENU BUTTON
================================================== */

.menu-button {

    width: 32px;
    height: 32px;

    border: 1px solid #c7cedb;

    background: #e9edf3;

    color: #173b76;

    cursor: pointer;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    gap: 4px;

    border-radius: 6px;

    margin-right: 13px;

}

.menu-button:hover {

    background: #e1e6ee;

}

.menu-button span {

    display: block;

    width: 17px;
    height: 2px;

    background: #294b84;

    border-radius: 5px;

}


/* ==================================================
   HEADER TITLE
================================================== */

.header-title {

    font-size: 14px;

    font-weight: 600;

    color: #173b76;

    white-space: nowrap;

}


/* ==================================================
   SIDEBAR
================================================== */

.sidebar {

    position: fixed;

    top: 54px;

    left: 0;

    bottom: 0;

    width: 208px;

    background: white;

    border-right: 1px solid #e0e4eb;

    padding: 25px 11px;

    transform: translateX(-100%);

    transition:
        transform 0.25s ease;

    z-index: 999;

    box-shadow:
        4px 0 15px
        rgba(0, 0, 0, 0.05);

}

.sidebar.active {

    transform: translateX(0);

}


/* ==================================================
   SIDEBAR TITLE
================================================== */

.sidebar-title {

    font-size: 11px;

    font-weight: 600;

    color: #8a94a7;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    margin: 0 12px 12px;

}


/* ==================================================
   SIDEBAR LINKS
================================================== */

.sidebar a {

    display: block;

    padding: 10px 12px;

    margin-bottom: 4px;

    border-radius: 7px;

    color: #354052;

    text-decoration: none;

    font-size: 12px;

    transition:
        background 0.2s,
        color 0.2s;

}

.sidebar a:hover {

    background: #eef2f8;

    color: #294b84;

}

.sidebar a.active {

    background: #e5ebf7;

    color: #294b84;

    font-weight: 600;

}


/* ==================================================
   LOGOUT
================================================== */

.sidebar a:last-child {

    position: absolute;

    left: 11px;
    right: 11px;

    bottom: 20px;

    color: #d32f2f;

    border-top: 1px solid #e3e6ec;

    border-radius: 0;

    padding-top: 20px;

}

.sidebar a:last-child:hover {

    background: transparent;

    color: #b71c1c;

}


/* ==================================================
   OVERLAY
================================================== */

.overlay {

    position: fixed;

    inset: 54px 0 0 0;

    background:
        rgba(30, 41, 59, 0.25);

    opacity: 0;

    visibility: hidden;

    transition:
        opacity 0.25s ease;

    z-index: 998;

}

.overlay.active {

    opacity: 1;

    visibility: visible;

}


/* ==================================================
   MAIN
================================================== */

.main {

    width: 100%;

    max-width: 980px;

    margin: 0 auto;

    padding:
        88px
        25px
        35px;

}


/* ==================================================
   PAGE HEADER
================================================== */

.page-header {

    margin-bottom: 18px;

}

.page-header h1 {

    margin: 0 0 5px;

    font-size: 24px;

    font-weight: 600;

    color: #172033;

}

.page-header p {

    margin: 0;

    color: #6f7b90;

    font-size: 12px;

}


/* ==================================================
   STUDENT CARD
================================================== */

.student-card {

    width: 100%;

    background: white;

    border:
        1px solid #e0e5ed;

    border-radius: 11px;

    padding: 25px;

    box-shadow:
        0 5px 20px
        rgba(29, 43, 76, 0.05);

}


/* ==================================================
   PROFILE TOP
================================================== */

.profile-top {

    display: flex;

    align-items: flex-start;

    gap: 22px;

    padding-bottom: 22px;

    border-bottom:
        1px solid #e5e8ee;

}


/* ==================================================
   PHOTO
================================================== */

.photo-container {

    flex-shrink: 0;

}

.student-photo {

    width: 123px;

    height: 123px;

    object-fit: cover;

    border-radius: 10px;

    border:
        1px solid #d8dee8;

    background: #f3f5f8;

    display: block;

}

.no-photo {

    width: 123px;

    height: 123px;

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    background: #f3f5f8;

    border:
        1px solid #d8dee8;

    border-radius: 10px;

    color: #8993a5;

    font-size: 12px;

}


/* ==================================================
   BASIC INFO
================================================== */

.basic-info {

    min-width: 0;

    flex: 1;

}

.student-name {

    margin: 2px 0 7px;

    font-size: 22px;

    font-weight: 600;

    color: #172033;

    overflow-wrap: anywhere;

}

.student-faculty {

    display: inline-block;

    padding: 4px 8px;

    background: #edf1f7;

    color: #40567f;

    border-radius: 4px;

    font-size: 11px;

    font-weight: 500;

}


/* ==================================================
   DETAILS TITLE
================================================== */

.details-title {

    margin: 21px 0 12px;

    font-size: 14px;

    font-weight: 600;

    color: #20385f;

}


/* ==================================================
   DETAILS
================================================== */

.student-details {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 10px;

}

.detail-item {

    padding: 12px;

    background: #f7f8fb;

    border:
        1px solid #e4e8ef;

    border-radius: 7px;

    min-width: 0;

}

.detail-label {

    display: block;

    margin-bottom: 5px;

    font-size: 9px;

    font-weight: 600;

    color: #8490a4;

    text-transform: uppercase;

    letter-spacing: 0.3px;

}

.detail-value {

    font-size: 12px;

    color: #303b50;

    overflow-wrap: anywhere;

    line-height: 1.5;

}


/* ==================================================
   ACTIONS
================================================== */

.actions {

    display: flex;

    gap: 9px;

    margin-top: 20px;

    padding-top: 18px;

    border-top:
        1px solid #e5e8ee;

}

.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 36px;

    padding: 0 15px;

    background: #294b84;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 11px;

    font-weight: 600;

    transition:
        background 0.2s ease;

}

.btn:hover {

    background: #203b6c;

}


/* ==================================================
   TABLET
================================================== */

@media (max-width: 800px) {

    .main {

        padding:
            82px
            20px
            30px;

    }

}


/* ==================================================
   MOBILE
================================================== */

@media (max-width: 600px) {

    .header {

        height: 52px;

        padding: 0 14px;

    }

    .menu-button {

        width: 31px;
        height: 31px;

        margin-right: 10px;

    }

    .header-title {

        font-size: 13px;

    }

    .sidebar {

        top: 52px;

        width: 210px;

    }

    .overlay {

        inset: 52px 0 0 0;

    }

    .main {

        padding:
            75px
            12px
            25px;

    }

    .page-header {

        margin-bottom: 15px;

    }

    .page-header h1 {

        font-size: 21px;

    }

    .page-header p {

        font-size: 11px;

        line-height: 1.5;

    }

    .student-card {

        padding: 17px;

        border-radius: 9px;

    }

    .profile-top {

        display: block;

        padding-bottom: 18px;

    }

    .photo-container {

        margin-bottom: 15px;

    }

    .student-photo,
    .no-photo {

        width: 115px;

        height: 115px;

    }

    .student-name {

        font-size: 20px;

    }

    .student-details {

        grid-template-columns: 1fr;

        gap: 9px;

    }

    .details-title {

        margin-top: 18px;

    }

    .detail-item {

        padding: 11px;

    }

    .actions {

        width: 100%;

    }

    .btn {

        width: 100%;

    }

}


/* ==================================================
   SMALL MOBILE
================================================== */

@media (max-width: 380px) {

    .main {

        padding-left: 10px;

        padding-right: 10px;

    }

    .student-card {

        padding: 14px;

    }

    .page-header h1 {

        font-size: 20px;

    }

    .student-photo,
    .no-photo {

        width: 105px;

        height: 105px;

    }

}

</style>

</head>


<body>


<!-- ==================================================
     HEADER
================================================== -->

<header class="header">

    <button
        type="button"
        class="menu-button"
        id="menuButton"
        aria-label="Open menu"
        aria-expanded="false"
    >

        <span></span>
        <span></span>
        <span></span>

    </button>

    <div class="header-title">
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

    <div class="sidebar-title">
        Menu
    </div>

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="add_student.php">
        Add Student
    </a>

    <a
        href="manage_students.php"
        class="active"
    >
        Manage Students
    </a>

    <a href="student_list.php">
        Student List
    </a>

    <a href="../logout.php">
        Logout
    </a>

</aside>


<!-- ==================================================
     OVERLAY
================================================== -->

<div
    class="overlay"
    id="overlay"
></div>


<!-- ==================================================
     MAIN
================================================== -->

<main class="main">


    <section class="student-card">


        <!-- PROFILE -->

        <div class="profile-top">


            <div class="photo-container">

                <?php if (!empty($student["photo"])): ?>

                    <img
                        src="../assets/uploads/<?php echo htmlspecialchars(
                            $student["photo"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                        alt="Student Photo"
                        class="student-photo"
                    >

                <?php else: ?>

                    <div class="no-photo">
                        No Photo
                    </div>

                <?php endif; ?>

            </div>


            <div class="basic-info">

                <h2 class="student-name">

                    <?php
                    echo htmlspecialchars(
                        $student["name"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </h2>


                <span class="student-faculty">

                    <?php
                    echo htmlspecialchars(
                        $student["faculty"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </span>

            </div>

        </div>


        <!-- DETAILS -->

        <h3 class="details-title">
            Student Information
        </h3>


        <div class="student-details">


            <div class="detail-item">

                <span class="detail-label">
                    Gender
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["gender"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Date of Birth
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["dob"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Faculty
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["faculty"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Phone
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["phone"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Email
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["email"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Address
                </span>

                <div class="detail-value">

                    <?php
                    echo htmlspecialchars(
                        $student["address"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            </div>


        </div>


        <!-- ONLY EDIT BUTTON -->

        <div class="actions">

            <a
                href="edit_student.php?id=<?php echo (int) $student["id"]; ?>"
                class="btn"
            >
                Edit Student
            </a>

        </div>


    </section>

</main>


<!-- ==================================================
     JAVASCRIPT
================================================== -->

<script>

const menuButton =
    document.getElementById("menuButton");

const sidebar =
    document.getElementById("sidebar");

const overlay =
    document.getElementById("overlay");


function openMenu() {

    sidebar.classList.add("active");

    overlay.classList.add("active");

    menuButton.setAttribute(
        "aria-expanded",
        "true"
    );

}


function closeMenu() {

    sidebar.classList.remove("active");

    overlay.classList.remove("active");

    menuButton.setAttribute(
        "aria-expanded",
        "false"
    );

}


menuButton.addEventListener(
    "click",
    function () {

        if (
            sidebar.classList.contains("active")
        ) {

            closeMenu();

        } else {

            openMenu();

        }

    }
);


overlay.addEventListener(
    "click",
    closeMenu
);


document.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Escape") {

            closeMenu();

        }

    }
);

</script>


</body>

</html>