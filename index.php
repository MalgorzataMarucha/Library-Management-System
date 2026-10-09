<?php
require_once 'connect.php';

$searchQuery = '';
$result = null;
$hasSearched = false;

if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $hasSearched = true;
    
    $stmt = $conn->prepare("SELECT * FROM library WHERE name LIKE ?");
    $searchTerm = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Library Finder</title>
</head>
<body>

<div class="container">
    <h2>Library Finder</h2>
    
    <form method="GET" action="">
        <div class="search-box">
            <input type="text" name="search" placeholder="Enter library name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </div>
    </form>

    <a href="add_library.php" class="btn btn-success">Add Library</a>
    
    <div class="results">
        <h3>Search Results</h3>
        
        <?php
        if (!$hasSearched) {
            echo "<p class='initial-msg'>Use the search bar above to find libraries.</p>";
        } else {
            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Library Name</th><th>Address</th><th>Opening Hours</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    $address = htmlspecialchars($row['address']);
                    $hours = htmlspecialchars($row['opening_time']) . " - " . htmlspecialchars($row['closing_time']);
                    echo "<tr>";
                    echo "<td><a href='library_info.php?id=" . $row['id_l'] . "'>" . htmlspecialchars($row['name']) . "</a></td>";
                    echo "<td>" . $address . "</td>";
                    echo "<td>" . $hours . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='no-results'>No libraries found matching your query.</p>";
            }
        }

        if (isset($conn)) {
            $conn->close();
        }
        ?>
    </div>
</div>

</body>
</html>