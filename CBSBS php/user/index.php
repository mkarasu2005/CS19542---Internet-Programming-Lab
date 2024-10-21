<?php
// Includes
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_seat_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, otherwise redirect to login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch user information from session
$userEmail = $_SESSION['user']['email'];
$userName = $_SESSION['user']['name']; // Assuming 'name' is stored in session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        /* General styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Body background */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        /* Dashboard container */
        .dashboard-container {
            width: 95%;
            max-width: 1100px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0px 15px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 20px;
            overflow: hidden;
        }

        /* Header section */
        .header-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Welcome message and email */
        .welcome-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 24px;
            color: #2c3e50;
        }

        /* Welcome text */
        .welcome-text {
            font-size: 36px;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 5px;
        }

        /* Email text */
        .email-text {
            font-size: 28px;
            color: #34495e;
            font-weight: 500;
        }

        /* Introductory message */
        .intro-message {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        /* Book Seat button */
        .book-seat-btn {
            display: inline-block;
            padding: 18px 35px;
            background-color: #3498db;
            color: #ffffff;
            font-size: 20px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        /* Hover effect on button */
        .book-seat-btn:hover {
            background-color: #2980b9;
            transform: scale(1.05);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Logout button */
        .logout-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 12px 25px;
            background-color: #e74c3c;
            color: #ffffff;
            font-size: 18px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        /* Hover effect on Logout button */
        .logout-btn:hover {
            background-color: #c0392b;
            transform: scale(1.05);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-container {
                width: 100%;
                padding: 20px;
            }

            .welcome-info {
                font-size: 20px;
            }

            .welcome-text {
                font-size: 30px;
            }

            .email-text {
                font-size: 22px;
            }

            .intro-message {
                font-size: 16px;
            }

            .book-seat-btn,
            .logout-btn {
                font-size: 16px;
                padding: 12px 25px;
            }
        }

        @media (max-width: 480px) {
            .welcome-text {
                font-size: 28px;
            }

            .email-text {
                font-size: 20px;
            }

            .intro-message {
                font-size: 14px;
            }

            .book-seat-btn,
            .logout-btn {
                font-size: 14px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Header section with welcome and email -->
        <div class="header-section">
            <div class="welcome-info">
                <div class="welcome-text">Welcome, <?php echo htmlspecialchars($userName); ?>!</div>
                <div class="email-text"><?php echo htmlspecialchars($userEmail); ?></div>
            </div>
        </div>

        <!-- Introductory message -->
        <p class="intro-message">We're delighted to have you with us. Use the options below to manage your bookings and explore more features.</p>

        <!-- Book a seat button -->
        <a href="book-seat.php" class="book-seat-btn">Book a Seat</a>

        <!-- Logout button -->
        <form action="login.php" method="post">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

</body>
</html>
