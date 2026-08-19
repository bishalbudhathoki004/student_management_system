<?php

session_start();

require_once "../config/database.php";


// Check admin login
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {

    header("Location: ../login.php");
    exit();

}


// Only allow POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: manage_students.php");
    exit();

}


// Check student ID
if (
    !isset($_POST["id"]) ||
    !is_numeric($_POST["id"])
) {

    header("Location: manage_students.php");
    exit();

}


$id = (int) $_POST["id"];


// Start transaction
$conn->begin_transaction();


try {

    /*
    ================================================
    DELETE STUDENT LOGIN
    ================================================
    */

    $sql = "
        DELETE FROM users
        WHERE student_id = ?
    ";

    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "Could not prepare user deletion."
        );

    }


    $stmt->bind_param(
        "i",
        $id
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Could not delete student login."
        );

    }


    /*
    ================================================
    DELETE STUDENT
    ================================================
    */

    $sql = "
        DELETE FROM students
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "Could not prepare student deletion."
        );

    }


    $stmt->bind_param(
        "i",
        $id
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Could not delete student."
        );

    }


    /*
    ================================================
    SUCCESS
    ================================================
    */

    $conn->commit();


    header(
        "Location: manage_students.php"
    );

    exit();


} catch (Exception $e) {


    /*
    ================================================
    ROLLBACK
    ================================================
    */

    $conn->rollback();


    die(
        "Unable to delete student: " .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>