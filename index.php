<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: php/loginpage.php');
    exit;
}
// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: php/loginpage.php');
    exit;
}
// Count publications from CSV
$publications_count = 0;
$pub_file = __DIR__ . '/php/publication_presentation.csv';
if (file_exists($pub_file)) {
    $file = fopen($pub_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row)) {
            $publications_count++;
        }
    }
    fclose($file);
}
// Gather recent updates from multiple sources
$recent_updates = [];
// Publications
if (file_exists($pub_file)) {
    $file = fopen($pub_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && !empty($row[0])) {
            $recent_updates[] = [
                'date' => $row[0],
                'title' => $row[2],
                'meta' => 'Publication • ' . $row[1],
                'type' => 'Publication',
            ];
        }
    }
    fclose($file);
}
// Research Activities
$research_file = __DIR__ . '/php/research_capacity_data.csv';
if (file_exists($research_file)) {
    $file = fopen($research_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && !empty($row[0])) {
            $recent_updates[] = [
                'date' => $row[0],
                'title' => $row[1],
                'meta' => 'Research Activity • ' . $row[3],
                'type' => 'Research Activity',
            ];
        }
    }
    fclose($file);
}
// Ethics Protocols (no date, use last row as latest)
$ethics_file = __DIR__ . '/php/ethics_reviewed_protocols.csv';
if (file_exists($ethics_file)) {
    $rows = [];
    $file = fopen($ethics_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row)) {
            $rows[] = $row;
        }
    }
    fclose($file);
    if (!empty($rows)) {
        $last = end($rows);
        $recent_updates[] = [
            'date' => '',
            'title' => $last[1],
            'meta' => 'Ethics Protocol • ' . $last[2],
            'type' => 'Ethics Protocol',
        ];
    }
}
// KPI Records (period as date, use last row)
$kpi_file = __DIR__ . '/php/kpi_records.csv';
if (file_exists($kpi_file)) {
    $rows = [];
    $file = fopen($kpi_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row)) {
            $rows[] = $row;
        }
    }
    fclose($file);
    if (!empty($rows)) {
        $last = end($rows);
        $recent_updates[] = [
            'date' => $last[1],
            'title' => 'KPI Update: ' . $last[0],
            'meta' => 'KPI Update • ' . $last[6],
            'type' => 'KPI',
        ];
    }
}
// Count ethics protocols from CSV
$ethics_count = 0;
$ethics_file = __DIR__ . '/php/ethics_reviewed_protocols.csv';
if (file_exists($ethics_file)) {
    $file = fopen($ethics_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row)) {
            $ethics_count++;
        }
    }
    fclose($file);
}
// Count research activities from CSV
$research_count = 0;
$research_file = __DIR__ . '/php/research_capacity_data.csv';
if (file_exists($research_file)) {
    $file = fopen($research_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row)) {
            $research_count++;
        }
    }
    fclose($file);
}
// Calculate average KPI score from CSV
$kpi_scores = [];
$kpi_file = __DIR__ . '/php/kpi_records.csv';
if (file_exists($kpi_file)) {
    $file = fopen($kpi_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && isset($row[5]) && is_numeric($row[5])) {
            $kpi_scores[] = floatval($row[5]);
        }
    }
    fclose($file);
}
$average_kpi = count($kpi_scores) ? round(array_sum($kpi_scores) / count($kpi_scores), 2) : 0;
// Sort by date (descending, where possible)
function parse_date($d) {
    if (preg_match('/\d{4}-\d{2}-\d{2}/', $d)) return strtotime($d);
    if (preg_match('/\d{4}/', $d)) return strtotime(str_replace(' -', '-01-01', $d));
    return 0;
}
usort($recent_updates, function($a, $b) {
    return parse_date($b['date']) <=> parse_date($a['date']);
});
$recent_updates = array_slice($recent_updates, 0, 5);
// Build research activity trends by month for the current year
$activity_by_month = array_fill(1, 12, 0);
$current_year = date('Y');
if (file_exists($research_file)) {
    $file = fopen($research_file, 'r');
    while (($row = fgetcsv($file)) !== false) {
        if (!empty($row) && !empty($row[0])) {
            $date = $row[0];
            $ts = strtotime($date);
            if ($ts && date('Y', $ts) == $current_year) {
                $month = (int)date('n', $ts);
                $activity_by_month[$month]++;
            }
        }
    }
    fclose($file);
}
$month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$max_activities = max($activity_by_month) ?: 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Faculty Research Management System</title>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="css/index.css">
 
</head>
<body>
  <div class="profile-menu" id="profileMenu">
    <button class="profile-icon-btn" id="profileIconBtn" aria-label="Profile">
      <!-- SVG user icon -->
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6a7a5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-2.5 3.5-4 8-4s8 1.5 8 4"/></svg>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
      <div style="font-weight: 600; margin-bottom: 4px;">
        <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?>
      </div>
      <div style="font-size:0.9em; color:#6a7a5e; margin-bottom:2px;">
        <?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?>
      </div>
      <div style="font-size:0.85em; color:#9a9a8a;">
        <?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?>
      </div>
      <div style="border-top: 1px solid #eee; margin: 10px 0; padding-top: 10px;">
        <a href="php/edit_profile.php" style="display: block; color: #6a7a5e; text-decoration: none; padding: 8px 0; font-size: 0.9em;">Edit Profile</a>
      </div>
      <form method="post">
        <button type="submit" name="logout">Logout</button>
      </form>
    </div>
  </div>
  <script>
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
  <header>
    <div class="logo">
      <img src="pics/rso-bg.png" alt="UC Logo">
      UC RSO
    </div>
    <nav>
      <a href="index.php" class="active">Dashboard</a>
      <a href="php/Research  Capacity Buildings Activities.php">Research Capacity Building</a>
      <a href="php/Data Collection Tools.php">Data Collection Tools</a>
      <a href="php/Ethicss Reviewed Protocols.php">Ethics Reviewed Protocols</a>
      <a href="php/Publication and Presentation.php">Publications and Presentations</a>
      <a href="php/KPI records.php">KPI Records</a>
    </nav>
  </header>
  <div class="dashboard-bg">
    <div class="container">
      <h1>Faculty Research Management System</h1>
      <div class="subtitle">Comprehensive overview of research activities and performance metrics</div>
      <div class="metrics">
        <div class="card">
          <div class="label">Research Activities</div>
          <div class="value"><?php echo $research_count; ?></div>
        </div>
        <div class="card">
          <div class="label">Ethics Protocols</div>
          <div class="value"><?php echo $ethics_count; ?></div>
        </div>
        <div class="card">
          <div class="label">Publications</div>
          <div class="value"><?php echo $publications_count; ?></div>
        </div>
        <div class="card">
          <div class="label">Average KPI Score</div>
          <div class="value"><?php echo $average_kpi; ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="container main-content">
    <div class="panel">
      <!-- Chart Title -->
      <div style="text-align:center; font-weight:600; font-size:1.15em; margin-bottom: 10px;">
        Research Activities per Month (<?php echo date('Y'); ?>)
      </div>
      <div class="bar-chart-modern" style="position:relative; height:240px; padding: 30px 30px 40px 30px; border-left: 2px solid #e0e0e0; border-bottom: 2px solid #e0e0e0; background: #fafbfa;">
        <?php
          // Y-axis grid lines and labels
          $y_max = ceil($max_activities / 5) * 5;
          $y_step = max(1, ceil($y_max / 5));
          for ($y = $y_max; $y >= 0; $y -= $y_step) {
            $y_pos = 30 + (180 - ($y / $y_max) * 180);
            echo '<div style="position:absolute;left:0;top:'.($y_pos-8).'px;width:100%;border-top:1px dashed #e0e0e0;font-size:0.85em;color:#b0b0a8;">';
            echo '<span style="position:absolute;left:-32px;width:30px;text-align:right;">'.$y.'</span>';
            echo '</div>';
          }
        ?>
        <div style="display:flex; align-items:flex-end; height:180px; position:relative; z-index:2;">
          <?php foreach ($activity_by_month as $i => $count): ?>
            <?php
              $bar_height = $max_activities ? round(($count/$max_activities)*160) : 0;
              $bar_color = "#4a90e2";
            ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; margin:0 6px;">
              <!-- Value label above bar -->
              <div style="font-size:0.95em; color:#333; margin-bottom:2px; height:22px;">
                <?php if ($count > 0) echo $count; ?>
              </div>
              <!-- Bar -->
              <div style="width: 32px; height: <?php echo $bar_height; ?>px; background: <?php echo $bar_color; ?>; border-radius: 4px 4px 0 0; box-shadow:0 2px 6px rgba(0,0,0,0.04);"></div>
              <!-- Month label below bar -->
              <div style="font-size:0.95em; color:#6a7a5e; margin-top:6px;"><?php echo $month_names[$i-1]; ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="panel right">
      <h2>Recent Updates</h2>
      <ul class="updates-list">
        <?php foreach ($recent_updates as $update): ?>
        <li>
          <div class="update-title"><?php echo htmlspecialchars($update['title']); ?></div>
          <div class="update-meta"><?php echo htmlspecialchars($update['meta']); ?></div>
          <?php if (!empty($update['date'])): ?>
          <div class="update-date"><?php echo htmlspecialchars($update['date']); ?></div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</body>
</html> 