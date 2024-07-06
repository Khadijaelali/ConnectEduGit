<?php
session_start();

// Assurez-vous que l'utilisateur vient du formulaire de réinitialisation du mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['userID']) && isset($_POST['newPassword'])) {
    // Établir la connexion à la base de données
    $conn = new mysqli("localhost", "root", "", "test");
    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    // Récupérer l'userID de la session et le nouveau mot de passe du POST
    $utilisateur_id = $_SESSION['userID'];
    $newPassword = $_POST['newPassword'];
    
    // Hasher le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Préparer la déclaration de mise à jour
    $stmt = $conn->prepare("UPDATE utilisateur SET password = ?, reset_token = NULL WHERE utilisateur_id = ?");
    if (!$stmt) {
        die("Erreur lors de la préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param("ss", $hashedPassword, $utilisateur_id);

    // Tenter d'exécuter la requête de mise à jour
    if ($stmt->execute()) {
        // Rediriger vers une page de confirmation ou la page de connexion
        header('Location: logging.php'); // Assurez-vous que ce fichier existe.
        exit();
    } else {
        echo "Erreur lors de la réinitialisation du mot de passe: " . $conn->error;
    }

    // Fermer la déclaration et la connexion
    $stmt->close();
    $conn->close();
} else {
    // Ajout d'informations supplémentaires pour diagnostiquer le problème
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        echo "La méthode de requête doit être POST.";
    }
    if (!isset($_SESSION['userID'])) {
        echo "Identifiant utilisateur non trouvé dans la session.";
    }
    if (!isset($_POST['newPassword'])) {
        echo "Nouveau mot de passe non fourni.";
    }
}
?>
