<?php

session_start();

require_once "config/database.php";

$message = "";

$username = "";


// Login

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");

    $password = $_POST["password"] ?? "";


    // Check empty fields

    if ($username === "" || $password === "") {

        $message =
            "Please enter username and password.";

    } else {


        // Find user

        $sql = "
            SELECT *
            FROM users
            WHERE username = ?
            LIMIT 1
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $message =
                "Database error.";

        } else {


            $stmt->bind_param(
                "s",
                $username
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if ($result->num_rows === 1) {


                $user =
                    $result->fetch_assoc();


                // Verify password

                if (
                    password_verify(
                        $password,
                        $user["password"]
                    )
                ) {


                    session_regenerate_id(true);


                    $_SESSION["user_id"] =
                        $user["id"];


                    $_SESSION["username"] =
                        $user["username"];


                    $_SESSION["role"] =
                        $user["role"];


                    // Admin Login

                    if (
                        $user["role"] === "admin"
                    ) {


                        header(
                            "Location: admin/dashboard.php"
                        );

                        exit();

                    }


                    // Student Login

                    elseif (
                        $user["role"] === "student"
                    ) {


                        // Store student ID

                        $_SESSION["student_id"] =
                            $user["student_id"];


                        // Check student account

                        if (
                            empty(
                                $user["student_id"]
                            )
                        ) {

                            $message =
                                "Student account is not linked to a student.";

                        } else {


                            // Student dashboard

                            header(
                                "Location: student/dashboard.php"
                            );

                            exit();

                        }

                    }


                    else {

                        $message =
                            "Invalid user role.";

                    }


                } else {

                    $message =
                        "Invalid username or password.";

                }


            } else {

                $message =
                    "Invalid username or password.";

            }


            $stmt->close();

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


    <title>
        Login - Student Management System
    </title>


    <link
        rel="stylesheet"
        href="assets/uploads/style.css"
    >


    <style>


        .login-page {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

        }


        .login-card {

            width: 100%;

            max-width: 420px;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 4px 20px
                rgba(0, 0, 0, 0.1);

        }


        .login-header {

            text-align: center;

            margin-bottom: 25px;

        }


        .login-header h1 {

            margin-bottom: 8px;

        }


        .login-header p {

            color: #666;

            margin: 0;

        }


        .login-button {

            width: 100%;

            margin-top: 10px;

            font-size: 16px;

        }


        .show-password {

            margin-top: 8px;

            display: flex;

            align-items: center;

            gap: 6px;

            font-size: 14px;

        }


        .show-password input {

            width: auto;

        }


        .login-error {

            transition: opacity 0.5s ease;

        }


    </style>


</head>


<body>


<div class="login-page">


    <div class="login-card">


        <div class="login-header">


            <h1>
                Student Management System
            </h1>


            <p>
                Login to your account
            </p>


        </div>



        <?php if ($message !== ""): ?>


            <div
                class="error login-error"
                id="loginMessage"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>


        <?php endif; ?>



        <form method="POST">


            <div class="form-group">


                <label for="username">
                    Username
                </label>


                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required
                >


            </div>



            <div class="form-group">


                <label for="password">
                    Password
                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >



                <label class="show-password">


                    <input
                        type="checkbox"
                        id="showPassword"
                    >


                    Show Password


                </label>


            </div>



            <button
                type="submit"
                class="login-button"
            >

                Login

            </button>


        </form>


    </div>


</div>



<script>


    // Show / hide password

    const showPassword =
        document.getElementById(
            "showPassword"
        );


    const password =
        document.getElementById(
            "password"
        );


    showPassword.addEventListener(
        "change",
        function () {


            if (this.checked) {

                password.type = "text";

            } else {

                password.type = "password";

            }


        }
    );



    // Hide error message

    const loginMessage =
        document.getElementById(
            "loginMessage"
        );


    if (loginMessage) {


        setTimeout(function () {


            loginMessage.style.opacity =
                "0";


            setTimeout(function () {


                loginMessage.style.display =
                    "none";


            }, 500);


        }, 3000);


    }


</script>


</body>

</html>