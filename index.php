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
// Count publications from database
require_once __DIR__ . '/database/config.php';
$db = getDB();
$publications_count = 0;
try {
    $result = $db->fetch('SELECT COUNT(*) as count FROM publication_presentations');
    $publications_count = $result['count'] ?? 0;
} catch (Exception $e) {
    $publications_count = 0;
}

// Gather recent updates from database
$recent_updates = [];

// Research Activities from database
try {
    $research_activities = $db->fetchAll('
        SELECT 
            activity_date as date,
            activity_title as title,
            venue,
            organizer,
            status,
            created_at,
            "Research Activity" as type
        FROM research_capacity_activities 
        ORDER BY created_at DESC 
        LIMIT 3
    ');
    
    foreach ($research_activities as $activity) {
        $recent_updates[] = [
            'date' => $activity['date'],
            'title' => $activity['title'],
            'meta' => 'Research Activity • ' . $activity['organizer'],
            'type' => 'Research Activity',
            'upload_date' => $activity['created_at'],
            'status' => $activity['status']
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}

// Ethics Protocols from database
try {
    $ethics_protocols = $db->fetchAll('
        SELECT 
            protocol_number,
            title,
            department,
            status,
            created_at,
            "Ethics Protocol" as type
        FROM ethics_reviewed_protocols 
        ORDER BY created_at DESC 
        LIMIT 3
    ');
    
    foreach ($ethics_protocols as $protocol) {
        $recent_updates[] = [
            'date' => date('Y-m-d', strtotime($protocol['created_at'])),
            'title' => $protocol['title'],
            'meta' => 'Ethics Protocol • ' . $protocol['department'],
            'type' => 'Ethics Protocol',
            'upload_date' => $protocol['created_at'],
            'status' => $protocol['status']
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}

// Publications from database
try {
    $publications = $db->fetchAll('
        SELECT 
            application_date as date,
            paper_title as title,
            author_name,
            department,
            status,
            created_at,
            "Publication" as type
        FROM publication_presentations 
        ORDER BY created_at DESC 
        LIMIT 3
    ');
    
    foreach ($publications as $publication) {
        $recent_updates[] = [
            'date' => $publication['date'],
            'title' => $publication['title'],
            'meta' => 'Publication • ' . $publication['author_name'],
            'type' => 'Publication',
            'upload_date' => $publication['created_at'],
            'status' => $publication['status']
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}

// KPI Records from database
try {
    $kpi_records = $db->fetchAll('
        SELECT 
            faculty_name,
            quarter,
            performance_score,
            performance_rating,
            created_at,
            "KPI" as type
        FROM kpi_records 
        ORDER BY created_at DESC 
        LIMIT 2
    ');
    
    foreach ($kpi_records as $kpi) {
        $recent_updates[] = [
            'date' => date('Y-m-d', strtotime($kpi['created_at'])),
            'title' => 'KPI Update: ' . $kpi['faculty_name'],
            'meta' => 'KPI Update • ' . $kpi['quarter'] . ' - Score: ' . $kpi['performance_score'],
            'type' => 'KPI',
            'upload_date' => $kpi['created_at'],
            'status' => $kpi['performance_rating']
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}

// Data Collection Tools from database
try {
    $data_tools = $db->fetchAll('
        SELECT 
            submission_date as date,
            research_title as title,
            researcher_name,
            research_area,
            created_at,
            "Data Collection Tool" as type
        FROM data_collection_tools 
        ORDER BY created_at DESC 
        LIMIT 2
    ');
    
    foreach ($data_tools as $tool) {
        $recent_updates[] = [
            'date' => $tool['date'],
            'title' => $tool['title'],
            'meta' => 'Data Collection Tool • ' . $tool['researcher_name'],
            'type' => 'Data Collection Tool',
            'upload_date' => $tool['created_at'],
            'status' => $tool['research_area']
        ];
    }
} catch (Exception $e) {
    // Handle error silently
}

// Count ethics protocols from database
$ethics_count = 0;
try {
    $result = $db->fetch('SELECT COUNT(*) as count FROM ethics_reviewed_protocols');
    $ethics_count = $result['count'] ?? 0;
} catch (Exception $e) {
    $ethics_count = 0;
}
// Count research activities from database
$research_count = 0;
try {
    $result = $db->fetch('SELECT COUNT(*) as count FROM research_capacity_activities');
    $research_count = $result['count'] ?? 0;
} catch (Exception $e) {
    $research_count = 0;
}
// Calculate average KPI score from database
$average_kpi = 0;
try {
    $result = $db->fetch('SELECT AVG(performance_score) as avg_score FROM kpi_records WHERE performance_score IS NOT NULL');
    $average_kpi = $result['avg_score'] !== null ? round($result['avg_score'], 2) : 0;
} catch (Exception $e) {
    $average_kpi = 0;
}

// Count data collection tools from database
$data_collection_count = 0;
try {
    $result = $db->fetch('SELECT COUNT(*) as count FROM data_collection_tools');
    $data_collection_count = $result['count'] ?? 0;
} catch (Exception $e) {
    $data_collection_count = 0;
}

// Sort by upload date (descending)
usort($recent_updates, function($a, $b) {
    return strtotime($b['upload_date']) <=> strtotime($a['upload_date']);
});
$recent_updates = array_slice($recent_updates, 0, 5);

// Build research activity trends by month for the selected year from database
$current_year = date('Y');
$min_year = $current_year;
$max_year = $current_year;
try {
    $years = $db->fetchAll('SELECT MIN(YEAR(activity_date)) as min_year, MAX(YEAR(activity_date)) as max_year FROM research_capacity_activities');
    if ($years && $years[0]['min_year']) {
        $min_year = $years[0]['min_year'];
        $max_year = $years[0]['max_year'];
    }
} catch (Exception $e) {
    // fallback to current year
}
$selected_year = isset($_GET['activity_year']) ? (int)$_GET['activity_year'] : $max_year;
$activity_by_month = array_fill(1, 12, 0);
try {
    $monthly_activities = $db->fetchAll('
        SELECT 
            MONTH(activity_date) as month,
            COUNT(*) as count
        FROM research_capacity_activities 
        WHERE YEAR(activity_date) = ? 
        GROUP BY MONTH(activity_date)
    ', [$selected_year]);
    
    foreach ($monthly_activities as $activity) {
        $activity_by_month[(int)$activity['month']] = (int)$activity['count'];
    }
} catch (Exception $e) {
    // Handle error silently
}

$month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$max_activities = max($activity_by_month) ?: 1;
// Round up to the next multiple of 10 for scaling
$max_activities_rounded = ceil($max_activities / 10) * 10;
if ($max_activities_rounded < 10) $max_activities_rounded = 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - RSO Research Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/modern-theme.css">
  <link rel="stylesheet" href="css/theme.css">
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="header-container">
      <div class="logo">
        <img src="pics/rso-bg.png" alt="UC Logo">
        <span>UC RSO</span>
      </div>
      <nav class="nav">
        <a href="index.php" class="nav-link active">
          <i class="fa-solid fa-house"></i>
          <span>Dashboard</span>
        </a>
        <a href="php/Research  Capacity Buildings Activities.php" class="nav-link">
          <i class="fa-solid fa-chart-line"></i>
          <span>Research Capacity</span>
        </a>
        <a href="php/Data Collection Tools.php" class="nav-link">
          <i class="fa-solid fa-database"></i>
          <span>Data Collection</span>
        </a>
        <a href="php/Ethicss Reviewed Protocols.php" class="nav-link">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Ethics Protocols</span>
        </a>
        <a href="php/Publication and Presentation.php" class="nav-link">
          <i class="fa-solid fa-book"></i>
          <span>Publications</span>
        </a>
        <a href="php/KPI records.php" class="nav-link">
          <i class="fa-solid fa-bullseye"></i>
          <span>KPI Records</span>
        </a>
      </nav>
      
      <!-- Profile Menu -->
      <div class="profile-menu" id="profileMenu">
        <button class="profile-btn" id="profileBtn">
          <?php
            $profile_picture = $_SESSION['profile_picture'] ?? '';
            $profile_picture_path = '';
            if (!empty($profile_picture)) {
              // Handle relative paths from php directory
              if (strpos($profile_picture, '../') === 0) {
                // Convert ../uploads/profile_pictures/filename.jpg to uploads/profile_pictures/filename.jpg
                $profile_picture_path = substr($profile_picture, 3);
              } else {
                $profile_picture_path = $profile_picture;
              }
            }
          ?>
          <?php if ($profile_picture_path): ?>
            <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile" class="profile-img">
          <?php else: ?>
            <img src="pics/rso-bg.png" alt="Profile" class="profile-img">
          <?php endif; ?>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?></div>
            <div class="profile-role"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?></div>
            <div class="profile-type"><?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?></div>
          </div>
          <div class="profile-actions">
            <a href="php/edit_profile.php" class="profile-action">
              <i class="fa-solid fa-user-pen"></i>
              Edit Profile
            </a>
            <form method="post" class="logout-form">
              <button type="submit" name="logout" class="profile-action logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main">
    <div class="container">
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-title">
          <h1>Research Dashboard</h1>
          <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?>! Here's your research overview.</p>
        </div>
        <div class="page-actions">
          <!-- Export Report and Quick Add buttons removed -->
        </div>
      </div>

      <!-- Dashboard Cards -->
      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-icon primary">
            <i class="fa-solid fa-book"></i>
          </div>
          <div class="card-title">Publications</div>
          <div class="card-value"><?php echo $publications_count; ?></div>
        </div>
        <div class="dashboard-card">
          <div class="card-icon success">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div class="card-title">Ethics Protocols</div>
          <div class="card-value"><?php echo $ethics_count; ?></div>
        </div>
        <div class="dashboard-card">
          <div class="card-icon warning">
            <i class="fa-solid fa-chart-line"></i>
          </div>
          <div class="card-title">Research Activities</div>
          <div class="card-value"><?php echo $research_count; ?></div>
        </div>
        <div class="dashboard-card">
          <div class="card-icon info">
            <i class="fa-solid fa-bullseye"></i>
          </div>
          <div class="card-title">Average KPI Score</div>
          <div class="card-value"><?php echo $average_kpi; ?></div>
        </div>
      </div>

      <!-- Charts and Analytics -->
      <div class="dashboard-grid">
        <!-- Activity Chart -->
        <div class="data-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fa-solid fa-chart-bar"></i>
              <h2>Research Activity Trends</h2>
            </div>
          </div>
          <form method="get" style="margin-bottom: 16px;">
            <label for="activity_year" style="font-weight:500;">Year:</label>
            <select name="activity_year" id="activity_year" onchange="this.form.submit()">
              <?php for ($y = $max_year; $y >= $min_year; $y--): ?>
                <option value="<?php echo $y; ?>" <?php if ($selected_year == $y) echo 'selected'; ?>><?php echo $y; ?></option>
              <?php endfor; ?>
            </select>
          </form>
          <div class="chart-container">
            <canvas id="activityChart"></canvas>
          </div>
        </div>

        <!-- Recent Updates -->
        <div class="data-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fa-solid fa-clock"></i>
              <h2>Recent Updates</h2>
            </div>
          </div>
          <div class="updates-list" style="padding: 0 24px 24px 24px;">
            <?php if (empty($recent_updates)): ?>
              <div class="empty-content" style="padding: 40px 0;">
                <i class="fa-solid fa-inbox"></i>
                <h3>No recent updates</h3>
                <p>Start adding content to see recent updates here</p>
              </div>
            <?php else: ?>
              <?php foreach ($recent_updates as $update): ?>
              <?php
                // Determine link based on type
                $type = $update['type'];
                $title_param = urlencode($update['title']);
                if ($type === 'Publication') {
                  $link = "php/Publication and Presentation.php?title=$title_param";
                } elseif ($type === 'Research Activity') {
                  $link = "php/Research  Capacity Buildings Activities.php?title=$title_param";
                } elseif ($type === 'Ethics Protocol') {
                  $link = "php/Ethicss Reviewed Protocols.php?title=$title_param";
                } elseif ($type === 'KPI') {
                  $link = "php/KPI records.php?title=$title_param";
                } elseif ($type === 'Data Collection Tool') {
                  $link = "php/Data Collection Tools.php?title=$title_param";
                } else {
                  $link = '#';
                }
                
                // Format upload date
                $upload_date = '';
                if (isset($update['upload_date'])) {
                  $upload_timestamp = strtotime($update['upload_date']);
                  $upload_date = date('M j, Y g:i A', $upload_timestamp);
                }
              ?>
              <a href="<?php echo $link; ?>" class="update-item update-link" style="text-decoration:none;color:inherit;">
                <div class="update-icon">
                  <?php if ($update['type'] === 'Publication'): ?>
                    <i class="fa-solid fa-book"></i>
                  <?php elseif ($update['type'] === 'Research Activity'): ?>
                    <i class="fa-solid fa-chart-line"></i>
                  <?php elseif ($update['type'] === 'Ethics Protocol'): ?>
                    <i class="fa-solid fa-shield-halved"></i>
                  <?php elseif ($update['type'] === 'KPI'): ?>
                    <i class="fa-solid fa-bullseye"></i>
                  <?php elseif ($update['type'] === 'Data Collection Tool'): ?>
                    <i class="fa-solid fa-database"></i>
                  <?php endif; ?>
                </div>
                <div class="update-content">
                  <div class="update-title"><?php echo htmlspecialchars($update['title']); ?></div>
                  <div class="update-meta"><?php echo htmlspecialchars($update['meta']); ?></div>
                  <div class="update-dates">
                    <?php if ($update['date']): ?>
                      <div class="update-date">Date: <?php echo htmlspecialchars($update['date']); ?></div>
                    <?php endif; ?>
                    <?php if ($upload_date): ?>
                      <div class="update-upload-date">Uploaded: <?php echo htmlspecialchars($upload_date); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
    .activity-chart {
      display: flex;
      align-items: end;
      gap: 12px;
      height: 300px;
      padding: 20px 0;
      background: rgba(255,0,0,0.07); /* Debug: light red background */
    }
    
    .chart-bar {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
    }
    
    .bar {
      width: 100%;
      background: linear-gradient(180deg, var(--btn-primary-bg) 0%, var(--btn-primary-hover) 100%);
      border-radius: 4px 4px 0 0;
      min-height: 0;
      transition: all 0.3s ease;
      border: 2px solid #fff200; /* Debug: yellow border */
      align-self: flex-end;
    }
    
    .bar:hover {
      background: linear-gradient(180deg, var(--btn-primary-hover) 0%, var(--btn-primary-bg) 100%);
      transform: scaleY(1.05);
    }
    
    .bar-label {
      font-size: 0.75rem;
      color: var(--text-secondary);
      font-weight: 500;
    }
    
    .bar-value {
      font-size: 0.875rem;
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .updates-list {
      max-height: 400px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: var(--btn-primary-bg) var(--bg-secondary);
    }
    
    /* Custom Webkit Scrollbar for Recent Updates */
    .updates-list::-webkit-scrollbar {
      width: 8px;
    }
    
    .updates-list::-webkit-scrollbar-track {
      background: var(--bg-secondary);
      border-radius: 10px;
      margin: 4px 0;
    }
    
    .updates-list::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, var(--btn-primary-bg) 0%, var(--btn-primary-hover) 100%);
      border-radius: 10px;
      border: 2px solid var(--bg-secondary);
      transition: all 0.3s ease;
    }
    
    .updates-list::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, var(--btn-primary-hover) 0%, var(--btn-primary-bg) 100%);
      transform: scaleX(1.2);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .updates-list::-webkit-scrollbar-corner {
      background: var(--bg-secondary);
    }
    
    /* Scrollbar animation on scroll */
    .updates-list::-webkit-scrollbar-thumb:active {
      background: var(--btn-primary-hover);
      transform: scaleX(1.3);
    }
    
    /* Firefox scrollbar styling */
    .updates-list {
      scrollbar-width: thin;
      scrollbar-color: var(--btn-primary-bg) var(--bg-secondary);
    }
    
    .update-item {
      display: flex;
      gap: 16px;
      padding: 16px 0;
      border-bottom: 1px solid var(--border-secondary);
    }
    
    .update-item:last-child {
      border-bottom: none;
    }
    
    .update-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }
    
    .update-item:nth-child(1) .update-icon {
      background: var(--status-under-review);
      color: var(--text-inverse);
    }
    
    .update-item:nth-child(2) .update-icon {
      background: var(--status-approved);
      color: var(--text-inverse);
    }
    
    .update-item:nth-child(3) .update-icon {
      background: var(--status-pending);
      color: var(--text-inverse);
    }
    
    .update-item:nth-child(4) .update-icon {
      background: var(--status-under-review);
      color: var(--text-inverse);
    }
    
    .update-item:nth-child(5) .update-icon {
      background: var(--status-draft);
      color: var(--text-inverse);
    }
    
    .update-content {
      flex: 1;
      min-width: 0;
    }
    
    .update-title {
      font-weight: 500;
      color: var(--text-primary);
      margin-bottom: 4px;
      line-height: 1.4;
    }
    
    .update-meta {
      font-size: 0.875rem;
      color: var(--text-secondary);
      margin-bottom: 2px;
    }
    
    .update-date {
      font-size: 0.75rem;
      color: var(--text-tertiary);
    }
    
    .update-dates {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    
    .update-upload-date {
      font-size: 0.75rem;
      color: var(--btn-primary-bg);
      font-weight: 500;
    }
    .chart-container {
      width: 100%;
      max-width: 100%;
      height: 300px;
      min-width: 0;
      padding: 24px;
    }
    #activityChart {
      width: 100% !important;
      height: 100% !important;
      display: block;
    }
    @media (max-width: 600px) {
      .chart-container {
        height: 200px;
        padding: 8px;
      }
    }
  </style>
  <!-- Custom UC Theme Overrides -->
  <style>
    :root {
      /* UC Light Mode Colors */
      --bg-header: #18813a;
      --text-primary: #fff;
      --text-secondary: #e0e7ef;
      --text-tertiary: #b6d7c9;
      --text-dashboard-title: #fff;
      --text-dashboard-subtitle: #e0e7ef;
      --border-primary: #b6d7c9;
      --border-secondary: #e0e7ef;
      --bg-primary: radial-gradient(circle at 60% 40%, #b3e0ff 0%, #0077b6 60%, #005b8f 100%);
      --bg-secondary: #e3f2fd;
      --bg-tertiary: #b3e0ff;
      --bg-card: #e3f2fd;
      --bg-modal: #e3f2fd;
      --bg-dropdown: #e3f2fd;
      --btn-primary-bg: #ff9800;
      --btn-primary-hover: #f57c00;
      --btn-secondary-bg: #b3e0ff;
      --btn-secondary-hover: #90caf9;
      --btn-success-bg: #18813a;
      --btn-success-hover: #145c2c;
      --btn-danger-bg: #e53935;
      --btn-danger-hover: #b71c1c;
      --btn-warning-bg: #ffb300;
      --btn-warning-hover: #ffa000;
      --status-approved: #18813a;
      --status-pending: #ff9800;
      --status-under-review: #1976d2;
      --status-rejected: #e53935;
      --status-draft: #90caf9;
      --shadow-sm: 0 2px 8px 0 rgba(0, 119, 182, 0.08);
      --shadow-md: 0 6px 16px -2px rgba(0, 119, 182, 0.12);
      --shadow-lg: 0 16px 32px -8px rgba(0, 119, 182, 0.18);
    }
    body {
      background: var(--bg-primary) !important;
      color: #fff !important;
    }
    .header {
      background: var(--bg-header) !important;
      color: #fff !important;
      border-bottom: 4px solid #ff9800 !important;
      box-shadow: 0 4px 16px 0 rgba(0,0,0,0.08);
    }
    .logo span {
      color: #fff !important;
      text-shadow: 1px 1px 2px #145c2c;
    }
    .nav-link {
      color: #fff !important;
      background: transparent !important;
    }
    .nav-link.active, .nav-link:hover {
      background: #ff9800 !important;
      color: #fff !important;
    }
    .dashboard-card, .data-card {
      background: var(--bg-card) !important;
      border-radius: 18px !important;
      box-shadow: var(--shadow-md) !important;
      border: 1.5px solid #b3e0ff !important;
    }
    .dashboard-card .card-title, .data-card .card-title {
      color: #18813a !important;
      font-weight: 700;
    }
    .dashboard-card .card-value {
      color: #1976d2 !important;
      font-weight: 700;
    }
    .card-header {
      background: #ff9800 !important;
      color: #fff !important;
      border-radius: 18px 18px 0 0 !important;
      padding-top: 18px !important;
      padding-bottom: 12px !important;
      box-shadow: 0 2px 8px 0 rgba(255,152,0,0.08);
    }
    .profile-menu, .profile-dropdown {
      background: #18813a !important;
      color: #fff !important;
      border: none !important;
      box-shadow: 0 4px 16px 0 rgba(0,0,0,0.10);
    }
    .profile-info {
      background: #18813a !important;
      border-radius: 12px 12px 0 0 !important;
      padding: 20px 20px 12px 20px !important;
      border-bottom: 1.5px solid #145c2c !important;
    }
    .profile-name, .profile-role, .profile-type {
      color: #fff !important;
    }
    .profile-action {
      color: #fff !important;
    }
    .profile-btn {
      background: #18813a !important;
      color: #fff !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: none !important;
      padding: 8px 14px !important;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s, color 0.2s, border 0.2s;
    }
    .profile-btn:hover {
      background: #145c2c !important;
      color: #fff !important;
      border-color: #ff9800 !important;
    }
    .profile-btn .fa-chevron-down {
      color: #fff !important;
      font-size: 1.2rem !important;
    }
    .profile-img {
      border: 2px solid #fff !important;
      box-shadow: 0 1px 4px 0 rgba(20,92,44,0.10);
      background: #fff;
      width: 40px !important;
      height: 40px !important;
      object-fit: cover;
      border-radius: 50% !important;
      margin-right: 0 !important;
    }
    .profile-action.logout-btn {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
      background: #ff9800 !important;
      color: #fff !important;
      border-radius: 12px !important;
      font-weight: 700 !important;
      font-size: 1.05rem !important;
      box-shadow: 0 2px 8px 0 rgba(255,152,0,0.12);
      border: 2px solid #ff9800 !important;
      margin-top: 12px !important;
      padding: 14px 0 !important;
      width: 100%;
      transition: background 0.2s, color 0.2s, border 0.2s, box-shadow 0.2s;
    }
    .profile-action.logout-btn:hover {
      background: #f57c00 !important;
      color: #fff !important;
      border-color: #18813a !important;
      box-shadow: 0 4px 16px 0 rgba(20,129,58,0.12);
    }
    .updates-list {
      background: #e3f2fd !important;
      border-radius: 12px !important;
      box-shadow: 0 2px 8px 0 rgba(0,119,182,0.08);
    }
    .update-item {
      background: transparent !important;
      border-bottom: 1px solid #b3e0ff !important;
    }
    .update-title {
      color: #18813a !important;
    }
    .update-meta {
      color: #1976d2 !important;
    }
    .update-date, .update-upload-date {
      color: #ff9800 !important;
    }
    .chart-container {
      background: #b3e0ff !important;
      border-radius: 16px !important;
      box-shadow: 0 2px 8px 0 rgba(0,119,182,0.08);
    }
    /* Scrollbar styling for blue theme */
    .updates-list::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #1976d2 0%, #90caf9 100%) !important;
      border: 2px solid #e3f2fd !important;
    }
    .updates-list::-webkit-scrollbar-track {
      background: #b3e0ff !important;
    }
    /* For Firefox */
    .updates-list {
      scrollbar-color: #1976d2 #b3e0ff !important;
    }
  </style>

  <script src="js/theme.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Pass PHP data to JS
    var xValues = <?php echo json_encode($month_names); ?>;
    var yValues = <?php echo json_encode(array_values($activity_by_month)); ?>;
    var maxY = Math.max.apply(null, yValues);
    var stepSize = maxY < 10 ? 1 : 10;
    var suggestedMax = stepSize === 1 ? Math.max(5, maxY) : Math.ceil(maxY / 10) * 10;
    var ctx = document.getElementById('activityChart').getContext('2d');
    var chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: xValues,
        datasets: [{
          label: 'Research Activities',
          backgroundColor: 'rgba(78, 140, 255, 0.7)',
          borderColor: 'rgba(30, 58, 138, 1)',
          borderWidth: 1,
          data: yValues
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 10,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    window.addEventListener('resize', function() {
      chart.resize();
    });
  </script>
  <script>
    // Profile menu toggle
    const profileMenu = document.getElementById('profileMenu');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
      if (!profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
      }
    });

    // Animate chart bars on load
    document.addEventListener('DOMContentLoaded', () => {
      const bars = document.querySelectorAll('.bar');
      bars.forEach((bar, index) => {
        setTimeout(() => {
          bar.style.opacity = '1';
          bar.style.transform = 'scaleY(1)';
        }, index * 100);
      });

      // Enhanced scrollbar functionality for recent updates
      const updatesList = document.querySelector('.updates-list');
      if (updatesList) {
        // Add smooth scrolling
        updatesList.style.scrollBehavior = 'smooth';
        
        // Add scroll indicator
        const scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator';
        scrollIndicator.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
        scrollIndicator.style.cssText = `
          position: absolute;
          bottom: 10px;
          right: 10px;
          width: 30px;
          height: 30px;
          background: var(--btn-primary-bg);
          color: var(--text-inverse);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          opacity: 0;
          transform: scale(0);
          transition: all 0.3s ease;
          z-index: 10;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        `;
        
        updatesList.parentElement.style.position = 'relative';
        updatesList.parentElement.appendChild(scrollIndicator);
        
        // Show/hide scroll indicator based on scroll position
        updatesList.addEventListener('scroll', () => {
          const isAtBottom = updatesList.scrollTop + updatesList.clientHeight >= updatesList.scrollHeight - 10;
          const isAtTop = updatesList.scrollTop <= 10;
          
          if (!isAtBottom && updatesList.scrollHeight > updatesList.clientHeight) {
            scrollIndicator.style.opacity = '1';
            scrollIndicator.style.transform = 'scale(1)';
            scrollIndicator.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
          } else if (isAtBottom) {
            scrollIndicator.style.opacity = '0.7';
            scrollIndicator.innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
          } else {
            scrollIndicator.style.opacity = '0';
            scrollIndicator.style.transform = 'scale(0)';
          }
        });
        
        // Scroll indicator click functionality
        scrollIndicator.addEventListener('click', () => {
          const isAtBottom = updatesList.scrollTop + updatesList.clientHeight >= updatesList.scrollHeight - 10;
          
          if (isAtBottom) {
            // Scroll to top
            updatesList.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          } else {
            // Scroll to bottom
            updatesList.scrollTo({
              top: updatesList.scrollHeight,
              behavior: 'smooth'
            });
          }
        });
        
        // Add hover effect to scroll indicator
        scrollIndicator.addEventListener('mouseenter', () => {
          scrollIndicator.style.transform = 'scale(1.1)';
          scrollIndicator.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.25)';
        });
        
        scrollIndicator.addEventListener('mouseleave', () => {
          scrollIndicator.style.transform = 'scale(1)';
          scrollIndicator.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.15)';
        });
        
        // Initialize scroll indicator visibility
        setTimeout(() => {
          if (updatesList.scrollHeight > updatesList.clientHeight) {
            scrollIndicator.style.opacity = '1';
            scrollIndicator.style.transform = 'scale(1)';
          }
        }, 500);
      }
    });
  </script>
</body>
</html> 