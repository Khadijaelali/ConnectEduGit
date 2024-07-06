<?php 
session_start();
require_once('db-connect.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./Prof.php') </script>";
    $conn->close();
    exit;
}

extract($_POST);
$professeurId = $_SESSION['userID'];

if (empty($id)) {
    // Insert a new event
    $sql = "INSERT INTO `schedule_list` (`title`, `description`, `start_datetime`, `end_datetime`, `created_by`, `course_code`) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $description, $start_datetime, $end_datetime, $professeurId, $course_code);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script> alert('Programme enregistré avec succès.'); location.replace('./Calendar.php') </script>";
    } else {
        echo "<pre>Error: " . $stmt->error . "</pre>";
    }
    $stmt->close();
} else {
    // Check if the current user is the creator of the event
    $checkSql = "SELECT created_by FROM `schedule_list` WHERE `id` = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        if ($row['created_by'] == $professeurId) {
            // Allow the update if the professor is the creator of the event
            $sql = "UPDATE `schedule_list` SET `title` = ?, `description` = ?, `start_datetime` = ?, `end_datetime` = ?, `course_code` = ? WHERE `id` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $title, $description, $start_datetime, $end_datetime, $course_code, $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script> alert('Programme modifié avec succès.'); location.replace('./Calendar.php') </script>";
            } else {
                echo "<pre>Error: " . $stmt->error . "</pre>";
            }
            $stmt->close();
        } else {
            // Deny the update if the professor is not the creator
            echo "<script> alert('Vous n'êtes pas autorisé à modifier cet événement.'); location.replace('./Calendar.php') </script>";
            $stmt->close();
        }
    } else {
        echo "<script> alert('Erreur: événement non trouvé.'); location.replace('./Calendar.php') </script>";
        $stmt->close();
    }
}
$conn->close();
?>
