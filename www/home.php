<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LibriVerse - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">

        <!-- Search Bar -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for books..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Filters -->
        <div class="filters">
            <form method="GET">
                <!-- Author Filter -->
                <label for="author">Author:</label>
                <select name="author">
                    <option value="">All Authors</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo $author['author']; ?>" <?php if ($selectedAuthor == $author['author']) echo 'selected'; ?>>
                            <?php echo $author['author']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Genre Filter -->
                <label for="genre">Genre:</label>
                <select name="genre">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo $genre['genre']; ?>" <?php if ($selectedGenre == $genre['genre']) echo 'selected'; ?>>
                            <?php echo $genre['genre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Language Filter -->
                <label for="language">Language:</label>
                <select name="language">
                    <option value="">All Languages</option>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language['language']; ?>" <?php if ($selectedLanguage == $language['language']) echo 'selected'; ?>>
                            <?php echo $language['language']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Type Filter -->
                <label for="type">Type:</label>
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo strtolower($type); ?>" <?php if ($selectedType == strtolower($type)) echo 'selected'; ?>>
                            <?php echo $type; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Apply Filters</button>
            </form>
        </div>

        <!-- Book Gallery -->
        <div class="book-gallery">
        </div>
    </div>
</body>
</html>