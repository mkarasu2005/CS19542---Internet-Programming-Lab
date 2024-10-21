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

// Check if user is logged in and has an email in session
if (!isset($_SESSION['user'])) {
    die("You must be logged in to book a seat.");
}

$user_email = $_SESSION['user']; // Ensure this is a string, not an array

// Fetch all buses for display (now fetching both bus_number and bus_name)
$buses = $conn->query("SELECT id, bus_name, bus_number FROM buses");

// Fetch seats when a bus is selected
$seats = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bus_id'])) {
    $bus_id = $conn->real_escape_string($_POST['bus_id']);
    $seats = $conn->query("SELECT * FROM seats WHERE bus_id = '$bus_id' ORDER BY seat_number ASC");
}

// Handle seat booking logic when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seat_id'])) {
    $seat_id = $conn->real_escape_string($_POST['seat_id']);
    
    // Ensure user_email is a string
    if (is_array($user_email)) {
        $user_email = implode(',', $user_email); // Convert array to string if necessary
    }

    // Check if user has already booked a seat
    $check = $conn->query("SELECT * FROM seats WHERE booked_by='$user_email'");
    if ($check->num_rows > 0) {
        $message = "<p class='text-danger'>You have already booked a seat!</p>";
    } else {
        // Book the seat
        $sql = "UPDATE seats SET is_booked=1, booked_by='$user_email' WHERE id='$seat_id' AND is_booked=0";
        if ($conn->query($sql)) {
            $message = "Seat booked successfully!";
            $showPopup = true; // Trigger the popup
        } else {
            $message = "<p class='text-danger'>Seat booking failed!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Seat Booking</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }

        .booking-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 50px auto;
        }

        .seat {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .available-seat {
            background-color: #28a745;
            color: #fff;
        }

        .booked-seat {
            background-color: #dc3545;
            color: #fff;
        }

        .selected-seat {
            background-color: #ffc107; /* Yellow for selected seat */
            color: #fff;
        }

        .seat input[type="radio"] {
            display: none;
        }

        .seat label {
            display: block;
            cursor: pointer;
            color: #fff;
        }

        .seat:hover {
            transform: translateY(-3px);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }

        /* Center the seats */
        .seat-wrapper {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .col-2 {
            flex: 0 0 auto;
            width: 18%;
        }
    </style>
</head>
<body>
    <div class="container booking-container">
        <h1 class="text-center text-primary mb-4">Book a Seat</h1>

        <!-- Step 1: Display Buses -->
        <?php if (!isset($_POST['bus_id'])): ?>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="bus_id" class="form-label">Select Bus:</label>
                    <select name="bus_id" id="bus_id" class="form-select" required>
                        <option value="" disabled selected>Select a bus</option>
                        <?php if ($buses && $buses->num_rows > 0): ?>
                            <?php while($bus = $buses->fetch_assoc()): ?>
                                <option value="<?= $bus['id'] ?>"><?= $bus['bus_name'] ?> - <?= $bus['bus_number'] ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Show Seats</button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Display Seats -->
        <?php if (isset($seats) && $seats->num_rows > 0): ?>
            <form action="" method="post">
                <div class="row seat-wrapper g-2 mb-4">
                    <?php 
                    $seatCounter = 0; // Counter to track seats in the row
                    while($seat = $seats->fetch_assoc()): 
                    ?>
                        <div class="col-2">
                            <div class="seat <?= $seat['is_booked'] ? 'booked-seat' : 'available-seat' ?>" data-seat-id="<?= $seat['id'] ?>">
                                <label for="seat_<?= $seat['id'] ?>">Seat <?= $seat['seat_number'] ?></label>
                                <input type="radio" name="seat_id" id="seat_<?= $seat['id'] ?>" value="<?= $seat['id'] ?>" <?= $seat['is_booked'] ? 'disabled' : '' ?>>
                            </div>
                        </div>
                        <?php
                        $seatCounter++; 
                        // After every 5th seat, create a row break
                        if ($seatCounter % 5 == 0): 
                        ?>
                            <div class="w-100"></div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
                <button type="submit" class="btn btn-success w-100">Confirm Seat Selection</button>
            </form>
        <?php endif; ?>

        <!-- Step 3: Display Messages -->
        <?= isset($message) ? $message : '' ?>
    </div>

    <!-- Bootstrap Modal for Success Popup -->
    <?php if (isset($showPopup) && $showPopup): ?>
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">Booking Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="checkmark">✔</div>
                        <p><?= htmlspecialchars($message) ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS and Popper.js (Optional for some Bootstrap features) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>

    <!-- JavaScript to change the seat color on selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add event listener to all seat elements
            document.querySelectorAll('.seat').forEach(function (seat) {
                seat.addEventListener('click', function () {
                    // Reset all seats to available color
                    document.querySelectorAll('.seat').forEach(function (s) {
                        s.classList.remove('selected-seat');
                    });

                    // Add selected class to the clicked seat
                    if (!seat.classList.contains('booked-seat')) {
                        seat.classList.add('selected-seat');
                    }
                });
            });

            <?php if (isset($showPopup) && $showPopup): ?>
                var myModal = new bootstrap.Modal(document.getElementById('bookingModal'), {
                    keyboard: false
                });
                myModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
