<?php
require_once 'connect.php';

$reader_id_param = isset($_GET['reader_id']) ? intval($_GET['reader_id']) : 0;
$error = '';

$readers_result = $conn->query("SELECT r.id_r, r.first_name, r.last_name, l.name as library_name FROM reader r JOIN library l ON r.library_id = l.id_l");
$readers = [];
while ($row = $readers_result->fetch_assoc()) {
    $readers[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reader_id = intval($_POST['reader_id'] ?? 0);
    $amount = trim($_POST['amount'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $is_paid = isset($_POST['is_paid']) ? 1 : 0;

    if ($reader_id > 0 && !empty($amount) && !empty($reason)) {
        $stmt = $conn->prepare("INSERT INTO fine (amount, is_paid, reason, reader_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("disi", $amount, $is_paid, $reason, $reader_id);
        
        if ($stmt->execute()) {
            header("Location: reader_profile.php?id=" . $reader_id);
            exit();
        } else {
            $error = "Error adding fine to the database.";
        }
    } else {
        $error = "All fields (Reader, Amount, Reason) are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Fine</title>
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
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        .form-group-checkbox input {
            width: auto;
        }
        .btn-submit {
            width: 100%;
            background: #28a745;
            color: #fff;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
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
    <a href="index.php" class="back-link">&larr; Back to Home</a>
    <h2>Add New Fine</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="reader_id">Select Reader</label>
            <select id="reader_id" name="reader_id" required>
                <option value="">-- Choose Reader --</option>
                <?php foreach ($readers as $reader): ?>
                    <option value="<?php echo $reader['id_r']; ?>" <?php echo ($reader_id_param == $reader['id_r']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($reader['first_name'] . ' ' . $reader['last_name'] . ' (' . $reader['library_name'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount ($)</label>
            <input type="number" step="0.01" id="amount" name="amount" required placeholder="e.g. 15.50">
        </div>

        <div class="form-group">
            <label for="reason">Reason (max 30 characters)</label>
            <input type="text" id="reason" name="reason" maxlength="30" required placeholder="e.g. late return">
        </div>

        <div class="form-group form-group-checkbox">
            <input type="checkbox" id="is_paid" name="is_paid" value="1">
            <label for="is_paid" style="margin-bottom: 0; cursor: pointer;">Is Paid</label>
        </div>

        <button type="submit" class="btn-submit">Add Fine</button>
    </form>
</div>

</body>
</html>