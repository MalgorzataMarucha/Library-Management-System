<?php
require_once 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $opening_time = trim($_POST['opening_time'] ?? '');
    $closing_time = trim($_POST['closing_time'] ?? '');
    $total_copies = 0; 

    if (!empty($name) && !empty($address) && !empty($opening_time) && !empty($closing_time)) {
        $stmt = $conn->prepare("INSERT INTO library (name, address, opening_time, closing_time, total_copies) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $address, $opening_time, $closing_time, $total_copies);

        if ($stmt->execute()) {
            $new_library_id = $conn->insert_id;
            
            header("Location: library_info.php?id=" . $new_library_id);
            exit();
        } else {
            $error = "Error adding library: " . $conn->error;
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
    <title>Add Library</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
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
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #0056b3;
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
    <a href="index.php" class="back-link">&larr; Back to Search</a>
    <h2>Add Library</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Library Name</label>
            <input type="text" id="name" name="name" placeholder="Enter library name" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Enter library address" required>
        </div>

        <div class="form-group">
            <label for="opening_time">Opening Time</label>
            <input type="time" id="opening_time" name="opening_time" required>
        </div>

        <div class="form-group">
            <label for="closing_time">Closing Time</label>
            <input type="time" id="closing_time" name="closing_time" required>
        </div>

        <button type="submit" class="btn-submit">Add Library</button>
    </form>
</div>

</body>
</html>