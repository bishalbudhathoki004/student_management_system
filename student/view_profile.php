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
// GET LOGGED-IN STUDENT
// ==================================================

$user_id = $_SESSION["user_id"];


$sql = "
    SELECT
        students.id AS student_id,
        students.name,
        students.faculty,
        students.gender,
        students.dob,
        students.address,
        students.phone,
        students.email,
        students.photo,
        students.profile_completed,
        users.username

    FROM users

    INNER JOIN students
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
// STUDENT RECORD NOT FOUND
// ==================================================

if (!$student) {

    die("Student record not found.");

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

<title>My Profile</title>

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
        class="sidebar-link"
    >

        Dashboard

    </a>


    <a
        href="view_profile.php"
        class="sidebar-link active"
    >

        My Profile

    </a>


    <?php if (empty($student["profile_completed"])): ?>

        <a
            href="complete_profile.php"
            class="sidebar-link"
        >

            Complete Profile

        </a>

    <?php endif; ?>


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
         PROFILE CARD
    ================================================== -->

    <div class="profile-card">


        <!-- ==================================================
             PROFILE INTRO
        ================================================== -->

        <div class="profile-intro">


            <?php if (!empty($student["photo"])): ?>


                <img
                    src="<?php
                        echo htmlspecialchars(
                            "../assets/uploads/students/" . $student["photo"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    class="profile-photo"
                    alt="Student Photo"
                >


            <?php else: ?>


                <div class="no-photo">

                    No Photo

                </div>


            <?php endif; ?>



            <div class="profile-name">


                <h2>

                    <?php

                    echo htmlspecialchars(
                        $student["name"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </h2>


                <p class="username">

                    Username:

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $student["username"] ?? "-",
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </strong>

                </p>


                <span class="faculty-badge-small">

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



        <!-- ==================================================
             PERSONAL INFORMATION
        ================================================== -->

        <h2 class="section-title">

            Personal Information

        </h2>



        <div class="profile-info">


            <!-- FULL NAME -->

            <div class="profile-row">

                <strong>
                    Full Name
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["name"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- GENDER -->

            <div class="profile-row">

                <strong>
                    Gender
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["gender"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- DATE OF BIRTH -->

            <div class="profile-row">

                <strong>
                    Date of Birth
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["dob"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- FACULTY -->

            <div class="profile-row">

                <strong>
                    Faculty
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["faculty"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- ADDRESS -->

            <div class="profile-row">

                <strong>
                    Address
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["address"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- PHONE -->

            <div class="profile-row">

                <strong>
                    Phone
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["phone"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- EMAIL -->

            <div class="profile-row">

                <strong>
                    Email
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["email"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>



            <!-- USERNAME -->

            <div class="profile-row">

                <strong>
                    Username
                </strong>

                <span>

                    <?php

                    echo htmlspecialchars(
                        $student["username"] ?? "-",
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </div>


        </div>


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