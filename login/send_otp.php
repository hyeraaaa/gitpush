<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


include 'dbh.inc.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_found = false;
    $user_type = '';
    $first_name = '';

    $stmt_student = $pdo->prepare("SELECT * FROM student WHERE email = :email");
    $stmt_student->execute(['email' => $email]);
    $result_student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if ($result_student) {
        $user_found = true;
        $user_type = 'student';
        $first_name = $result_student['first_name'];
    }

    $stmt_staff = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
    $stmt_staff->execute(['email' => $email]);
    $result_staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);

    if ($result_staff) {
        $user_found = true;
        $user_type = 'admin';
        $first_name = $result_staff['first_name'];
    }

    if ($user_found) {
        $otp = rand(100000, 999999);
        $otp_expiry = gmdate("Y-m-d H:i:s", strtotime('+10 minutes'));

        if ($user_type == 'student') {
            $update_stmt = $pdo->prepare("UPDATE student SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
        } else {
            $update_stmt = $pdo->prepare("UPDATE admin SET otp = :otp, otp_expiry = :otp_expiry WHERE email = :email");
        }

        $update_stmt->execute([
            'otp' => $otp,
            'otp_expiry' => $otp_expiry,
            'email' => $email
        ]);

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();                                 // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';                  // Specify main SMTP server
            $mail->SMTPAuth = true;                          // Enable SMTP authentication
            $mail->Username = 'ranonline1219@gmail.com';     // SMTP username
            $mail->Password = 'cavv jhhh onzy rwiu';        // SMTP password or app password
            $mail->SMTPSecure = 'tls';                       // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                               // TCP port to connect to

            // Recipients
            $mail->setFrom('ranonline1219@gmail.com', 'ISMS - BSU Announcement Portal');
            $mail->addAddress($email);                       // Add recipient

            // Content
            $mail->isHTML(true);                             // Set email format to HTML
            $mail->Subject = 'Your Password Reset OTP';
            $mail->addEmbeddedImage('pics/brand.png', 'brand_logo');
            $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>OTP</title>
            </head>
            <body style="margin: 0;">
                <div style="width: 500px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 30px; margin: 20px auto; font-family: Arial, sans-serif;">
                    <div style="background-color: #f9f9f9; border-radius: 15px; padding: 20px;">
                        <div style="display: flex; flex-direction: row; align-items: center; margin-bottom: 20px;">
                            <img src="cid:brand_logo" alt="" style="height: 70px;">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <h2 style="margin: 20px 0 10px; font-size: 20px;">Hello ' . htmlspecialchars($first_name) . ',</h2>
                            <p style="font-size: 16px; color: #555; margin: 10px 0;">We received a request to reset your password. To proceed, please use the following One-Time Password (OTP):</p>
                        </div>
            
                        <div style="text-align: center; margin: 15px 0;">
                            <div style="font-size: 36px; font-weight: bold; background-color: #ffffff; padding: 15px 25px; border: 2px solid rgb(182, 29, 29); border-radius: 5px; display: inline-block;">
                                ' . htmlspecialchars($otp) . '
                            </div>
                        </div>
            
                        <p style="font-size: 16px; color: #555; margin: 10px 0;">This code is valid for 10 minutes.</p>
                        <p style="font-size: 16px; color: #555; margin: 10px 0;">If you did not request a password reset, please ignore this email.</p>
                        <div style="margin-top: 20px; font-size: 14px; color: #777;">
                            <p>Best regards,</p>
                            <p>I-SMS Team</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail->send();

?>


            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Validation OTP</title>
                <!-- Bootstrap CSS v5.3.2 -->
                <link
                    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
                    rel="stylesheet"
                    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
                    crossorigin="anonymous" />

                <link rel="stylesheet" href="login.css">
            </head>

            <body>
                <section class="login_container py-5 px-4 d-flex justify-content-center align-items-center">
                    <div class="container">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="form-container col-12 col-md-6 bg-body-tertiary p-4">
                                <h2 class="text-center">Validate OTP</h2>
                                <div class="form-body p-2">
                                    <form method="POST" action="validate_otp.php">
                                        <?php
                                        echo 'OTP has been sent to your email.';
                                        ?>
                                        <div class="form-group mb-3">
                                            <label for="email">Enter your email:</label>
                                            <input type="email" name="email" required class="form-control p-3">
                                        </div>
                                        <div class="form-group mb-3 position-relative">
                                            <label for="otp">Enter OTP:</label>
                                            <input type="text" name="otp" required class="form-control p-3">
                                        </div>
                                        <div class="button_container d-flex justify-content-center">
                                            <input type="submit" value="Validate OTP" class="btn btn-warning px-4 mb-2">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </body>

            </html>
<?php
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email does not exist in either student or school staff records.";
    }
}
