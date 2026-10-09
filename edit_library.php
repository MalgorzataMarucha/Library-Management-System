<?php
require_once 'connect.php';

$library_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

if ($library_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM library WHERE id_l = ?");
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Library not found!");
    }
    $library = $result->fetch_assoc();
} else {
    die("Invalid library ID!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $opening_time = trim($_POST['opening_time'] ?? '');
    $closing_time = trim($_POST['closing_time'] ?? '');

    if (!empty($name) && !empty($address) && !empty($opening_time) && !empty($closing_time)) {
        $update_stmt = $conn->prepare("UPDATE library SET name = ?, address = ?, opening_time = ?, closing_time = ? WHERE id_l = ?");
        $update_stmt->bind_param("ssssi", $name, $address, $opening_time, $closing_time, $library_id);

        if ($update_stmt->execute()) {
            header("Location: library_info.php?id=" . $library_id);
            exit();
        } else {
            $error = "Error updating library: " . $conn->error;
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
    <title>Edit Library - <?php echo htmlspecialchars($library['name']); ?></title>
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
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-submit:hover {
            background: #e0a800;
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
    <h2>Edit Library</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Library Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($library['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($library['address']); ?>" required>
        </div>

        <div class="form-group">
            <label for="opening_time">Opening Time</label>
            <input type="time" id="opening_time" name="opening_time" value="<?php echo htmlspecialchars($library['opening_time']); ?>" required>
        </div>

        <div class="form-group">
            <label for="closing_time">Closing Time</label>
            <input type="time" id="closing_time" name="closing_time" value="<?php echo htmlspecialchars($library['closing_time']); ?>" required>
        </div>

        <button type="submit" class="btn-submit">Update Library</button>
    </form>
</div>

</body>
</html>