<?php
session_start();
require 'C:\xampp\htdocs\ConnectEduProject\ConnectEdu\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\ConnectEduProject\ConnectEdu\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\ConnectEduProject\ConnectEdu\PHPMailer-master\src\SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$professeurId = $_SESSION['userID'];

// Establish database connection
$conn = new mysqli("localhost", "root", "", "test");

// Check connection
if ($conn->connect_error) {
    die("Failed to connect: " . $conn->connect_error);
}

// Retrieve form data
$titre = $conn->real_escape_string($_POST['assignmentTitle']);
$description = $conn->real_escape_string($_POST['assignmentDescription']);
$dateLimite = $conn->real_escape_string($_POST['assignmentDeadline']);
$coursId = $conn->real_escape_string($_POST['courseID']);

// File upload handling
$uploadDir = "uploads_Dev/";
$fichierPath = '';

// Check if a file is uploaded without errors
if (isset($_FILES['assignmentFile']) && $_FILES['assignmentFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['assignmentFile']['tmp_name'];
    $fileName = $_FILES['assignmentFile']['name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Generate a unique filename
    $newFileName = md5(time() . $fileName) . '.' . $fileType;
    $fichierPath = $uploadDir . $newFileName;

    // Move the file to the upload directory
    if (!move_uploaded_file($fileTmpPath, $fichierPath)) {
        die('Error uploading file.');
    }
}

// Insert the assignment and file info into the database
$stmt = $conn->prepare("INSERT INTO devoir (titre, description, date_limite, cours_id, professeur_id, fichier_chemin) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiss", $titre, $description, $dateLimite, $coursId, $professeurId, $fichierPath);
$stmt->execute();

// Fetch course code using courseID
$sqlCourse = "SELECT code_cours FROM cours WHERE id = ?";
$stmtCourse = $conn->prepare($sqlCourse);
$stmtCourse->bind_param("i", $coursId);
$stmtCourse->execute();
$resultCourse = $stmtCourse->get_result();
$rowCourse = $resultCourse->fetch_assoc();

// Fetch all student emails who are enrolled in this course
$sqlUsers = "SELECT u.mail FROM utilisateur u JOIN student_course_access s ON u.utilisateur_id = s.student_id WHERE s.course_code = ?";
$stmtUsers = $conn->prepare($sqlUsers);
$stmtUsers->bind_param("s", $rowCourse['code_cours']);
$stmtUsers->execute();
$resultUsers = $stmtUsers->get_result();

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'connectedu16@gmail.com';
$mail->Password   = 'fsht wlbf zfig swsq';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->setFrom('connectedu16@gmail.com', 'ConnectEdu System');
$mail->isHTML(true);
$mail->Subject = 'Nouveau Devoir: ' . $titre;

$mailContent = "
<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Notification de Nouveau Devoir</title>
    <link href=\"https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap\" rel=\"stylesheet\">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #0056b3;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            padding: 20px;
            line-height: 1.5;
        }
        .footer {
            text-align: center;
            padding: 12px 20px;
            font-size: 14px;
            background: #e8e8e8;
            color: #555;
            border-top: 1px solid #ddd;
            border-radius: 0 0 10px 10px;
        }
        .due-date {
            color: #d10000;
            font-weight: 700;
        }
        .button {
            background-color: #28a745; /* Green background */
            color: #ffffff; /* White text */
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block; /* Keeps the button properly aligned */
            margin-top: 20px;
            border: none; /* No border unless you want one */
            cursor: pointer; /* Makes the mouse cursor turn to a hand icon when hovered */
        }
    
        .button:hover {
            background-color: #218838; /* Darker shade of green for hover effect */
        }
        .highlight {
            font-weight: 600;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Nouveau Devoir Disponible</h1>
        </div>
        <div class='content'>
            <h2 class='highlight'>Titre du Devoir: {$titre}</h2>
            <p><strong>Description:</strong> {$description}</p>
            <p><strong>Date Limite:</strong> <span class='due-date'>{$dateLimite}</span></p>
            <a href='http://localhost/connectEduF/connectEdu/logging.php' class='button'>Aller aux Devoirs</a>
        </div>
        <div class='footer'>
            Veuillez vous assurer de soumettre vos devoirs avant la date limite.
        </div>
    </div>
</body>
</html>";

$mail->Body = $mailContent;

while ($rowUser = $resultUsers->fetch_assoc()) {
    $mail->addAddress($rowUser['mail']);
}

try {
    $mail->send();
    echo 'L\'email a été envoyé à tous les étudiants inscrits.';
} catch (Exception $e) {
    echo 'Erreur de Mailer: ' . $mail->ErrorInfo;
}

$stmt->close();
$conn->close();
header('Location: Prof.php');
exit();
?>