<?php
require_once 'connect.php';

$library_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

if ($library_id > 0) {
    $lib_stmt = $conn->prepare("SELECT name FROM library WHERE id_l = ?");
    $lib_stmt->bind_param("i", $library_id);
    $lib_stmt->execute();
    $lib_result = $lib_stmt->get_result();
    if ($lib_result->num_rows === 0) {
        die("Library not found!");
    }
    $library = $lib_result->fetch_assoc();
} else {
    die("Invalid library ID!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $cover_type = trim($_POST['cover_type'] ?? '');
    $damage_status = trim($_POST['damage_status'] ?? '');
    $copies_count = intval($_POST['copies_count'] ?? 1);

    if (!empty($title) && !empty($author) && !empty($genre) && !empty($release_date) && !empty($cover_type) && !empty($damage_status) && $copies_count > 0) {
        
        $check_book = $conn->prepare("SELECT id_b FROM book WHERE title = ? AND author = ?");
        $check_book->bind_param("ss", $title, $author);
        $check_book->execute();
        $book_result = $check_book->get_result();

        if ($book_result->num_rows > 0) {
            $book_row = $book_result->fetch_assoc();
            $book_id = $book_row['id_b'];
        } else {
            $insert_book = $conn->prepare("INSERT INTO book (title, author, genre, release_date) VALUES (?, ?, ?, ?)");
            $insert_book->bind_param("ssss", $title, $author, $genre, $release_date);
            if ($insert_book->execute()) {
                $book_id = $conn->insert_id;
            } else {
                $error = "Error adding book: " . $conn->error;
            }
        }

        if (empty($error) && $book_id > 0) {
            $insert_copy = $conn->prepare("INSERT INTO copy (damage_status, cover_type, book_id, library_id, status_id) VALUES (?, ?, ?, ?, NULL)");
            
            for ($i = 0; $i < $copies_count; $i++) {
                $insert_copy->bind_param("ssii", $damage_status, $cover_type, $book_id, $library_id);
                $insert_copy->execute();
            }

            $update_lib = $conn->prepare("UPDATE library SET total_copies = total_copies + ? WHERE id_l = ?");
            $update_lib->bind_param("ii", $copies_count, $library_id);
            $update_lib->execute();

            header("Location: library_info.php?id=" . $library_id);
            exit();
        }

    } else {
        $error = "All fields are required and copies count must be greater than 0!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book to <?php echo htmlspecialchars($library['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #218838;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
            text-align: center;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="library_info.php?id=<?php echo $library_id; ?>" class="back-link">&larr; Back to Library Info</a>
    <h2>Add Book & Copies</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Target Library: <strong><?php echo htmlspecialchars($library['name']); ?></strong></p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" placeholder="e.g. The Hobbit" required>
        </div>

        <div class="form-group">
            <label for="author">Author</label>
            <input type="text" id="author" name="author" placeholder="e.g. J.R.R. Tolkien" required>
        </div>

        <div class="form-group">
            <label for="genre">Genre</label>
            <input type="text" id="genre" name="genre" placeholder="e.g. Fantasy" required>
        </div>

        <div class="form-group">
            <label for="release_date">Release Date</label>
            <input type="date" id="release_date" name="release_date" required>
        </div>

        <div class="form-group">
            <label for="cover_type">Cover Type</label>
            <select id="cover_type" name="cover_type" required>
                <option value="">-- Select cover type --</option>
                <option value="hardcover">Hardcover</option>
                <option value="paperback">Paperback</option>
            </select>
        </div>

        <div class="form-group">
            <label for="damage_status">Damage Status</label>
            <select id="damage_status" name="damage_status" required>
                <option value="">-- Select status --</option>
                <option value="intact">Intact</option>
                <option value="slightly damaged">Slightly damaged</option>
                <option value="worn out">Worn out</option>
            </select>
        </div>

        <div class="form-group">
            <label for="copies_count">Number of Copies</label>
            <input type="number" id="copies_count" name="copies_count" value="1" min="1" required>
        </div>

        <button type="submit" class="btn-submit">Add Books</button>
    </form>
</div>

</body>
</html>