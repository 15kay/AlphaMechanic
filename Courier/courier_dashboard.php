<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Add any additional styles here */
        /* Main Content Styles */
.main-content {
    margin-left: 240px;
    padding: 20px;
}

.main-content h1 {
    font-size: 24px;
    margin-bottom: 20px;
}

.main-content p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

table th {
    background-color: #f4f4f4;
    font-weight: bold;
}

/* Button Styles */
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
        .btn:hover{
            background: #15630b;
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
            <a href="courier_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="assigned_deliveries.php"><i class="fas fa-truck"></i> Assigned Deliveries</a>
            <a href="completed_deliveries.php"><i class="fas fa-check"></i> Completed Deliveries</a>
            <a href="courier_portal.php"><i class="fas fa-user"></i>Courier Portal</a>
            <a href="courier_profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </section>

    <div class="main-content">
        <h1>Welcome to Your Dashboard</h1>
        <p>Here you can manage your deliveries and view your profile information.</p>
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
