<?php
include 'includes/db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;

    if ($role === 'admin') {
        $sql = "INSERT INTO Admins (name, email, password) VALUES ('$name', '$email', '$password')";
    } else {
        $sql = "INSERT INTO Users (name, email, password, gender) VALUES ('$name', '$email', '$password', '$gender')";
    }

    if ($conn->query($sql)) {
        $successMessage = "<div class='alert alert-success text-center' id='successMessage'>
                <div class='d-flex justify-content-between align-items-center'>
                    <div><i class='bi bi-check-circle'></i> Registration successful!</div>
                </div>
              </div>";
    } else {
        $errorMessage = "<div class='alert alert-danger text-center' id='errorMessage'>
                <div class='d-flex justify-content-between align-items-center'>
                    <div><i class='bi bi-exclamation-circle'></i> Registration failed!</div>
                </div>
              </div>";
    }
}
?>

<!-- HTML form for registration -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            border-radius: 15px;
        }
        .btn-close {
            border: none;
            background: transparent;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg p-4 bg-light" style="max-width: 500px; width: 100%;">
            <h2 class="text-center mb-4">Register</h2>
            <?php if ($successMessage) echo $successMessage; ?>
            <?php if ($errorMessage) echo $errorMessage; ?>
            <form action="register.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role:</label>
                    <select name="role" class="form-select" id="role" required onchange="toggleGenderField()">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-3 d-none" id="gender-group">
                    <label for="gender" class="form-label">Gender:</label>
                    <select name="gender" class="form-select">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100 mb-2">Register</button>
                <a href="login.php" class="btn btn-primary w-100">Login</a>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleGenderField() {
            var role = document.getElementById('role').value;
            var genderGroup = document.getElementById('gender-group');
            if (role === 'user') {
                genderGroup.classList.remove('d-none');
            } else {
                genderGroup.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
