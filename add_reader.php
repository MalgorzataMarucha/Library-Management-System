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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    if (!empty($first_name) && !empty($last_name)) {
        $stmt = $conn->prepare("INSERT INTO reader (first_name, last_name, library_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $first_name, $last_name, $library_id);

        if ($stmt->execute()) {
            header("Location: library_info.php?id=" . $library_id);
            exit();
        } else {
            $error = "Error adding reader: " . $conn->error;
        }
    } else {
        $error = "All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reader to <?php echo htmlspecialchars($library['name']); ?></title>
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
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            background: #17a2b8;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #138496;
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
    <h2>Add Reader</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Target Library: <strong><?php echo htmlspecialchars($library['name']); ?></strong></p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required>
        </div>

        <button type="submit" class="btn-submit">Add Reader</button>
    </form>
</div>

</body>
</html>