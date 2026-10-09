<?php
require_once 'connect.php';

$copy_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

if ($copy_id > 0) {
    $stmt = $conn->prepare("
        SELECT c.*, b.title, b.author, l.id_l as library_id, l.name as library_name,
               s.id_s, s.is_borrowed, s.is_reserved, s.start_date, s.end_date, s.reader_id,
               r.first_name, r.last_name
        FROM copy c 
        JOIN book b ON c.book_id = b.id_b 
        JOIN library l ON c.library_id = l.id_l 
        LEFT JOIN status s ON c.id_c = s.copy_id
        LEFT JOIN reader r ON s.reader_id = r.id_r
        WHERE c.id_c = ?
    ");
    $stmt->bind_param("i", $copy_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Copy not found!");
    }
    $copy = $result->fetch_assoc();
} else {
    die("Invalid copy ID!");
}

$readers_stmt = $conn->prepare("SELECT id_r, first_name, last_name FROM reader WHERE library_id = ?");
$readers_stmt->bind_param("i", $copy['library_id']);
$readers_stmt->execute();
$readers_result = $readers_stmt->get_result();
$readers = [];
while ($row = $readers_result->fetch_assoc()) {
    $readers[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_type = trim($_POST['cover_type'] ?? '');
    $damage_status = trim($_POST['damage_status'] ?? '');
    $status_type = trim($_POST['status_type'] ?? 'none'); 
    $reader_id = intval($_POST['reader_id'] ?? 0);
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if (!empty($cover_type) && !empty($damage_status)) {
        $update_copy = $conn->prepare("UPDATE copy SET cover_type = ?, damage_status = ? WHERE id_c = ?");
        $update_copy->bind_param("ssi", $cover_type, $damage_status, $copy_id);
        $update_copy->execute();

        if ($status_type === 'none') {
            $delete_status = $conn->prepare("DELETE FROM status WHERE copy_id = ?");
            $delete_status->bind_param("i", $copy_id);
            $delete_status->execute();
        } else {
            $is_borrowed = ($status_type === 'borrowed') ? 1 : 0;
            $is_reserved = ($status_type === 'reserved') ? 1 : 0;

            if ($reader_id > 0 && !empty($start_date) && !empty($end_date)) {
                if ($copy['id_s']) {
                    $update_status = $conn->prepare("UPDATE status SET is_borrowed = ?, is_reserved = ?, start_date = ?, end_date = ?, reader_id = ? WHERE copy_id = ?");
                    $update_status->bind_param("iissii", $is_borrowed, $is_reserved, $start_date, $end_date, $reader_id, $copy_id);
                    $update_status->execute();
                } else {
                    $insert_status = $conn->prepare("INSERT INTO status (is_borrowed, is_reserved, start_date, end_date, copy_id, reader_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_status->bind_param("iissii", $is_borrowed, $is_reserved, $start_date, $end_date, $copy_id, $reader_id);
                    $insert_status->execute();
                }
            } else {
                $error = "Reader, start date, and end date are required when active status is selected!";
            }
        }

        if (empty($error)) {
            header("Location: library_info.php?id=" . $copy['library_id']);
            exit();
        }
    } else {
        $error = "Cover type and damage status are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Copy #<?php echo $copy_id; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 550px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
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
        .form-group select, .form-group input {
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
        .status-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="library_info.php?id=<?php echo $copy['library_id']; ?>" class="back-link">&larr; Back to Library Info</a>
    <h2>Edit Copy & Status</h2>
    <div class="subtitle">
        Book: <strong><?php echo htmlspecialchars($copy['title']); ?></strong> (<?php echo htmlspecialchars($copy['author']); ?>)<br>
        Library: <strong><?php echo htmlspecialchars($copy['library_name']); ?></strong>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="cover_type">Cover Type</label>
            <select id="cover_type" name="cover_type" required>
                <option value="hardcover" <?php echo ($copy['cover_type'] === 'hardcover') ? 'selected' : ''; ?>>Hardcover</option>
                <option value="paperback" <?php echo ($copy['cover_type'] === 'paperback') ? 'selected' : ''; ?>>Paperback</option>
            </select>
        </div>

        <div class="form-group">
            <label for="damage_status">Damage Status</label>
            <select id="damage_status" name="damage_status" required>
                <option value="intact" <?php echo ($copy['damage_status'] === 'intact') ? 'selected' : ''; ?>>Intact</option>
                <option value="slightly damaged" <?php echo ($copy['damage_status'] === 'slightly damaged') ? 'selected' : ''; ?>>Slightly damaged</option>
                <option value="worn out" <?php echo ($copy['damage_status'] === 'worn out') ? 'selected' : ''; ?>>Worn out</option>
            </select>
        </div>

        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">

        <h3>Status & Assignment</h3>
        <div class="status-box">
            <?php 
                $current_status_type = 'none';
                if ($copy['is_borrowed'] == 1) {
                    $current_status_type = 'borrowed';
                    echo "Current Status: <strong>Borrowed</strong> by " . htmlspecialchars($copy['first_name'] . ' ' . $copy['last_name']) . " (" . $copy['start_date'] . " to " . $copy['end_date'] . ")";
                } elseif ($copy['is_reserved'] == 1) {
                    $current_status_type = 'reserved';
                    echo "Current Status: <strong>Reserved</strong> by " . htmlspecialchars($copy['first_name'] . ' ' . $copy['last_name']) . " (" . $copy['start_date'] . " to " . $copy['end_date'] . ")";
                } else {
                    echo "Current Status: <strong>Not borrowed / Not reserved (No)</strong>";
                }
            ?>
        </div>

        <div class="form-group">
            <label for="status_type">Change Status Type</label>
            <select id="status_type" name="status_type">
                <option value="none" <?php echo ($current_status_type === 'none') ? 'selected' : ''; ?>>No (Available / Neither)</option>
                <option value="borrowed" <?php echo ($current_status_type === 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                <option value="reserved" <?php echo ($current_status_type === 'reserved') ? 'selected' : ''; ?>>Reserved</option>
            </select>
        </div>

        <div class="form-group">
            <label for="reader_id">Select Reader (from this library)</label>
            <select id="reader_id" name="reader_id">
                <option value="">-- Select Reader --</option>
                <?php foreach ($readers as $reader): ?>
                    <option value="<?php echo $reader['id_r']; ?>" <?php echo ($copy['reader_id'] == $reader['id_r']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($reader['first_name'] . ' ' . $reader['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($copy['start_date'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($copy['end_date'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn-submit">Update Copy & Status</button>
    </form>
</div>

</body>
</html>