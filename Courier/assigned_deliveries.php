<?php
session_start(); // Start session at the top
ob_start(); // Start output buffering

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alphamec";

// Create a connection to the database using PDO
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Retrieve the courier ID from session or default to 1 (for debugging)
$CourierID = $_SESSION['courier_id'] ?? 1; // Change this later as needed

// Handle Status Update for Complete or Cancel
if (isset($_POST['DeliveryID']) && isset($_POST['status'])) {
    $DeliveryID = $_POST['DeliveryID'];
    $new_status = $_POST['status'];

    // Update the status in the database
    $sql = "UPDATE deliveries SET Status = :status WHERE DeliveryID = :DeliveryID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':DeliveryID', $DeliveryID);

    if ($stmt->execute()) {
        // Redirect to 'Completed Deliveries' page if status is Delivered
        if ($new_status == 'Delivered') {
            header("Location: completed_deliveries.php");
            exit();
        }
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $stmt->errorInfo()[2];
    }
}

// Fetch courier details
$courierSql = "SELECT * FROM courier WHERE CourierID = ?";
$courierStmt = $pdo->prepare($courierSql);
$courierStmt->bindParam(1, $CourierID, PDO::PARAM_INT);
$courierStmt->execute();

// Fetch assigned deliveries for the logged-in courier
$deliverySql = "SELECT d.DeliveryID, o.OrderID, d.ScheduledDate, d.Status, d.CourierID, 
                d.Street, d.Suburb, d.Town, d.Province, d.PostalCode
                FROM deliveries d 
                JOIN orders o ON d.OrderID = o.OrderID 
                WHERE d.CourierID = :CourierID";
$deliveryStmt = $pdo->prepare($deliverySql);
$deliveryStmt->bindParam(':CourierID', $CourierID);
$deliveryStmt->execute();

// Check if there are deliveries
$rows = $deliveryStmt->fetchAll(PDO::FETCH_ASSOC);
$courier = $courierStmt->fetch(PDO::FETCH_ASSOC); // Only fetch a single courier record
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Assigned Deliveries</title>
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
    </style>
</head>
<body>

<div class="header">
    <div class="top-header">
        <a href="#"><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($courier['CourierService']); ?></a>
    </div>
</div>

<section class="sidebar">
    <div class="nav-menu">
        <a href="courier_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="assigned_deliveries.php" class="active"><i class="fas fa-truck"></i> Assigned Deliveries</a>
        <a href="completed_deliveries.php"><i class="fas fa-check"></i> Completed Deliveries</a>
        <a href="courier_portal.php"><i class="fas fa-user"></i> Courier Portal</a>
        <a href="courier_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</section>

<div class="main-content">
    <h1>Assigned Deliveries</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>Delivery ID</th>
                <th>Order ID</th>
                <th>Destination</th>
                <th>Scheduled Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $destination = $row['Street'] . ', ' . $row['Suburb'] . ', ' . $row['Town'] . ', ' . $row['Province'] . ', ' . $row['PostalCode'];
                    echo '<tr>
                              <td>' . htmlspecialchars($row["DeliveryID"]) . '</td>
                              <td>' . htmlspecialchars($row["OrderID"]) . '</td>
                              <td>' . htmlspecialchars($destination) . '</td>
                              <td>' . htmlspecialchars($row["ScheduledDate"]) . '</td>
                              <td>' . htmlspecialchars($row["Status"]) . '</td>
                              <td>
                                  <form method="POST" action="">
                                      <input type="hidden" name="DeliveryID" value="' . htmlspecialchars($row["DeliveryID"]) . '">
                                      <button type="submit" name="status" value="Delivered" class="btn">Complete Delivery</button>
                                      <button type="submit" name="status" value="Cancelled" class="btn">Cancel Delivery</button>
                                  </form>
                              </td>
                          </tr>';
                }
            } else {
                echo "<tr><td colspan='6'>No deliveries found</td></tr>";
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
