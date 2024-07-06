<?php
session_start();
    // Include PHPMailer classes to the global namespace
    require 'C:\xampp\htdocs\ConnectEdu\PHPMailer-master\src\Exception.php';
    require 'C:\xampp\htdocs\ConnectEdu\PHPMailer-master\src\PHPMailer.php';
    require 'C:\xampp\htdocs\ConnectEdu\PHPMailer-master\src\SMTP.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

$host = '127.0.0.1';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'test';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

if (!isset($_POST['userID']) || !isset($_POST['email'])) {
    die("Les données nécessaires n'ont pas été envoyées.");
}

$userID = $conn->real_escape_string($_POST['userID']);
$email = $conn->real_escape_string($_POST['email']);

$sql = "SELECT mail FROM utilisateur WHERE utilisateur_id = ? AND mail = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $conn->error);
}

$stmt->bind_param("ss", $userID, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $token = bin2hex(random_bytes(32));

    $update = $conn->prepare("UPDATE utilisateur SET reset_token = ? WHERE utilisateur_id = ?");
    $update->bind_param("ss", $token, $userID);
    $update->execute();



    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'connectedu16@gmail.com';                 // SMTP username
        $mail->Password   = 'fsht wlbf zfig swsq';                        // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('your-email@gmail.com', 'Mailer');
        $mail->addAddress($email);                                  // Add a recipient

        // Content
        $mail->isHTML(true);  // Set email format to HTML
$mail->Subject = 'Reinitialisation de votre mot de passe ConnectEdu';
$mail->Body    = '
<html>
<head>
  <title>Réinitialisation de votre mot de passe</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; background-color: #f4f4f4; }
    .container { background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .header { background: #0056b3; color: #fff; padding: 10px; text-align: center; border-radius: 10px 10px 0 0; }
    .footer { text-align: center; padding: 10px; font-size: 12px; color: #999; }
    a.button { background-color: #007bff; color: #ffffff; padding: 10px 20px; text-align: center; display: inline-block; border-radius: 5px; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>ConnectEdu</h1>
    </div>
    <p>Bonjour,</p>
    <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour choisir un nouveau mot de passe :</p>
    <a href="http://localhost/connectEduF/connectEdu/reset_password_form.php?token=' . $token . '" class="button">Réinitialiser mon mot de passe</a>
    <p>Si vous n\'avez pas demandé ce changement, veuillez ignorer cet email.</p>
    <div class="footer">
      <p>Merci de faire confiance à ConnectEdu.</p>
    </div>
  </div>
</body>
</html>';
$mail->AltBody = 'Pour réinitialiser votre mot de passe, copiez et collez l\'URL suivante dans votre navigateur : http://localhost/connectEduF/connectEdu/reset_password_form.php?token=' . $token;


        $mail->send();
        echo 'Un email de réinitialisation a été envoyé.';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Redirection to a confirmation page (optional)
    header("Location: email_sent_confirmation_page.php?id=" . $userID);
    exit();
} else {
    echo "Aucun utilisateur trouvé correspondant à ces identifiants.";
}

$stmt->close();
$conn->close();
?>
