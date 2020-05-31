<?php
include '../connection.php';
include '../Email Templates/confirmCodeEmailTemplate.php';
include '../../sendmail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpMailer/Exception.php';
require '../phpMailer/PHPMailer.php';
require '../phpMailer/SMTP.php';

/*
Created by Samuel Arminana (armi.sam99@gmail.com)
 */

// Set response header
header('Content-Type: application/json');

// Read raw data from the request
$json = file_get_contents('php://input');
$data = json_decode($json);

// Confirm required data
if(isset($data->Email) == FALSE || isset($data->Password) == FALSE)
{
    // do something
    error("Missing Parameters");
    die();
}

// Get data
$Email = $data->Email;
$Fullname = $data->Name;
// Hash password
$Password = md5($data->Password);

// Create connection
$conn = dbConnection();
$UsersTbl = $GLOBALS['table_users'];

// Check if user exists
$result = $conn->prepare("SELECT Email FROM $UsersTbl WHERE Email='$Email'");
$result->execute();
$amount = $result->rowCount();

if($amount > 0)
{
    error("User Already Exists");
    closeConnectionAndDie($conn);
}

// Generate confirm code
$confirmCode = rand(1000,9999);

// Send email
//$mail = new PHPMailer;

// Don't use SMTP, just use mail function
//$mail->SMTPDebug = 3;
//$mail->isSMTP();
//$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//$mail->Host = "smtp.gmail.com";
//$mail->SMTPAuth = true;
//$mail->Username = $app_email;
//$mail->Password = $app_pass;
//$mail->Port = 587;

//$mail->setFrom("info@contactdeluxe.com", "Contact Manager Deluxe");
//$mail->addAddress($Email);
//$mail->isHTML(true);
//$mail->Subject = "Your Registration to Contact Manager Deluxe";
//$mail->Body = getEmail($confirmCode, $Email);
//$mail->AltBody = "Your confirmation code is " . $confirmCode;
/*
if(!$mail->send())
{
    error("Couldn't send email ");
    closeConnectionAndDie($conn);
}
*/
// Add user entry

$mail = new NewMail();
$mail->Subject = "Your Registration to Contact Manager Deluxe";
$mail->Email = $Email;
$mail->Name = $Fullname;
$mail->Body = getEmail($confirmCode, $Email,$Fullname);
$mail->AltBody = "Your confirmation code is " . $confirmCode;

if(!$mail->send())
{
    error("Couldn't send email");
    closeConnectionAndDie($conn);
}

$result = null;

$updateUser = $conn->prepare("INSERT INTO $UsersTbl (UserID, Email, Password, ConfirmCode, Name) VALUES (DEFAULT, '$Email', '$Password', '$confirmCode', '$Fullname')");
$updateUser->execute();

// Close connection
$conn = null;

success(TRUE);

?>
