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


$message = "";


// ==================================================
// GET LOGGED-IN STUDENT
// ==================================================

$user_id = intval($_SESSION["user_id"]);


$sql = "
    SELECT
        students.id,
        students.name,
        students.faculty,
        students.gender,
        students.dob,
        students.address,
        students.phone,
        students.email,
        students.photo,
        students.profile_completed

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
// STUDENT NOT FOUND
// ==================================================

if (!$student) {

    die("Student record not found.");

}


// ==================================================
// PROFILE ALREADY COMPLETED
// ==================================================

if (
    intval($student["profile_completed"]) === 1
) {

    header("Location: dashboard.php");
    exit();

}


// ==================================================
// FORM SUBMISSION
// ==================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // ==================================================
    // GET FORM DATA
    // ==================================================

    $dob =
        trim($_POST["dob"] ?? "");

    $gender =
        trim($_POST["gender"] ?? "");

    $address =
        trim($_POST["address"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $email =
        trim($_POST["email"] ?? "");


    // ==================================================
    // BASIC VALIDATION
    // ==================================================

    if (
        $dob === "" ||
        $gender === "" ||
        $address === "" ||
        $phone === "" ||
        $email === ""
    ) {

        $message =
            "Please complete all required fields.";

    }


    elseif (
        !in_array(
            $gender,
            ["Male", "Female", "Other"],
            true
        )
    ) {

        $message =
            "Please select a valid gender.";

    }


    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

    }


    elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $phone
        )
    ) {

        $message =
            "Phone number must contain exactly 10 digits.";

    }


    // ==================================================
    // PHOTO CHECK
    // ==================================================

    elseif (
        !isset($_FILES["photo"]) ||
        $_FILES["photo"]["error"] !== UPLOAD_ERR_OK
    ) {

        $message =
            "Please take a photo or choose a profile photo.";

    }


    else {


        $photo =
            $_FILES["photo"];


        // ==================================================
        // FILE SIZE
        // ==================================================

        if (
            $photo["size"] >
            2 * 1024 * 1024
        ) {

            $message =
                "Photo size must be less than 2MB.";

        }


        else {


            // ==================================================
            // CHECK REAL IMAGE
            // ==================================================

            $image_info =
                getimagesize(
                    $photo["tmp_name"]
                );


            if ($image_info === false) {

                $message =
                    "The selected file is not a valid image.";

            }


            else {


                // ==================================================
                // ALLOWED IMAGE TYPES
                // ==================================================

                $allowed_types = [

                    "image/jpeg",
                    "image/png",
                    "image/webp"

                ];


                if (
                    !in_array(
                        $image_info["mime"],
                        $allowed_types,
                        true
                    )
                ) {

                    $message =
                        "Only JPG, PNG and WEBP images are allowed.";

                }


                else {


                    // ==================================================
                    // UPLOAD DIRECTORY
                    // ==================================================

                    $upload_dir =
                        "../assets/uploads/students/";


                    if (
                        !is_dir(
                            $upload_dir
                        )
                    ) {

                        mkdir(
                            $upload_dir,
                            0777,
                            true
                        );

                    }


                    // ==================================================
                    // PHOTO EXTENSION
                    // ==================================================

                    $extension = "jpg";


                    if (
                        $image_info["mime"] ===
                        "image/png"
                    ) {

                        $extension = "png";

                    }


                    elseif (
                        $image_info["mime"] ===
                        "image/webp"
                    ) {

                        $extension = "webp";

                    }


                    // ==================================================
                    // UNIQUE PHOTO NAME
                    // ==================================================

                    $photo_name =
                        "student_" .
                        $student["id"] .
                        "_" .
                        time() .
                        "." .
                        $extension;


                    $photo_path =
                        $upload_dir .
                        $photo_name;


                    // ==================================================
                    // SAVE PHOTO
                    // ==================================================

                    if (
                        move_uploaded_file(
                            $photo["tmp_name"],
                            $photo_path
                        )
                    ) {


                        // ==================================================
                        // UPDATE STUDENT
                        //
                        // NAME AND FACULTY ARE NOT UPDATED.
                        // THEY REMAIN ADMIN CONTROLLED.
                        // ==================================================

                        $sql = "
                            UPDATE students

                            SET
                                dob = ?,
                                gender = ?,
                                address = ?,
                                phone = ?,
                                email = ?,
                                photo = ?,
                                profile_completed = 1

                            WHERE id = ?
                        ";


                        $stmt =
                            $conn->prepare(
                                $sql
                            );


                        if (!$stmt) {

                            $message =
                                "Database error.";

                        }


                        else {


                            $stmt->bind_param(
                                "ssssssi",
                                $dob,
                                $gender,
                                $address,
                                $phone,
                                $email,
                                $photo_name,
                                $student["id"]
                            );


                            if (
                                $stmt->execute()
                            ) {

                                header(
                                    "Location: dashboard.php"
                                );

                                exit();

                            }


                            else {

                                $message =
                                    "Could not save your profile.";

                            }


                            $stmt->close();

                        }

                    }


                    else {

                        $message =
                            "Could not upload the photo.";

                    }

                }

            }

        }

    }

}


// ==================================================
// VALUES TO DISPLAY
//
// POST VALUE IS USED AFTER VALIDATION ERROR.
// OTHERWISE DATABASE VALUE IS USED.
// ==================================================

$display_dob =
    $_POST["dob"]
    ?? ($student["dob"] ?? "");


$display_gender =
    $_POST["gender"]
    ?? ($student["gender"] ?? "");


$display_address =
    $_POST["address"]
    ?? ($student["address"] ?? "");


$display_phone =
    $_POST["phone"]
    ?? ($student["phone"] ?? "");


$display_email =
    $_POST["email"]
    ?? ($student["email"] ?? "");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Complete Profile</title>

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
        class="sidebar-link"
    >
        My Profile
    </a>


    <a
        href="complete_profile.php"
        class="sidebar-link active"
    >
        Complete Profile
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
     OVERLAY
================================================== -->

<div
    class="sidebar-overlay"
    id="overlay"
></div>


<!-- ==================================================
     MAIN
================================================== -->

<main class="container">


    <div class="card form-card">


        <h2
            style="
                margin:0 0 18px;
                font-size:18px;
                color:#26364d;
            "
        >
            Personal Information
        </h2>


        <!-- ==================================================
             ERROR MESSAGE
        ================================================== -->

        <?php if ($message !== ""): ?>

            <div class="message error-message">

                <?php

                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FORM
        ================================================== -->

        <form
            method="POST"
            enctype="multipart/form-data"
            id="profileForm"
        >


            <div class="form-grid">


                <!-- ==================================================
                     NAME
                     ADMIN CONTROLLED
                ================================================== -->

                <div class="form-group">

                    <label>
                        Full Name
                    </label>


                    <input
                        type="text"
                        value="<?php

                            echo htmlspecialchars(
                                $student["name"] ?? "-",
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        disabled
                    >

                </div>


                <!-- ==================================================
                     FACULTY
                     ADMIN CONTROLLED
                ================================================== -->

                <div class="form-group">

                    <label>
                        Faculty
                    </label>


                    <input
                        type="text"
                        value="<?php

                            echo htmlspecialchars(
                                $student["faculty"] ?? "-",
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        disabled
                    >

                </div>


                <!-- ==================================================
                     DATE OF BIRTH
                ================================================== -->

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
                                $display_dob,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        required
                    >

                </div>


                <!-- ==================================================
                     GENDER
                ================================================== -->

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

                            echo $display_gender === "Male"
                                ? "selected"
                                : "";

                            ?>
                        >
                            Male
                        </option>


                        <option
                            value="Female"
                            <?php

                            echo $display_gender === "Female"
                                ? "selected"
                                : "";

                            ?>
                        >
                            Female
                        </option>


                        <option
                            value="Other"
                            <?php

                            echo $display_gender === "Other"
                                ? "selected"
                                : "";

                            ?>
                        >
                            Other
                        </option>


                    </select>

                </div>


                <!-- ==================================================
                     ADDRESS
                ================================================== -->

                <div class="form-group full">

                    <label for="address">
                        Address
                    </label>


                    <input
                        type="text"
                        id="address"
                        name="address"
                        placeholder="Enter your address"
                        value="<?php

                            echo htmlspecialchars(
                                $display_address,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        required
                    >

                </div>


                <!-- ==================================================
                     PHONE
                ================================================== -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>


                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        maxlength="10"
                        inputmode="numeric"
                        placeholder="10 digit phone number"
                        value="<?php

                            echo htmlspecialchars(
                                $display_phone,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        required
                    >

                </div>


                <!-- ==================================================
                     EMAIL
                ================================================== -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        value="<?php

                            echo htmlspecialchars(
                                $display_email,
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                        required
                    >

                </div>


                <!-- ==================================================
                     PHOTO
                ================================================== -->

                <div class="form-group full">


                    <label>
                        Profile Photo
                    </label>


                    <div class="photo-box">


                        <!-- CAMERA -->

                        <div
                            class="camera-preview"
                            id="cameraPreview"
                        >

                            <video
                                id="camera"
                                autoplay
                                playsinline
                            ></video>

                        </div>


                        <!-- PHOTO PREVIEW -->

                        <img
                            id="photoPreview"
                            class="photo-preview"
                            alt="Profile Photo Preview"
                        >


                        <canvas
                            id="canvas"
                            style="display:none;"
                        ></canvas>


                        <!-- FILE INPUT -->

                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                        >


                        <!-- PHOTO BUTTONS -->

                        <div class="photo-buttons">


                            <button
                                type="button"
                                id="cameraButton"
                            >
                                📷 Take Photo
                            </button>


                            <button
                                type="button"
                                id="captureButton"
                                style="display:none;"
                            >
                                📸 Capture
                            </button>


                            <button
                                type="button"
                                id="retakeButton"
                                style="display:none;"
                            >
                                🔄 Retake
                            </button>


                            <button
                                type="button"
                                id="chooseButton"
                            >
                                📁 Choose Photo
                            </button>


                        </div>


                        <div class="photo-info">

                            Use your camera or choose an existing photo.
                            Maximum size: 2MB.

                        </div>


                    </div>


                </div>


            </div>


            <!-- ==================================================
                 FORM ACTIONS
            ================================================== -->

            <div class="form-actions">


                <a
                    href="dashboard.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Complete Profile
                </button>


            </div>


        </form>


    </div>


</main>


<!-- ==================================================
     SIDEBAR JAVASCRIPT
================================================== -->

<script>

const menuButton =
    document.getElementById("menuButton");

const sidebar =
    document.getElementById("sidebar");

const overlay =
    document.getElementById("overlay");


function openMenu() {

    sidebar.classList.add("open");

    overlay.classList.add("show");

    menuButton.setAttribute(
        "aria-expanded",
        "true"
    );

}


function closeMenu() {

    sidebar.classList.remove("open");

    overlay.classList.remove("show");

    menuButton.setAttribute(
        "aria-expanded",
        "false"
    );

}


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


<!-- ==================================================
     CAMERA / PHOTO JAVASCRIPT
================================================== -->

<script>

const cameraButton =
    document.getElementById("cameraButton");

const captureButton =
    document.getElementById("captureButton");

const retakeButton =
    document.getElementById("retakeButton");

const chooseButton =
    document.getElementById("chooseButton");

const photoInput =
    document.getElementById("photo");

const cameraPreview =
    document.getElementById("cameraPreview");

const camera =
    document.getElementById("camera");

const canvas =
    document.getElementById("canvas");

const photoPreview =
    document.getElementById("photoPreview");


let cameraStream = null;


// ==================================================
// OPEN CAMERA
// ==================================================

cameraButton.addEventListener(
    "click",
    async function () {

        try {

            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {

                alert(
                    "Camera is not supported. Please choose a photo."
                );

                return;

            }


            cameraStream =
                await navigator.mediaDevices.getUserMedia({

                    video: {
                        facingMode: "user"
                    },

                    audio: false

                });


            camera.srcObject =
                cameraStream;


            cameraPreview.style.display =
                "block";


            photoPreview.style.display =
                "none";


            cameraButton.style.display =
                "none";


            captureButton.style.display =
                "inline-flex";


            retakeButton.style.display =
                "none";

        }

        catch (error) {

            alert(
                "Camera could not be opened. Please allow camera permission or choose a photo."
            );

        }

    }
);


// ==================================================
// CAPTURE PHOTO
// ==================================================

captureButton.addEventListener(
    "click",
    function () {

        const width =
            camera.videoWidth;

        const height =
            camera.videoHeight;


        if (!width || !height) {

            alert(
                "Camera is not ready yet."
            );

            return;

        }


        canvas.width =
            width;

        canvas.height =
            height;


        const context =
            canvas.getContext("2d");


        context.drawImage(
            camera,
            0,
            0,
            width,
            height
        );


        canvas.toBlob(
            function (blob) {

                if (!blob) {

                    alert(
                        "Could not capture photo."
                    );

                    return;

                }


                const file =
                    new File(
                        [blob],
                        "profile_photo.jpg",
                        {
                            type: "image/jpeg"
                        }
                    );


                const dataTransfer =
                    new DataTransfer();


                dataTransfer.items.add(
                    file
                );


                photoInput.files =
                    dataTransfer.files;


                photoPreview.src =
                    URL.createObjectURL(blob);


                photoPreview.style.display =
                    "block";


                cameraPreview.style.display =
                    "none";


                captureButton.style.display =
                    "none";


                retakeButton.style.display =
                    "inline-flex";


                stopCamera();

            },
            "image/jpeg",
            0.85
        );

    }
);


// ==================================================
// CHOOSE PHOTO
// ==================================================

chooseButton.addEventListener(
    "click",
    function () {

        photoInput.click();

    }
);


// ==================================================
// FILE SELECTED
// ==================================================

photoInput.addEventListener(
    "change",
    function () {

        if (
            !this.files ||
            !this.files[0]
        ) {

            return;

        }


        const file =
            this.files[0];


        if (
            file.size >
            2 * 1024 * 1024
        ) {

            alert(
                "Photo must be less than 2MB."
            );


            this.value = "";

            return;

        }


        const allowedTypes = [

            "image/jpeg",
            "image/png",
            "image/webp"

        ];


        if (
            !allowedTypes.includes(
                file.type
            )
        ) {

            alert(
                "Only JPG, PNG and WEBP images are allowed."
            );


            this.value = "";

            return;

        }


        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                photoPreview.src =
                    event.target.result;


                photoPreview.style.display =
                    "block";


                cameraPreview.style.display =
                    "none";


                cameraButton.style.display =
                    "inline-flex";


                captureButton.style.display =
                    "none";


                retakeButton.style.display =
                    "inline-flex";

            };


        reader.readAsDataURL(file);

    }
);


// ==================================================
// RETAKE
// ==================================================

retakeButton.addEventListener(
    "click",
    function () {

        photoInput.value = "";

        photoPreview.src = "";

        photoPreview.style.display =
            "none";

        cameraPreview.style.display =
            "none";

        cameraButton.style.display =
            "inline-flex";

        captureButton.style.display =
            "none";

        retakeButton.style.display =
            "none";

        stopCamera();

    }
);


// ==================================================
// STOP CAMERA
// ==================================================

function stopCamera() {

    if (cameraStream) {

        cameraStream
            .getTracks()
            .forEach(
                function (track) {

                    track.stop();

                }
            );


        cameraStream = null;

        camera.srcObject = null;

    }

}


window.addEventListener(
    "beforeunload",
    stopCamera
);

</script>


</body>

</html>