<?php
session_start();

// Security: Redirect to login if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #DFE8E7;
            margin: 0;
            padding: 50px;
            text-align: center;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #A0430A;
        }

        h1 {
            color: #A0430A;
        }

        .badge {
            background: #A0430A;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #A0430A;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-logout:hover {
            background: #8a3808;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Administrator Dashboard</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! <span class="badge">ADMIN</span>
        </p>
        <p>You are logged in as an <strong>Administrator</strong>.</p>
        <p>Role: <?= htmlspecialchars($_SESSION['role']) ?></p>
        <p>You have access to administrator-only content.</p>
        <a href="index.php?logout=1" class="btn-logout">Logout</a>
    </div>
</body>

</html>