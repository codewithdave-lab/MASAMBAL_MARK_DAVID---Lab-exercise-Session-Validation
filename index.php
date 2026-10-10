<?php
session_start();

// 1. Handle Logout FIRST
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// 2. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirectPage = ($_SESSION['role'] === 'admin') ? 'home2.php' : 'home.php';
    header("Location: $redirectPage");
    exit;
}

require_once 'db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
$error = "";
$success = "";

// 3. Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // --- REGISTER LOGIC ---
    if ($action === "register") {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $page = "register";

        if (empty($username) || empty($password) || empty($confirm_password)) {
            $error = "Please fill in all fields.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username exists
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
                $stmt->bind_param("ss", $username, $hashedPassword);

                if ($stmt->execute()) {
                    $success = "Registration successful! Please log in.";
                    $page = "login";
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    // --- LOGIN LOGIC ---
    elseif ($action === "login") {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        $page = "login";

        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                $redirectPage = ($user["role"] === "admin") ? "home2.php" : "home.php";
                header("Location: $redirectPage");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #DFE8E7;
            /* Sea Mist */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            border-top: 5px solid #A0430A;
            /* Burnt Copper */
        }

        h2 {
            text-align: center;
            color: #A0430A;
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        /* --- NEW CSS FOR EYE ICON --- */
        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            padding-right: 40px;
            /* Space for the eye icon */
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #A0430A;
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            fill: #888;
            transition: fill 0.3s;
        }

        .toggle-password:hover svg {
            fill: #A0430A;
        }

        /* -------------------------------- */

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #A0430A;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #8a3808;
        }

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .link a {
            color: #A0430A;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($page === "login"): ?>
            <h2>Login</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="login-pass" required>
                        <!-- Eye Icon Button -->
                        <button type="button" class="toggle-password" onclick="togglePassword('login-pass', this)">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            <div class="link">
                Don't have an account? <a href="index.php?page=register">Register here</a>
            </div>

        <?php else: ?>
            <h2>Register</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password (min 8 chars)</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="reg-pass" required>
                        <!-- Eye Icon Button -->
                        <button type="button" class="toggle-password" onclick="togglePassword('reg-pass', this)">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_password" id="reg-confirm-pass" required>
                        <!-- Eye Icon Button -->
                        <button type="button" class="toggle-password" onclick="togglePassword('reg-confirm-pass', this)">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <div class="link">
                Already registered? <a href="index.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple script to toggle password visibility
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text"; // Show password
            } else {
                input.type = "password"; // Hide password
            }
        }
    </script>

</body>

</html>