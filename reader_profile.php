<?php
require_once 'connect.php';

$reader_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$reader = null;
$fines_result = null;
$borrowed_result = null;
$reserved_result = null;

if ($reader_id > 0) {
    $stmt = $conn->prepare("SELECT r.*, l.name as library_name FROM reader r JOIN library l ON r.library_id = l.id_l WHERE r.id_r = ?");
    $stmt->bind_param("i", $reader_id);
    $stmt->execute();
    $reader_result = $stmt->get_result();

    if ($reader_result->num_rows > 0) {
        $reader = $reader_result->fetch_assoc();

        $fines_stmt = $conn->prepare("SELECT * FROM fine WHERE reader_id = ?");
        $fines_stmt->bind_param("i", $reader_id);
        $fines_stmt->execute();
        $fines_result = $fines_stmt->get_result();

        $borrowed_stmt = $conn->prepare("
            SELECT b.title, b.author, s.start_date, s.end_date 
            FROM status s 
            JOIN copy c ON s.copy_id = c.id_c 
            JOIN book b ON c.book_id = b.id_b 
            WHERE s.reader_id = ? AND s.is_borrowed = 1
        ");
        $borrowed_stmt->bind_param("i", $reader_id);
        $borrowed_stmt->execute();
        $borrowed_result = $borrowed_stmt->get_result();

        $reserved_stmt = $conn->prepare("
            SELECT b.title, b.author, s.start_date, s.end_date 
            FROM status s 
            JOIN copy c ON s.copy_id = c.id_c 
            JOIN book b ON c.book_id = b.id_b 
            WHERE s.reader_id = ? AND s.is_reserved = 1
        ");
        $reserved_stmt->bind_param("i", $reader_id);
        $reserved_stmt->execute();
        $reserved_result = $reserved_stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $reader ? htmlspecialchars($reader['first_name'] . " " . $reader['last_name']) . " - Reader Profile" : "Reader Not Found"; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        .btn-edit { background: #007bff; }
        .btn-edit:hover { background: #0056b3; }
        .btn-action { background: #28a745; }
        .btn-action:hover { background: #218838; }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .section-title {
            text-align: center;
            margin-top: 30px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container" style="width: 750px;">
    <?php if ($reader): ?>
        <a href="library_info.php?id=<?php echo $reader['library_id']; ?>" class="back-link">&larr; Back to Library Info</a>
        <h2>Reader Profile</h2>
        
        <table>
            <tr>
                <th>Property</th>
                <th>Details</th>
            </tr>
            <tr>
                <td><strong>First Name</strong></td>
                <td><?php echo htmlspecialchars($reader['first_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Last Name</strong></td>
                <td><?php echo htmlspecialchars($reader['last_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Library</strong></td>
                <td><?php echo htmlspecialchars($reader['library_name']); ?></td>
            </tr>
        </table>

        <div class="actions">
            <a href="edit_reader.php?id=<?php echo $reader_id; ?>" class="btn btn-edit">Edit Reader</a>
        </div>

        <h3 class="section-title">Fines</h3>
        <table>
            <tr>
                <th>Reason</th>
                <th>Amount ($)</th>
                <th>Is Paid</th>
            </tr>
            <?php if ($fines_result && $fines_result->num_rows > 0): ?>
                <?php while ($fine = $fines_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fine['reason']); ?></td>
                        <td><?php echo htmlspecialchars($fine['amount']); ?></td>
                        <td><?php echo $fine['is_paid'] == 1 ? 'Yes' : 'No'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="no-results">No fines recorded for this reader.</td></tr>
            <?php endif; ?>
        </table>

        <div class="actions" style="margin-top: 15px;">
            <a href="add_fine.php?reader_id=<?php echo $reader_id; ?>" class="btn btn-action">Add Fine</a>
        </div>

        <h3 class="section-title">Borrowed Books</h3>
        <?php if ($borrowed_result && $borrowed_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
                <?php while ($book = $borrowed_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($book['end_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="results" style="margin-top: 10px;">
                <p class="initial-msg" style="margin: 0;">No currently borrowed books for this reader.</p>
            </div>
        <?php endif; ?>

        <h3 class="section-title">Reserved Books</h3>
        <?php if ($reserved_result && $reserved_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Reservation Date</th>
                </tr>
                <?php while ($book = $reserved_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['start_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="results" style="margin-top: 10px;">
                <p class="initial-msg" style="margin: 0;">No reserved books for this reader.</p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <h2>Reader Not Found</h2>
        <div class="results">
            <p class="no-results">The requested reader does not exist or has been removed.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>