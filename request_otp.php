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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email address from the form
    $email = $_POST['email'];

    if (validateEmail($email)) {
        // Generate OTP
        $sent = false;
        $otp = generateOTP();

        // Save OTP in the database
        saveOTP($email, $otp);

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();                                    // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';               // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                           // Enable SMTP authentication
            $mail->Username   = 'bonefiderequest@gmail.com';    // SMTP username
            $mail->Password   = 'ieir xirl wyvq pbce';          // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                            // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            // Recipients
            $mail->setFrom('bonefiderequest@gmail.com', 'SREC BONAFIDE');
            $mail->addAddress($email);                          // Add recipient dynamically

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = '<html><body>';
            $mail->Body   .= "<h1>Your OTP is $otp</h1>";
            $mail->Body   .= '<p>Enter your OTP in the OTP field area and type your password to change it.</p>';
            $mail->Body   .= '</body></html>';
            $mail->AltBody = 'Welcome to srec bonafide section made by IT department.';

            // Send the email
            $mail->send();
            // echo 'Email has been sent successfully';
            $sent = true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $sent = false;
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
        <div class="wrapper w-75 shadow" style="margin-top:20vh;">
            <form method="post" class="form-group p-3" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <h2 style="background: linear-gradient(to right, #f32170, 
                    #ff6b08, #cf23cf, #eedd44); 
            -webkit-text-fill-color: transparent; 
            -webkit-background-clip: text; 
            font-weight:bold;">
                    Request OTP
                </h2>
                <hr>
                <label  for="email">Enter your college email : </label>
                <hr style="background-color:white; height:1.5px;">
                <input class="form-control" type="email" id="email" name="email" required><br><br>
                <input  type="submit" value="Send OTP" class="btn btn-primary">
                <br><br>
                <hr>
                <?php
                if( $sent === true){
                    echo "<p>OTP sent successfully</p>";
                    header("Location: http://localhost/change_password.php");
                }else{
                    echo "<p>The email you entered is wrong or not in the database</p>";
                }
                
                ?>
            </form>
        </div>
    </div>
    <br><br><br><br><br>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
