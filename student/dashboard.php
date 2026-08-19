<?php

session_start();

require_once "../config/database.php";


// ==================================================
// CHECK STUDENT LOGIN
// ==================================================

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
) {

    header("Location: ../login.php");
    exit();

}


// ==================================================
// GET STUDENT RECORD
// ==================================================

$user_id = intval($_SESSION["user_id"]);


$sql = "
    SELECT
        students.*
    FROM students
    INNER JOIN users
        ON users.student_id = students.id
    WHERE users.id = ?
    AND users.role = 'student'
    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die("Database error.");

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result = $stmt->get_result();


$student = $result->fetch_assoc();


$stmt->close();


// ==================================================
// STUDENT NOT FOUND
// ==================================================

if (!$student) {

    die("Student record not found.");

}


// ==================================================
// PROFILE STATUS
// ==================================================

$profile_completed =
    intval($student["profile_completed"] ?? 0);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Student Dashboard</title>

<link
    rel="stylesheet"
    href="student.css"
>

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
        class="sidebar-link active"
    >
        Dashboard
    </a>


    <a
        href="view_profile.php"
        class="sidebar-link"
    >
        My Profile
    </a>


    <a
        href="id_card.php"
        class="sidebar-link"
    >
        ID Card
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
    id="overlay"
></div>



<!-- ==================================================
     MAIN
================================================== -->

<main class="container">


    <!-- ==================================================
         WELCOME
    ================================================== -->

    <div class="welcome">


        <h2>

            Welcome,

            <?php

            echo htmlspecialchars(
                $student["name"] ?? "-",
                ENT_QUOTES,
                "UTF-8"
            );

            ?>

        </h2>


        <p>

            Manage your student profile
            and access your student services.

        </p>


        <?php if (!empty($student["faculty"])): ?>


            <span class="faculty-badge">

                <?php

                echo htmlspecialchars(
                    $student["faculty"],
                    ENT_QUOTES,
                    "UTF-8"
                );

                ?>

            </span>


        <?php endif; ?>


    </div>



    <!-- ==================================================
         SERVICES
    ================================================== -->

    <div class="services">


        <!-- ==================================================
             MY PROFILE
        ================================================== -->

        <div class="service">


            <div class="icon">
                👤
            </div>


            <h3>
                My Profile
            </h3>


            <p>
                View your personal and academic information.
            </p>


            <a
                href="view_profile.php"
                class="service-btn"
            >
                View Profile
            </a>


        </div>



        <!-- ==================================================
             STUDENT ID CARD
        ================================================== -->

        <div class="service">


            <div class="icon">
                🪪
            </div>


            <h3>
                Student ID Card
            </h3>


            <p>
                View and print your student ID card.
            </p>


            <a
                href="id_card.php"
                class="service-btn"
            >
                View ID Card
            </a>


        </div>


    </div>



    <!-- ==================================================
         PROFILE STATUS
    ================================================== -->

    <div class="status">


        <h3>
            Profile Status
        </h3>



        <?php if ($profile_completed === 1): ?>


            <!-- ==================================================
                 COMPLETED
            ================================================== -->

            <div class="complete">

                ✓ Your profile is complete

            </div>


            <div class="progress">

                <div
                    class="progress-bar"
                    style="width:100%;"
                ></div>

            </div>


            <p>

                All required information has been completed.

            </p>


        <?php else: ?>


            <!-- ==================================================
                 INCOMPLETE
            ================================================== -->

            <div class="incomplete">

                ⚠ Your profile is incomplete

            </div>


            <div class="progress">

                <div
                    class="progress-bar"
                    style="width:35%;"
                ></div>

            </div>


            <p>

                Please complete your profile information.

            </p>


            <a
                href="complete_profile.php"
                class="service-btn"
            >
                Complete Profile
            </a>


        <?php endif; ?>


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


const overlay =
    document.getElementById("overlay");



// ==================================================
// OPEN MENU
// ==================================================

function openMenu() {

    sidebar.classList.add("open");

    overlay.classList.add("show");

    menuButton.setAttribute(
        "aria-expanded",
        "true"
    );

}



// ==================================================
// CLOSE MENU
// ==================================================

function closeMenu() {

    sidebar.classList.remove("open");

    overlay.classList.remove("show");

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

            closeMenu();

        } else {

            openMenu();

        }

    }
);



// ==================================================
// OVERLAY
// ==================================================

overlay.addEventListener(
    "click",
    closeMenu
);



// ==================================================
// ESC KEY
// ==================================================

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