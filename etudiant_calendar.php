<?php
session_start();
require_once('db-connect.php'); // Ensure this file correctly sets up your database connection.

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Etudiant') {
    header('Location: index.html');
    exit();
}

$studentId = $_SESSION['userID'];
$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
    die("Failed to connect: " . $conn->connect_error);
}

// Fetch student information
$sql = "SELECT nom, prenom FROM utilisateur WHERE utilisateur_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("No student found with the specified ID.");
}

$nom = $student['nom'];
$prenom = $student['prenom'];

// Fetch the courses the student has joined
$courseQuery = "SELECT c.code_cours, c.titre 
                FROM cours c
                INNER JOIN student_course_access sca ON c.code_cours = sca.course_code 
                WHERE sca.student_id = ?";
$courseStmt = $conn->prepare($courseQuery);
if (!$courseStmt) {
    die("Error preparing course query: " . $conn->error);
}
$courseStmt->bind_param("s", $studentId);
$courseStmt->execute();
$courses = $courseStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$courseStmt->close();

// Debugging: Output the courses fetched


$events = [];
if (count($courses) > 0) {
    $courseCodes = array_column($courses, 'code_cours');
    $placeholders = implode(',', array_fill(0, count($courseCodes), '?'));
    $types = str_repeat('s', count($courseCodes)); // 's' for string types
    $eventsQuery = "SELECT id, title, description, start_datetime AS start, end_datetime AS end, created_by, course_code 
                    FROM schedule_list WHERE course_code IN ($placeholders)";
    $eventsStmt = $conn->prepare($eventsQuery);
    if (!$eventsStmt) {
        die("Error preparing events query: " . $conn->error);
    }
    $eventsStmt->bind_param($types, ...$courseCodes);
    $eventsStmt->execute();
    $events = $eventsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $eventsStmt->close();

    // Debugging: Output the events fetched

} else {
    echo "<pre>No courses found for the student.</pre>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Calendar</title>
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
            font-family: "Font Awesome 5 Free";
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
        /*** Navbar ***/
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
        /* Modal Header */
.modal-header {
    background-color: #007bff;
    color: white;
}

/* Close Button in Modal Header */
.modal-header .btn-close {
    background-color: white;
}

/* Modal Footer */
.modal-footer {
    background-color: #f8f9fa;
}

/* Title */
#modal-title {
    font-weight: bold;
    font-size: 1.5rem;
}
/* Start Date */
#modal-start {
    color: #28a745; /* Green */
    font-weight: bold;
}

/* End Date */
#modal-end {
    color: #dc3545; /* Red */
    font-weight: bold;}

    </style>
</head>
<body class="bg-light">
    <!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow fixed-top p-0">
    <a href="Prof.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <?php if (isset($nom) && isset($prenom)): ?>
            <h2 class="m-0" style="color: #06BBCC !important;"><i class="fa fa-calendar me-3" style="color: #06BBCC;"></i>ConnectEdu Etudiant: <?= htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom); ?></h2>
        <?php else: ?>
            <h2 class="m-0" style="color: #06BBCC !important;"><i class="fa fa-chalkboard-teacher me-3" style="color: #06BBCC;"></i>ConnectEdu Etudiant</h2>
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
        <a href="index.html"><i class="fas fa-home"></i> Acceuil</a>
        <?php foreach ($courses as $course): ?>
            <a href="etudiant_cours.php?code=<?= htmlspecialchars($course['code_cours']) ?>"><i class="fas fa-book"></i> <?= htmlspecialchars($course['titre']) ?></a>
        <?php endforeach; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
    </div>

    <div class="container content py-5" id="page-container">
        <div id="calendar"></div>
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
                            <dd id="modal-title" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Description</dt>
                            <dd id="modal-description" class=""></dd>
                            <dt class="text-muted">Début</dt>
                            <dd id="modal-start" class=""></dd>
                            <dt class="text-muted">Fin</dt>
                            <dd id="modal-end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event Details Modal -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var events = <?php echo json_encode($events); ?>;

        // Ensure event properties match FullCalendar's expectations
        var formattedEvents = events.map(function(event) {
            return {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                description: event.description
            };
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: formattedEvents,
            editable: false,
            eventClick: function(info) {
                info.jsEvent.preventDefault();

                // Populate modal with event details
                document.getElementById('modal-title').innerText = info.event.title;
                document.getElementById('modal-description').innerText = info.event.extendedProps.description;
                document.getElementById('modal-start').innerText = info.event.start.toLocaleString();
                document.getElementById('modal-end').innerText = info.event.end ? info.event.end.toLocaleString() : '';

                // Show modal
                var eventDetailsModal = new bootstrap.Modal(document.getElementById('event-details-modal'));
                eventDetailsModal.show();
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>
