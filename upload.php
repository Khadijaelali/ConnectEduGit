<?php
require 'C:\xampp\htdocs\ConnectEduF\ConnectEdu\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ConnectEduF\ConnectEdu\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ConnectEduF\ConnectEdu\PHPMailer-master\src\SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$courseID = $_POST['courseID'] ?? null;
if ($courseID === null) {
    echo "No course ID provided";
    exit;
}

// Fetching course and professor details
$stmt = $conn->prepare("SELECT c.titre, c.code_cours, u.nom, u.prenom FROM cours c JOIN utilisateur u ON c.professeur_id = u.utilisateur_id WHERE c.id = ?");
$stmt->bind_param("i", $courseID);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
if (!$course) {
    echo "No course or professor found with the specified ID";
    exit;
}

$description = $_POST['announcement'] ?? '';
$uploadDir = "uploads/";
$allowedFileTypes = ['pdf', 'jpeg', 'jpg', 'png'];
$uploadSuccess = false;

// Handling file uploads
if (!empty($_FILES['fileToUpload']['name'][0])) {
    foreach ($_FILES['fileToUpload']['name'] as $i => $name) {
        $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($fileType, $allowedFileTypes)) {
            $fileName = basename($_FILES["fileToUpload"]["name"][$i]);
            $fileTmpName = $_FILES["fileToUpload"]["tmp_name"][$i];
            $newFileName = md5(time() . $fileName) . ".$fileType";
            $uploadFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
                $stmt = $conn->prepare("INSERT INTO materials (course_id, file_name, file_path, description, file_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $courseID, $fileName, $uploadFilePath, $description, $fileType);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $uploadSuccess = true;
                }
                $stmt->close();
            }
        }
    }
}

if (!$uploadSuccess && !empty($description)) {
    $stmt = $conn->prepare("INSERT INTO annonces (course_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $courseID, $description);
    $stmt->execute();
    $stmt->close();
}

// Fetching student emails
$stmtUsers = $conn->prepare("SELECT u.mail FROM utilisateur u JOIN student_course_access s ON u.utilisateur_id = s.student_id WHERE s.course_code = ?");
$stmtUsers->bind_param("s", $course['code_cours']);
$stmtUsers->execute();
$resultUsers = $stmtUsers->get_result();

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'connectedu16@gmail.com';
$mail->Password = 'fsht wlbf zfig swsq';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->setFrom('connectedu16@gmail.com', 'ConnectEdu System');
$mail->isHTML(true);

// Email content setup
$mail->Subject = $uploadSuccess ? 'Nouveaux documents ' : 'Nouvelle annonce ';
$mailContent = $uploadSuccess ?
    "De nouveaux documents pédagogiques ont été téléchargés <strong>{$course['titre']}</strong> par <strong>{$course['nom']} {$course['prenom']}</strong>. Veuillez les consulter maintenant!" :
    "Une nouvelle annonce a été faite dans <strong>{$course['titre']}</strong> par <strong>{$course['nom']} {$course['prenom']}</strong>. Veuillez les consulter maintenant!";
$mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$mail->Subject}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 10px; }
        .header { background-color: #0056b3; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; color: #333; }
        .button { background-color: #28a745; color: white; text-decoration: none; padding: 10px 20px; display: inline-block; border-radius: 5px; font-weight: bold; margin-top: 20px; text-align: center; }
        .button:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">{$mail->Subject}</div>
        <div class="content">
            <p>{$mailContent}</p>
            <a href="http://localhost/connectEduF/connectEdu/logging.php" class="button">Voir les détails</a>
        </div>
    </div>
</body>
</html>
HTML;

foreach ($resultUsers as $rowUser) {
    $mail->addAddress($rowUser['mail']);
}

try {
    $mail->send();
    echo 'Notification sent to all enrolled students.';
    // Redirect back to the same course page
    header("Location: course.php?courseID={$courseID}");
    exit(); // Ensure that no further code is executed after redirect
} catch (Exception $e) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}

$conn->close();
?>