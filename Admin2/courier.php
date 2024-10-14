<?php
session_start(); // Start session at the top
ob_start(); // Start output buffering

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

// Handling the form submission to send order to courier
if (isset($_POST['send_to_courier'])) {
    $order_id = $_POST['order_id'];
    $courier_service = $_POST['courier_service'];

    // Retrieve order data for insertion into courier table
    $order_data = getOrderDataById($order_id, $conn);
    if ($order_data) {
        // Get the CustomerID from the orders table
        $customer_id = getCustomerIdByOrderId($order_id, $conn);

        // Insert into the courier table
        $insert_query = "INSERT INTO courier (CustomerID, OrderID, OrderDate, Status, CourierService) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisss", $customer_id, $order_id, $order_data['OrderDate'], $order_data['Status'], $courier_service);

        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['success_message'] = "Order ID $order_id has been successfully sent to the courier!";
            // Redirect to the same page after form submission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Prevent further execution
        } else {
            echo "<tr><td colspan='7'>Error sending order to courier: " . $stmt->error . "</td></tr>";
        }
        $stmt->close();
    } else {
        echo "<tr><td colspan='7'>Error: Order data not found for Order ID: $order_id</td></tr>";
    }
}

function getCustomerIdByOrderId($order_id, $conn) {
    $query = "SELECT CustomerID FROM orders WHERE OrderID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer_id = $result->fetch_assoc();
    $stmt->close();
    return $customer_id['CustomerID']; // Return the CustomerID
}

function getOrderDataById($order_id, $conn) {
    $query = "SELECT OrderDate, Status FROM orders WHERE OrderID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_data = $result->fetch_assoc();
    $stmt->close();
    return $order_data;
}

function getCustomerData($customer_id, $conn) {
    $query = "SELECT p.fullname
              FROM persons p
              JOIN customer c ON c.PersonID = p.PersonID 
              WHERE c.CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fullname = $result->fetch_assoc();
    $stmt->fetch();
    $stmt->close();
    return ['fullname' => $fullname['fullname']];
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

        .table,
        .table th,
        .table td {
            border: 1px solid #ddd;
        }

        .table th,
        .table td {
            padding: 10px;
            text-align: left;
        }

        .thead-dark th {
            background-color: #343a40;
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

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 5px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
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
        <a href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="courier.php" style="background-color: #0c0d0f;"><i class="fas fa-truck"></i> Courier</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h2 class="mb-4">Send Orders to Couriers</h2>

            <?php
            // Display success message if it exists
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']); // Clear the message after displaying
            }
            ?>

            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Full Name</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Courier Service</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch orders without filtering by status
                    $query = "SELECT o.OrderID, o.OrderDate, o.Status, p.fullname, cr.CourierService
                              FROM orders o
                              JOIN customer c ON o.CustomerID = c.CustomerID
                              JOIN persons p ON c.PersonID = p.PersonID
                              LEFT JOIN courier cr ON o.OrderID = cr.OrderID";

                    $result = $conn->query($query);

                    if (!$result) {
                        echo "Error: " . $conn->error;
                    } else {
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['OrderID']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['fullname']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['OrderDate']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Status']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['CourierService'] ?? 'Not Assigned') . '</td>';
                                echo '<td>
                                        <form method="post" style="display:inline;">
                                            <select class="form-control" name="courier_service" required>
                                                <option value="">Select Courier</option>
                                                <option value="DHL">DHL</option>
                                                <option value="FedEx">FedEx</option>
                                                <option value="RAM">RAM</option>
                                                <option value="Courier Guy">Courier Guy</option>
                                            </select>
                                            <input type="hidden" name="order_id" value="' . htmlspecialchars($row['OrderID']) . '">
                                            <button type="submit" name="send_to_courier" class="btn"><i class="fas fa-truck"></i> Send to Courier</button>
                                        </form>
                                      </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">No orders available.</td></tr>';
                        }
                    }

                    // Close connection
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <a href="#" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</body>

</html>
