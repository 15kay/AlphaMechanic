<?php
session_start(); // Start session

// Database connection
$servername = "localhost";   
$username = "root";          
$password = "";              
$dbname = "alphamec";        

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Save updated appointments data when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['status']) && is_array($_POST['status'])) {
            foreach ($_POST['status'] as $appointmentID => $status) {
                // Update the appointment status in the database
                $stmt = $conn->prepare("UPDATE Appointment SET AppointmentStatus = ? WHERE AppointmentID = ?");
                $stmt->bind_param("si", $status, $appointmentID);
                $stmt->execute();
            }
        }
        // Redirect after processing
        header("Location: appointments.php"); 
        exit;
    } catch (mysqli_sql_exception $e) {
        echo 'Error updating appointments: ' . $e->getMessage();
        exit;
    }
}

// Fetch all appointments with the client name from the persons table
$appointments_query = "
    SELECT a.AppointmentID, p.fullname AS ClientName, a.ServiceType, a.ServiceDate, a.Comments, a.AppointmentStatus 
    FROM Appointment a
    JOIN persons p ON a.CustomerID = p.PersonID";
      // Assuming the foreign key is PersonID in Appointment
    // $query = "SELECT a.AppointmentID, p.fullname AS ClientName, a.ServiceType, a.ServiceDate, a.Comments,a.AppointmentStatus
    // FROM Appointment a
    // JOIN customer c ON o.CustomerID = c.CustomerID
    // JOIN persons p ON c.PersonID = p.PersonID
    // ";
$result = $conn->query($appointments_query);

// Fetch appointments in an array
$appointments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masten Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="STYLE.CSS">
    
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
            background-color: #1c2125;
            color: #fff;
        }
        .form-control {
            width: 100%;
            padding: 5px;
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
            <a href="#"><i class="fas fa-user"></i></a>
            <h5>Kgaugelo Mmakola</h5>
        </div>
    </div>

    <div class="sidebar">
        <a href="admin_dashboard.html"><i class="fas fa-home"></i> Dashboard</a>
        <a href="update_stock.php"><i class="fas fa-book"></i> Update Stock</a>
        <a href="add_stocks.php"><i class="fas fa-clipboard-check"></i> Add Stock </a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="appointments.php" style="background-color: #0c0d0f;"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="courier.php"><i class="fas fa-truck"></i> Courier</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h2 class="mb-4">Appointment Records</h2>

            <form id="appointmentForm" method="POST" action="">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Appointment ID</th>
                            <th>Client Name</th>
                            <th>Appointment Date</th>
                            <th>Service Type</th>
                            <th>Comments</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['AppointmentID']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['ClientName']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($appointment['ServiceDate']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['ServiceType']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['Comments']); ?></td>
                                <td>
                                    <select class="form-control" name="status[<?php echo htmlspecialchars($appointment['AppointmentID']); ?>]">
                                        <option value="Pending" <?php echo $appointment['AppointmentStatus'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $appointment['AppointmentStatus'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Completed" <?php echo $appointment['AppointmentStatus'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Update</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <!-- Optional Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
