<?php
// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connexion à la base de données
    $conn = new mysqli("localhost", "root", "", "test");
    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    // Récupération et hachage du mot de passe
    $utilisateur_id = $conn->real_escape_string($_POST['utilisateur_id']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hachage du mot de passe
    $role = $conn->real_escape_string($_POST['role']);
    $email = $conn->real_escape_string($_POST['email']); // Récupération de l'email

    // Préparation de la requête pour inclure l'email
    $stmt = $conn->prepare("INSERT INTO utilisateur (utilisateur_id, nom, prenom, password, role, mail) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $utilisateur_id, $nom, $prenom, $password, $role, $email);

    // Exécution de la requête
    if ($stmt->execute()) {
        header('Location: logging.php'); // Rediriger l'utilisateur après l'inscription
    } else {
        echo "Erreur lors de l'inscription: " . $conn->error;
    }

    // Fermeture de la connexion
    $stmt->close();
    $conn->close();
} else {
    echo "Méthode de requête non autorisée.";
}
?>
