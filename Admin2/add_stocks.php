<?php
session_start(); // Start the session

$servername = "localhost";    // Change this if your MySQL server is different
$username = "root";           // Replace with your MySQL username
$password = "";               // Replace with your MySQL password, if you have one
$dbname = "alphamec";      // The name of the database you're connecting to

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);


// Check the connection
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$message = "";

// Check if the form has been submitted
if (isset($_POST['save'])) {
    // Sanitize and validate inputs
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $product_price = $_POST['product_price'];
    $quantity = $_POST['quantity'];

    // Validate product price and quantity
    if (!is_numeric($product_price) || !is_numeric($quantity)) {
        $message = "Price and Quantity must be valid numbers.";
    } else {
        // Handle the image upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $message = "Error uploading image. Error code: " . $_FILES['image']['error'];
        } else {
            // Read the image file and convert it to binary
            $image_temp = file_get_contents($_FILES['image']['tmp_name']);
            
            // Prepare the insert query
            $query = "INSERT INTO products (ProductName, Description, Price, Quantity, Image) VALUES (?, ?, ?, ?, ?)";

            // Using prepared statements to prevent SQL injection
            $stmt = $conn->prepare($query);

            if ($stmt) {
                // Bind the parameters (Price is a decimal, Quantity is an integer, Image is BLOB)
                $stmt->bind_param("ssdss", $product_name, $description, $product_price, $quantity, $image_temp);

                // Execute the statement
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Product added successfully!";
                } else {
                    $_SESSION['message'] = "Error adding product: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                $_SESSION['message'] = "Error preparing statement: " . $conn->error;
            }
        }
    }

    // Redirect to the same page with a success/failure message
    header("Location: add_stocks.php");
    exit();
}

// Close connection
$conn->close();
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
        /* Add your custom styles here */
        .message {
            color: green;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .stock-form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .stock-form label {
            font-weight: bold;
        }

        .stock-form input,
        .stock-form textarea,
        .stock-form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .stock-form button {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
        }

        .stock-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="top-header">
            <a href="#" style="text-decoration: none; color: aliceblue;"><i class="fas fa-user"></i> Kgaugelo Mmakola</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="admin_dashboard.html"><i class="fas fa-home"></i> Dashboard</a>
        <a href="update_stock.php"><i class="fas fa-book"></i> Update Stock</a>
        <a href="add_stocks.php" style="background-color: #0c0d0f;"><i class="fas fa-clipboard-check"></i> Add Stock</a>
        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a>
        <a href="courier.php"><i class="fas fa-truck"></i> Courier</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main-content">
        <h1 style="margin-top:50px">Add Stock</h1>

        <!-- Display the message from the session -->
        <?php
        if (isset($_SESSION['message'])):
            echo "<p class='message'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying
        endif;
        ?>

        <form action="add_stocks.php" method="post" class="stock-form" enctype="multipart/form-data">
            <label for="item-name">Product Item Name:</label>
            <input type="text" id="product-name" name="product_name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="unit-price">Price:</label>
            <input type="text" id="product-price" name="product_price" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <button type="submit" name="save" value="submit">Add Stock</button>
        </form>
    </div>
    <p></p>
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
