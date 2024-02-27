<?php
session_start();

require_once "config.php";

$email = $password = "";
$email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT email_id, password FROM student WHERE email_id = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($email_id, $db_password);
                    if ($stmt->fetch()) {
                        if ($password === $db_password) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["email_id"] = $email_id;
                            echo '<script>alert("Logged in");</script>';
                            // header("location: dashboard.php");
                            
                        } else {
                            $password_err = "Invalid password.";
                        }
                    }
                } else {
                    $email_err = "No account found with that email.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        .wrapper { width: 360px; padding: 20px; }
    </style>
</head>
<body class="">
    <div class="d-flex flex-row justify-content-center">
        <div class="wrapper w-75 shadow" style="margin-top:20vh;">
            <h2 style="background: linear-gradient(to right, #f32170, 
                    #ff6b08, #cf23cf, #eedd44); 
            -webkit-text-fill-color: transparent; 
            -webkit-background-clip: text; font-weight:bold;">
                Login
            </h2>
            <hr>
            <p>Please fill in your credentials to login.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
                    <span class="help-block"><?php echo $email_err; ?></span>
                </div>    
                <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control">
                    <span class="help-block"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <p>Forgot password? <a href="request_otp.php">Reset password now</a>.</p>
            </form>
        </div>
    </div>
    <br><br><br><br><br>
</body>
</html>
