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

// Fetch reviews for this book
$review_stmt = $conn->prepare("
    SELECT 
        r.*,
        COALESCE(u.username, 'Deleted User') as username,
        COALESCE(u.first_name, '') as first_name,
        COALESCE(u.last_name, '') as last_name
    FROM review r 
    LEFT JOIN user u ON r.user_id = u.id 
    WHERE r.catalog_id = ? 
    ORDER BY r.id DESC
");
$review_stmt->bind_param("i", $id);
$review_stmt->execute();
$reviews = $review_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating from both catalog and review table
$avg_rating_stmt = $conn->prepare("
    SELECT 
        (c.ratings + COALESCE(SUM(r.rating), 0)) / (CASE WHEN c.ratings > 0 THEN 1 ELSE 0 END + COUNT(r.id)) AS avg_rating,
        (CASE WHEN c.ratings > 0 THEN 1 ELSE 0 END + COUNT(r.id)) AS total_ratings
    FROM 
        catalog c
    LEFT JOIN 
        review r ON c.id = r.catalog_id
    WHERE 
        c.id = ?
    GROUP BY
        c.id, c.ratings
");
$avg_rating_stmt->bind_param("i", $id);
$avg_rating_stmt->execute();
$rating_result = $avg_rating_stmt->get_result()->fetch_assoc();

$avg_rating = number_format($rating_result['avg_rating'], 1);
$total_ratings = $rating_result['total_ratings'] - 1;

// Update the book array with the new average rating
$book['ratings'] = $avg_rating;
$book['ratings_count'] = $total_ratings;

$is_logged_in = isset($_SESSION['user_id']);
$is_bookmarked = false;
$rental_status = false;

if ($is_logged_in) {
    // Check if the user has bookmarked this book
    $bookmark_stmt = $conn->prepare("SELECT id FROM bookmark WHERE user_id = ? AND catalog_id = ?");
    $bookmark_stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $bookmark_stmt->execute();
    $bookmark_result = $bookmark_stmt->get_result();
    $is_bookmarked = $bookmark_result->num_rows > 0;

    // Check if the user has an active rental
    $rental_check_stmt = $conn->prepare("SELECT status FROM request WHERE user_id = ? AND catalog_id = ? AND status IN ('Requested', 'Preparing', 'Ready', 'Collected') ORDER BY id DESC LIMIT 1");
    $rental_check_stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $rental_check_stmt->execute();
    $rental_result = $rental_check_stmt->get_result();
    if ($rental_result->num_rows > 0) {
        $rental_status = $rental_result->fetch_assoc()['status'];
    }
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
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/item.css">

    <script>
        // Immediately set the theme before the page renders
        (function() {
            let theme = localStorage.getItem('theme');

            // If no theme is saved, check system preference
            if (!theme) {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            // Apply theme immediately
            document.documentElement.setAttribute('data-theme', theme);

            // Optional: Add a class to body to indicate JS is loaded
            document.documentElement.classList.add('theme-loaded');
        })();
    </script>
</head>

<body>
    <!-- Sidebar Navbar (Left Sidebar) -->
    <div class="navbar">

        <!-- Logo/Title Section -->
        <div class="navbar-logo-section">
            <a href="index.php" class="navbar-logo">
                <img src="assets/logo/libriverse_logo.png" alt="Libriverse Logo" class="navbar-logo-image">
                <span class="navbar-logo-text">Libriverse</span>
            </a>
        </div>

        <!-- Pages Section -->
        <ul class="navbar-pages">
            <li><a href="index.php" class="navbar-item"><img src="assets/icons/home.svg" alt="Home" class="navbar-icon white"><span class="navbar-label">Home</span></a></li>
            <li><a href="discover.php" class="navbar-item"><img src="assets/icons/explore.svg" alt="Discover" class="navbar-icon white"><span class="navbar-label">Discover</span></a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="bookshelf.php" class="navbar-item"><img src="assets/icons/collections.svg" alt="Bookshelf" class="navbar-icon white"><span class="navbar-label">Bookshelf</span></a></li>
            <?php endif; ?>
        </ul>

        <!-- User Section -->
        <ul class="navbar-user-section">
            <?php if (is_logged_in()): ?>
                <!-- Profile photo and username -->
                <li><a href="profile.php" class="navbar-item username">
                        <img src="assets/profile-photo/golden_retriever.jpeg" alt="User Photo" class="navbar-user-photo">
                        <span class="navbar-username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    </a>
                </li>
                <!-- Icon Toggle -->
                <li class="icon-toggle">
                    <button id="themeToggle" class="navbar-item">
                        <img src="assets/icons/light-mode.svg" alt="Light Mode" class="navbar-icon theme-light">
                        <img src="assets/icons/dark-mode.svg" alt="Dark Mode" class="navbar-icon theme-dark">
                    </button>
                    <a href="logout.php" class="navbar-item logout">
                        <img src="assets/icons/logout.svg" alt="Logout" class="navbar-icon white logout">
                    </a>
                </li>
            <?php else: ?>
                <!-- Login link -->
                <li><a href="login.php" class="navbar-item">
                        <img src="assets/icons/login.svg" alt="Login" class="navbar-icon white">
                        <span class="navbar-label">Login</span>
                    </a></li>
            <?php endif; ?>
        </ul>
    </div>

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

                            <?php if ($rental_status): ?>
                                <button class="rental-button" onclick="window.location.href='bookshelf.php?search=<?php echo urlencode($book['title']); ?>'">
                                    View Rental Status
                                </button>
                            <?php else: ?>
                                <button id="openRentalPopup" class="rental-button">Rent This Book</button>
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

            <!-- Single Rental Popup -->
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

        </div>

        <div class="reviews-box">
            <h2>Reviews</h2>
            <div class="average-rating">
                <span class="rating-stars">
                    <?php echo str_repeat('★', floor($avg_rating)) . str_repeat('☆', 5 - floor($avg_rating)); ?>
                </span>
                <span class="rating-number"><?php echo $avg_rating; ?></span>
                <span class="total-ratings">(<?php echo $total_ratings; ?> reviews)</span>
            </div>
            <div id="reviews-container">
                <?php if (empty($reviews)): ?>
                    <p>No reviews yet. Be the first to review this book!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review">
                            <div class="review-header">
                                <span class="review-author">
                                    <?php
                                    echo htmlspecialchars(
                                        (trim($review['first_name']) && trim($review['last_name']))
                                            ? $review['first_name'] . ' ' . $review['last_name']
                                            : ($review['username'] !== 'Deleted User' ? $review['username'] : 'Anonymous')
                                    );
                                    ?>
                                </span>
                                <span class="review-rating">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </span>
                            </div>
                            <p class="review-text"><?php echo htmlspecialchars($review['review'] ?? ''); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listener to handle dark/light mode toggle

            const themeToggle = document.getElementById('themeToggle');

            // Check for saved theme preference or default to system preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }

            // Toggle theme
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });


            // Get elements - checking for null to avoid errors
            const openRentalPopup = document.getElementById('openRentalPopup');
            const rentalPopup = document.getElementById('rentalPopup');
            const durationInput = document.getElementById('duration');
            const costDisplay = document.getElementById('costDisplay');
            const totalCostSpan = document.getElementById('totalCost');
            const dailyRate = <?php echo $daily_rate; ?>;

            // Duration input event listener - only add if element exists
            if (durationInput) {
                durationInput.addEventListener('input', function() {
                    // Ensure the value is between 1 and 30
                    this.value = Math.max(1, Math.min(30, parseInt(this.value) || 1));
                    updateTotalCost();
                });
            }

            // Function to update total cost
            function updateTotalCost() {
                if (durationInput && costDisplay && totalCostSpan) {
                    const duration = parseInt(durationInput.value) || 0;
                    if (duration > 0) {
                        const totalCost = (duration * dailyRate).toFixed(2);
                        totalCostSpan.textContent = totalCost;
                        costDisplay.style.display = 'block';
                    } else {
                        costDisplay.style.display = 'none';
                    }
                }
            }

            // Popup handling - only add if elements exist
            if (openRentalPopup && rentalPopup) {
                const closePopup = rentalPopup.querySelector('.close');

                openRentalPopup.onclick = function() {
                    rentalPopup.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }

                if (closePopup) {
                    closePopup.onclick = function() {
                        rentalPopup.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                }

                window.onclick = function(event) {
                    if (event.target == rentalPopup) {
                        rentalPopup.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                }
            }

            // Form submission handling
            const rentalForm = document.getElementById('rentalForm');
            if (rentalForm && durationInput) {
                rentalForm.addEventListener('submit', function(e) {
                    const duration = parseInt(durationInput.value);
                    const pickup = document.getElementById('pickup_location')?.value;

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