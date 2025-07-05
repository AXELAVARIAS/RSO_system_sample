<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}

// File to store entries
$data_file = __DIR__ . '/research_capacity_data.csv';

// Handle form submission (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read all entries
    $entries = [];
    if (file_exists($data_file)) {
        $fp = fopen($data_file, 'r');
        $is_first_row = true;
        while ($row = fgetcsv($fp)) {
            if ($is_first_row) {
                $is_first_row = false;
                continue; // skip header
            }
            $entries[] = $row;
        }
        fclose($fp);
    }

    // Delete entry
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
            fputcsv($fp, ['Date', 'Activity Name', 'Venue', 'Facilitators/Participants', 'No. of Participants', 'Status']);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Edit entry
    elseif (isset($_POST['edit']) && isset($_POST['index'])) {
        // Handled below (show edit form)
    }
    // Save edited entry
    elseif (isset($_POST['save_edit']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $date = $_POST['date'] ?? '';
        $name = $_POST['name'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $facilitators = $_POST['facilitators'] ?? '';
        $num_participants = $_POST['num_participants'] ?? '';
        $status = $_POST['status'] ?? '';
        if ($date && $name && $venue && $facilitators && $num_participants && $status) {
            $entries[$index] = [$date, $name, $venue, $facilitators, $num_participants, $status];
            $fp = fopen($data_file, 'w');
            fputcsv($fp, ['Date', 'Activity Name', 'Venue', 'Facilitators/Participants', 'No. of Participants', 'Status']);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Add new entry
    elseif (isset($_POST['add_entry'])) {
        $date = $_POST['date'] ?? '';
        $name = $_POST['name'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $facilitators = $_POST['facilitators'] ?? '';
        $num_participants = $_POST['num_participants'] ?? '';
        $status = $_POST['status'] ?? '';
        if ($date && $name && $venue && $facilitators && $num_participants && $status) {
            $entries[] = [$date, $name, $venue, $facilitators, $num_participants, $status];
            $fp = fopen($data_file, 'w');
            fputcsv($fp, ['Date', 'Activity Name', 'Venue', 'Facilitators/Participants', 'No. of Participants', 'Status']);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    $is_first_row = true;
    while ($row = fgetcsv($fp)) {
        if ($is_first_row) {
            $is_first_row = false;
            continue; // skip header
        }
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
  <title>Research Capacity Building Activities</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="../css/research capacity.css">
 <style>
 .add-entry-form { display: none; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
 .add-entry-form.active { display: block; }
 .add-entry-form input, .add-entry-form select { margin-bottom: 10px; width: 100%; padding: 8px; }
 .add-entry-form label { font-weight: bold; }
 .edit-entry-form { background: #f1f1f1; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
 header {
   position: fixed;
   top: 0;
   left: 0;
   width: 100vw;
   z-index: 1000;
   background: #fff;
   box-shadow: 0 2px 8px rgba(0,0,0,0.04);
 }
 body {
   margin: 0;
   padding-top: 80px;
 }
 .profile-menu {
   position: fixed;
   top: 18px;
   right: 40px;
   z-index: 1100;
   display: flex;
   align-items: center;
 }
 .profile-icon-btn {
   background: #e9ecdf;
   border: none;
   border-radius: 50%;
   width: 44px;
   height: 44px;
   display: flex;
   align-items: center;
   justify-content: center;
   cursor: pointer;
   box-shadow: 0 2px 8px rgba(0,0,0,0.08);
   transition: background 0.2s;
   font-size: 1.7rem;
   padding: 0;
 }
 .profile-icon-btn:hover {
   background: #d2d8c2;
 }
 .profile-dropdown {
   display: none;
   position: absolute;
   top: 54px;
   right: 0;
   background: #fff;
   border-radius: 10px;
   box-shadow: 0 4px 16px rgba(0,0,0,0.13);
   min-width: 240px;
   padding: 16px 20px 12px 20px;
   text-align: left;
   animation: fadeIn 0.2s;
   box-sizing: border-box;
 }
 .profile-menu.open .profile-dropdown {
   display: block;
 }
 .profile-dropdown form {
   margin: 0;
   padding: 0 18px;
 }
 .profile-dropdown button {
   background: #b94a48;
   color: #fff;
   border: none;
   border-radius: 6px;
   padding: 7px 18px;
   font-size: 1rem;
   cursor: pointer;
   margin-top: 6px;
   width: 100%;
   text-align: center;
   transition: background 0.2s;
 }
 .profile-dropdown button:hover {
   background: #a94442;
 }
 @keyframes fadeIn {
   from { opacity: 0; transform: translateY(-10px); }
   to { opacity: 1; transform: translateY(0); }
 }
 </style>
</head>
<body>
<div class="profile-menu" id="profileMenu">
    <button class="profile-icon-btn" id="profileIconBtn" aria-label="Profile">
      <!-- SVG user icon -->
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6a7a5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-2.5 3.5-4 8-4s8 1.5 8 4"/></svg>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
      <div style="font-weight: 600; margin-bottom: 4px; text-align: left;">
        <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?>
      </div>
      <div style="font-size:0.9em; color:#6a7a5e; margin-bottom:2px; text-align: left;">
        <?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?>
      </div>
      <div style="font-size:0.85em; color:#9a9a8a; text-align: left;">
        <?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?>
      </div>
      <div style="border-top: 1px solid #eee; margin: 10px 0; padding-top: 10px; text-align: left;">
        <a href="edit_profile.php" style="display: block; color: #6a7a5e; text-decoration: none; padding: 8px 0; font-size: 0.9em; text-align: left;">Edit Profile</a>
      </div>
      <form method="post">
        <button type="submit" name="logout">Logout</button>
      </form>
    </div>
  </div>
  <header>
    <div class="logo">
      <img src="../pics/rso-bg.png" alt="UC Logo">
      UC RSO
    </div>
    <nav>
      <a href="../index.php">Dashboard</a>
      <a href="Research  Capacity Buildings Activities.php" class="active">Research Capacity Building</a>
      <a href="Data Collection Tools.php">Data Collection Tools</a>
      <a href="Ethicss Reviewed Protocols.php">Ethics Reviewed Protocols</a>
      <a href="Publication and Presentation.php">Publications and Presentations</a>
      <a href="KPI records.php">KPI Records</a>
    </nav>
      
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Research Capacity Building Activities</h1>
      <div class="subtitle">Track faculty development and research enhancement programs</div>
      <div class="actions">
        <button class="btn upload">&#8682; Upload Excel File</button>
        <button class="btn add" id="showAddForm">+ Add New Entry</button>
      </div>
      <form class="add-entry-form" id="addEntryForm" method="post" action="">
        <input type="hidden" name="add_entry" value="1">
        <label>Date of Activity:<br><input type="date" name="date" required></label><br>
        <label>Name of Activity:<br><input type="text" name="name" required></label><br>
        <label>Venue:<br><input type="text" name="venue" required></label><br>
        <label>Facilitators/Participants:<br><input type="text" name="facilitators" required></label><br>
        <label>Number of Participants:<br><input type="number" name="num_participants" min="1" required></label><br>
        <label>Activity Report Status:<br>
          <select name="status" required>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
          </select>
        </label><br>
        <button type="submit" class="btn">Add Entry</button>
        <button type="button" class="btn" id="cancelAddForm">Cancel</button>
      </form>
      <div class="panel">
        <h2>Research Activities Overview</h2>
        <div class="search-bar-wrapper">
          <span class="search-icon">&#128269;</span>
          <input class="search-bar" type="text" placeholder="Search research activities..." onkeyup="filterTable()">
        </div>
        <div class="table-container">
          <table id="activitiesTable">
            <thead>
              <tr>
                <th>Date of Activity</th>
                <th>Name of Activity</th>
                <th>Venue</th>
                <th>Facilitators/Participants</th>
                <th>Number of Participants</th>
                <th>Activity Report</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $i => $entry): ?>
              <tr<?php if ($edit_index === $i) echo ' style="background:#ffeeba;"'; ?>>
                <td data-label="Date of Activity"><?php echo htmlspecialchars($entry[0]); ?></td>
                <td data-label="Name of Activity"><strong><?php echo htmlspecialchars($entry[1]); ?></strong></td>
                <td data-label="Venue"><?php echo htmlspecialchars($entry[2]); ?></td>
                <td data-label="Facilitators/Participants"><?php echo htmlspecialchars($entry[3]); ?></td>
                <td data-label="Number of Participants"><strong><?php echo htmlspecialchars($entry[4]); ?></strong></td>
                <td data-label="Activity Report"><span class="status <?php echo strtolower($entry[5]); ?>"><?php echo htmlspecialchars($entry[5]); ?></span></td>
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
                <td colspan="7">
                  <form class="edit-entry-form" method="post" action="">
                    <input type="hidden" name="save_edit" value="1">
                    <input type="hidden" name="index" value="<?php echo $edit_index; ?>">
                    <label>Date of Activity:<br><input type="date" name="date" value="<?php echo htmlspecialchars($edit_entry[0]); ?>" required></label><br>
                    <label>Name of Activity:<br><input type="text" name="name" value="<?php echo htmlspecialchars($edit_entry[1]); ?>" required></label><br>
                    <label>Venue:<br><input type="text" name="venue" value="<?php echo htmlspecialchars($edit_entry[2]); ?>" required></label><br>
                    <label>Facilitators/Participants:<br><input type="text" name="facilitators" value="<?php echo htmlspecialchars($edit_entry[3]); ?>" required></label><br>
                    <label>Number of Participants:<br><input type="number" name="num_participants" min="1" value="<?php echo htmlspecialchars($edit_entry[4]); ?>" required></label><br>
                    <label>Activity Report Status:<br>
                      <select name="status" required>
                        <option value="Completed" <?php if ($edit_entry[5]==='Completed') echo 'selected'; ?>>Completed</option>
                        <option value="Pending" <?php if ($edit_entry[5]==='Pending') echo 'selected'; ?>>Pending</option>
                      </select>
                    </label><br>
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
      document.getElementById('addEntryForm').classList.add('active');
    };
    document.getElementById('cancelAddForm').onclick = function() {
      document.getElementById('addEntryForm').classList.remove('active');
    };
    function filterTable() {
      var input = document.querySelector('.search-bar');
      var filter = input.value.toLowerCase();
      var table = document.getElementById('activitiesTable');
      var trs = table.getElementsByTagName('tr');
      for (var i = 1; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        // Only filter data rows (7 columns), skip edit form rows (colspan=7)
        if (tds.length === 7) {
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
    const profileMenu = document.getElementById('profileMenu');
    const profileIconBtn = document.getElementById('profileIconBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    document.addEventListener('click', function(e) {
      if (profileMenu.contains(e.target)) {
        profileMenu.classList.toggle('open');
      } else {
        profileMenu.classList.remove('open');
      }
    });
  </script>
</body>
</html>