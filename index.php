<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Faculty Research Management System</title>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="css/index.css">
</head>
<body>
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
    <button class="login-btn" onclick="window.location.href='html/loginpage.html'">Login</button>
    
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