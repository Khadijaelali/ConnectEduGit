<?php
// PHP code to check the token and retrieve user information
session_start();

$host = '127.0.0.1';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'test';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['token'])) {
    die("Token is missing.");
}

$token = $conn->real_escape_string($_GET['token']);

$sql = "SELECT utilisateur_id, prenom, nom FROM utilisateur WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in query preparation: " . $conn->error);
}

$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['userID'] = $row['utilisateur_id'];
    $prenom = $row['prenom'];
    $nom = $row['nom'];
} else {
    die("Invalid token.");
}

$stmt->close();
// End of PHP code
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your custom styles as you provided */
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

        /* Additional styles for the password input */
        .form-control {
            font-size: 1em; /* Match the font size for accessibility */
            padding: .375rem .75rem; /* Bootstrap's default padding for inputs */
            border-radius: 5px; /* Consistent border radius with button */
            border: 1px solid #ced4da; /* Bootstrap's default border color */
        }
        
        /* Any additional custom styles you want to add */
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Réinitialisation du mot de passe pour <?php echo htmlspecialchars($prenom) . " " . htmlspecialchars($nom); ?></h4>
                        <form id="newPasswordForm" action="update_password.php" method="post">
                            <div class="form-group">
                                <label for="newPassword">Nouveau mot de passe:</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Enregistrer le nouveau mot de passe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
