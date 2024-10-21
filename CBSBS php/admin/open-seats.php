<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_seat_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bus_id = $_POST['bus_id'];
    $bus_name = $_POST['bus_name'];
    $total_seats = $_POST['total_seats'];

    // Check if bus_id already exists
    $check_bus_sql = "SELECT * FROM buses WHERE bus_id = ?";
    $stmt = $conn->prepare($check_bus_sql);
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Bus ID $bus_id already exists!";
    } else {
        // Insert new bus into the buses table
        $insert_bus_sql = "INSERT INTO buses (bus_id, bus_name) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_bus_sql);
        $stmt->bind_param("is", $bus_id, $bus_name);
        if ($stmt->execute()) {
            // Insert seats for the new bus
            $stmt = $conn->prepare("INSERT INTO seats (bus_id, seat_number) VALUES (?, ?)");
            for ($i = 1; $i <= $total_seats; $i++) {
                $stmt->bind_param("ii", $bus_id, $i);
                if (!$stmt->execute()) {
                    $message = "Error adding seat $i: " . $stmt->error;
                    break;
                }
            }

            if (!isset($message)) {
                $message = "Bus '$bus_name' (#$bus_id) with $total_seats seats successfully added!";
            }
        } else {
            $message = "Error adding bus: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all buses to populate the dropdown
$buses = $conn->query("SELECT * FROM buses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Bus and Open Seats</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Add Bus and Open Seats for Booking</h1>
        
        <form action="open-seats.php" method="post" class="open-seats-form">
            <label for="bus_id" style="margin-bottom: 5px;">Enter Bus ID:</label>
            <input type="number" name="bus_id" id="bus_id" placeholder="Enter Bus ID" required style="margin-bottom: 15px;">

            <label for="bus_name" style="margin-bottom: 5px;">Enter Bus Name:</label>
            <input type="text" name="bus_name" id="bus_name" placeholder="Enter Bus Name" required style="margin-bottom: 15px;">
                    
            <!-- Add total seats -->
            <label for="total_seats">Enter Total Seats:</label>
            <input type="number" name="total_seats" id="total_seats" placeholder="Enter seat count" required>

            <button type="submit" class="open-seats-button">Add Bus & Open Seats</button>
        </form>

        <?php if (isset($message)) { ?>
            <p class='success-message'><?= htmlspecialchars($message); ?></p>
        <?php } ?>
    </div>
</body>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .admin-container {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
        text-align: center;
    }

    h1 {
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .open-seats-form {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    label {
        margin-top: 10px;
    }

    select, input {
        margin-bottom: 15px;
        padding: 10px;
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .open-seats-button {
        background-color: #27ae60;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .open-seats-button:hover {
        background-color: #2ecc71;
    }

    .success-message {
        color: #27ae60;
        margin-top: 20px;
    }
</style>
</html>
