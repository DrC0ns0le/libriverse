<?php
include '../utils/db.php';
include '../utils/auth.php';

// Ensure only admins can access this page
init_admin_session();

$message = '';
$image_upload_dir = '../assets/thumbnails/';
$pdf_upload_dir = '../assets/pdfs/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publisher = $_POST['publisher'];
    $genre = $_POST['genre'];
    $language = $_POST['language'];
    $ratings = $_POST['ratings'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $inventory = ($type === 'physical') ? $_POST['inventory'] : 0;

    // Handle file uploads
    $image_link = handle_file_upload('image_file', ['jpg', 'jpeg', 'png'], $image_upload_dir);
    $pdf_link = ($type === 'electronic') ? handle_file_upload('pdf_file', ['pdf'], $pdf_upload_dir) : null;

    if ($image_link === false || ($type === 'electronic' && $pdf_link === false)) {
        $message = "Error uploading files. Please try again.";
    } else {
        // Prepare SQL statement
        $sql = "INSERT INTO catalog (title, author, publisher, genre, language, ratings, description, type, price, inventory, pdf_link, image_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdssdiss", $title, $author, $publisher, $genre, $language, $ratings, $description, $type, $price, $inventory, $pdf_link, $image_link);

        if ($stmt->execute()) {
            $message = "New book added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();

function handle_file_upload($file_input_name, $allowed_extensions, $upload_dir)
{
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $file_tmp = $_FILES[$file_input_name]['tmp_name'];
        $file_name = $_FILES[$file_input_name]['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $upload_path)) {
                return $upload_path;
            }
        }
        return false;
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Admin</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="css/add_book.css">
</head>

<body>
    <div class="admin-form">
        <h1>Add New Book</h1>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-layout">
                <!-- Left Column - Basic Information -->
                <div class="form-column">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" required>
                    </div>

                    <div class="form-group">
                        <label for="publisher">Publisher</label>
                        <input type="text" id="publisher" name="publisher">
                    </div>

                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <input type="text" id="genre" name="genre">
                    </div>

                    <div class="form-group">
                        <label for="language">Language</label>
                        <input type="text" id="language" name="language">
                    </div>
                </div>

                <!-- Right Column - Additional Details -->
                <div class="form-column">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ratings">Ratings (0-5)</label>
                        <input type="number" id="ratings" name="ratings" step="0.01" min="0" max="5">
                    </div>

                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required onchange="toggleFields()">
                            <option value="physical">Physical</option>
                            <option value="electronic">Electronic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>

                    <div class="form-group" id="inventoryGroup">
                        <label for="inventory">Inventory *</label>
                        <input type="number" id="inventory" name="inventory" required>
                    </div>
                </div>
            </div>

            <!-- Bottom Section - File Uploads -->
            <div class="upload-section">
                <div class="form-group" id="pdfGroup" style="display:none;">
                    <label for="pdf_file">PDF File *</label>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf">
                </div>

                <div class="form-group">
                    <label for="image_file">Cover Image *</label>
                    <input type="file" id="image_file" name="image_file" accept="image/*" required>
                </div>
            </div>

            <div class="submit-section">
                <input type="submit" value="Add Book">
            </div>
        </form>
    </div>

    <script>
        function toggleFields() {
            var type = document.getElementById('type').value;
            var inventoryGroup = document.getElementById('inventoryGroup');
            var pdfGroup = document.getElementById('pdfGroup');
            var pdfFile = document.getElementById('pdf_file');
            var inventory = document.getElementById('inventory');

            if (type === 'physical') {
                inventoryGroup.style.display = 'block';
                pdfGroup.style.display = 'none';
                pdfFile.required = false;
                inventory.required = true;
            } else {
                inventoryGroup.style.display = 'none';
                pdfGroup.style.display = 'block';
                pdfFile.required = true;
                inventory.required = false;
            }
        }

        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>

</html>