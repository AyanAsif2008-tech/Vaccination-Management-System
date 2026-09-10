<?php
/**
 * mail.php
 * ------------------------------------------------------------
 * This file has ONE job: send the "your hospital was approved"
 * email using PHPMailer (already installed in /vendor via Composer).
 *
 * HOW TO USE IT:
 * 1. Fill in YOUR real email details in the 5 variables below
 *    (SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD, etc).
 * 2. That's it. Nothing else in this file needs to change.
 * ------------------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------------------------------------------------
// STEP 1: Fill in your real email account details here.
// ---------------------------------------------------------
// Example using Gmail:
//   SMTP_USERNAME = your full Gmail address
//   SMTP_PASSWORD = a 16-character "App Password" (NOT your normal
//                   Gmail password) — create one at:
//                   https://myaccount.google.com/apppasswords
// ---------------------------------------------------------
$SMTP_HOST     = 'smtp.gmail.com';
$SMTP_PORT     = 587;
$SMTP_USERNAME = 'ayanasifmemon08@gmail.com';
$SMTP_PASSWORD = 'cpyvlqoaavlyvigr';
$SMTP_FROM_NAME = 'Vaccination Management System';


/**
 * Sends the approval email to a hospital.
 *
 * @param string $toEmail       The hospital's email address
 * @param string $hospitalName  The hospital's name (used in the greeting)
 * @return bool true if the email was sent, false if it failed
 */
function send_hospital_approval_email($toEmail, $hospitalName) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USERNAME, $SMTP_PASSWORD, $SMTP_FROM_NAME;

    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USERNAME;
        $mail->Password   = $SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = $SMTP_PORT;

        // --- Who it's from / who it's going to ---
        $mail->setFrom($SMTP_USERNAME, $SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $hospitalName);

        // --- The actual email content ---
        $mail->Subject = 'Your hospital account has been approved';
        $mail->Body    = "Hello $hospitalName,\n\n"
                        . "Your registration request has been approved! "
                        . "You can now log in to your dashboard.\n\n"
                        . "— Vaccination Management System";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // If sending fails, don't crash the page — just report it failed.
        return false;
    }
}
