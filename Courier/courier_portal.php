<?php
session_start();

// Database connection variables
$servername = "localhost";    // Change this if your MySQL server is different
$username = "root";           // Replace with your MySQL username
$password = "";               // Replace with your MySQL password, if you have one
$dbname = "alphamec";         // The name of the database you're connecting to

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve courier information based on session ID
$courier_id = $_SESSION['courier_id'];

$logged_in_user = null;
$person_type = 'C';

    // Query to retrieve the name of the logged-in courier
    $sql = "SELECT * FROM Persons WHERE  PersonType = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $person_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $courier_profile = $result->fetch_assoc();
          // Retrieve the full name
    }

    $stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .main-content {
            padding: 20px;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .table, .table th, .table td {
            border: 1px solid #ddd;
        }

        .table th, .table td {
            padding: 10px;
            text-align: left;
        }

        .thead-dark th {
            background-color: #343a40;
            color: #fff;
        }

        .btn {
            padding: 5px 10px;
            margin-right: 5px;
            background: #1c2125;
            border: none;
            border-radius: 5px;
            color: #fff;
        }

        .btn:hover {
            background: #15630b;
        }

        .top-header {
            display: flex;
            align-items: center;
        }

        .top-header a {
            margin-left: -25px;
            text-decoration: none;
            color: inherit;
            
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="top-header">
            <a href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($courier_profile['Username']); ?></a>
        </div>
    </div>

    <section class="sidebar">
        <div class="nav-menu">
            <a href="courier_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="assigned_deliveries.php"><i class="fas fa-truck"></i> Assigned Deliveries</a>
            <a href="completed_deliveries.php"><i class="fas fa-check"></i> Completed Deliveries</a>
            <a href="courier_portal.php" class="active"><i class="fas fa-user"></i> Courier Portal</a>
            <a href="courier_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </section>

    <div class="main-content">
        <h1>Courier Dashboard</h1>

        <!-- Courier Profile -->
        <div class="courier-profile">
            <h2>Profile Information</h2>
            
            <p><strong>Name:</strong> <?php echo htmlspecialchars($courier_profile['fullname']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($courier_profile['PhoneNumber']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($courier_profile['Username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($courier_profile['Email']); ?></p>
        </div>
        
    </div>

    <div class="header">
    <div>
    <p style="margin-left:750px;font-size:22px;"><?php echo htmlspecialchars($courier_profile['fullname']); ?></p>
    </div>
</div>
<script type="text/javascript">
    function confirmLogout() {
        var logout = confirm("Are you sure you want to log out?");
        if (logout) {
            window.location.href = 'logout.php'; // Redirect to the logout PHP page
        }
    }
</script>
</body>

</html>
