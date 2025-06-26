<?php
// File to store entries
$data_file = __DIR__ . '/data_collection_tools.csv';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle delete
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $entries = [];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle edit save
    if (isset($_POST['save_edit']) && isset($_POST['index'])) {
        $entries = [];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        $faculty = $_POST['faculty'] ?? '';
        $degree = $_POST['degree'] ?? '';
        $sex = $_POST['sex'] ?? '';
        $title = $_POST['title'] ?? '';
        $ownership = $_POST['ownership'] ?? '';
        $presented = $_POST['presented'] ?? '';
        $published = $_POST['published'] ?? '';
        $journal = $_POST['journal'] ?? '';
        if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
            $entries[$index] = [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal];
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle add
    $faculty = $_POST['faculty'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $title = $_POST['title'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $presented = $_POST['presented'] ?? '';
    $published = $_POST['published'] ?? '';
    $journal = $_POST['journal'] ?? '';
    if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
        $entry = [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['Dr. Sarah Johnson', 'Ph.D.', 'Female', 'Machine Learning Applications in Healthcare', 'Author', '05,15,25 - International AI Conference', '2025-04-20', 'Journal of Medical Informatics'],
    ['Prof. Michael Chen', 'Ph.D.', 'Male', 'Sustainable Energy Systems in Urban Planning', 'Co-Author', '04,22,25 - Green Cities Summit', '2025-03-15', 'Environmental Engineering Review'],
    ['Dr. Emily Rodriguez', 'Ph.D.', 'Female', 'Educational Technology Impact on Student Learning', 'Author', '03,10,25 - EdTech Innovation Forum', '2025-02-28', 'Educational Technology Research'],
];

// On first load, if CSV is empty, populate with default entries
if (!file_exists($data_file) || filesize($data_file) === 0) {
    $fp = fopen($data_file, 'w');
    foreach ($default_entries as $entry) {
        fputcsv($fp, $entry);
    }
    fclose($fp);
}

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    while ($row = fgetcsv($fp)) {
        $entries[] = $row;
    }
    fclose($fp);
}

// Check if editing
$edit_index = null;
$edit_entry = null;
if (isset($_GET['edit'])) {
    $edit_index = (int)$_GET['edit'];
    if (isset($entries[$edit_index])) {
        $edit_entry = $entries[$edit_index];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Collection Tools</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/data collection tools.css">
  <style>
    .edit-entry-form { background: #f1f1f1; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="../pics/rso-bg.png" alt="UC Logo">
      UC RSO
    </div>
    <nav>
      <a href="../index.php">Dashboard</a>
      <a href="Research  Capacity Buildings Activities.php">Research Capacity Building</a>
      <a href="Data Collection Tools.php" class="active">Data Collection Tools</a>
      <a href="Ethicss Reviewed Protocols.php">Ethics Reviewed Protocols</a>
      <a href="Publication and Presentation.php">Publications and Presentations</a>
      <a href="KPI records.php">KPI Records</a>
    </nav>
    <button class="login-btn" onclick="window.location.href='loginpage.php'">Login</button>
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Data Collection Tools</h1>
      <div class="subtitle">Manage faculty research publications and presentation records</div>
      <div class="actions">
        <button class="btn upload">&#8682; Upload Excel File</button>
        <button class="btn add" id="showAddForm">+ Add New Entry</button>
      </div>
      <form class="add-entry-form" id="addEntryForm" method="post" action="" style="display:none; background:#f9f9f9; padding:20px; border-radius:8px; margin-bottom:20px;">
        <label>Name of Faculty:<br><input type="text" name="faculty" required></label><br>
        <label>Degree:<br><input type="text" name="degree" required></label><br>
        <label>Sex:<br>
          <select name="sex" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </label><br>
        <label>Research Title:<br><input type="text" name="title" required></label><br>
        <label>Ownership:<br>
          <select name="ownership" required>
            <option value="Author">Author</option>
            <option value="Co-Author">Co-Author</option>
          </select>
        </label><br>
        <label>Date & Venue Presented:<br><input type="text" name="presented" required></label><br>
        <label>Date Published:<br><input type="date" name="published" required></label><br>
        <label>Journal Published:<br><input type="text" name="journal" required></label><br>
        <button type="submit" class="btn">Add Entry</button>
        <button type="button" class="btn" id="cancelAddForm">Cancel</button>
      </form>
      <div class="panel">
        <h2>Data Collection Overview</h2>
        <div class="search-bar-wrapper">
          <span class="search-icon">&#128269;</span>
          <input class="search-bar" type="text" placeholder="Search data collection records..." onkeyup="filterTable()">
        </div>
        <div class="table-container">
          <table id="dataTable">
            <thead>
              <tr>
                <th>Name of Faculty</th>
                <th>Degree</th>
                <th>Sex</th>
                <th>Research Title</th>
                <th>Ownership</th>
                <th>Date & Venue Presented</th>
                <th>Date Published</th>
                <th>Journal Published</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $i => $entry): ?>
              <tr<?php if ($edit_index === $i) echo ' style="background:#ffeeba;"'; ?>>
                <td data-label="Name of Faculty"><strong><?php echo htmlspecialchars($entry[0]); ?></strong></td>
                <td data-label="Degree"><?php echo htmlspecialchars($entry[1]); ?></td>
                <td data-label="Sex"><?php echo htmlspecialchars($entry[2]); ?></td>
                <td data-label="Research Title"><?php echo htmlspecialchars($entry[3]); ?></td>
                <td data-label="Ownership"><strong><?php echo htmlspecialchars($entry[4]); ?></strong></td>
                <td data-label="Date & Venue Presented"><?php echo htmlspecialchars($entry[5]); ?></td>
                <td data-label="Date Published"><?php echo htmlspecialchars($entry[6]); ?></td>
                <td data-label="Journal Published"><?php echo htmlspecialchars($entry[7]); ?></td>
                <td>
                  <form method="get" action="" style="display:inline;">
                    <input type="hidden" name="edit" value="<?php echo $i; ?>">
                    <button type="submit" class="btn">Edit</button>
                  </form>
                  <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                    <input type="hidden" name="index" value="<?php echo $i; ?>">
                    <button type="submit" name="delete" class="btn">Delete</button>
                  </form>
                </td>
              </tr>
              <?php if ($edit_index === $i && $edit_entry): ?>
              <tr>
                <td colspan="9">
                  <form class="edit-entry-form" method="post" action="">
                    <input type="hidden" name="save_edit" value="1">
                    <input type="hidden" name="index" value="<?php echo $edit_index; ?>">
                    <label>Name of Faculty:<br><input type="text" name="faculty" value="<?php echo htmlspecialchars($edit_entry[0]); ?>" required></label><br>
                    <label>Degree:<br><input type="text" name="degree" value="<?php echo htmlspecialchars($edit_entry[1]); ?>" required></label><br>
                    <label>Sex:<br>
                      <select name="sex" required>
                        <option value="Male" <?php if ($edit_entry[2]==='Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($edit_entry[2]==='Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($edit_entry[2]==='Other') echo 'selected'; ?>>Other</option>
                      </select>
                    </label><br>
                    <label>Research Title:<br><input type="text" name="title" value="<?php echo htmlspecialchars($edit_entry[3]); ?>" required></label><br>
                    <label>Ownership:<br>
                      <select name="ownership" required>
                        <option value="Author" <?php if ($edit_entry[4]==='Author') echo 'selected'; ?>>Author</option>
                        <option value="Co-Author" <?php if ($edit_entry[4]==='Co-Author') echo 'selected'; ?>>Co-Author</option>
                      </select>
                    </label><br>
                    <label>Date & Venue Presented:<br><input type="text" name="presented" value="<?php echo htmlspecialchars($edit_entry[5]); ?>" required></label><br>
                    <label>Date Published:<br><input type="date" name="published" value="<?php echo htmlspecialchars($edit_entry[6]); ?>" required></label><br>
                    <label>Journal Published:<br><input type="text" name="journal" value="<?php echo htmlspecialchars($edit_entry[7]); ?>" required></label><br>
                    <button type="submit" class="btn">Save Changes</button>
                  </form>
                </td>
              </tr>
              <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('showAddForm').onclick = function() {
      document.getElementById('addEntryForm').style.display = 'block';
    };
    document.getElementById('cancelAddForm').onclick = function() {
      document.getElementById('addEntryForm').style.display = 'none';
    };
    function filterTable() {
      var input = document.querySelector('.search-bar');
      var filter = input.value.toLowerCase();
      var table = document.getElementById('dataTable');
      var trs = table.getElementsByTagName('tr');
      for (var i = 1; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        // Only filter data rows (9 columns), skip edit form rows (colspan=9)
        if (tds.length === 9) {
          var show = false;
          for (var j = 0; j < tds.length - 1; j++) { // Exclude Actions column if you want
            if (tds[j].textContent.toLowerCase().indexOf(filter) > -1) {
              show = true;
              break;
            }
          }
          trs[i].style.display = show ? '' : 'none';
          // Also hide the edit form row if its data row is hidden
          if (trs[i + 1] && trs[i + 1].querySelector('.edit-entry-form')) {
            trs[i + 1].style.display = show ? '' : 'none';
          }
        }
      }
    }
  </script>
</body>
</html> 