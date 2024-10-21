<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_seat_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['admin']);
}

function logout() {
    session_unset();
    session_destroy();
}

// If the admin selects to open or view seats
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Handle bus and seat addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page == 'open_seat') {
    $bus_id = $_POST['bus_id'];
    $bus_name = $_POST['bus_name'];
    $bus_number = $_POST['bus_number'];
    $total_seats = $_POST['total_seats'];

    // Insert or update the bus information including bus number
    $stmt = $conn->prepare("INSERT INTO buses (id, bus_name, bus_number) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE bus_name = VALUES(bus_name), bus_number = VALUES(bus_number)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iss", $bus_id, $bus_name, $bus_number);
    $stmt->execute();
    $stmt->close();

    // Open seats for booking
    $stmt = $conn->prepare("INSERT INTO seats (bus_id, seat_number, is_booked) VALUES (?, ?, 0)");
    for ($i = 1; $i <= $total_seats; $i++) {
        $stmt->bind_param("ii", $bus_id, $i);
        $stmt->execute();
    }
    $stmt->close();

    // Redirect to view-seats.php after seat opening
    header("Location: view-seats.php");
    exit();
}

// Handle seat release
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page == 'view_seat' && isset($_POST['seat_id'])) {
    $seat_id = $_POST['seat_id'];

    // Update seat to mark it as available
    $stmt = $conn->prepare("UPDATE seats SET is_booked = 0, booked_by = NULL WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $seat_id);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Seat Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        /* General Styles */
        body {
            background-color: #f4f4f4;
        }
        .admin-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h1 {
            color: #11998e;
            margin-bottom: 20px;
        }
        /* Success and Error Messages */
        .success-message {
            color: #27ae60;
            margin-top: 20px;
        }
        .error-message {
            color: #e74c3c;
            margin-top: 20px;
        }
        /* Flexbox for Centering Content */
        .content-area {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container d-flex flex-column min-vh-100">
        <div class="admin-container flex-fill d-flex flex-column">
            <h1 class="text-center">Admin Dashboard - Seat Management</h1>

            <!-- Carousel for Images -->
            <div id="imageCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://th.bing.com/th/id/R.adde5efee679ccd026dbde4cfb8969c3?rik=TXe%2bbO7tpNmRSg&riu=http%3a%2f%2fwww.rajalakshmi.org%2fimage%2fbanner-1.jpg&ehk=jGrGrW1JV4FHHJAZywUr4GbIM3rLWB0y8%2bhPvtDa%2fx4%3d&risl=&pid=ImgRaw&r=0" class="d-block w-100" alt="Image 1">
                    </div>
                    <div class="carousel-item">
                        <img src="https://th.bing.com/th/id/R.adde5efee679ccd026dbde4cfb8969c3?rik=TXe%2bbO7tpNmRSg&riu=http%3a%2f%2fwww.rajalakshmi.org%2fimage%2fbanner-1.jpg&ehk=jGrGrW1JV4FHHJAZywUr4GbIM3rLWB0y8%2bhPvtDa%2fx4%3d&risl=&pid=ImgRaw&r=0" class="d-block w-100" alt="Image 2">
                    </div>
                    <div class="carousel-item">
                        <img src="https://th.bing.com/th/id/R.adde5efee679ccd026dbde4cfb8969c3?rik=TXe%2bbO7tpNmRSg&riu=http%3a%2f%2fwww.rajalakshmi.org%2fimage%2fbanner-1.jpg&ehk=jGrGrW1JV4FHHJAZywUr4GbIM3rLWB0y8%2bhPvtDa%2fx4%3d&risl=&pid=ImgRaw&r=0" class="d-block w-100" alt="Image 3">
                    </div>
                </div>
                <a class="carousel-control-prev" href="#imageCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#imageCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>

            <!-- Navigation Options -->
            <div class="mt-4 text-center">
                <a href="index.php?page=open_seat" class="btn btn-primary">Open Seat</a>
                <a href="view-seats.php" class="btn btn-secondary">View Seats</a>
            </div>

            <!-- Display content based on selected option -->
            <div class="content-area mt-4">
                <?php if ($page == 'dashboard'): ?>
                    <p>Welcome to the Admin Dashboard. Please choose an option.</p>
                <?php elseif ($page == 'open_seat'): ?>
                    <h2>Open Seat</h2>
                    <form action="index.php?page=open_seat" method="POST">
                        <div class="form-group">
                            <label for="bus_id">Enter Bus ID:</label>
                            <input type="number" class="form-control" name="bus_id" id="bus_id" placeholder="Enter Bus ID" required>
                        </div>
                        <div class="form-group">
                            <label for="bus_name">Enter Bus Name:</label>
                            <input type="text" class="form-control" name="bus_name" id="bus_name" placeholder="Enter Bus Name" required>
                        </div>
                        <div class="form-group">
                            <label for="bus_number">Enter Bus Number:</label>
                            <input type="text" class="form-control" name="bus_number" id="bus_number" placeholder="Enter Bus Number" required>
                        </div>
                        <div class="form-group">
                            <label for="total_seats">Enter Total Seats:</label>
                            <input type="number" class="form-control" name="total_seats" id="total_seats" placeholder="Enter seat count" required>
                        </div>
                        <button type="submit" class="btn btn-success">Add Bus & Open Seats</button>
                    </form>
                <?php elseif ($page == 'view_seat'): ?>
                    <h2>View Seats</h2>
                    <!-- Display seats logic here -->
                <?php endif; ?>

                <!-- Success/Error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
