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

$message = "";

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


// ==================================================
// UPDATE STUDENT
// ==================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $gender  = trim($_POST["gender"] ?? "");
    $dob     = trim($_POST["dob"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone   = trim($_POST["phone"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $faculty = trim($_POST["faculty"] ?? "");

    $oldPhoto = $student["photo"] ?? "";

    $photoName = $oldPhoto;


    // ==================================================
    // VALIDATION
    // ==================================================

    if (
        $gender === "" ||
        $dob === "" ||
        $address === "" ||
        $phone === "" ||
        $email === "" ||
        $faculty === ""
    ) {

        $message = "Please fill all fields.";

    } elseif (
        !in_array(
            $gender,
            ["Male", "Female", "Other"],
            true
        )
    ) {

        $message = "Select a valid gender.";

    } elseif (
        !in_array(
            $faculty,
            ["BCA", "BIT", "BSc CSIT"],
            true
        )
    ) {

        $message = "Select a valid faculty.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message = "Enter a valid email.";

    } elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $phone
        )
    ) {

        $message = "Phone must be 10 digits.";

    } else {


        // ==================================================
        // PHOTO UPLOAD
        // ==================================================

        $newPhotoUploaded = false;

        $newPhotoPath = "";


        if (
            isset($_FILES["photo"]) &&
            $_FILES["photo"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["photo"]["error"] !== UPLOAD_ERR_OK
            ) {

                $message =
                    "Could not upload photo.";

            } elseif (
                $_FILES["photo"]["size"] >
                2 * 1024 * 1024
            ) {

                $message =
                    "Photo size must be less than 2 MB.";

            } else {


                $allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];


                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                if (!$finfo) {

                    $message =
                        "Unable to verify photo.";

                } else {


                    $fileType =
                        finfo_file(
                            $finfo,
                            $_FILES["photo"]["tmp_name"]
                        );


                    finfo_close($finfo);


                    if (
                        $fileType === false ||
                        !in_array(
                            $fileType,
                            $allowedTypes,
                            true
                        )
                    ) {

                        $message =
                            "Invalid photo format. Use JPG, PNG or WEBP.";

                    } else {


                        $extensionMap = [

                            "image/jpeg" => "jpg",

                            "image/png" => "png",

                            "image/webp" => "webp"

                        ];


                        $extension =
                            $extensionMap[$fileType];


                        $newPhotoName =
                            "student_" .
                            $id .
                            "_" .
                            time() .
                            "." .
                            $extension;


                        $uploadPath =
                            "../assets/uploads/" .
                            $newPhotoName;


                        if (
                            move_uploaded_file(
                                $_FILES["photo"]["tmp_name"],
                                $uploadPath
                            )
                        ) {

                            $photoName =
                                $newPhotoName;

                            $newPhotoUploaded =
                                true;

                            $newPhotoPath =
                                $uploadPath;

                        } else {

                            $message =
                                "Could not save uploaded photo.";

                        }

                    }

                }

            }

        }


        // ==================================================
        // UPDATE DATABASE
        // ==================================================

        if ($message === "") {


            $sql = "
                UPDATE students
                SET
                    gender = ?,
                    dob = ?,
                    address = ?,
                    phone = ?,
                    email = ?,
                    faculty = ?,
                    photo = ?
                WHERE id = ?
            ";


            $stmt =
                $conn->prepare($sql);


            if (!$stmt) {

                $message =
                    "Database error.";

            } else {


                $stmt->bind_param(
                    "sssssssi",
                    $gender,
                    $dob,
                    $address,
                    $phone,
                    $email,
                    $faculty,
                    $photoName,
                    $id
                );


                if ($stmt->execute()) {

                    $stmt->close();


                    // Delete old photo
                    // only after successful update

                    if (
                        $newPhotoUploaded &&
                        !empty($oldPhoto) &&
                        $oldPhoto !== $photoName
                    ) {

                        $oldPhotoPath =
                            "../assets/uploads/" .
                            $oldPhoto;


                        if (
                            file_exists(
                                $oldPhotoPath
                            )
                        ) {

                            unlink(
                                $oldPhotoPath
                            );

                        }

                    }


                    header(
                        "Location: view_student.php?id=" .
                        $id
                    );

                    exit();


                } else {


                    if (
                        $newPhotoUploaded &&
                        $newPhotoPath !== "" &&
                        file_exists(
                            $newPhotoPath
                        )
                    ) {

                        unlink(
                            $newPhotoPath
                        );

                    }


                    $message =
                        "Unable to update student.";


                    $stmt->close();

                }

            }

        }

    }


    // ==================================================
    // KEEP ENTERED VALUES
    // ==================================================

    $student["gender"] =
        $gender;

    $student["dob"] =
        $dob;

    $student["address"] =
        $address;

    $student["phone"] =
        $phone;

    $student["email"] =
        $email;

    $student["faculty"] =
        $faculty;

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

<title>Edit Student</title>

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
   SAME AS MANAGE STUDENTS
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

    border-bottom:
        1px solid #d9dde5;

}


/* ==================================================
   MENU BUTTON
================================================== */

.menu-button {

    width: 32px;
    height: 32px;

    border:
        1px solid #c7cedb;

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

    border-right:
        1px solid #e0e4eb;

    padding: 25px 11px;

    transform:
        translateX(-100%);

    transition:
        transform 0.25s ease;

    z-index: 999;

    box-shadow:
        4px 0 15px
        rgba(0,0,0,0.05);

}

.sidebar.active {

    transform:
        translateX(0);

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

    border-top:
        1px solid #e3e6ec;

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
        rgba(30,41,59,0.25);

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

    margin:
        0 0 5px;

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
   CARD
================================================== */

.card {

    background: white;

    border:
        1px solid #e0e5ed;

    border-radius: 11px;

    padding: 25px;

    box-shadow:
        0 5px 20px
        rgba(29,43,76,0.05);

}


/* ==================================================
   MESSAGE
================================================== */

.message {

    margin-bottom: 18px;

    padding: 11px 13px;

    border-radius: 7px;

    background: #fff1f1;

    border:
        1px solid #ffd2d2;

    color: #a32323;

    font-size: 12px;

}


/* ==================================================
   PROFILE LAYOUT
================================================== */

.profile-layout {

    display: grid;

    grid-template-columns:
        160px
        1fr;

    gap: 25px;

    align-items: start;

}


/* ==================================================
   PHOTO
================================================== */

.photo-section {

    text-align: center;

}

.photo-preview {

    width: 123px;
    height: 123px;

    margin:
        0 auto 12px;

    border-radius: 10px;

    object-fit: cover;

    border:
        1px solid #d8dee8;

    background: #f1f4f8;

    display: block;

}

.no-photo {

    width: 123px;
    height: 123px;

    margin:
        0 auto 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    border:
        1px dashed #cbd3df;

    background: #f7f8fb;

    color: #8a94a7;

    font-size: 11px;

}

.photo-input {

    width: 123px;

    max-width: 100%;

    font-size: 10px;

    color: #667085;

}


/* ==================================================
   STUDENT NAME
================================================== */

.student-name {

    margin:
        0 0 19px;

    font-size: 22px;

    color: #172033;

}


/* ==================================================
   FORM GRID
================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 14px;

}

.form-group {

    min-width: 0;

}

.form-group label {

    display: block;

    margin-bottom: 6px;

    color: #475467;

    font-size: 11px;

    font-weight: 600;

}

.form-group input,
.form-group select {

    width: 100%;

    height: 38px;

    padding:
        0 11px;

    border:
        1px solid #d8dee8;

    border-radius: 7px;

    background: white;

    color: #172033;

    font-family: inherit;

    font-size: 11px;

    outline: none;

    transition:
        border-color 0.2s,
        box-shadow 0.2s;

}

.form-group input:focus,
.form-group select:focus {

    border-color: #9aaac3;

    box-shadow:
        0 0 0 2px
        rgba(41,69,138,0.07);

}


/* ==================================================
   ACTIONS
================================================== */

.actions {

    margin-top: 22px;

    padding-top: 17px;

    border-top:
        1px solid #e5e8ee;

    display: flex;

    justify-content: flex-end;

    gap: 8px;

}

.btn {

    height: 36px;

    padding:
        0 15px;

    border-radius: 7px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: none;

    font-family: inherit;

    font-size: 11px;

    font-weight: 600;

    cursor: pointer;

    text-decoration: none;

    transition:
        background 0.2s;

}

.btn-primary {

    background: #294b84;

    color: white;

}

.btn-primary:hover {

    background: #203b6c;

}

.btn-secondary {

    background: #f1f3f6;

    color: #445066;

    border:
        1px solid #dfe4ec;

}

.btn-secondary:hover {

    background: #e8ecf2;

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

    .profile-layout {

        grid-template-columns:
            135px
            1fr;

        gap: 20px;

    }

    .photo-preview,
    .no-photo {

        width: 115px;
        height: 115px;

    }

    .photo-input {

        width: 115px;

    }

}


/* ==================================================
   MOBILE
================================================== */

@media (max-width: 600px) {

    .header {

        height: 52px;

        padding:
            0 14px;

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

    .page-header h1 {

        font-size: 21px;

    }

    .page-header p {

        font-size: 11px;

    }

    .card {

        padding: 17px;

        border-radius: 9px;

    }

    .profile-layout {

        display: block;

    }

    .photo-section {

        text-align: left;

        margin-bottom: 20px;

    }

    .photo-preview,
    .no-photo {

        margin-left: 0;

        width: 115px;
        height: 115px;

    }

    .photo-input {

        width: 100%;

        max-width: 250px;

    }

    .student-name {

        font-size: 20px;

        margin-bottom: 17px;

    }

    .form-grid {

        grid-template-columns: 1fr;

        gap: 12px;

    }

    .form-group input,
    .form-group select {

        height: 40px;

    }

    .actions {

        flex-direction: column;

        gap: 8px;

    }

    .btn {

        width: 100%;

    }

}


/* ==================================================
   SMALL MOBILE
================================================== */

@media (max-width: 360px) {

    .main {

        padding-left: 10px;

        padding-right: 10px;

    }

    .card {

        padding: 14px;

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

    </div>


    <div class="card">


        <?php if ($message !== ""): ?>

            <div class="message">

                <?php

                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                );

                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="profile-layout">


                <!-- PHOTO -->

                <div class="photo-section">


                    <?php if (!empty($student["photo"])): ?>

                        <img
                            src="../assets/uploads/<?php echo htmlspecialchars(
                                $student["photo"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            alt="Student Photo"
                            class="photo-preview"
                        >

                    <?php else: ?>

                        <div class="no-photo">
                            No Photo
                        </div>

                    <?php endif; ?>


                    <input
                        type="file"
                        name="photo"
                        class="photo-input"
                        accept="image/jpeg,image/png,image/webp"
                    >

                </div>


                <!-- INFORMATION -->

                <div>


                    <h2 class="student-name">

                        <?php

                        echo htmlspecialchars(
                            $student["name"] ?? "-",
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </h2>


                    <div class="form-grid">


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
                                        ($student["gender"] ?? "") ===
                                        "Male"
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
                                        ($student["gender"] ?? "") ===
                                        "Female"
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
                                        ($student["gender"] ?? "") ===
                                        "Other"
                                    )
                                        ? "selected"
                                        : "";

                                    ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- DOB -->

                        <div class="form-group">

                            <label for="dob">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                id="dob"
                                name="dob"
                                value="<?php

                                echo htmlspecialchars(
                                    $student["dob"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>"
                                required
                            >

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
                                        ($student["faculty"] ?? "") ===
                                        "BCA"
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
                                        ($student["faculty"] ?? "") ===
                                        "BIT"
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
                                        ($student["faculty"] ?? "") ===
                                        "BSc CSIT"
                                    )
                                        ? "selected"
                                        : "";

                                    ?>
                                >
                                    BSc CSIT
                                </option>

                            </select>

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
                                inputmode="numeric"
                                value="<?php

                                echo htmlspecialchars(
                                    $student["phone"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>"
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
                                value="<?php

                                echo htmlspecialchars(
                                    $student["email"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>"
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
                                value="<?php

                                echo htmlspecialchars(
                                    $student["address"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>"
                                required
                            >

                        </div>


                    </div>

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="actions">


                <a
                    href="view_student.php?id=<?php echo (int) $student["id"]; ?>"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>


            </div>


        </form>

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