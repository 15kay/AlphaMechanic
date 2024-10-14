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

$message = ""; // Initialize message variable

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize the form data
    $fullName = $conn->real_escape_string(trim($_POST['FullName'])); // Retrieve first name
    $lname = $conn->real_escape_string(trim($_POST['LastName']));
    $username = $conn->real_escape_string(trim($_POST['Username']));
    $companyName = $conn->real_escape_string(trim($_POST['CourierService']));
    $email = $conn->real_escape_string(trim($_POST['Email'])); // Ensure Email is included
    $contactNo = $conn->real_escape_string(trim($_POST['PhoneNo']));
    $loginPassword = $conn->real_escape_string(trim($_POST['Password']));
    $courier_id = $_SESSION['courier_id'] ?? 1; // Retrieve from session
    $Fname = $fullName .' '. $lname;
    // Update persons table
    $sql_persons = "UPDATE persons SET fullname=?, Username=?, PhoneNumber=?, Email=?, Password=? WHERE PersonType='C' ";
    $stmt_persons = $conn->prepare($sql_persons);
    
    if ($stmt_persons) {
        // Correct the parameter types and bind parameters
        $stmt_persons->bind_param("sssss", $Fname, $username, $contactNo, $email, $loginPassword);

        // Execute the persons update
        if ($stmt_persons->execute()) {
            // Now update the courier service in the courier table
            $sql_courier = "UPDATE courier SET CourierService=? WHERE CourierID=?";
            $stmt_courier = $conn->prepare($sql_courier);
            
            if ($stmt_courier) {
                $stmt_courier->bind_param("si", $companyName, $courier_id);
                
                if ($stmt_courier->execute()) {
                    $message = "<p style='color: green;'>Profile updated successfully!</p>";
                } else {
                    error_log("Error updating courier service: " . $stmt_courier->error);
                    $message = "<p style='color: red;'>Error updating courier service. Please try again later.</p>";
                }
                
                // Close statement
                $stmt_courier->close();
            } else {
                error_log("Preparation for courier update failed: " . $conn->error);
                $message = "<p style='color: red;'>Error preparing courier update. Please try again later.</p>";
            }
        } else {
            error_log("Error updating profile: " . $stmt_persons->error);
            $message = "<p style='color: red;'>Error updating profile. Please try again later.</p>";
        }

        // Close persons statement
        $stmt_persons->close();
    } else {
        error_log("Preparation for persons update failed: " . $conn->error);
        $message = "<p style='color: red;'>Error preparing profile update. Please try again later.</p>";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 300px;
            padding: 8px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .message {
            margin-top: 20px;
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
        <a href="completed_deliveries.php"><i class="fas fa-check"></i> Completed Deliveries</a>
        <a href="courier_portal.php"><i class="fas fa-user"></i>Courier Portal</a>
        <a href="courier_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</section>
<div class="main-content">
    <h1>Update Courier Profile</h1>

    <!-- Display success/error message -->
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="fname">First Name:</label>
        <input type="text" name="FullName" required><br>

        <label for="lname">Last Name:</label>
        <input type="text" name="LastName" required><br>

        <label for="username">Username:</label>
        <input type="text" name="Username" required><br>

        <label for="company_name">Company Name:</label>
        <input type="text" name="CourierService" required><br>

        <label for="contact_no">Contact Number:</label>
        <input type="text" name="PhoneNo" required><br>

        <label for="email">Email:</label>
        <input type="text" name="Email" required><br>

        <label for="login_password">Login Password:</label>
        <input type="password" name="Password" required><br>

        <input type="submit" value="Update Profile">
    </form>
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
