<?php
// Database connection
$servername = "localhost";   
$username = "root";         
$password = "";             
$dbname = "alphamec";       

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$message = ""; // To hold any success or error messages

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $id = intval($_POST['id']);  // Get the ProductID from hidden input field
    $description = htmlspecialchars(trim($_POST['description']));
    $product_price = floatval($_POST['product_price']);  // Ensure price is a float
    $quantity = intval($_POST['quantity']);  // Ensure quantity is an integer

    // Check for valid input data
    if (empty($description) || $product_price <= 0 || $quantity < 0) {
        $message = "Invalid input. Please check your entries.";
    } else {
        // Fetch current product details from the database
        $select_query = "SELECT Description, Price, Quantity FROM Product WHERE ProductID = ?";
        $stmt = $conn->prepare($select_query);
        
        if ($stmt) {
            $stmt->bind_param("i", $id);  // Bind the ProductID to the query
            $stmt->execute();
            $stmt->bind_result($current_description, $current_price, $current_quantity);
            $stmt->fetch();
            $stmt->close();
            
            // Check if there are any changes to be made
            if ($description == $current_description && $product_price == $current_price && $quantity == $current_quantity) {
                $message = "No changes made. Product found but values are the same.";
            } else {
                // Prepare the update query
                $query = "UPDATE Product 
                          SET Description = ?, Price = ?, Quantity = ? 
                          WHERE ProductID = ?";

                // Using prepared statements to prevent SQL injection
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    // Bind parameters (s = string, d = double, i = integer)
                    $stmt->bind_param("sdii", $description, $product_price, $quantity, $id);

                    // Execute the statement
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $message = "Details updated successfully!";
                        } else {
                            $message = "Error: No rows were updated.";
                        }
                    } else {
                        $message = "Error updating details: " . $stmt->error;
                    }

                    // Close the statement
                    $stmt->close();
                } else {
                    $message = "Error preparing statement: " . $conn->error;
                }
            }
        } else {
            $message = "Error preparing select query: " . $conn->error;
        }
    }
}

// Fetch product list for the dropdown
$query = "SELECT ProductID, ProductName FROM Product";
$result = $conn->query($query);

// Handle AJAX call to fetch product details
if (isset($_GET['ProductID'])) {
    $id = intval($_GET['ProductID']);
    $query = "SELECT Description, Price, Quantity FROM Product WHERE ProductID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($description, $price, $quantity);

    if ($stmt->fetch()) {
        $response = [
            "Description" => $description,
            "Price" => $price,
            "Quantity" => $quantity,
        ];
        echo json_encode($response);
        exit;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="STYLE.CSS">

    <style>
        .message {
            font-size: 18px;
            margin-bottom: 20px;
            color: green;
        }
        .error {
            color: red;
        }
    </style>

    <script>
        function updateProductDetails() {
            var selectBox = document.getElementById("product-name");
            var selectedId = selectBox.value;
            document.getElementById("product-id").value = selectedId;

            fetch('<?= $_SERVER['PHP_SELF']; ?>?ProductID=' + selectedId)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById("description").value = data.Description;
                        document.getElementById("product-price").value = data.Price;
                        document.getElementById("quantity").value = data.Quantity;
                    } else {
                        document.getElementById("description").value = "";
                        document.getElementById("product-price").value = "";
                        document.getElementById("quantity").value = "";
                    }
                })
                .catch(error => console.error('Error fetching product details:', error));
        }
    </script>
</head>

<body>
    <div class="header">
        <div class="top-header">
            <a href="#"><i class="fas fa-user"></i></a>
            <h5>Kgaugelo Mmakola</h5>
        </div>
    </div>

    <section class="sidebar">
        <a href="admin_dashboard.html"><i class="fas fa-home"></i> Dashboard</a>
        <a href="update_stock.php" style="background-color: #0c0d0f;"><i class="fas fa-book"></i> Update Stock</a>
        <a href="add_stocks.php"><i class="fas fa-clipboard-check"></i> Add Stock</a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="courier.php"><i class="fas fa-truck"></i> Courier</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </section>

    <section class="main-content">
        <h1>Update Stock</h1>

        <!-- Display success or error message -->
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="stock-form">
            <label for="product-name">Product Item Name:</label>
            <select id="product-name" name="product_name" onchange="updateProductDetails()" required>
                <option value="" disabled selected>Select a product</option>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['ProductID'] . "'>" . $row['ProductName'] . "</option>";
                    }
                }
                ?>
            </select>

            <input type="hidden" id="product-id" name="id"> <!-- Hidden field for id -->

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="product-price">Price:</label>
            <input type="text" id="product-price" name="product_price" required>

            <button type="submit" name="save">Update Stock</button>
        </form>
    </section>
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
