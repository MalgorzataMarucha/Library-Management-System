<?php
require_once 'connect.php';

$library_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$library = null;
$copy_count = 0;
$reader_count = 0;
$readers_result = null;
$copies_result = null;

if (isset($_GET['delete_copy_id']) && $library_id > 0) {
    $copy_to_delete = intval($_GET['delete_copy_id']);
    
    $check_stmt = $conn->prepare("SELECT s.is_borrowed FROM copy c LEFT JOIN status s ON c.id_c = s.copy_id WHERE c.id_c = ? AND c.library_id = ?");
    $check_stmt->bind_param("ii", $copy_to_delete, $library_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    
    if ($check_res->num_rows > 0) {
        $copy_data = $check_res->fetch_assoc();
        if (!isset($copy_data['is_borrowed']) || $copy_data['is_borrowed'] != 1) {
            $del_status = $conn->prepare("DELETE FROM status WHERE copy_id = ?");
            $del_status->bind_param("i", $copy_to_delete);
            $del_status->execute();
            
            $del_copy = $conn->prepare("DELETE FROM copy WHERE id_c = ? AND library_id = ?");
            $del_copy->bind_param("ii", $copy_to_delete, $library_id);
            $del_copy->execute();
        }
    }
    header("Location: library_info.php?id=" . $library_id . "&copies=show");
    exit();
}

$show_readers = isset($_GET['readers']) && $_GET['readers'] === 'show';
$show_copies = isset($_GET['copies'] ) && $_GET['copies'] === 'show';

if ($library_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM library WHERE id_l = ?");
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $library_result = $stmt->get_result();

    if ($library_result->num_rows > 0) {
        $library = $library_result->fetch_assoc();

        $reader_stmt = $conn->prepare("SELECT COUNT(*) as count FROM reader WHERE library_id = ?");
        $reader_stmt->bind_param("i", $library_id);
        $reader_stmt->execute();
        $reader_count = $reader_stmt->get_result()->fetch_assoc()['count'];

        $copy_stmt = $conn->prepare("SELECT COUNT(*) as count FROM copy WHERE library_id = ?");
        $copy_stmt->bind_param("i", $library_id);
        $copy_stmt->execute();
        $copy_count = $copy_stmt->get_result()->fetch_assoc()['count'];

        if ($show_readers) {
            $r_list_stmt = $conn->prepare("SELECT * FROM reader WHERE library_id = ?");
            $r_list_stmt->bind_param("i", $library_id);
            $r_list_stmt->execute();
            $readers_result = $r_list_stmt->get_result();
        }

        if ($show_copies) {
            $c_list_stmt = $conn->prepare("
                SELECT c.*, b.title, b.author, b.genre, b.release_date, 
                       s.is_borrowed, s.is_reserved 
                FROM copy c 
                JOIN book b ON c.book_id = b.id_b 
                LEFT JOIN status s ON c.id_c = s.copy_id 
                WHERE c.library_id = ?
            ");
            $c_list_stmt->bind_param("i", $library_id);
            $c_list_stmt->execute();
            $copies_result = $c_list_stmt->get_result();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $library ? htmlspecialchars($library['name']) . " - Library Info" : "Library Not Found"; ?></title>
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
        .btn-action { background: #17a2b8; }
        .btn-action:hover { background: #138496; }
        .btn-danger { background: #dc3545; padding: 5px 10px; font-size: 12px; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-danger:hover { background: #c82333; }
        .btn-edit-small { background: #007bff; padding: 5px 10px; font-size: 12px; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-edit-small:hover { background: #0056b3; }
        .action-buttons {
            display: flex;
            gap: 5px;
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
        .section-title {
            text-align: center;
            margin-top: 30px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container" style="width: 800px;">
    <a href="index.php" class="back-link">&larr; Back to Search</a>

    <?php if ($library): ?>
        <h2><?php echo htmlspecialchars($library['name']); ?></h2>
        
        <table>
            <tr>
                <th>Property</th>
                <th>Details</th>
            </tr>
            <tr>
                <td><strong>Name</strong></td>
                <td><?php echo htmlspecialchars($library['name']); ?></td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td><?php echo htmlspecialchars($library['address']); ?></td>
            </tr>
            <tr>
                <td><strong>Opening Time</strong></td>
                <td><?php echo htmlspecialchars($library['opening_time']); ?></td>
            </tr>
            <tr>
                <td><strong>Closing Time</strong></td>
                <td><?php echo htmlspecialchars($library['closing_time']); ?></td>
            </tr>
            <tr>
                <td><strong>Total Copies</strong></td>
                <td><?php echo $copy_count; ?></td>
            </tr>
            <tr>
                <td><strong>Total Readers</strong></td>
                <td><?php echo $reader_count; ?></td>
            </tr>
            <tr>
                <td><strong>Readers List</strong></td>
                <td>
                    <?php if ($show_readers): ?>
                        <a href="library_info.php?id=<?php echo $library_id; ?>">Hide readers</a>
                    <?php else: ?>
                        <a href="library_info.php?id=<?php echo $library_id; ?>&readers=show">Show readers</a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Copies List</strong></td>
                <td>
                    <?php if ($show_copies): ?>
                        <a href="library_info.php?id=<?php echo $library_id; ?>">Hide copies</a>
                    <?php else: ?>
                        <a href="library_info.php?id=<?php echo $library_id; ?>&copies=show">Show copies</a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php if ($show_readers): ?>
            <h3 class="section-title">Readers</h3>
            <table>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Profile</th>
                </tr>
                <?php if ($readers_result && $readers_result->num_rows > 0): ?>
                    <?php while ($reader = $readers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reader['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($reader['last_name']); ?></td>
                            <td><a href="reader_profile.php?id=<?php echo $reader['id_r']; ?>">Profile</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="no-results">No readers found for this library.</td></tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>

        <?php if ($show_copies): ?>
            <h3 class="section-title">Book Copies</h3>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Release Date</th>
                    <th>Rented</th>
                    <th>Reserved</th>
                    <th>Actions</th>
                </tr>
                <?php if ($copies_result && $copies_result->num_rows > 0): ?>
                    <?php while ($copy = $copies_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($copy['title'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($copy['author'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($copy['genre'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($copy['release_date'] ?? ''); ?></td>
                            <td><?php echo (isset($copy['is_borrowed']) && $copy['is_borrowed'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo (isset($copy['is_reserved']) && $copy['is_reserved'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_copy.php?id=<?php echo $copy['id_c']; ?>" class="btn-edit-small">Edit</a>
                                    <?php if (!isset($copy['is_borrowed']) || $copy['is_borrowed'] != 1): ?>
                                        <a href="library_info.php?id=<?php echo $library_id; ?>&copies=show&delete_copy_id=<?php echo $copy['id_c']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this copy?');">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="no-results">No book copies found for this library.</td></tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>

        <div class="actions">
            <a href="edit_library.php?id=<?php echo $library_id; ?>" class="btn btn-edit">Edit Library</a>
            <a href="add_copy.php?id=<?php echo $library_id; ?>" class="btn btn-action">Add Copy</a>
            <a href="add_reader.php?id=<?php echo $library_id; ?>" class="btn btn-action">Add Reader</a>
        </div>

    <?php else: ?>
        <h2>Library Not Found</h2>
        <div class="results">
            <p class="no-results">The requested library does not exist or has been removed.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>