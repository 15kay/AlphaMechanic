<?php
// Database connection variables
$servername = "localhost";    // Change this if your MySQL server is different
$username = "root";           // Replace with your MySQL username
$password = "";               // Replace with your MySQL password, if you have one
$dbname = "alphamec";      // The name of the database you're connecting to

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get email and password from the form
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query to check if email exists in the database
    $sql = "SELECT * FROM persons WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check if the provided password matches the one in the database
        if ($password == $row['Password']) {
            $personType = $row['PersonType']; // Assuming 'PersonType' is the column name

            // Check the user type and redirect accordingly
            if ($personType == 'A') {
                echo "
                <script>
                    alert('Welcome Admin! You will be redirected to the admin dashboard in 2 seconds...');
                    setTimeout(function() {
                        window.location.href = 'Admin2/admin_dashboard.html'; // Redirect to admin dashboard
                    }, 2000); // 2-second delay
                </script>";
            } elseif ($personType == 'C') {
                echo "
                <script>
                    alert('Welcome Courier! You will be redirected to the courier dashboard in 2 seconds...');
                    setTimeout(function() {
                        window.location.href = 'Courier/courier_dashboard.php'; // Redirect to courier dashboard
                    }, 2000); // 2-second delay
                </script>";
            } else {
                $error = "Invalid user type!";
                echo "<script>alert('$error');</script>";
            }
        } else {
            $error = "Invalid password!";
            echo "<script>alert('$error');</script>";
        }
    } else {
        $error = "Invalid email!";
        echo "<script>alert('$error');</script>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    /* Your existing styles go here */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: white;
    }

    .container {
        display: flex;
        min-height: 100vh;
        align-items: center;
        justify-content: space-between;
    }

    .left-section {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .background-img {
        width: 800px;
        max-width: 100%;
        height: 300px;
    }

    .right-section {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-box {
        max-width: 400px;
        width: 100%;
        padding: 30px;
        border-radius: 15px;
        background-color: #fff;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    h2 {
        color: #333;
        margin-bottom: 20px;
    }

    .input-box {
        margin-bottom: 15px;
        text-align: left;
    }

    .input-box label {
        display: block;
        color: #333;
        margin-bottom: 5px;
    }

    .input-box input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .signin-btn {
        width: 100%;
        padding: 10px;
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .signin-btn:hover {
        background-color: #555;
    }

    .logo {
        max-width: 150px;
        margin-bottom: 20px;
    }

    .error-message {
        color: red;
        margin-bottom: 15px;
    }
</style>
<body>
    <div class="container">
        <div class="left-section">
            <img src="Courier\rover-removebg-preview.png" alt="Background Image" class="background-img">
        </div>

        <div class="right-section">
            <div class="login-box">
                <img src="Courier\new_logo(1).png" alt="Logo" class="logo">
                <h2>Welcome To AlphaMechanic</h2>
                <form method="POST" action="">
                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="signin-btn">Sign In</button>
                    <?php if (!empty($error)) : ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
