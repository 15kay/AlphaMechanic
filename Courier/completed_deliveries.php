<?php
// Database connection variables
$servername = "localhost";    // Change this if your MySQL server is different
$username = "root";           // Replace with your MySQL username
$password = "";               // Replace with your MySQL password, if you have one
$dbname = "alphamec";         // The name of the database you're connecting to

// Create a DSN for the PDO connection
$dsn = "mysql:host=$servername;dbname=$dbname";

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Fetch deliveries that have been marked as delivered
$sql = "SELECT * FROM deliveries WHERE Status = 'Delivered'";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insert the fetched deliveries into completeddeliveries table
foreach ($rows as $row) {
    // Check if the delivery already exists in the completeddeliveries table to avoid duplicates
    $checkSql = "SELECT * FROM completed_deliveries WHERE DeliveryID = :DeliveryID";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':DeliveryID' => $row['DeliveryID']]);
    $destination = $row['Street'] . ', ' . $row['Suburb'] . ', ' . $row['Town'] . ', ' . $row['Province'] . ', ' . $row['PostalCode'];
    if ($checkStmt->rowCount() === 0) {
        // If not already present, insert the completed delivery
        $insertSql = "INSERT INTO completed_deliveries (DeliveryID, OrderID, Destination, DeliveryDate, Status) 
                      VALUES (:DeliveryID, :OrderID, :Destination, :DeliveryDate, :Status)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':DeliveryID' => $row['DeliveryID'],
            ':OrderID' => $row['OrderID'],
            ':Destination' =>    $destination ,
            ':DeliveryDate' => $row['ScheduledDate'],  // Assuming this column stores the completion date
            ':Status' => $row['Status']
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Completed Deliveries</title>
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
    </style>
</head>
<body>
<div class="header">
    <div class="top-header">
    <a href="#"><i class="fas fa-user"></i></a>
    </div>
</div>

<section class="sidebar">
    <div class="nav-menu">
        <a href="courier_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="assigned_deliveries.php"><i class="fas fa-truck"></i> Assigned Deliveries</a>
        <a href="completed_deliveries.php" class="active"><i class="fas fa-check"></i> Completed Deliveries</a>
        <a href="courier_portal.php"><i class="fas fa-user"></i>Courier Portal</a>
        <a href="courier_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</section>


<div class="main-content">
        <h1>Completed Deliveries</h1>
        <table class="table">
            <thead>
            <tr>
             
                <th>Delivery ID</th>
                <th>Order ID</th>
                <th>Destination</th>
                <th>Delivery Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch completed deliveries to display
            $completedSql = "SELECT * FROM completed_deliveries";
            $completedStmt = $pdo->query($completedSql);
            $completedRows = $completedStmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($completedRows) > 0) {
                foreach ($completedRows as $row) {
                    echo '<tr>
                              <td>' . htmlspecialchars($row["DeliveryID"]) . '</td>
                              <td>' . htmlspecialchars($row["OrderID"]) . '</td>
                              <td>' . htmlspecialchars($row["Destination"]) . '</td>
                              <td>' . htmlspecialchars($row["DeliveryDate"]) . '</td>
                              <td>' . htmlspecialchars($row["Status"]) . '</td>
                              
                          </tr>';
                }
            } else {
                echo "<tr><td colspan='6'>No completed deliveries found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
    function confirmLogout() {
        var logout = confirm("Are you sure you want to log out?");
        if (logout) {
            window.location.href = 'logout.php'; // Redirect to the logout PHP page
        }
    }
</script>
</div>

</body>
</html>
