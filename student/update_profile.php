<?php

session_start();

require_once "../config/database.php";


// Check student login
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "student"
) {

    header("Location: ../login.php");
    exit();

}


$message = "";
$error = "";


// Get logged-in user ID
$user_id = $_SESSION["user_id"];


// Get student record
$sql = "
    SELECT
        s.id,
        s.student_id,
        s.name,
        s.gender,
        s.dob,
        s.address,
        s.phone,
        s.email,
        s.faculty
    FROM users u
    INNER JOIN students s
        ON u.student_id = s.id
    WHERE u.id = ?
    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error.");

}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    session_destroy();

    header("Location: ../login.php");
    exit();

}


$student = $result->fetch_assoc();


// Update profile
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $gender = trim($_POST["gender"] ?? "");
    $dob = trim($_POST["dob"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");


    // Validation

    if (
        $gender === "" ||
        $dob === "" ||
        $address === "" ||
        $phone === "" ||
        $email === ""
    ) {

        $error = "All fields are required.";

    }

    elseif (
        !in_array(
            $gender,
            ["Male", "Female", "Other"],
            true
        )
    ) {

        $error = "Invalid gender.";

    }

    elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $phone
        )
    ) {

        $error = "Phone number must contain exactly 10 digits.";

    }

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error = "Please enter a valid email address.";

    }

    else {


        // Update student information

        $update_sql = "
            UPDATE students
            SET
                gender = ?,
                dob = ?,
                address = ?,
                phone = ?,
                email = ?
            WHERE id = ?
        ";


        $update_stmt = $conn->prepare($update_sql);


        if (!$update_stmt) {

            $error = "Unable to update profile.";

        }

        else {

            $update_stmt->bind_param(
                "sssssi",
                $gender,
                $dob,
                $address,
                $phone,
                $email,
                $student["id"]
            );


            if ($update_stmt->execute()) {

                $message = "Profile updated successfully.";


                // Update displayed information

                $student["gender"] = $gender;
                $student["dob"] = $dob;
                $student["address"] = $address;
                $student["phone"] = $phone;
                $student["email"] = $email;

            }

            else {

                $error = "Unable to update profile.";

            }

        }

    }

}

?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Update Profile</title><link rel="stylesheet" href="student.css"></head>
<body>
<header class="topbar"><button type="button" class="menu-button" id="menuButton" aria-label="Open navigation menu" aria-expanded="false"><span></span><span></span><span></span></button><div class="brand">Student Management System</div></header>
<aside class="sidebar" id="sidebar"><a href="dashboard.php" class="sidebar-link">Dashboard</a>
<a href="view_profile.php" class="sidebar-link">My Profile</a>
<a href="complete_profile.php" class="sidebar-link">Complete Profile</a>
<a href="update_profile.php" class="sidebar-link active">Update Profile</a>
<a href="id_card.php" class="sidebar-link">ID Card</a><div class="sidebar-bottom"><a href="../logout.php" class="sidebar-logout">Logout</a></div></aside><div class="sidebar-overlay" id="overlay"></div>
<main class="container">
<div class="card form-card">
<h2 style="margin:0 0 18px;font-size:18px;color:#26364d;">Update Profile</h2>
<?php if ($message !== ""): ?><div class="message success-message"><?php echo htmlspecialchars($message,ENT_QUOTES,"UTF-8"); ?></div><?php endif; ?>
<?php if ($error !== ""): ?><div class="message error-message"><?php echo htmlspecialchars($error,ENT_QUOTES,"UTF-8"); ?></div><?php endif; ?>
<form method="POST"><div class="form-grid">
<div class="form-group"><label>Full Name</label><input type="text" value="<?php echo htmlspecialchars($student["name"]??"-",ENT_QUOTES,"UTF-8"); ?>" disabled></div>
<div class="form-group"><label>Faculty</label><input type="text" value="<?php echo htmlspecialchars($student["faculty"]??"-",ENT_QUOTES,"UTF-8"); ?>" disabled></div>
<div class="form-group"><label for="gender">Gender</label><select id="gender" name="gender" required><option value="">Select Gender</option><option value="Male" <?php echo $student["gender"]==="Male"?"selected":""; ?>>Male</option><option value="Female" <?php echo $student["gender"]==="Female"?"selected":""; ?>>Female</option><option value="Other" <?php echo $student["gender"]==="Other"?"selected":""; ?>>Other</option></select></div>
<div class="form-group"><label for="dob">Date of Birth</label><input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student["dob"]??"",ENT_QUOTES,"UTF-8"); ?>" required></div>
<div class="form-group full"><label for="address">Address</label><input type="text" id="address" name="address" value="<?php echo htmlspecialchars($student["address"]??"",ENT_QUOTES,"UTF-8"); ?>" required></div>
<div class="form-group"><label for="phone">Phone</label><input type="text" id="phone" name="phone" maxlength="10" value="<?php echo htmlspecialchars($student["phone"]??"",ENT_QUOTES,"UTF-8"); ?>" required></div>
<div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student["email"]??"",ENT_QUOTES,"UTF-8"); ?>" required></div>
</div><div class="form-actions"><a href="view_profile.php" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Update Profile</button></div></form>
</div></main>
<script>
const menuButton=document.getElementById("menuButton");
const sidebar=document.getElementById("sidebar");
const overlay=document.getElementById("overlay");
function openMenu(){sidebar.classList.add("open");overlay.classList.add("show");menuButton.setAttribute("aria-expanded","true")}
function closeMenu(){sidebar.classList.remove("open");overlay.classList.remove("show");menuButton.setAttribute("aria-expanded","false")}
menuButton.addEventListener("click",()=>sidebar.classList.contains("open")?closeMenu():openMenu());
overlay.addEventListener("click",closeMenu);
document.addEventListener("keydown",e=>{if(e.key==="Escape")closeMenu()});
</script>
</body></html>
