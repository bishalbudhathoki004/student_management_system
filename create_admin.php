<?php

require_once "config/database.php";

$username = "admin";
$password = "admin9810";

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// Delete existing admin account
$sql = "DELETE FROM users WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();


// Create admin account
$sql = "INSERT INTO users
        (
            username,
            password,
            role
        )
        VALUES (?, ?, 'admin')";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ss",
    $username,
    $hashed_password
);


if ($stmt->execute()) {

    echo "<h2>Admin account created successfully!</h2>";

    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin9810</strong><br><br>";

    echo "You can now login.";

} else {

    echo "Error: " . $stmt->error;

}

?>