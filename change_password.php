<?php
// Include PHPMailer autoloader
require 'vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to validate if email exists in the database
function validateEmail($email) {
    // Replace these with your actual database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "bonafide";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind the SQL statement
    $sql = "SELECT * FROM student WHERE email_id = ?";
    $stmt = $conn->prepare($sql);

    // Check if preparing the statement was successful
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameter
    $stmt->bind_param("s", $email);

    // Execute the query
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Get the result
    $result = $stmt->get_result();

    // Check if the email exists
    if ($result->num_rows > 0) {
        // Email exists in the database
        return true;
    } else {
        // Email does not exist in the database
        return false;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Function to generate OTP
function generateOTP($length = 6) {
    // Generate a random OTP of specified length
    $otp = "";
    for ($i = 0; $i < $length; $i++) {
        $otp .= rand(0, 9);
    }
    return $otp;
}

// Function to save OTP in the database
function saveOTP($email, $otp) {
    // Replace these with your actual database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "bonafide";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind the SQL statement
    $sql = "UPDATE student SET otp = ? WHERE email_id = ?";
    $stmt = $conn->prepare($sql);

    // Check if preparing the statement was successful
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ss", $otp, $email);

    // Execute the query
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Function to update password in the database
// Function to update password and delete OTP in the database
function updatePasswordAndDeleteOTP($email, $e_password) {
    // Replace these with your actual database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "bonafide";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind the SQL statement to update password
    $sqlUpdatePassword = "UPDATE student SET password = ? WHERE email_id = ?";
    $stmtUpdatePassword = $conn->prepare($sqlUpdatePassword);

    // Check if preparing the statement was successful
    if (!$stmtUpdatePassword) {
        die("Error preparing update password statement: " . $conn->error);
    }

    // Bind the parameters for updating password
    $stmtUpdatePassword->bind_param("ss", $e_password, $email);

    // Execute the query to update password
    if (!$stmtUpdatePassword->execute()) {
        die("Error executing update password statement: " . $stmtUpdatePassword->error);
    }

    // Prepare and bind the SQL statement to delete OTP
    $sqlDeleteOTP = "UPDATE student SET otp = NULL WHERE email_id = ?";
    $stmtDeleteOTP = $conn->prepare($sqlDeleteOTP);

    // Check if preparing the statement was successful
    if (!$stmtDeleteOTP) {
        die("Error preparing delete OTP statement: " . $conn->error);
    }

    // Bind the parameter for deleting OTP
    $stmtDeleteOTP->bind_param("s", $email);

    // Execute the query to delete OTP
    if (!$stmtDeleteOTP->execute()) {
        die("Error executing delete OTP statement: " . $stmtDeleteOTP->error);
    }

    // Check if any rows were affected
    if ($stmtDeleteOTP->affected_rows > 0) {
        echo "Password updated and OTP deleted successfully.";
    } else {
        echo "Error updating password: No rows affected.";
    }

    // Close the statements and connection
    $stmtUpdatePassword->close();
    $stmtDeleteOTP->close();
    $conn->close();
}


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email address, OTP, and password from the form
    $email = $_POST['email'];
    $enteredOTP = $_POST['otp'];
    $e_password = $_POST['password'];

    if (validateEmail($email)) {
        // Retrieve OTP from the database
        // Replace these with your actual database credentials
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "bonafide";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind the SQL statement
        $sql = "SELECT otp FROM student WHERE email_id = ?";
        $stmt = $conn->prepare($sql);

        // Check if preparing the statement was successful
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        // Bind the parameter
        $stmt->bind_param("s", $email);

        // Execute the query
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        // Get the result
        $result = $stmt->get_result();

        // Fetch the OTP from the result
        $row = $result->fetch_assoc();
        $savedOTP = $row['otp'];

        // Close the statement and connection
        $stmt->close();
        $conn->close();

        // Compare the entered OTP with the saved OTP
        if ($enteredOTP == $savedOTP) {
            // OTPs match, update the password
            updatePasswordAndDeleteOTP($email, $e_password);
            echo "Password updated successfully.";
        } else {
            // OTPs do not match
            echo "Incorrect OTP. Please try again.";
        }
    } else {
        // Email not found in the database
        echo "Email not found in the database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Srec Request OTP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex flex-row justify-content-center">
        <div class="wrapper shadow w-75 p-4" style="margin-top:20vh;">
            <h2 style="background: linear-gradient(to right, #f32170, 
                    #ff6b08, #cf23cf, #eedd44); 
            -webkit-text-fill-color: transparent; 
            -webkit-background-clip: text; font-weight:bold;x">
                Set Password
            </h2>
            <hr>
            <form method="post" class="form-group" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="email">Enter your college email : </label><br>
                <input class="form-control" type="email" id="email" name="email" required><br><br>
                <label for="otp">Enter OTP received in email : </label><br>
                <input class="form-control" type="text" id="e_otp" name="otp" required><br><br>
                <label for="password">Enter new password : </label><br>
                <input class="form-control" type="password" id="password" name="password" required><br><br>
                <input class="btn btn-primary" type="submit" value="Submit">
            </form>
        </div>
        
    </div>
    <br><br><br><br><br>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
