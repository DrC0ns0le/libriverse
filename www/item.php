<?php
include 'utils/db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT c.*, COUNT(r.id) as active_rentals 
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
    <link rel="stylesheet" href="styles.css">
</head>

<body>
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
            </div>

            <div class="bookmark-box">
                <?php if ($is_logged_in): ?>
                    <button onclick="toggleBookmark(<?php echo $book['id']; ?>)" id="bookmarkButton">
                        <?php echo $is_bookmarked ? 'Remove from Bookmarks' : 'Add to Bookmarks'; ?>
                    </button>
                <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="button">Login to Bookmark</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rental-box">
        <h2>Rental Details</h2>
        <div class="detail-item">
            <strong>Format:</strong> <?php echo htmlspecialchars($book['type']); ?>
        </div>
        <div class="detail-item">
            <strong>Daily Rate:</strong> $<?php echo number_format($daily_rate, 2); ?>
        </div>
        <div class="detail-item">
            <strong>Availability:</strong>
            <?php if ($available_copies > 0): ?>
                <?php echo $available_copies; ?> out of <?php echo $book['inventory']; ?> copies available
            <?php else: ?>
                Currently unavailable
            <?php endif; ?>
        </div>
        <?php if ($available_copies <= 0 && $earliest_available_date): ?>
            <div class="detail-item">
                <strong>Earliest Available Date:</strong> <?php echo date('F j, Y', strtotime($earliest_available_date)); ?>
            </div>
        <?php endif; ?>

        <?php if ($available_copies > 0): ?>
            <form id="rentalForm" method="POST">
                <input type="hidden" name="action" value="rent">
                <div class="form-group">
                    <label for="duration">Rental Duration (days):</label>
                    <input type="number" id="duration" name="duration" min="1" max="30" required>
                </div>

                <div id="costDisplay" style="display: none;">
                    <p><strong>Total Cost:</strong> $<span id="totalCost"></span></p>
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

                <button type="submit" class="button reserve-button">Reserve Now</button>
            </form>
        <?php else: ?>
            <button class="button reserve-button" disabled>Not Available</button>
            <p>You can try renting this book after <?php echo date('F j, Y', strtotime($earliest_available_date)); ?></p>
        <?php endif; ?>
    </div>

    <div class="reviews-box">
        <h2>Reviews</h2>
        <div id="reviews-container">Loading reviews...</div>
    </div>

    <script>
        function toggleBookmark(bookId) {
            fetch('toggle_bookmark.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'book_id=' + bookId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const button = document.getElementById('bookmarkButton');
                        if (data.bookmarked) {
                            button.textContent = 'Remove from Bookmarks';
                            alert('Book added to bookmarks!');
                        } else {
                            button.textContent = 'Add to Bookmarks';
                            alert('Book removed from bookmarks!');
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the bookmark.');
                });
        }

        function rentBook(bookId) {
            // Add your rental functionality here
            alert('Rental process started!');
        }

        // Simulate loading reviews
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.getElementById('reviews-container').innerHTML =
                    '<p>WIP.</p>';
            }, 1000);
        });

        document.getElementById('duration').addEventListener('input', function() {
            var duration = this.value;
            var dailyRate = <?php echo $daily_rate; ?>;
            var totalCost = (duration * dailyRate).toFixed(2);
            document.getElementById('totalCost').textContent = totalCost;
            document.getElementById('costDisplay').style.display = 'block';
        });
    </script>
</body>

</html>