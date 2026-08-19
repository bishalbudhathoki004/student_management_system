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
// SEARCH
// ==========================================

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


// ==========================================
// GET STUDENTS
// ==========================================

if ($search !== "") {

    $search_value = "%" . $search . "%";

    $sql = "
        SELECT
            students.id,
            students.name,
            students.gender,
            students.faculty,
            students.phone,
            students.email,
            users.username

        FROM students

        LEFT JOIN users
            ON users.student_id = students.id
            AND users.role = 'student'

        WHERE
            students.name LIKE ?
            OR students.faculty LIKE ?
            OR students.phone LIKE ?
            OR students.email LIKE ?
            OR users.username LIKE ?

        ORDER BY students.name ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error.");
    }

    $stmt->bind_param(
        "sssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "
        SELECT
            students.id,
            students.name,
            students.gender,
            students.faculty,
            students.phone,
            students.email,
            users.username

        FROM students

        LEFT JOIN users
            ON users.student_id = students.id
            AND users.role = 'student'

        ORDER BY students.name ASC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        die("Database error.");
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

    <title>Manage Students</title>


    <style>

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


        /* ==========================================
           TOP BAR
        ========================================== */

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


        /* ==========================================
           MENU BUTTON
        ========================================== */

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


        /* ==========================================
           BRAND
        ========================================== */

        .brand {

            margin-left: 14px;

            font-size: 18px;

            font-weight: 700;

            color: #29458a;

            white-space: nowrap;

        }


        /* ==========================================
           SIDEBAR
        ========================================== */

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


        /* ==========================================
           SIDEBAR LINKS
        ========================================== */

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


        /* ==========================================
           SIDEBAR LOGOUT
        ========================================== */

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


        /* ==========================================
           OVERLAY
        ========================================== */

        .sidebar-overlay {

            position: fixed;

            inset: 0;

            background:
                rgba(15, 23, 42, 0.25);

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


        /* ==========================================
           MAIN
        ========================================== */

        .container {

            width: 100%;

            max-width: 1450px;

            margin: 0 auto;

            padding:
                100px 40px 50px;

        }


        /* ==========================================
           SEARCH
        ========================================== */

        .search-area {

            width: 100%;

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 8px;

            margin-bottom: 22px;

        }


        .search-form {

            display: flex;

            align-items: center;

            gap: 7px;

            width: 100%;

            max-width: 430px;

        }


        .search-input {

            flex: 1;

            height: 42px;

            padding: 0 13px;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            background: #ffffff;

            color: #172033;

            font-size: 14px;

            outline: none;

        }


        .search-input:focus {

            border-color: #29458a;

            box-shadow:
                0 0 0 3px rgba(41, 69, 138, 0.08);

        }


        .search-button {

            height: 42px;

            padding: 0 17px;

            border: 1px solid #29458a;

            border-radius: 7px;

            background: #29458a;

            color: #ffffff;

            font-size: 13px;

            cursor: pointer;

        }


        .search-button:hover {
            background: #20386f;
        }


        .clear-button {

            height: 42px;

            padding: 0 14px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            background: #ffffff;

            color: #374151;

            text-decoration: none;

            font-size: 13px;

        }


        .clear-button:hover {
            background: #f5f7fa;
        }


        /* ==========================================
           TABLE
        ========================================== */

        .table-card {

            width: 100%;

            background: #ffffff;

            border: 1px solid #dce1e8;

            border-radius: 10px;

            overflow: hidden;

            box-shadow:
                0 5px 18px rgba(15, 23, 42, 0.04);

        }


        .table-wrapper {

            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;

        }


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

            border-bottom: 1px solid #e0e5eb;

            white-space: nowrap;

        }


        td {

            padding: 15px;

            border-bottom: 1px solid #edf0f3;

            font-size: 13px;

            vertical-align: middle;

        }


        tbody tr:last-child td {
            border-bottom: none;
        }


        tbody tr:hover {
            background: #fafbfc;
        }


        /* ==========================================
           STUDENT
        ========================================== */

        .student-name {

            font-weight: 600;

            color: #26364d;

            margin-bottom: 4px;

        }


        .username {

            color: #8a96a6;

            font-size: 11px;

        }


        /* ==========================================
           FACULTY
        ========================================== */

        .faculty {

            display: inline-block;

            padding: 5px 9px;

            background: #eef3fb;

            color: #29458a;

            border-radius: 5px;

            font-size: 11px;

            font-weight: 600;

        }


        /* ==========================================
           ACTIONS
        ========================================== */

        .actions {

            display: flex;

            align-items: center;

            gap: 6px;

            white-space: nowrap;

        }


        .action-btn {

            min-height: 34px;

            padding: 0 10px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border: 1px solid #cbd5e1;

            border-radius: 6px;

            background: #ffffff;

            color: #374151;

            text-decoration: none;

            font-size: 11px;

            cursor: pointer;

            transition:
                background 0.2s ease,
                border 0.2s ease;

        }


        .action-btn:hover {
            background: #f5f7fa;
        }


        .reset-btn {

            border-color: #dfd3a9;

            color: #806d32;

        }


        .reset-btn:hover {
            background: #fffaf0;
        }


        .delete-btn {

            border-color: #e2baba;

            color: #a04444;

        }


        .delete-btn:hover {
            background: #fff5f5;
        }


        /* ==========================================
           MESSAGES
        ========================================== */

        .message {

            margin-bottom: 18px;

            padding: 13px 16px;

            border-radius: 7px;

            font-size: 13px;

            background: #ffffff;

            border: 1px solid #dce3ea;

        }


        .success-message {

            color: #36704c;

            border-color: #c8e4d1;

            background: #f3faf5;

        }


        .error-message {

            color: #a04444;

            border-color: #efd0d0;

            background: #fff7f7;

        }


        /* ==========================================
           PASSWORD RESULT
        ========================================== */

        .password-result {

            margin-bottom: 18px;

            padding: 17px;

            background: #ffffff;

            border: 1px solid #dce3ea;

            border-radius: 8px;

            box-shadow:
                0 3px 10px rgba(15, 23, 42, 0.03);

            font-size: 13px;

        }


        .password-result p {
            margin: 6px 0;
        }


        .password-value {

            font-weight: 600;

            color: #29458a;

        }


        /* ==========================================
           EMPTY
        ========================================== */

        .no-students {

            padding: 60px 20px;

            text-align: center;

            color: #7b8796;

            font-size: 14px;

        }


        /* ==========================================
           MOBILE
        ========================================== */

        @media (max-width: 900px) {

            .container {

                padding:
                    95px 24px 45px;

            }


            .search-area {

                justify-content: flex-start;

            }


            .search-form {

                max-width: 100%;

            }

        }


        @media (max-width: 700px) {

            .topbar {

                height: 62px;

                padding: 0 15px;

            }


            .brand {

                margin-left: 12px;

                font-size: 15px;

            }


            .sidebar {

                top: 62px;

                width: min(270px, 82vw);

                height: calc(100vh - 62px);

            }


            .container {

                padding:
                    84px 15px 35px;

            }


            .search-area {

                flex-direction: column;

                align-items: stretch;

                gap: 8px;

            }


            .search-form {

                width: 100%;

                max-width: none;

            }


            .search-input {

                height: 40px;

                font-size: 13px;

            }


            .search-button {
                height: 40px;
            }


            .clear-button {

                width: 100%;

                height: 39px;

            }


            th,
            td {
                padding: 12px;
            }


            table {
                min-width: 900px;
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

                padding-left: 11px;

                padding-right: 11px;

            }


            .menu-button {

                width: 38px;

                height: 38px;

            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     TOP BAR
========================================== -->

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



<!-- ==========================================
     SIDEBAR
========================================== -->

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
        class="sidebar-link active"
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



<!-- ==========================================
     OVERLAY
========================================== -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>



<!-- ==========================================
     MAIN
========================================== -->

<main class="container">


    <!-- ==========================================
         RESET SUCCESS
    ========================================== -->

    <?php if (isset($_SESSION["reset_success"])): ?>

        <div class="message success-message">

            <?php

            echo htmlspecialchars(
                $_SESSION["reset_success"],
                ENT_QUOTES,
                "UTF-8"
            );

            ?>

        </div>

        <?php unset($_SESSION["reset_success"]); ?>

    <?php endif; ?>



    <!-- ==========================================
         RESET ERROR
    ========================================== -->

    <?php if (isset($_SESSION["reset_error"])): ?>

        <div class="message error-message">

            <?php

            echo htmlspecialchars(
                $_SESSION["reset_error"],
                ENT_QUOTES,
                "UTF-8"
            );

            ?>

        </div>

        <?php unset($_SESSION["reset_error"]); ?>

    <?php endif; ?>



    <!-- ==========================================
         PASSWORD RESULT
    ========================================== -->

    <?php if (isset($_SESSION["password_reset"])): ?>

        <div class="password-result">

            <p>
                Password reset successfully.
            </p>


            <p>

                Username:

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["password_reset"]["username"],
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </strong>

            </p>


            <p>

                New Password:

                <span class="password-value">

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["password_reset"]["password"],
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </span>

            </p>

        </div>

        <?php unset($_SESSION["password_reset"]); ?>

    <?php endif; ?>



    <!-- ==========================================
         SEARCH
    ========================================== -->

    <div class="search-area">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search student..."
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
            >


            <button
                type="submit"
                class="search-button"
            >
                Search
            </button>

        </form>


        <?php if ($search !== ""): ?>

            <a
                href="manage_students.php"
                class="clear-button"
            >
                Clear
            </a>

        <?php endif; ?>

    </div>



    <!-- ==========================================
         TABLE
    ========================================== -->

    <div class="table-card">

        <div class="table-wrapper">


            <?php if ($result && $result->num_rows > 0): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Student
                            </th>

                            <th>
                                Gender
                            </th>

                            <th>
                                Faculty
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($student = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- STUDENT -->

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


                                    <?php if (!empty($student["username"])): ?>

                                        <div class="username">

                                            <?php

                                            echo htmlspecialchars(
                                                $student["username"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </div>

                                    <?php endif; ?>

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

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student["email"] ?? "-",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </td>



                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">


                                        <!-- VIEW -->

                                        <a
                                            href="view_student.php?id=<?php echo (int) $student["id"]; ?>"
                                            class="action-btn"
                                        >
                                            View
                                        </a>



                                        <!-- EDIT -->

                                        <a
                                            href="edit_student.php?id=<?php echo (int) $student["id"]; ?>"
                                            class="action-btn"
                                        >
                                            Edit
                                        </a>



                                        <!-- RESET PASSWORD -->

                                        <?php if (!empty($student["username"])): ?>

                                            <a
                                                href="reset_password.php?id=<?php echo (int) $student["id"]; ?>"
                                                class="action-btn reset-btn"
                                                onclick="return confirm('Reset password for <?php echo htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8"); ?>?');"
                                            >
                                                Reset Password
                                            </a>

                                        <?php else: ?>

                                            <span
                                                class="action-btn"
                                                style="color:#999; cursor:not-allowed;"
                                            >
                                                No Account
                                            </span>

                                        <?php endif; ?>



                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            action="delete_student.php"
                                            style="display:inline;"
                                            onsubmit="return confirm('Delete <?php echo htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8"); ?>?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int) $student["id"]; ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="no-students">

                    <?php if ($search !== ""): ?>

                        No students found for
                        "<strong><?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?></strong>".

                    <?php else: ?>

                        No students found.

                    <?php endif; ?>

                </div>


            <?php endif; ?>


        </div>

    </div>


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

        if (
            sidebar.classList.contains("open")
        ) {

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
        function(event) {

            if (event.key === "Escape") {
                closeSidebar();
            }

        }
    );


    sidebar
        .querySelectorAll(".sidebar-link, .sidebar-logout")
        .forEach(function(link) {

            link.addEventListener(
                "click",
                closeSidebar
            );

        });

</script>


</body>

</html>