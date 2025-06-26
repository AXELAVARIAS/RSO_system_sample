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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Faculty Research Management System</title>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="css/index.css">
  <style>
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
      min-width: 180px;
      padding: 12px 0 6px 0;
      text-align: right;
      animation: fadeIn 0.2s;
    }
    .profile-menu.open .profile-dropdown {
      display: block;
    }
    .profile-dropdown .profile-info {
      padding: 0 18px 8px 18px;
      color: #6a7a5e;
      font-size: 0.98rem;
      border-bottom: 1px solid #e3e3d9;
      margin-bottom: 8px;
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
  </style>
</head>
<body>
  <div class="profile-menu" id="profileMenu">
    <button class="profile-icon-btn" id="profileIconBtn" aria-label="Profile">
      <!-- SVG user icon -->
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6a7a5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-2.5 3.5-4 8-4s8 1.5 8 4"/></svg>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
      <div class="profile-info">
        <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'User'); ?><br>
        <span style="font-size:0.92em; color:#9a9a8a;">
          <?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?>
        </span>
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
          <div class="value">124</div>
          <div class="change">+12% from last month</div>
        </div>
        <div class="card">
          <div class="label">Ethics Protocols</div>
          <div class="value">38</div>
          <div class="change">+5% from last month</div>
        </div>
        <div class="card">
          <div class="label">Publications</div>
          <div class="value">267</div>
          <div class="change">+18% from last month</div>
        </div>
        <div class="card">
          <div class="label">Average KPI Score</div>
          <div class="value">8.4</div>
          <div class="change">+0.3 from last quarter</div>
        </div>
      </div>
    </div>
  </div>
  <div class="container main-content">
    <div class="panel">
      <h2>Research Activity Trends</h2>
      <div class="bar-chart">
        <div class="bar-group">
          <div class="bar bar1" style="height: 60px;"></div>
          <div class="bar bar2" style="height: 40px;"></div>
          <div class="bar bar3" style="height: 20px;"></div>
          <div class="bar-label">Jan</div>
        </div>
        <div class="bar-group">
          <div class="bar bar1" style="height: 90px;"></div>
          <div class="bar bar2" style="height: 60px;"></div>
          <div class="bar bar3" style="height: 30px;"></div>
          <div class="bar-label">Feb</div>
        </div>
        <div class="bar-group">
          <div class="bar bar1" style="height: 120px;"></div>
          <div class="bar bar2" style="height: 80px;"></div>
          <div class="bar bar3" style="height: 40px;"></div>
          <div class="bar-label">Mar</div>
        </div>
        <div class="bar-group">
          <div class="bar bar1" style="height: 100px;"></div>
          <div class="bar bar2" style="height: 100px;"></div>
          <div class="bar bar3" style="height: 60px;"></div>
          <div class="bar-label">Apr</div>
        </div>
        <div class="bar-group">
          <div class="bar bar1" style="height: 140px;"></div>
          <div class="bar bar2" style="height: 120px;"></div>
          <div class="bar bar3" style="height: 40px;"></div>
          <div class="bar-label">May</div>
        </div>
        <div class="bar-group">
          <div class="bar bar1" style="height: 110px;"></div>
          <div class="bar bar2" style="height: 90px;"></div>
          <div class="bar bar3" style="height: 70px;"></div>
          <div class="bar-label">Jun</div>
        </div>
      </div>
    </div>
    <div class="panel right">
      <h2>Recent Updates</h2>
      <ul class="updates-list">
        <li>
          <div class="update-title">AI in Education Research Project</div>
          <div class="update-meta">Research Activity • Dr. Smith</div>
          <div class="update-date">2025-05-28</div>
        </li>
        <li>
          <div class="update-title">Machine Learning Applications in Healthcare</div>
          <div class="update-meta">Publication • Prof. Johnson</div>
          <div class="update-date">2025-05-27</div>
        </li>
        <li>
          <div class="update-title">Student Learning Behavior Study</div>
          <div class="update-meta">Ethics Protocol • Dr. Williams</div>
          <div class="update-date">2025-05-26</div>
        </li>
        <li>
          <div class="update-title">Q2 Performance Review</div>
          <div class="update-meta">KPI Update • Dr. Brown</div>
          <div class="update-date">2025-05-25</div>
        </li>
      </ul>
    </div>
  </div>
</body>
</html> 