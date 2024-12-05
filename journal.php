<?php
/**
 * Trading Journal
 * Copyright (c) 2024 @edimonndi
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

// Database connection (update credentials as needed)
$conn = new mysqli('localhost', 'root', 'mysql', 'trade_journal');

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Something went wrong. Please try again later.</p>";
    exit;
}

// Handle form submission for adding or updating an entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_entry'])) {
    $date = $_POST['date'];
    $trade = $_POST['trade'];
    $strategy = $_POST['strategy'];
    $mistakes = $_POST['mistakes'];
    $tags = $_POST['tags'];
    $win = isset($_POST['win']) ? 1 : 0;
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Update existing entry
        $stmt = $conn->prepare('UPDATE entries SET date=?, trade=?, strategy=?, mistakes=?, tags=?, win=? WHERE id=?');
        $stmt->bind_param('sssssis', $date, $trade, $strategy, $mistakes, $tags, $win, $id);
    } else {
        // Add new entry
        $stmt = $conn->prepare('INSERT INTO entries (date, trade, strategy, mistakes, tags, win) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssi', $date, $trade, $strategy, $mistakes, $tags, $win);
    }

    if ($stmt->execute()) {
        header("Location: journal.php?message=success");
        exit;
    } else {
        error_log("Error saving entry: " . $stmt->error);
        echo '<script>alert("Error saving entry. Please try again.");</script>';
    }
    $stmt->close();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM entries WHERE id = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        header("Location: journal.php?message=deleted");
        exit();
    } else {
        error_log("Error deleting entry: " . $stmt->error);
        echo '<script>alert("Error deleting entry. Please try again.");</script>';
    }
    $stmt->close();
}

// Search and sort functionality
$search_query = '';
$sort_by = $_GET['sort'] ?? 'date';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM entries WHERE trade LIKE ? OR strategy LIKE ? OR tags LIKE ? ORDER BY date DESC");
    $like_query = "%$search_query%";
    $stmt->bind_param('sss', $like_query, $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM entries ORDER BY $sort_by DESC");
    $stmt->execute();
    $result = $stmt->get_result();
}

// Statistics
$total_entries_query = $conn->query('SELECT COUNT(*) as count FROM entries');
$total_entries = $total_entries_query->fetch_assoc()['count'];
$wins_query = $conn->query('SELECT COUNT(*) as count FROM entries WHERE win=1');
$wins = $wins_query->fetch_assoc()['count'];
$win_rate = $total_entries > 0 ? round(($wins / $total_entries) * 100, 2) : 0;

// Export functionality
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="trading_journal.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Trade', 'Strategy', 'Mistakes', 'Tags', 'Win']);
    $entries = $conn->query('SELECT * FROM entries');
    while ($row = $entries->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
        }
        .footer a {
            color: lightblue; /* tomato color */
            text-decoration: solid #000;
        }
    </style>

</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Trade Journal</h1>

    <!-- Add/Edit Form -->
    <form method="POST" class="my-4">
        <input type="hidden" id="id" name="id">
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="mb-3">
            <label for="trade" class="form-label">Trade</label>
            <textarea class="form-control" id="trade" name="trade" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label for="strategy" class="form-label">Strategy</label>
            <textarea class="form-control" id="strategy" name="strategy" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label for="mistakes" class="form-label">Mistakes</label>
            <textarea class="form-control" id="mistakes" name="mistakes" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label>
            <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g., Scalping, Swing Trading">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="win" name="win">
            <label for="win" class="form-check-label">Win</label>
        </div>
        <button type="submit" class="btn btn-primary" name="save_entry">Save Entry</button>
    </form>

    <!-- Search and Export -->
    <form class="d-flex mb-4" method="GET">
        <input class="form-control me-2" type="search" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search_query); ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
        <a href="?export=true" class="btn btn-outline-info ms-2">Export CSV</a>
    </form>

    <!-- Statistics -->
    <div class="mb-4">
        <h5>Statistics:</h5>
        <p style="color: green;font-weight: bold;">Total Entries: <?php echo $total_entries; ?></p>
        <p style="color: red;font-weight: bold;">Wins: <?php echo $wins;?></p>
        <p style="color: blue;font-weight: bold;">Win Rate: <?php echo $win_rate; ?>%</p>

        <canvas id="myChart" width="200" height="50"></canvas>
    </div>

    <!-- Journal Entries -->
    <h2 class="mt-5">Journal Entries</h2>
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th><a href="?sort=date">Date</a></th>
                    <th><a href="?sort=trade">Trade</a></th>
                    <th><a href="?sort=strategy">Strategy</a></th>
                    <th>Mistakes</th>
                    <th>Tags</th>
                    <th>Win</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row["date"]; ?></td>
                        <td><?php echo htmlspecialchars($row["trade"]); ?></td>
                        <td><?php echo htmlspecialchars($row["strategy"]); ?></td>
                        <td><?php echo htmlspecialchars($row["mistakes"]); ?></td>
                        <td><?php echo htmlspecialchars($row["tags"]); ?></td>
                        <td><?php echo $row["win"] ? "Yes" : "No"; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn" 
                                data-id="<?php echo $row["id"]; ?>" 
                                data-date="<?php echo $row["date"]; ?>" 
                                data-trade="<?php echo htmlspecialchars($row["trade"]); ?>" 
                                data-strategy="<?php echo htmlspecialchars($row["strategy"]); ?>" 
                                data-mistakes="<?php echo htmlspecialchars($row["mistakes"]); ?>" 
                                data-tags="<?php echo htmlspecialchars($row["tags"]); ?>" 
                                data-win="<?php echo $row["win"]; ?>">
                                Edit
                            </button>
                            <a href="?delete=<?php echo $row["id"]; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this entry?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No entries found.</p>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const date = this.dataset.date;
            const trade = this.dataset.trade;
            const strategy = this.dataset.strategy;
            const mistakes = this.dataset.mistakes;
            const tags = this.dataset.tags;
            const win = this.dataset.win;

            document.getElementById('id').value = id;
            document.getElementById('date').value = date;
            document.getElementById('trade').value = trade;
            document.getElementById('strategy').value = strategy;
            document.getElementById('mistakes').value = mistakes;
            document.getElementById('tags').value = tags;
            document.getElementById('win').checked = win === '1';
        });
    });
</script>

<br>
<br>
<br>
<br>
<br>

<!-- Adding Chart.js Integration -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total Entries', 'Wins'], // Labels for the bars
        datasets: [{
            label: 'Statistics',
            data: [<?php echo $total_entries; ?>, <?php echo $wins; ?>], // PHP variables
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Trading Journal Statistics'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</canvas>



    <div class="footer">
        <p>&copy; 2024 <a href="https://github.com/edimonndi" target="_blank">@edimonndi</a> | Made with <span style="color:red;">♥</span> by <a href="https://twitter.com/edimonndi" target="_blank">@edimonndi</a></p>
    </div>


</body>
</html>
