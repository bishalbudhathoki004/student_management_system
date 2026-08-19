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


$message = "";
$error = "";


// ==========================================
// ADD STUDENT
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $faculty = trim($_POST["faculty"] ?? "");
    $dob = trim($_POST["dob"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");


    // ======================================
    // VALIDATION
    // ======================================

    if (
        $name === "" ||
        $gender === "" ||
        $faculty === "" ||
        $dob === "" ||
        $address === "" ||
        $phone === "" ||
        $email === ""
    ) {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $error = "Phone number must contain 10 digits.";

    } else {

        $conn->begin_transaction();

        try {

            // ==================================
            // INSERT STUDENT
            // ==================================

            $sql = "
                INSERT INTO students
                (
                    name,
                    gender,
                    faculty,
                    dob,
                    address,
                    phone,
                    email,
                    profile_completed
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, 0
                )
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Could not prepare student query.");
            }

            $stmt->bind_param(
                "sssssss",
                $name,
                $gender,
                $faculty,
                $dob,
                $address,
                $phone,
                $email
            );

            if (!$stmt->execute()) {
                throw new Exception("Could not add student.");
            }

            $student_id = $conn->insert_id;

            $stmt->close();


            // ==================================
            // CREATE USERNAME
            // ==================================

            $base_username = strtolower(
                preg_replace(
                    "/[^a-zA-Z0-9]/",
                    "",
                    $name
                )
            );

            if ($base_username === "") {
                $base_username = "student";
            }


            // ==================================
            // GENERATE UNIQUE USERNAME
            // ==================================

            do {

                $username =
                    $base_username .
                    random_int(100, 999);

                $check_sql = "
                    SELECT id
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ";

                $check_stmt = $conn->prepare($check_sql);

                if (!$check_stmt) {
                    throw new Exception("Could not check username.");
                }

                $check_stmt->bind_param(
                    "s",
                    $username
                );

                $check_stmt->execute();

                $check_result =
                    $check_stmt->get_result();

                $username_exists =
                    $check_result->num_rows > 0;

                $check_stmt->close();

            } while ($username_exists);


            // ==================================
            // GENERATE PASSWORD
            // ==================================

            $characters =
                "ABCDEFGHJKLMNPQRSTUVWXYZ" .
                "abcdefghijkmnopqrstuvwxyz" .
                "23456789";

            $plain_password = "";

            for ($i = 0; $i < 8; $i++) {

                $plain_password .=
                    $characters[
                        random_int(
                            0,
                            strlen($characters) - 1
                        )
                    ];

            }


            // ==================================
            // HASH PASSWORD
            // ==================================

            $hashed_password = password_hash(
                $plain_password,
                PASSWORD_DEFAULT
            );


            // ==================================
            // CREATE USER ACCOUNT
            // ==================================

            $user_sql = "
                INSERT INTO users
                (
                    username,
                    password,
                    role,
                    student_id
                )
                VALUES
                (
                    ?, ?, 'student', ?
                )
            ";

            $user_stmt =
                $conn->prepare($user_sql);

            if (!$user_stmt) {
                throw new Exception(
                    "Could not prepare user query."
                );
            }

            $user_stmt->bind_param(
                "ssi",
                $username,
                $hashed_password,
                $student_id
            );

            if (!$user_stmt->execute()) {
                throw new Exception(
                    "Could not create student login."
                );
            }

            $user_id =
                $conn->insert_id;

            $user_stmt->close();


            // ==================================
            // CONNECT STUDENT WITH USER
            // ==================================

            $update_sql = "
                UPDATE students
                SET user_id = ?
                WHERE id = ?
            ";

            $update_stmt =
                $conn->prepare($update_sql);

            if (!$update_stmt) {
                throw new Exception(
                    "Could not connect student account."
                );
            }

            $update_stmt->bind_param(
                "ii",
                $user_id,
                $student_id
            );

            if (!$update_stmt->execute()) {
                throw new Exception(
                    "Could not connect student account."
                );
            }

            $update_stmt->close();


            // ==================================
            // COMMIT
            // ==================================

            $conn->commit();


            // ==================================
            // SAVE CREDENTIALS
            // ==================================

            $_SESSION["new_student"] = [

                "student_id" =>
                    $student_id,

                "name" =>
                    $name,

                "username" =>
                    $username,

                "password" =>
                    $plain_password

            ];


            $message =
                "Student added successfully.";


        } catch (Exception $e) {

            $conn->rollback();

            $error =
                $e->getMessage();
        }
    }
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

    <title>Add Student</title>


    <style>

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f6f9;

            color: #172033;

        }


        /* =========================================
           TOP BAR
        ========================================= */

        .topbar {

            position: fixed;

            top: 0;
            left: 0;
            right: 0;

            height: 68px;

            background: #ffffff;

            border-bottom: 1px solid #e4e8ef;

            display: flex;

            align-items: center;

            padding: 0 28px;

            z-index: 1000;

        }


        .menu-button {

            width: 40px;
            height: 40px;

            border: 1px solid #dce2ea;

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


        .brand {

            margin-left: 14px;

            font-size: 18px;

            font-weight: 700;

            color: #29458a;

            white-space: nowrap;

        }


        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {

            position: fixed;

            top: 68px;
            left: 0;

            width: 260px;

            height: calc(100vh - 68px);

            background: #ffffff;

            border-right: 1px solid #e3e7ed;

            box-shadow:
                4px 0 18px rgba(15, 23, 42, 0.08);

            z-index: 1100;

            padding: 22px 14px;

            transform: translateX(-100%);

            transition:
                transform 0.25s ease;

            display: flex;

            flex-direction: column;

        }


        .sidebar.open {
            transform: translateX(0);
        }


        .sidebar-link {

            display: block;

            padding: 13px 15px;

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


        .sidebar-bottom {

            margin-top: auto;

            padding-top: 15px;

            border-top: 1px solid #e5e7eb;

        }


        .sidebar-logout {

            display: block;

            padding: 13px 15px;

            color: #b42318;

            text-decoration: none;

            font-size: 14px;

            border-radius: 7px;

        }


        .sidebar-logout:hover {

            background: #fff1f0;

        }


        /* =========================================
           OVERLAY
        ========================================= */

        .sidebar-overlay {

            position: fixed;

            inset: 0;

            background: rgba(15, 23, 42, 0.25);

            z-index: 1050;

            opacity: 0;

            visibility: hidden;

            transition:
                opacity 0.25s ease;

        }


        .sidebar-overlay.show {

            opacity: 1;

            visibility: visible;

        }


        /* =========================================
           MAIN
        ========================================= */

        .container {

            width: 100%;

            max-width: 900px;

            margin: 0 auto;

            padding:
                105px 40px 50px;

        }


        /* =========================================
           FORM
        ========================================= */

        .form-card {

            width: 100%;

            background: #ffffff;

            border: 1px solid #dce1e8;

            border-radius: 10px;

            padding: 28px;

            box-shadow:
                0 5px 18px rgba(15, 23, 42, 0.04);

        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        label {

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: 600;

            color: #374151;

        }


        input,
        select {

            width: 100%;

            height: 42px;

            padding: 0 12px;

            border: 1px solid #cbd5e1;

            border-radius: 6px;

            background: #ffffff;

            color: #111827;

            font-size: 14px;

            outline: none;

        }


        input:focus,
        select:focus {

            border-color: #29458a;

            box-shadow:
                0 0 0 3px rgba(41, 69, 138, 0.08);

        }


        .form-buttons {

            grid-column: 1 / -1;

            display: flex;

            gap: 10px;

            margin-top: 4px;

        }


        button,
        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 40px;

            padding: 0 17px;

            border: 1px solid #29458a;

            border-radius: 6px;

            background: #29458a;

            color: #ffffff;

            text-decoration: none;

            font-size: 14px;

            cursor: pointer;

        }


        button:hover,
        .btn:hover {

            background: #20386f;

        }


        .btn-light {

            background: #ffffff;

            color: #374151;

            border-color: #cbd5e1;

        }


        .btn-light:hover {

            background: #f5f7fa;

        }


        /* =========================================
           MESSAGES
        ========================================= */

        .success,
        .error {

            padding: 13px 16px;

            border-radius: 7px;

            margin-bottom: 18px;

            font-size: 14px;

        }


        .success {

            background: #ecfdf3;

            border: 1px solid #a7e3bd;

            color: #166534;

        }


        .error {

            background: #fff4f2;

            border: 1px solid #f1b4aa;

            color: #b42318;

        }


        /* =========================================
           CREDENTIALS
        ========================================= */

        .credentials-box {

            background: #ffffff;

            border: 1px solid #dce1e8;

            border-radius: 10px;

            padding: 24px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 18px rgba(15, 23, 42, 0.04);

        }


        .credentials-box h3 {

            margin: 0 0 20px;

            font-size: 18px;

        }


        .credential-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 12px 0;

            border-bottom: 1px solid #edf0f3;

            font-size: 14px;

        }


        .credential-row:last-of-type {
            border-bottom: none;
        }


        .credential-label {

            color: #64748b;

            font-weight: 600;

        }


        .credential-buttons {

            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            margin-top: 18px;

        }


        .print-button {

            border: 1px solid #29458a;

            background: #29458a;

            color: white;

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

                font-size: 15px;

                margin-left: 12px;

            }


            .sidebar {

                top: 62px;

                height: calc(100vh - 62px);

                width: min(270px, 82vw);

            }


            .container {

                padding:
                    88px 20px 40px;

            }


            .form-card,
            .credentials-box {

                padding: 20px;

            }


            .form-grid {

                grid-template-columns: 1fr;

                gap: 17px;

            }


            .form-buttons {

                grid-column: auto;

                flex-direction: column;

            }


            .form-buttons button,
            .form-buttons .btn {

                width: 100%;

            }


            .credential-row {

                flex-direction: column;

                gap: 5px;

            }


            .credential-buttons {

                flex-direction: column;

            }


            .credential-buttons .btn,
            .credential-buttons button {

                width: 100%;

            }

        }


        @media (max-width: 400px) {

            .topbar {

                padding: 0 12px;

            }


            .brand {

                font-size: 14px;

            }


            .container {

                padding-left: 12px;

                padding-right: 12px;

            }


            .form-card,
            .credentials-box {

                padding: 16px;

            }


            input,
            select {

                font-size: 13px;

            }

        }


        /* =========================================
           PRINT CREDENTIALS
        ========================================= */

        @media print {

            body {

                background: white !important;

                padding: 0 !important;

            }


            .topbar,
            .sidebar,
            .sidebar-overlay,
            .add-form,
            .success,
            .error,
            .credential-buttons {

                display: none !important;

            }


            .container {

                width: 100% !important;

                max-width: 100% !important;

                margin: 0 !important;

                padding: 0 !important;

            }


            .credentials-box {

                display: block !important;

                width: 400px !important;

                margin: 100px auto !important;

                padding: 30px !important;

                border: 2px solid #000 !important;

                border-radius: 8px !important;

                background: white !important;

                box-shadow: none !important;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     TOP BAR
========================================= -->

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



<!-- =========================================
     SIDEBAR
========================================= -->

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
        class="sidebar-link active"
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
     MAIN
========================================= -->

<main class="container">


    <!-- =========================================
         SUCCESS
    ========================================== -->

    <?php if ($message !== ""): ?>

        <div class="success">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>


        <?php if (isset($_SESSION["new_student"])): ?>

            <div class="credentials-box">

                <h3>
                    Student Login Credentials
                </h3>


                <div class="credential-row">

                    <span class="credential-label">
                        Name
                    </span>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["new_student"]["name"]
                        );
                        ?>
                    </span>

                </div>


                <div class="credential-row">

                    <span class="credential-label">
                        Username
                    </span>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["new_student"]["username"]
                        );
                        ?>
                    </span>

                </div>


                <div class="credential-row">

                    <span class="credential-label">
                        Password
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["new_student"]["password"]
                        );
                        ?>
                    </strong>

                </div>


                <div class="credential-buttons">

                    <button
                        type="button"
                        class="print-button"
                        onclick="window.print()"
                    >
                        Print Credentials
                    </button>


                    <a
                        href="add_student.php"
                        class="btn btn-light"
                    >
                        Add Another Student
                    </a>


                    <a
                        href="manage_students.php"
                        class="btn btn-light"
                    >
                        Manage Students
                    </a>

                </div>

            </div>


            <?php
            unset($_SESSION["new_student"]);
            ?>

        <?php endif; ?>

    <?php endif; ?>



    <!-- =========================================
         ERROR
    ========================================== -->

    <?php if ($error !== ""): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>



    <!-- =========================================
         ADD STUDENT FORM
    ========================================== -->

    <?php if ($message === ""): ?>

        <div class="form-card add-form">

            <form method="POST">

                <div class="form-grid">


                    <!-- FULL NAME -->

                    <div class="form-group">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- GENDER -->

                    <div class="form-group">

                        <label for="gender">
                            Gender
                        </label>

                        <select
                            id="gender"
                            name="gender"
                            required
                        >

                            <option value="">
                                Select Gender
                            </option>


                            <option
                                value="Male"
                                <?php
                                echo (
                                    ($_POST["gender"] ?? "") === "Male"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Male
                            </option>


                            <option
                                value="Female"
                                <?php
                                echo (
                                    ($_POST["gender"] ?? "") === "Female"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Female
                            </option>


                            <option
                                value="Other"
                                <?php
                                echo (
                                    ($_POST["gender"] ?? "") === "Other"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>



                    <!-- FACULTY -->

                    <div class="form-group">

                        <label for="faculty">
                            Faculty
                        </label>

                        <select
                            id="faculty"
                            name="faculty"
                            required
                        >

                            <option value="">
                                Select Faculty
                            </option>


                            <option
                                value="BCA"
                                <?php
                                echo (
                                    ($_POST["faculty"] ?? "") === "BCA"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                BCA
                            </option>


                            <option
                                value="BIT"
                                <?php
                                echo (
                                    ($_POST["faculty"] ?? "") === "BIT"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                BIT
                            </option>


                            <option
                                value="BSc CSIT"
                                <?php
                                echo (
                                    ($_POST["faculty"] ?? "") === "BSc CSIT"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                BSc CSIT
                            </option>

                        </select>

                    </div>



                    <!-- DATE OF BIRTH -->

                    <div class="form-group">

                        <label for="dob">
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            id="dob"
                            name="dob"
                            value="<?php echo htmlspecialchars($_POST["dob"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- ADDRESS -->

                    <div class="form-group">

                        <label for="address">
                            Address
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="<?php echo htmlspecialchars($_POST["address"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="phone">
                            Phone
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            maxlength="10"
                            value="<?php echo htmlspecialchars($_POST["phone"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- BUTTONS -->

                    <div class="form-buttons">

                        <button type="submit">
                            Add Student
                        </button>


                        <a
                            href="manage_students.php"
                            class="btn btn-light"
                        >
                            Cancel
                        </a>

                    </div>


                </div>

            </form>

        </div>

    <?php endif; ?>


</main>



<script>

    const menuButton =
        document.getElementById("menuButton");

    const sidebar =
        document.getElementById("sidebar");

    const overlay =
        document.getElementById("sidebarOverlay");


    function openSidebar() {

        sidebar.classList.add("open");

        overlay.classList.add("show");

        menuButton.setAttribute(
            "aria-expanded",
            "true"
        );

    }


    function closeSidebar() {

        sidebar.classList.remove("open");

        overlay.classList.remove("show");

        menuButton.setAttribute(
            "aria-expanded",
            "false"
        );

    }


    function toggleSidebar() {

        if (sidebar.classList.contains("open")) {

            closeSidebar();

        } else {

            openSidebar();

        }

    }


    menuButton.addEventListener(
        "click",
        toggleSidebar
    );


    overlay.addEventListener(
        "click",
        closeSidebar
    );


    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeSidebar();

            }

        }
    );


    sidebar.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

        }
    );


</script>


</body>

</html>