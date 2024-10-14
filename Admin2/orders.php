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
        <a href="orders.php" style="background-color: #0c0d0f;"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="courier.php"><i class="fas fa-truck"></i> Courier</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h2 class="mb-4">Order Records</h2>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Order Number</th> <!-- New Customer ID column -->
                        <th>Parts</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>CustomerID</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Database connection
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "alphamec";

                    // Create connection
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Fetch orders along with CustomerID
                    $query = "SELECT * FROM orders";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        $order_count = 0;
                        while ($row = $result->fetch_assoc()) {
                            $order_count++;
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['OrderID']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['OrderNumber']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['Parts']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['Status']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['OrderDate']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['CustomerID']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['TotalAmount']) . '</td>';
                            echo '</tr>';
                        }
                        echo "<tr><td colspan='7'><strong>Total Orders: $order_count</strong></td></tr>"; // Adjust colspan to 7
                    } else {
                        echo '<tr><td colspan="7">No orders found.</td></tr>'; // Adjust colspan to 7
                    }

                    // Close connection
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
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
