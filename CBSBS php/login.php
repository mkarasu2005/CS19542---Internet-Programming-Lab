<?php
include 'includes/db.php';
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and trim input data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepared statement to check if the email belongs to an admin
    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Admin found, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password matches, set session and redirect to admin dashboard
            $_SESSION['admin'] = [
                'email' => $row['email'],
                'name' => $row['name'] // Assuming there's a 'name' field in the admins table
            ];
            header("Location: admin/index.php");
            exit;
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        // Check if the email belongs to a regular user
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, verify password
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password matches, set session and redirect to user dashboard
                $_SESSION['user'] = [
                    'email' => $row['email'],
                    'name' => $row['name'] // Assuming there's a 'name' field in the users table
                ];
                header("Location: user/index.php");
                exit;
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "User not found!";
        }
    }

    $stmt->close();
}
?>

<!-- HTML form for login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-primary d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow-lg" style="width: 100%; max-width: 400px;">
    <div class="card-body">
        <form action="login.php" method="post" class="login-form">
            <h2 class="text-center mb-4">Login</h2>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
            <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
        </form>

        <!-- Register Button -->
        <form action="register.php" method="get">
            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS and dependencies (Popper.js and jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>

</body>
</html>
