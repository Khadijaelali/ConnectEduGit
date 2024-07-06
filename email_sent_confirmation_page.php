<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe confirmée</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-primary {
            padding: 10px 20px;
            margin-left: 5px;
            font-size: 1em;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #06BBCC;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #5c6bc0;
        }
        
        .container {
            margin-top: 100px;
        }
    </style>
</head>
<body>

<?php
// Establish connection to the database
$host = '127.0.0.1';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'test';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Retrieve user ID from the URL parameter
$userId = isset($_GET['id']) ? $_GET['id'] : '';

if (!empty($userId)) {
    $userId = $conn->real_escape_string($userId);
    $query = "SELECT nom FROM utilisateur WHERE utilisateur_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $userName = $row['nom'];
            echo "<div class='container text-center'>
                    <div class='alert alert-success' role='alert'>
                        <h4 class='alert-heading'>Bien fait, $userName!</h4>
                        <p>Un email de réinitialisation de mot de passe a été envoyé à votre adresse enregistrée.</p>
                        <hr>
                        <p class='mb-0'>Assurez-vous de vérifier votre boîte de réception et votre dossier spam pour le lien de réinitialisation.</p>
                    </div>
                </div>";
        } else {
            echo "<div class='container text-center'><div class='alert alert-danger'>Utilisateur non trouvé.</div></div>";
        }
        $stmt->close();
    } else {
        echo "<div class='container text-center'><div class='alert alert-danger'>Erreur de préparation de la requête : " . $conn->error . "</div></div>";
    }
} else {
    echo "<div class='container text-center'><div class='alert alert-danger'>Aucun identifiant utilisateur fourni.</div></div>";
}

$conn->close();
?>

<!-- Bootstrap Bundle JS (include Popper.js) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
