<?php
include 'utils/db.php';
include 'utils/auth.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT c.*, COUNT(r.id) as active_rentals, c.description 
                        FROM catalog c 
                        LEFT JOIN request r ON c.id = r.catalog_id 
                            AND r.status IN ('Requested', 'Preparing', 'Ready', 'Collected')
                        WHERE c.id = ?
                        GROUP BY c.id");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    echo "Book not found";
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$is_bookmarked = false;

if ($is_logged_in) {
    $bookmark_stmt = $conn->prepare("SELECT id FROM bookmark WHERE user_id = ? AND catalog_id = ?");
    $bookmark_stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $bookmark_stmt->execute();
    $bookmark_result = $bookmark_stmt->get_result();
    $is_bookmarked = $bookmark_result->num_rows > 0;
}

$daily_rate = ($book['price'] * 1.5) / 30;
$pickup_locations = $conn->query("SELECT * FROM pickup_location")->fetch_all(MYSQLI_ASSOC);

// Calculate availability
$available_copies = $book['inventory'] - $book['active_rentals'];

// Get earliest available date if book is unavailable
$earliest_available_date = null;
if ($available_copies <= 0) {
    $date_stmt = $conn->prepare("SELECT MIN(DATE_ADD(status_last_updated, INTERVAL rental_duration DAY)) as earliest_date 
                                 FROM request 
                                 WHERE catalog_id = ? AND status IN ('Requested', 'Preparing', 'Ready', 'Collected')");
    $date_stmt->bind_param("i", $id);
    $date_stmt->execute();
    $date_result = $date_stmt->get_result();
    $earliest_available_date = $date_result->fetch_assoc()['earliest_date'];
}

// Process rental form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rent') {
    if (!$is_logged_in) {
        $error = "Please log in to rent a book.";
    } elseif ($available_copies <= 0) {
        $error = "Sorry, this book is currently unavailable.";
    } else {
        $duration = intval($_POST['duration']);
        $pickup_location_id = intval($_POST['pickup_location']);
        $total_cost = $daily_rate * $duration;

        $stmt = $conn->prepare("INSERT INTO request (user_id, catalog_id, status, status_last_updated, rental_duration, pickup_location_id) VALUES (?, ?, 'Requested', NOW(), ?, ?)");
        $stmt->bind_param("iiii", $_SESSION['user_id'], $id, $duration, $pickup_location_id);

        if ($stmt->execute()) {
            $success_message = "Rental request submitted successfully!";
        } else {
            $error = "Failed to submit rental request. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="item.css">
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo/Title Section -->
            <div class="navbar-logo-section">
                <a href="index.php" class="navbar-logo">Libriverse</a>
            </div>

            <!-- Pages Section -->
            <ul class="navbar-pages">
                <li><a href="index.php" class="navbar-item">Home</a></li>
                <li><a href="discover.php" class="navbar-item">Discover</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="bookshelf.php" class="navbar-item">Bookshelf</a></li>
                <?php endif; ?>
            </ul>

            <!-- User Section -->
            <div class="navbar-user-section">
                <?php if (is_logged_in()): ?>
                    <a href="profile.php" class="navbar-item">Profile</a>
                    <a href="logout.php" class="navbar-item">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="navbar-item">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</head>

<body>
    <div class="main-container">
        <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="book-container">
            <div class="cover-section">
                <img
                    src="<?php echo htmlspecialchars($book['image_link']); ?>"
                    alt="<?php echo htmlspecialchars($book['title']); ?> cover"
                    class="cover-image">
            </div>

            <div class="details-section">
                <div class="details-box">
                    <h2>Details</h2>
                    <div class="detail-item">
                        <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Language:</strong> <?php echo htmlspecialchars($book['language']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Rating:</strong>
                        <span class="rating-stars">
                            <?php echo str_repeat('★', floor($book['ratings'])) . str_repeat('☆', 5 - floor($book['ratings'])); ?>
                        </span>
                        (<?php echo number_format($book['ratings'], 2); ?>)
                    </div>
                    <div class="detail-item description">
                        <strong>Description:</strong>
                        <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                    </div>
                    <?php if ($is_logged_in): ?>
                        <div class="rental-action-box">
                            <div class="bookmark-box">
                                <form action="toggle_bookmark.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" id="bookmarkButton">
                                        <?php echo $is_bookmarked ? 'Remove from Bookmarks' : 'Add to Bookmarks'; ?>
                                    </button>
                                </form>
                            </div>

                            <button id="openRentalPopup" class="rental-button">Rent This Book</button>
                        </div>
                        <div id="rentalPopup" class="popup">
                            <div class="popup-content">
                                <span class="close">&times;</span>
                                <h2>Rental Details</h2>

                                <div class="rental-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Format:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($book['type'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Daily Rate:</span>
                                        <span class="detail-value">$<?php echo number_format($daily_rate, 2); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Availability:</span>
                                        <span class="detail-value">
                                            <?php if ($available_copies > 0): ?>
                                                <?php echo $available_copies; ?> out of <?php echo $book['inventory']; ?> copies available
                                            <?php else: ?>
                                                Currently unavailable
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($available_copies > 0): ?>
                                    <form id="rentalForm" method="POST" class="rental-form">
                                        <input type="hidden" name="action" value="rent">

                                        <div class="form-group">
                                            <label for="duration">Rental Duration (days):</label>
                                            <input type="number" id="duration" name="duration" min="1" max="30" required>
                                        </div>

                                        <div id="costDisplay" class="cost-display" style="display: none;">
                                            <div class="detail-row">
                                                <span class="detail-label">Total Cost:</span>
                                                <span class="detail-value total-cost">$<span id="totalCost">0.00</span></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="pickup_location">Pickup Location:</label>
                                            <select id="pickup_location" name="pickup_location" required>
                                                <option value="">Select a pickup location</option>
                                                <?php foreach ($pickup_locations as $location): ?>
                                                    <option value="<?php echo $location['id']; ?>">
                                                        <?php echo htmlspecialchars($location['library_name'] . ' - ' . $location['address']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <button type="submit" class="submit-button">Reserve Now</button>
                                    </form>
                                <?php else: ?>
                                    <div class="availability-notice">
                                        <p>This book is currently unavailable.</p>
                                        <?php if ($earliest_available_date): ?>
                                            <p>Expected to be available after:<br>
                                                <strong><?php echo date('F j, Y', strtotime($earliest_available_date)); ?></strong>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <id="rentalPopup" class="popup">
                            <div class="popup-content">
                                <span class="close">&times;</span>
                                <h2>Rental Details</h2>

                                <div class="rental-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Format:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(ucfirst($book['type'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Daily Rate:</span>
                                        <span class="detail-value">$<?php echo number_format($daily_rate, 2); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Availability:</span>
                                        <span class="detail-value">
                                            <?php if ($available_copies > 0): ?>
                                                <?php echo $available_copies; ?> out of <?php echo $book['inventory']; ?> copies available
                                            <?php else: ?>
                                                Currently unavailable
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($available_copies > 0): ?>
                                    <form id="rentalForm" method="POST" class="rental-form">
                                        <input type="hidden" name="action" value="rent">

                                        <div class="form-group">
                                            <label for="duration">Rental Duration (days):</label>
                                            <input type="number" id="duration" name="duration" min="1" max="30" required>
                                        </div>

                                        <div id="costDisplay" class="cost-display" style="display: none;">
                                            <div class="detail-row">
                                                <span class="detail-label">Total Cost:</span>
                                                <span class="detail-value total-cost">$<span id="totalCost">0.00</span></span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="pickup_location">Pickup Location:</label>
                                            <select id="pickup_location" name="pickup_location" required>
                                                <option value="">Select a pickup location</option>
                                                <?php foreach ($pickup_locations as $location): ?>
                                                    <option value="<?php echo $location['id']; ?>">
                                                        <?php echo htmlspecialchars($location['library_name'] . ' - ' . $location['address']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <button type="submit" class="submit-button">Reserve Now</button>
                                    </form>
                                <?php else: ?>
                                    <div class="availability-notice">
                                        <p>This book is currently unavailable.</p>
                                        <?php if ($earliest_available_date): ?>
                                            <p>Expected to be available after:<br>
                                                <strong><?php echo date('F j, Y', strtotime($earliest_available_date)); ?></strong>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php else: ?>

                            <div class="rental-action-box">
                                <div class="login-prompt">
                                    <button class="ask-login-button" onclick="window.location.href='login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'">
                                        Login to Bookmark or Rent
                                    </button>
                                </div>
                            </div>

                        <?php endif; ?>
                </div>

            </div>

        </div>
        <div class="reviews-box">
            <h2>Reviews</h2>
            <div id="reviews-container">Loading reviews...</div>
        </div>
    </div>


    <script>
        document.getElementById('duration').addEventListener('input', function() {
            // Ensure the value is between 1 and 30
            this.value = Math.max(1, Math.min(30, parseInt(this.value) || 1));

            var duration = this.value;
            var dailyRate = <?php echo $daily_rate; ?>;
            var totalCost = (duration * dailyRate).toFixed(2);
            document.getElementById('totalCost').textContent = totalCost;
            document.getElementById('costDisplay').style.display = 'block';
        });

        // Simulate loading reviews
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.getElementById('reviews-container').innerHTML =
                    '<p>WIP.</p>';
            }, 1000);

            const openRentalPopup = document.getElementById('openRentalPopup');
            const rentalPopup = document.getElementById('rentalPopup');
            const closePopup = rentalPopup.querySelector('.close');
            const durationInput = document.getElementById('duration');
            const costDisplay = document.getElementById('costDisplay');
            const totalCostSpan = document.getElementById('totalCost');
            const dailyRate = <?php echo $daily_rate; ?>;

            function updateTotalCost() {
                const duration = parseInt(durationInput.value) || 0;
                if (duration > 0) {
                    const totalCost = (duration * dailyRate).toFixed(2);
                    totalCostSpan.textContent = totalCost;
                    costDisplay.style.display = 'block';
                } else {
                    costDisplay.style.display = 'none';
                }
            }

            openRentalPopup.onclick = function() {
                rentalPopup.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            closePopup.onclick = function() {
                rentalPopup.style.display = 'none';
                document.body.style.overflow = '';
            }

            window.onclick = function(event) {
                if (event.target == rentalPopup) {
                    rentalPopup.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            durationInput.addEventListener('input', function() {
                this.value = Math.max(1, Math.min(30, parseInt(this.value) || 1));
                updateTotalCost();
            });

            // Form submission handling
            const rentalForm = document.getElementById('rentalForm');
            if (rentalForm) {
                rentalForm.addEventListener('submit', function(e) {
                    const duration = parseInt(durationInput.value);
                    const pickup = document.getElementById('pickup_location').value;

                    if (!duration || duration < 1 || duration > 30) {
                        e.preventDefault();
                        alert('Please enter a valid rental duration between 1 and 30 days.');
                        return;
                    }

                    if (!pickup) {
                        e.preventDefault();
                        alert('Please select a pickup location.');
                        return;
                    }
                });
            }
        });
    </script>
</body>

</html>