<?php 
 
session_start(); 
 
require_once "../config/database.php"; 
 
 
// ================================================== 
// CHECK STUDENT LOGIN 
// ================================================== 
 
if ( 
    !isset($_SESSION["user_id"]) || 
    $_SESSION["role"] !== "student" 
) { 
    header("Location: ../login.php"); 
    exit(); 
} 
 
 
// ================================================== 
// GET LOGGED-IN STUDENT 
// ================================================== 
 
$user_id = intval($_SESSION["user_id"]); 
 
$sql = " 
    SELECT 
        s.id, 
        s.name, 
        s.gender, 
        s.dob, 
        s.phone, 
        s.faculty, 
        s.photo, 
        s.profile_completed 
 
    FROM users u 
 
    INNER JOIN students s 
        ON u.student_id = s.id 
 
    WHERE u.id = ? 
    AND u.role = 'student' 
 
    LIMIT 1 
"; 
 
$stmt = $conn->prepare($sql); 
 
if (!$stmt) { 
    die("Database error."); 
} 
 
$stmt->bind_param("i", $user_id); 
 
$stmt->execute(); 
 
$result = $stmt->get_result(); 
 
$student = $result->fetch_assoc(); 
 
$stmt->close(); 
 
 
// ================================================== 
// STUDENT RECORD NOT FOUND 
// ================================================== 
 
if (!$student) { 
    session_destroy(); 
 
    header("Location: ../login.php"); 
    exit(); 
} 
 
 
// ================================================== 
// PROFILE MUST BE COMPLETED 
// ================================================== 
 
if (intval($student["profile_completed"]) !== 1) { 
 
    $profile_incomplete = true; 
 
} else { 
 
    $profile_incomplete = false; 
 
} 
 
 
// ================================================== 
// BATCH LOGIC 
// ================================================== 
// 
// Current BCA batch example: 
// 2083 -> 2087 
// 
// If batch changes to 2084: 
// 2084 -> 2088 
// 
// Change only $batchYear when your system's 
// academic batch changes. 
// 
 
$batchYear = 2083; 
 
$validYear = $batchYear + 4; 
 
 
// ================================================== 
// VALID TILL 
// ================================================== 
 
$validTill = $validYear . "/12/30"; 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
 
<meta charset="UTF-8"> 
 
<meta 
    name="viewport" 
    content="width=device-width, initial-scale=1.0" 
> 
 
<title>Student ID Card</title> 
 
<link 
    rel="stylesheet" 
    href="student.css" 
> 
 
<style> 
 
/* ================================================== 
   ID CARD PAGE 
   ================================================== */ 
 
.id-card-page { 
 
    min-height: calc(100vh - 60px); 
 
    display: flex; 
 
    justify-content: center; 
 
    align-items: center; 
 
    padding: 40px 20px; 
 
} 
 
 
/* ================================================== 
   INCOMPLETE PROFILE MESSAGE 
   ================================================== */ 
 
.profile-warning { 
 
    width: 100%; 
 
    max-width: 500px; 
 
    background: #ffffff; 
 
    border: 1px solid #dfe4ea; 
 
    border-radius: 10px; 
 
    padding: 30px; 
 
    text-align: center; 
 
    box-shadow: 0 8px 25px rgba(0,0,0,0.08); 
 
} 
 
.profile-warning h2 { 
 
    margin: 0 0 10px; 
 
    color: #26364d; 
 
    font-size: 22px; 
 
} 
 
.profile-warning p { 
 
    margin: 0 0 20px; 
 
    color: #666; 
 
    line-height: 1.6; 
 
} 
 
.complete-profile-btn { 
 
    display: inline-block; 
 
    padding: 11px 20px; 
 
    background: #26364d; 
 
    color: #ffffff; 
 
    text-decoration: none; 
 
    border-radius: 5px; 
 
    font-size: 14px; 
 
} 
 
.complete-profile-btn:hover { 
 
    background: #1d2b3f; 
 
} 
 
 
/* ================================================== 
   ID CARD 
   ================================================== */ 
 
.id-card-container { 
 
    width: 100%; 
 
    max-width: 680px; 
 
} 
 
 
/* ================================================== 
   CARD 
   ================================================== */ 
 
.student-id-card { 
 
    width: 100%; 
 
    background: #ffffff; 
 
    border-radius: 12px; 
 
    overflow: hidden; 
 
    border: 1px solid #dfe4ea; 
 
    box-shadow: 0 10px 30px rgba(0,0,0,0.10); 
 
} 
 
 
/* ================================================== 
   CARD HEADER 
   ================================================== */ 
 
.id-header { 
 
    background: #26364d; 
 
    color: #ffffff; 
 
    padding: 20px 28px; 
 
} 
 
.id-header h1 { 
 
    margin: 0; 
 
    font-size: 23px; 
 
    font-weight: 600; 
 
    letter-spacing: 0.3px; 
 
} 
 
 
/* ================================================== 
   CARD BODY 
   ================================================== */ 
 
.id-body { 
 
    display: flex; 
 
    gap: 28px; 
 
    padding: 28px; 
 
    align-items: flex-start; 
 
} 
 
 
/* ================================================== 
   PHOTO 
   ================================================== */ 
 
.id-photo { 
 
    width: 125px; 
 
    height: 150px; 
 
    flex-shrink: 0; 
 
    border: 2px solid #26364d; 
 
    border-radius: 7px; 
 
    overflow: hidden; 
 
    background: #f3f4f6; 
 
    display: flex; 
 
    align-items: center; 
 
    justify-content: center; 
 
} 
 
.id-photo img { 
 
    width: 100%; 
 
    height: 100%; 
 
    object-fit: cover; 
 
} 
 
.no-photo { 
 
    color: #777; 
 
    font-size: 13px; 
 
    text-align: center; 
 
    padding: 10px; 
 
} 
 
 
/* ================================================== 
   STUDENT INFORMATION 
   ================================================== */ 
 
.id-information { 
 
    flex: 1; 
 
    min-width: 0; 
 
} 
 
 
/* ================================================== 
   INFORMATION ROW 
   ================================================== */ 
 
.info-row { 
 
    display: grid; 
 
    grid-template-columns: 120px 15px 1fr; 
 
    margin-bottom: 10px; 
 
    font-size: 14px; 
 
    line-height: 1.5; 
 
} 
 
.info-label { 
 
    color: #26364d; 
 
    font-weight: 500; 
 
} 
 
.info-colon { 
 
    color: #777; 
 
} 
 
.info-value { 
 
    color: #333; 
 
    word-break: break-word; 
 
} 
 
 
/* ================================================== 
   PRINT BUTTON 
   ================================================== */ 
 
.id-actions { 
 
    margin-top: 20px; 
 
    text-align: center; 
 
} 
 
.print-btn { 
 
    border: none; 
 
    background: #26364d; 
 
    color: #ffffff; 
 
    padding: 11px 22px; 
 
    border-radius: 5px; 
 
    font-size: 14px; 
 
    cursor: pointer; 
 
} 
 
.print-btn:hover { 
 
    background: #1d2b3f; 
 
} 
 
 
/* ================================================== 
   RESPONSIVE 
   ================================================== */ 
 
@media (max-width: 600px) { 
 
    .id-card-page { 
 
        padding: 25px 15px; 
 
        align-items: flex-start; 
 
    } 
 
    .id-header { 
 
        padding: 18px 20px; 
 
    } 
 
    .id-header h1 { 
 
        font-size: 20px; 
 
    } 
 
    .id-body { 
 
        flex-direction: column; 
 
        align-items: center; 
 
        padding: 24px 20px; 
 
        gap: 20px; 
 
    } 
 
    .id-information { 
 
        width: 100%; 
 
    } 
 
    .info-row { 
 
        grid-template-columns: 105px 12px 1fr; 
 
        font-size: 13px; 
 
    } 
 
    .id-actions { 
 
        margin-top: 10px; 
 
    } 
 
} 
 
 
/* ================================================== 
   PRINT 
   ================================================== */ 
 
@media print { 
 
    body { 
 
        background: #ffffff !important; 
 
    } 
 
    .topbar, 
    .sidebar, 
    .sidebar-overlay, 
    .id-actions { 
 
        display: none !important; 
 
    } 
 
    .id-card-page { 
 
        min-height: auto; 
 
        padding: 0; 
 
        display: block; 
 
    } 
 
    .id-card-container { 
 
        max-width: 680px; 
 
        margin: 40px auto; 
 
    } 
 
    .student-id-card { 
 
        box-shadow: none; 
 
        border: 1px solid #26364d; 
 
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
        href="view_profile.php" 
        class="sidebar-link" 
    > 
        My Profile 
    </a> 
 
    <a 
        href="id_card.php" 
        class="sidebar-link active" 
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
 
 
<div 
    class="sidebar-overlay" 
    id="overlay" 
></div> 
 
 
<!-- ================================================== 
     PAGE 
     ================================================== --> 
 
<main class="id-card-page"> 
 
 
<?php if ($profile_incomplete): ?> 
 
 
    <!-- ============================================== 
         PROFILE NOT COMPLETED 
         ============================================== --> 
 
    <div class="profile-warning"> 
 
        <h2> 
            Profile Not Completed 
        </h2> 
 
        <p> 
            You need to complete your profile before 
            you can view your Student ID Card. 
        </p> 
 
        <a 
            href="complete_profile.php" 
            class="complete-profile-btn" 
        > 
            Complete Profile 
        </a> 
 
    </div> 
 
 
<?php else: ?> 
 
 
    <!-- ============================================== 
         ID CARD 
         ============================================== --> 
 
    <div class="id-card-container"> 
 
        <div class="student-id-card"> 
 
 
            <!-- HEADER --> 
 
            <div class="id-header"> 
 
                <h1> 
                    STUDENT ID CARD 
                </h1> 
 
            </div> 
 
 
            <!-- BODY --> 
 
            <div class="id-body"> 
 
 
                <!-- PHOTO --> 
 
                <div class="id-photo"> 
 
                    <?php if (!empty($student["photo"])): ?> 
 
                        <img 
                            src="<?php 
                                echo htmlspecialchars( 
                                    "../assets/uploads/students/" . 
                                    $student["photo"], 
                                    ENT_QUOTES, 
                                    "UTF-8" 
                                ); 
                            ?>" 
                            alt="Student Photo" 
                        > 
 
                    <?php else: ?> 
 
                        <div class="no-photo"> 
                            No Photo 
                        </div> 
 
                    <?php endif; ?> 
 
                </div> 
 
 
                <!-- INFORMATION --> 
 
                <div class="id-information"> 
 
 
                    <!-- NAME --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Name 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo htmlspecialchars( 
                                $student["name"] ?? "-", 
                                ENT_QUOTES, 
                                "UTF-8" 
                            ); 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                    <!-- PROGRAM --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Program 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo htmlspecialchars( 
                                $student["faculty"] ?? "-", 
                                ENT_QUOTES, 
                                "UTF-8" 
                            ); 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                    <!-- GENDER --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Gender 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
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
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Date of Birth 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo htmlspecialchars( 
                                $student["dob"] ?? "-", 
                                ENT_QUOTES, 
                                "UTF-8" 
                            ); 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                    <!-- PHONE --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Phone 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo htmlspecialchars( 
                                $student["phone"] ?? "-", 
                                ENT_QUOTES, 
                                "UTF-8" 
                            ); 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                    <!-- BATCH --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Batch 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo $batchYear; 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                    <!-- VALID TILL --> 
 
                    <div class="info-row"> 
 
                        <span class="info-label"> 
                            Valid Till 
                        </span> 
 
                        <span class="info-colon"> 
                            : 
                        </span> 
 
                        <span class="info-value"> 
 
                            <?php 
 
                            echo $validTill; 
 
                            ?> 
 
                        </span> 
 
                    </div> 
 
 
                </div> 
 
            </div> 
 
 
        </div> 
 
 
        <!-- PRINT --> 
 
        <div class="id-actions"> 
 
            <button 
                type="button" 
                class="print-btn" 
                onclick="window.print()" 
            > 
                Print ID Card 
            </button> 
 
        </div> 
 
 
    </div> 
 
 
<?php endif; ?> 
 
 
</main> 
 
 
<!-- ================================================== 
     SIDEBAR SCRIPT 
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
    () => { 
 
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
    (event) => { 
 
        if (event.key === "Escape") { 
 
            closeMenu(); 
 
        } 
 
    } 
); 
 
</script> 
 
 
</body> 
 
</html>