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

$showPopup = false; // Variable to control popup display
$message = ''; // Message to display in the popup

// Fetch all buses for display in the dropdown
$searchQuery = '';
if (isset($_POST['search_bus'])) {
    $searchQuery = $conn->real_escape_string($_POST['search_query']);
}
$buses = $conn->query("SELECT * FROM buses WHERE bus_name LIKE '%$searchQuery%'");

// Fetch seats for the selected bus
$seats = null;
$bus_name = '';
$bus_number = '';
$bus_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['bus_id'])) {
        $bus_id = $conn->real_escape_string($_POST['bus_id']);

        // Fetch the selected bus details
        $bus_query = $conn->query("SELECT bus_name, bus_number FROM buses WHERE id = '$bus_id'");
        if ($bus_query && $bus_query->num_rows > 0) {
            $bus_data = $bus_query->fetch_assoc();
            $bus_name = $bus_data['bus_name'];
            $bus_number = $bus_data['bus_number'];
        }

        // Fetch the seats for the selected bus
        $seats = $conn->query("SELECT * FROM seats WHERE bus_id = '$bus_id' ORDER BY seat_number ASC");
    }

    // Handle seat management logic when admin releases a seat
    if (isset($_POST['release_seat_id'])) {
        $seat_id = $conn->real_escape_string($_POST['release_seat_id']);
        $sql = "UPDATE seats SET is_booked=0, booked_by=NULL WHERE id='$seat_id'";
        if ($conn->query($sql)) {
            $message = 'Seat released successfully!';
            $showPopup = true;
        } else {
            $message = 'Failed to release seat!';
            $showPopup = true;
        }

        // Fetch updated seats
        if ($bus_id) {
            $seats = $conn->query("SELECT * FROM seats WHERE bus_id = '$bus_id' ORDER BY seat_number ASC");
        }
    }

    // Handle deleting all seats and the bus itself
    if (isset($_POST['delete_all_seats'])) {
        // Delete seats
        $sql_seats = "DELETE FROM seats WHERE bus_id='$bus_id'";
        $conn->query($sql_seats);

        // Delete bus
        $sql_bus = "DELETE FROM buses WHERE id='$bus_id'";
        if ($conn->query($sql_bus)) {
            $message = 'All seats and bus deleted successfully!';
            $showPopup = true;
        } else {
            $message = 'Failed to delete bus!';
            $showPopup = true;
        }

        // Clear the seats variable
        $seats = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bus Seat Management</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .admin-container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .bus-selection-form {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .bus-selection-form select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #bdc3c7;
            font-size: 16px;
        }

        .manage-button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .manage-button:hover {
            background-color: #2980b9;
        }

        .bus-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .row {
            display: flex;
            flex-wrap: nowrap;
            gap: 15px;
            margin-bottom: 15px;
            justify-content: center;
        }

        .seat {
            background-color: #ecf0f1;
            padding: 20px;
            text-align: center;
            border: 1px solid #bdc3c7;
            border-radius: 10px;
            width: 130px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .seat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 10px rgba(0, 0, 0, 0.2);
        }

        .booked-seat {
            background-color: #e74c3c;
            color: white;
        }

        .available-seat {
            background-color: #2ecc71;
            color: white;
        }

        .release-button {
            background-color: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-top: 5px;
        }

        .release-button:hover {
            background-color: #c0392b;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        .popup .message {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .close-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .close-btn:hover {
            background: #c0392b;
        }

        .popup {
            display: none; /* Hidden by default */
            position: fixed; /* Fixed position for popup */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            z-index: 1000; /* Ensure it's on top */
        }

        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px; /* Width of the popup */
        }

        .popup .message {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .close-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .close-btn:hover {
            background: #c0392b;
        }

    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Admin Bus Seat Management</h1>

        <!-- Filter and Display Buses -->
        <form action="view-seats.php" method="post" class="bus-selection-form">
            <label for="bus_id">Select a Bus:</label>
            <select name="bus_id" id="bus_id" required>
                <option value="" disabled selected>Select Bus</option>
                <?php while ($bus = $buses->fetch_assoc()) : ?>
                    <option value="<?= htmlspecialchars($bus['id']); ?>" <?= $bus_id == $bus['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($bus['bus_name']); ?> (<?= htmlspecialchars($bus['bus_number']); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="manage-button">Show Seats</button>
        </form>

        <?php if ($seats): ?>
            <h2>Seats for <?= htmlspecialchars($bus_name); ?> (Bus No: <?= htmlspecialchars($bus_number); ?>)</h2>

            <!-- Display Bus Layout -->
            <div class="bus-layout">
                <?php
                $rowCount = 0;
                while ($seat = $seats->fetch_assoc()) : 
                    if ($rowCount % 5 == 0) {
                        if ($rowCount > 0) {
                            echo '</div>'; // Close previous row div
                        }
                        echo '<div class="row">'; // Start new row div
                    }
                ?>
                    <div class="seat <?= $seat['is_booked'] ? 'booked-seat' : 'available-seat'; ?>">
                        <label>Seat <?= htmlspecialchars($seat['seat_number']); ?></label>
                        <?php if ($seat['is_booked']) : ?>
                            <?php
                            list($booked_email, $booked_name) = explode(',', $seat['booked_by']);
                            $email_username = explode('@', $booked_email)[0];
                            ?>
                            <p>Booked by: <?= htmlspecialchars($booked_name); ?> (<?= htmlspecialchars($email_username); ?>)</p>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="release_seat_id" value="<?= htmlspecialchars($seat['id']); ?>">
                                <button type="submit" class="release-button">Release Seat</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php
                    $rowCount++;
                endwhile;
                if ($rowCount > 0) {
                    echo '</div>'; // Close the last row div
                }
                ?>
            </div>

            <!-- Delete Bus and All Seats -->
            <form method="post">
                <input type="hidden" name="bus_id" value="<?= htmlspecialchars($bus_id); ?>">
                <button type="submit" name="delete_all_seats" class="manage-button">Delete All Seats and Bus</button>
            </form>
        <?php endif; ?>

        <!-- Popup for Success/Error Messages -->
        <?php if ($showPopup) : ?>
            <div class="popup">
                <div class="popup-content">
                    <div class="message"><?= htmlspecialchars($message); ?></div>
                    <button class="close-btn" onclick="document.querySelector('.popup').style.display='none'">Close</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

<script>
    function closePopup() {
        document.querySelector('.popup').style.display = 'none';
    }

    window.onload = function() {
        <?php if ($showPopup): ?>
            document.querySelector('.popup').style.display = 'flex';
        <?php endif; ?>
    }
</script>

</html>
