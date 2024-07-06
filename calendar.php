
<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0");

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Professeur') {
    header('Location: index.html');
    exit();
}

$professeurId = $_SESSION['userID']; // ID du professeur connecté




// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "test");

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
// Assurez-vous que votre connexion à la base de données est sécurisée et établie


$sql = "SELECT nom, prenom FROM utilisateur WHERE utilisateur_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $professeurId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$nom = $user['nom'];
$prenom = $user['prenom'];


// Requête pour obtenir les cours du professeur
$sql = "SELECT id, titre FROM cours WHERE professeur_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $professeurId); // 
$stmt->execute();
$result = $stmt->get_result();



// Start of your sidebar code
$sidebarHTML = '<div class="sidebar"><a href="prof.php"><i class="fas fa-home"></i> Acceuil</a>';

// Loop through each course and add a link to the sidebar
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Update the href to point to 'course.php?courseID='
        $sidebarHTML .= '<a href="course.php?courseID='.$row['id'].'"><i class="fas fa-book"></i> '.htmlspecialchars($row['titre']).'</a>';
    }
} else {
    // If no courses were found, display a message
    $sidebarHTML .= '<p>Aucun cours trouvé.</p>';
}

// Finish building the sidebar HTML
$sidebarHTML .= '<a href="logout.php"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></div>';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        html, body {
            height: 100%;
            width: 100%;
            font-family:"Font Awesome 5 Free" ;
        }
        .btn-info.text-light:hover, .btn-info.text-light:focus {
            background: #000;
        }
        table, tbody, td, tfoot, th, thead, tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }
        .container-fluid {
            min-height: calc(50vh - 50px); 
        }
        .footer {
            position: relative;
            width: 100%;
        }
        .sidebar {
            width: 250px; /* Sidebar width */
            position: fixed; 
            top: 0; /* Stay at the top */
            left: 0;
            height: 100vh; /* Full-height */
            padding-top: 100px; /* Place content below the top navigation */
            background-color: #f8f9fa; /* Sidebar background color */
        }
        /* Sidebar links */
        .sidebar a {
            padding: 10px 15px; /* Padding for sidebar links */
            text-decoration: none; /* No underlines on links */
            font-size: 1.1em; /* Increase font size */
            color: #333; /* Link color */
            display: block; /* Make the links appear below each other */
        }

        .sidebar a:hover {
            background-color: #ddd; /* Link hover color */
            border-radius: 5px; /* Rounded corners on hover */
        }
        
        /* Navbar */
.navbar .dropdown-toggle::after {
    border: none;
    content: "\f107";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    vertical-align: middle;
    margin-left: 8px;
}

.navbar-light .navbar-nav .nav-link {
    margin-right: 30px;
    padding: 25px 0;
    color: #FFFFFF;
    font-size: 15px;
    text-transform: uppercase;
    outline: none;
}

.navbar-light .navbar-nav .nav-link:hover,
.navbar-light .navbar-nav .nav-link.active {
    color: var(--primary);
}

@media (max-width: 991.98px) {
    .navbar-light .navbar-nav .nav-link  {
        margin-right: 0;
        padding: 10px 0;
    }

    .navbar-light .navbar-nav {
        border-top: 1px solid #EEEEEE;
    }
}

.navbar-light .navbar-brand,
.navbar-light a.btn {
    height: 75px;
}

.navbar-light .navbar-nav .nav-link {
    color: var(--dark);
    font-weight: 500;
}

.navbar-light.sticky-top {
    top: -100px;
    transition: .5s;
}

@media (min-width: 992px) {
    .navbar .nav-item .dropdown-menu {
        display: block;
        margin-top: 0;
        opacity: 0;
        visibility: hidden;
        transition: .5s;
    }

    .navbar .dropdown-menu.fade-down {
        top: 100%;
        transform: rotateX(-75deg);
        transform-origin: 0% 0%;
    }

    .navbar .nav-item:hover .dropdown-menu {
        top: 100%;
        transform: rotateX(0deg);
        visibility: visible;
        transition: .5s;
        opacity: 1;
    }
}
.content {
            margin-top: 100px; /* Adjust this value according to the height you need */
            margin-left: 250px; /* Match this value to the sidebar width */
        }
        /* Custom styles for events */
        .fc-event.my-event {
            background-color: #28a745; /* Green for user's events */
            color: white;
        }
        .fc-event.other-event {
            background-color: #dc3545; /* Red for other events */
            color: white;
        }
        .bg-custom {
    background-color: #06BBCC; /* Bleu solide */
}
    </style>
</head>

<body class="bg-light">
 <!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow fixed-top p-0">
    <a href="Prof.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <?php if (isset($nom) && isset($prenom)): ?>
            <h2 class="m-0" style="color: #06BBCC !important;"><i class="fa fa-calendar me-3" style="color: #06BBCC;"></i>ConnectEdu Professeur: <?= htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom); ?></h2>
        <?php else: ?>
            <h2 class="m-0" style="color: #06BBCC !important;"><i class="fa fa-chalkboard-teacher me-3" style="color: #06BBCC;"></i>ConnectEdu Professeur</h2>
        <?php endif; ?>
    </a>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <!-- autres éléments du menu si nécessaire -->
        </div> 
    </div>
</nav>
<!-- Navbar End -->

    <div class="sidebar">
    <?php echo $sidebarHTML; ?>
    </div>

    <div class="container content py-5" id="page-container">
        <div class="row">
            <div class="col-md-9">
                <div id="calendar"></div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow">
                <div class="card-header bg-gradient bg-custom text-light">
    <h5 class="card-title">Formulaire de planification</h5>
</div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="">
                                
    
    <input type="hidden" name="created_by" value="<?php echo $_SESSION['userID']; ?>"> <!-- Ajoutez ceci -->
                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Titre</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required>
                                </div>
                                <div class="form-group mb-2">
    <label for="course_code" class="control-label">Nom du cours</label>
    <select class="form-control form-control-sm rounded-0" name="course_code" id="course_code" required>
        <?php
        $sql = "SELECT titre, code_cours FROM cours WHERE professeur_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $professeurId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<option value="'.htmlspecialchars($row['code_cours']).'">'.htmlspecialchars($row['titre']).'</option>';
            }
        } else {
            echo '<option value="">No courses found</option>';
        }
        ?>
    </select>
</div>

                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Description</label>
                                    <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required></textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="start_datetime" class="control-label">Début</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_datetime" id="start_datetime" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="end_datetime" class="control-label">Fin</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_datetime" id="end_datetime" required>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-center">
                            <button class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form"><i class="fa fa-save"></i> Enregistrer</button>
                            <button class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form"><i class="fa fa-reset"></i> Annuler</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">Détails de la planification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid">
                        <dl>
                            <dt class="text-muted">Titre</dt>
                            <dd id="title" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Description</dt>
                            <dd id="description" class=""></dd>
                            <dt class="text-muted">Début</dt>
                            <dd id="start" class=""></dd>
                            <dt class="text-muted">Fin</dt>
                            <dd id="end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Modifier</button>
                        <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Supprimer</button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event Details Modal -->

    <?php 
    $schedules = $conn->query("SELECT * FROM schedule_list");
    $sched_res = [];
    foreach($schedules->fetch_all(MYSQLI_ASSOC) as $row){
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
        $sched_res[$row['id']] = $row;
    }
    if(isset($conn)) $conn->close();
    ?>

    <script>
        var scheds = $.parseJSON('<?= json_encode($sched_res) ?>');
    </script>
    <script src="./js/script.js"></script>
</body>
</html>