<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // Handle delete
        if (isset($_POST['delete']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM kpi_records WHERE id = ?", [$id]);
            $success_message = 'KPI record deleted successfully!';
        }
        // Handle edit save
        elseif (isset($_POST['save_edit']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $faculty_name = $_POST['faculty_name'] ?? '';
            $period = $_POST['period'] ?? '';
            $publications = $_POST['publications'] ?? '';
            $trainings = $_POST['trainings'] ?? '';
            $presentations = $_POST['presentations'] ?? '';
            $kpi_score = $_POST['kpi_score'] ?? '';
            $performance = $_POST['performance'] ?? '';
            
            if ($faculty_name && $period && $publications && $trainings && $presentations && $kpi_score && $performance) {
                $db->query("UPDATE kpi_records SET faculty_name = ?, quarter = ?, publications_count = ?, presentations_count = ?, research_projects_count = ?, performance_score = ?, performance_rating = ? WHERE id = ?", 
                    [$faculty_name, $period, $publications, $trainings, $presentations, $kpi_score, $performance, $id]);
                $success_message = 'KPI record updated successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
        // Handle add
        else {
            $faculty_name = $_POST['faculty_name'] ?? '';
            $period = $_POST['period'] ?? '';
            $publications = $_POST['publications'] ?? '';
            $trainings = $_POST['trainings'] ?? '';
            $presentations = $_POST['presentations'] ?? '';
            $kpi_score = $_POST['kpi_score'] ?? '';
            $performance = $_POST['performance'] ?? '';
            
            if ($faculty_name && $period && $publications && $trainings && $presentations && $kpi_score && $performance) {
                $db->query("INSERT INTO kpi_records (faculty_name, quarter, publications_count, presentations_count, research_projects_count, performance_score, performance_rating) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                    [$faculty_name, $period, $publications, $trainings, $presentations, $kpi_score, $performance]);
                $success_message = 'KPI record added successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Read all entries from database
$entries = [];
try {
    $db = getDB();
    $entries = $db->fetchAll("SELECT * FROM kpi_records ORDER BY quarter DESC, faculty_name ASC");
} catch (Exception $e) {
    $error_message = 'Failed to load KPI records: ' . $e->getMessage();
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
  <title>KPI Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/modern-theme.css">
  <link rel="stylesheet" href="../css/theme.css">
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="header-container">
      <div class="logo">
        <img src="../pics/rso-bg.png" alt="UC Logo">
        <span>UC RSO</span>
      </div>
      <nav class="nav">
        <a href="../index.php" class="nav-link">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
        <a href="Research  Capacity Buildings Activities.php" class="nav-link">
          <i class="fas fa-chart-line"></i>
          <span>Research Capacity</span>
        </a>
        <a href="Data Collection Tools.php" class="nav-link">
          <i class="fas fa-database"></i>
          <span>Data Collection</span>
        </a>
        <a href="Ethicss Reviewed Protocols.php" class="nav-link">
          <i class="fas fa-shield-alt"></i>
          <span>Ethics Protocols</span>
        </a>
        <a href="Publication and Presentation.php" class="nav-link">
          <i class="fas fa-book"></i>
          <span>Publications</span>
        </a>
        <a href="KPI records.php" class="nav-link active">
          <i class="fas fa-bullseye"></i>
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
              if (strpos($profile_picture, '../') === 0) {
                $full_path = __DIR__ . '/' . $profile_picture;
                if (file_exists($full_path)) {
                  $profile_picture_path = $profile_picture;
                }
              } else {
                $profile_picture_path = $profile_picture;
              }
            }
          ?>
          <?php if ($profile_picture_path): ?>
            <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile" class="profile-img">
          <?php else: ?>
            <img src="../pics/rso-bg.png" alt="Profile" class="profile-img">
          <?php endif; ?>
          <i class="fas fa-chevron-down"></i>
        </button>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'User'); ?></div>
            <div class="profile-role"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Department'); ?></div>
            <div class="profile-type"><?php echo htmlspecialchars(ucfirst($_SESSION['user_type'] ?? '')); ?></div>
          </div>
          <div class="profile-actions">
            <label class="theme-switch" title="Toggle Theme">
              <input type="checkbox" id="themeSwitchInput" style="display:none;">
              <span class="slider">
                <i class="fa-solid fa-moon moon-icon"></i>
                <i class="fa-solid fa-sun sun-icon"></i>
              </span>
            </label>
            <a href="edit_profile.php" class="profile-action">
              <i class="fas fa-user-edit"></i>
              Edit Profile
            </a>
            <form method="post" class="logout-form">
              <button type="submit" name="logout" class="profile-action logout-btn">
                <i class="fas fa-sign-out-alt"></i>
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
          <h1>KPI Records</h1>
          <p>Track and manage Key Performance Indicators for research activities</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn">
            <i class="fas fa-plus"></i>
            Add New KPI
          </button>
        </div>
      </div>

      <!-- Add Entry Modal -->
      <div class="modal" id="addModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New KPI Record</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <div class="form-group">
              <label for="faculty_name">Faculty Name</label>
              <input type="text" id="faculty_name" name="faculty_name" required placeholder="Enter faculty name">
            </div>
            <div class="form-group">
              <label for="period">Period</label>
              <input type="text" id="period" name="period" required placeholder="e.g., Q1 2025">
            </div>
            <div class="form-group">
              <label for="publications">Publications</label>
              <input type="number" id="publications" name="publications" required placeholder="Enter number of publications">
            </div>
            <div class="form-group">
              <label for="trainings">Presentations</label>
              <input type="number" id="trainings" name="trainings" required placeholder="Enter number of presentations">
            </div>
            <div class="form-group">
              <label for="presentations">Research Projects</label>
              <input type="number" id="presentations" name="presentations" required placeholder="Enter number of research projects">
            </div>
            <div class="form-group">
              <label for="kpi_score">KPI Score</label>
              <input type="number" id="kpi_score" name="kpi_score" required placeholder="Enter KPI score">
            </div>
            <div class="form-group">
              <label for="performance">Performance Rating</label>
              <select id="performance" name="performance" required>
                <option value="">Select performance rating</option>
                <option value="Poor">Poor</option>
                <option value="Fair">Fair</option>
                <option value="Good">Good</option>
                <option value="Very Good">Very Good</option>
                <option value="Excellent">Excellent</option>
                <option value="Outstanding">Outstanding</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelAdd">Cancel</button>
              <button type="submit" class="btn btn-primary">Add KPI</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Entry Modal -->
      <div class="modal" id="editModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Edit KPI Record</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="" id="editForm">
            <input type="hidden" name="save_edit" value="1">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
              <label for="editFacultyName">Faculty Name</label>
              <input type="text" id="editFacultyName" name="faculty_name" required>
            </div>
            <div class="form-group">
              <label for="editPeriod">Period</label>
              <input type="text" id="editPeriod" name="period" required>
            </div>
            <div class="form-group">
              <label for="editPublications">Publications</label>
              <input type="number" id="editPublications" name="publications" required>
            </div>
            <div class="form-group">
              <label for="editTrainings">Presentations</label>
              <input type="number" id="editTrainings" name="trainings" required>
            </div>
            <div class="form-group">
              <label for="editPresentations">Research Projects</label>
              <input type="number" id="editPresentations" name="presentations" required>
            </div>
            <div class="form-group">
              <label for="editKpiScore">KPI Score</label>
              <input type="number" id="editKpiScore" name="kpi_score" required>
            </div>
            <div class="form-group">
              <label for="editPerformance">Performance Rating</label>
              <select id="editPerformance" name="performance" required>
                <option value="Poor">Poor</option>
                <option value="Fair">Fair</option>
                <option value="Good">Good</option>
                <option value="Very Good">Very Good</option>
                <option value="Excellent">Excellent</option>
                <option value="Outstanding">Outstanding</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Upload Excel Modal -->
      <div class="modal" id="uploadModal">
        <div class="modal-content upload-modal-simple">
          <div class="modal-header">
            <h3>Upload Excel File</h3>
            <button class="modal-close" id="closeUploadModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <div class="upload-simple-instructions">
              <p><strong>Instructions:</strong></p>
              <ul>
                <li>Upload an Excel file (.xls, .xlsx) or CSV file</li>
                <li>File should contain these columns (in any order):</li>
                <ul>
                  <li><b>Faculty Name</b> (or Name, Researcher Name) - <strong>Required</strong></li>
                  <li><b>Period</b> (or Quarter, Time Period) - <strong>Required</strong></li>
                  <li><b>Publications</b> (or Publications Count, Number of Publications) - <strong>Required</strong></li>
                  <li><b>Presentations</b> (or Presentations Count, Number of Presentations) - <strong>Required</strong></li>
                  <li><b>Research Projects</b> (or Research Projects Count, Projects Count) - <strong>Required</strong></li>
                  <li><b>KPI Score</b> (or Performance Score, Score) - <strong>Required</strong></li>
                  <li><b>Performance Rating</b> (or Rating, Performance Level) - <em>Optional (default: "Good")</em></li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
                <li><strong>Note:</strong> The system will automatically map common column name variations</li>
              </ul>
              <div class="template-download-simple">
                <a href="download_template_kpi_records.php" class="template-link" download>Download Template</a>
              </div>
            </div>
            <form id="uploadForm" enctype="multipart/form-data" class="upload-form-simple">
              <label for="excelFile" class="file-label-simple">Select File</label>
              <input type="file" id="excelFile" name="excel_file" accept=".xls,.xlsx,.csv" required>
              <div class="file-info" id="fileInfo"></div>
              <div class="upload-progress" id="uploadProgress" style="display: none;">
                <div class="progress-bar">
                  <div class="progress-fill"></div>
                </div>
                <div class="progress-text">Uploading...</div>
              </div>
              <div class="upload-result" id="uploadResult" style="display: none;"></div>
            </form>
          </div>
          <div class="modal-footer simple-footer">
            <button type="button" class="btn btn-secondary" id="cancelUpload">Cancel</button>
            <button type="button" class="btn btn-primary" id="submitUpload" disabled>Upload File</button>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="data-card">
        <div class="card-header">
          <div class="card-title">
            <i class="fas fa-bullseye"></i>
            <h2>KPI Records Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search KPIs..." id="searchInput">
          </div>
        </div>
        
        <div class="table-container">
          <form id="bulkDeleteForm" method="post" action="" onsubmit="return confirm('Are you sure you want to delete the selected KPI records?');">
            <div class="bulk-delete-bar">
              <div class="select-all-container">
                <input type="checkbox" id="selectAll" class="styled-checkbox">
                <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
              </div>
              <button type="submit" name="bulk_delete" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
            </div>
            <table class="data-table" id="kpiTable">
              <thead>
                <tr>
                  <th style="width:32px;"></th>
                  <th>Faculty Name</th>
                  <th>Period</th>
                  <th>Publications</th>
                  <th>Presentations</th>
                  <th>Research Projects</th>
                  <th>KPI Score</th>
                  <th>Performance</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr class="empty-state">
                    <td colspan="9">
                      <div class="empty-content">
                        <i class="fas fa-bullseye"></i>
                        <h3>No KPI records found</h3>
                        <p>Add your first KPI record to get started</p>
                        <button class="btn btn-primary" id="addFirstBtn">
                          <i class="fas fa-plus"></i>
                          Add KPI
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $i => $entry): ?>
                  <tr data-id="<?php echo $entry['id']; ?>">
                    <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                    <td data-label="Faculty Name">
                      <div class="kpi-name">
                        <strong><?php echo htmlspecialchars($entry['faculty_name']); ?></strong>
                      </div>
                    </td>
                    <td data-label="Period">
                      <span class="period-info"><?php echo htmlspecialchars($entry['quarter']); ?></span>
                    </td>
                    <td data-label="Publications">
                      <span class="target-value"><?php echo htmlspecialchars($entry['publications_count']); ?></span>
                    </td>
                    <td data-label="Trainings">
                      <span class="actual-value"><?php echo htmlspecialchars($entry['presentations_count']); ?></span>
                    </td>
                    <td data-label="Presentations">
                      <?php echo htmlspecialchars($entry['research_projects_count']); ?>
                    </td>
                    <td data-label="KPI Score">
                      <?php echo htmlspecialchars($entry['performance_score']); ?>
                    </td>
                    <td data-label="Performance">
                      <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry['performance_rating'])); ?>">
                        <?php echo htmlspecialchars($entry['performance_rating']); ?>
                      </span>
                    </td>
                    <td data-label="Actions">
                      <div class="action-buttons">
                        <button class="action-btn edit-btn" data-id="<?php echo $entry['id']; ?>" 
                                data-faculty-name="<?php echo htmlspecialchars($entry['faculty_name']); ?>"
                                data-period="<?php echo htmlspecialchars($entry['quarter']); ?>"
                                data-publications="<?php echo htmlspecialchars($entry['publications_count']); ?>"
                                data-trainings="<?php echo htmlspecialchars($entry['presentations_count']); ?>"
                                data-presentations="<?php echo htmlspecialchars($entry['research_projects_count']); ?>"
                                data-kpi-score="<?php echo htmlspecialchars($entry['performance_score']); ?>"
                                data-performance="<?php echo htmlspecialchars($entry['performance_rating']); ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this KPI record?');">
                          <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                          <button type="submit" name="delete" class="action-btn delete-btn">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </main>

  <style>
    .kpi-name strong {
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .period-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .target-value,
    .actual-value {
      font-weight: 500;
      color: var(--text-primary);
    }
    
    .status-excellent {
      background: #dcfce7;
      color: #166534;
    }
    .status-good {
      background: #fef3c7;
      color: #92400e;
    }
    .status-outstanding {
      background: #c7d2fe;
      color: #1e40af;
    }
    /* Hide checkboxes by default */
    .data-table .styled-checkbox {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }
    .data-table tr:hover .styled-checkbox,
    .data-table tr:focus-within .styled-checkbox {
      opacity: 1;
      pointer-events: auto;
    }
    .data-table .styled-checkbox:checked {
      opacity: 1;
      pointer-events: auto;
    }
    #bulkDeleteForm.show-all-checkboxes .styled-checkbox {
      opacity: 1;
      pointer-events: auto;
    }

    /* Hide select-all container by default */
    #bulkDeleteForm .select-all-container {
      display: none;
    }
    #bulkDeleteForm.show-all-checkboxes .select-all-container {
      display: flex !important;
      align-items: center;
    }

    /* Hide bulk delete button by default */
    #bulkDeleteBtn {
      display: none;
    }
    #bulkDeleteForm.show-all-checkboxes #bulkDeleteBtn {
      display: inline-block;
    }

    /* Custom styled checkbox */
    .styled-checkbox {
      appearance: none;
      -webkit-appearance: none;
      background-color: #232e3e;
      border: 2px solid #4285f4;
      border-radius: 6px;
      width: 24px;
      height: 24px;
      cursor: pointer;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      display: inline-block;
      vertical-align: middle;
      position: relative;
    }
    .styled-checkbox:focus {
      box-shadow: 0 0 0 2px #4285f455;
      border-color: #4285f4;
    }
    .styled-checkbox:checked {
      background-color: #4285f4;
      border-color: #4285f4;
    }
    .styled-checkbox:checked::after {
      content: '';
      display: block;
      position: absolute;
      left: 6px;
      top: 2px;
      width: 8px;
      height: 14px;
      border: solid #fff;
      border-width: 0 3px 3px 0;
      border-radius: 1px;
      transform: rotate(45deg);
      box-sizing: border-box;
    }

    /* Simplified Upload Modal */
    .upload-modal-simple {
      max-width: 400px;
      min-width: 0;
      width: 100%;
      padding: 0;
      background: var(--bg-modal);
      border-radius: 12px;
      box-shadow: var(--shadow-lg);
    }

    .upload-simple-instructions {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: var(--bg-secondary);
      border-radius: 8px;
      border-left: 4px solid var(--primary-color);
    }

    .upload-simple-instructions p {
      margin: 0 0 0.75rem 0;
      color: var(--text-primary);
      font-weight: 600;
    }

    .upload-simple-instructions ul {
      margin: 0;
      padding-left: 1.25rem;
      color: var(--text-secondary);
      font-size: 0.875rem;
      line-height: 1.5;
    }

    .upload-simple-instructions ul ul {
      margin-top: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .upload-simple-instructions li {
      margin-bottom: 0.25rem;
    }

    .template-download-simple {
      margin-top: 1rem;
      text-align: center;
    }

    .template-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: var(--primary-color);
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      transition: background-color 0.2s;
    }

    .template-link:hover {
      background: var(--primary-hover);
      color: white;
    }

    .upload-form-simple {
      padding: 1rem;
    }

    .file-label-simple {
      display: block;
      width: 100%;
      padding: 1rem;
      border: 2px dashed var(--border-color);
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background-color 0.2s;
      color: var(--text-secondary);
      font-weight: 500;
    }

    .file-label-simple:hover {
      border-color: var(--primary-color);
      background: var(--bg-secondary);
    }

    .file-label-simple input[type="file"] {
      display: none;
    }

    .file-info {
      margin-top: 0.75rem;
      padding: 0.75rem;
      background: var(--bg-secondary);
      border-radius: 6px;
      font-size: 0.875rem;
      color: var(--text-secondary);
      display: none;
    }

    .file-info.show {
      display: block;
    }

    .upload-progress {
      margin-top: 1rem;
      padding: 1rem;
      background: var(--bg-secondary);
      border-radius: 8px;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: var(--border-color);
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 0.5rem;
    }

    .progress-fill {
      height: 100%;
      background: var(--primary-color);
      width: 0%;
      transition: width 0.3s ease;
    }

    .progress-text {
      text-align: center;
      font-size: 0.875rem;
      color: var(--text-secondary);
    }

    .upload-result {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 8px;
      font-size: 0.875rem;
    }

    .upload-result.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .upload-result.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .modal-footer.simple-footer {
      padding: 1rem;
      border-top: 1px solid var(--border-color);
      display: flex;
      gap: 0.75rem;
      justify-content: flex-end;
    }
  </style>

  <script src="../js/theme.js"></script>
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

    // Modal functionality
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const uploadModal = document.getElementById('uploadModal');
    const addBtn = document.getElementById('addBtn');
    const addFirstBtn = document.getElementById('addFirstBtn');
    const closeAddModal = document.getElementById('closeAddModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const closeUploadModal = document.getElementById('closeUploadModal');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');
    const cancelUpload = document.getElementById('cancelUpload');

    function openModal(modal) {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    addBtn.addEventListener('click', () => openModal(addModal));
    if (addFirstBtn) addFirstBtn.addEventListener('click', () => openModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));
    cancelAdd.addEventListener('click', () => closeModal(addModal));
    closeEditModal.addEventListener('click', () => closeModal(editModal));
    cancelEdit.addEventListener('click', () => closeModal(editModal));
    closeUploadModal.addEventListener('click', () => closeModal(uploadModal));
    cancelUpload.addEventListener('click', () => closeModal(uploadModal));

    // Close modal when clicking outside
    [addModal, editModal, uploadModal].forEach(modal => {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          closeModal(modal);
        }
      });
    });

    // Edit functionality
    document.addEventListener('click', (e) => {
      if (e.target.closest('.edit-btn')) {
        const btn = e.target.closest('.edit-btn');
        const id = btn.dataset.id;
        const facultyName = btn.dataset.facultyName;
        const period = btn.dataset.period;
        const publications = btn.dataset.publications;
        const trainings = btn.dataset.trainings;
        const presentations = btn.dataset.presentations;
        const kpiScore = btn.dataset.kpiScore;
        const performance = btn.dataset.performance;

        document.getElementById('editId').value = id;
        document.getElementById('editFacultyName').value = facultyName;
        document.getElementById('editPeriod').value = period;
        document.getElementById('editPublications').value = publications;
        document.getElementById('editTrainings').value = trainings;
        document.getElementById('editPresentations').value = presentations;
        document.getElementById('editKpiScore').value = kpiScore;
        document.getElementById('editPerformance').value = performance;

        openModal(editModal);
      }
    });

    // Dark mode toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    const themeText = themeToggle.querySelector('span');
    
    // Check current theme
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
      document.body.classList.add('dark-theme');
      themeIcon.className = 'fas fa-sun';
      themeText.textContent = 'Light Mode';
    }
    
    themeToggle.addEventListener('click', () => {
      const isDark = document.body.classList.contains('dark-theme');
      
      if (isDark) {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('theme', 'light');
        themeIcon.className = 'fas fa-moon';
        themeText.textContent = 'Dark Mode';
      } else {
        document.body.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark');
        themeIcon.className = 'fas fa-sun';
        themeText.textContent = 'Light Mode';
      }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#kpiTable tbody tr');

    searchInput.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase();
      
      tableRows.forEach(row => {
        if (row.classList.contains('empty-state')) return;
        
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Upload functionality
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadForm = document.getElementById('uploadForm');
    const excelFileInput = document.getElementById('excelFile');
    const fileInfo = document.getElementById('fileInfo');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadResult = document.getElementById('uploadResult');
    const submitUpload = document.getElementById('submitUpload');

    // Debug logging
    console.log('Upload elements found:', {
      uploadBtn: !!uploadBtn,
      uploadForm: !!uploadForm,
      excelFileInput: !!excelFileInput,
      fileInfo: !!fileInfo,
      uploadProgress: !!uploadProgress,
      uploadResult: !!uploadResult,
      submitUpload: !!submitUpload,
      uploadModal: !!uploadModal
    });

    if (uploadBtn) {
      uploadBtn.addEventListener('click', () => {
        console.log('Upload button clicked');
        openModal(uploadModal);
      });
    } else {
      console.error('Upload button not found');
    }

    // File selection handling
    if (excelFileInput) {
      excelFileInput.addEventListener('change', (e) => {
        console.log('File selected:', e.target.files[0]);
        const file = e.target.files[0];
        if (file) {
          const fileSize = (file.size / 1024 / 1024).toFixed(2);
          fileInfo.innerHTML = `
            <strong>Selected file:</strong> ${file.name}<br>
            <strong>Size:</strong> ${fileSize} MB<br>
            <strong>Type:</strong> ${file.type || 'Unknown'}
          `;
          fileInfo.classList.add('show');
          submitUpload.disabled = false;
          console.log('File info updated, submit button enabled');
        } else {
          fileInfo.classList.remove('show');
          submitUpload.disabled = true;
          console.log('No file selected, submit button disabled');
        }
      });
    } else {
      console.error('Excel file input not found');
    }

    // Upload form submission
    if (submitUpload) {
      submitUpload.addEventListener('click', async () => {
        console.log('Submit upload clicked');
        const formData = new FormData(uploadForm);
        const file = excelFileInput.files[0];
        
        console.log('File to upload:', file);
        
        if (!file) {
          alert('Please select a file first.');
          return;
        }

      // Show progress
      uploadProgress.style.display = 'block';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
      
      try {
        console.log('Sending fetch request to upload_excel_kpi_records.php');
        const response = await fetch('upload_excel_kpi_records.php', {
          method: 'POST',
          body: formData
        });
        
        console.log('Response received:', response);
        const result = await response.json();
        console.log('Result:', result);
        
        // Hide progress
        uploadProgress.style.display = 'none';
        
        // Show result
        uploadResult.style.display = 'block';
        uploadResult.className = `upload-result ${result.success ? 'success' : 'error'}`;
        
        let errorDetails = '';
        if (result.data) {
          if (result.data.errors && result.data.errors.length > 0) {
            errorDetails += '<br><br><strong>Row Errors:</strong><br>' + result.data.errors.join('<br>');
          }
          if (result.data.found_headers) {
            errorDetails += '<br><br><strong>Found Headers:</strong><br>' + result.data.found_headers.join(', ');
          }
          if (result.data.matched_columns) {
            errorDetails += '<br><br><strong>Matched Columns:</strong><br>' + result.data.matched_columns.join(', ');
          }
          if (result.data.missing_columns) {
            errorDetails += '<br><br><strong>Missing Required Columns:</strong><br>' + result.data.missing_columns.join(', ');
          }
          if (result.data.unmatched_headers) {
            errorDetails += '<br><br><strong>Unmatched Headers:</strong><br>' + result.data.unmatched_headers.join(', ');
          }
          if (result.data.missing_optional_columns && result.data.missing_optional_columns.length > 0) {
            errorDetails += '<br><br><strong>Missing Optional Columns (defaults applied):</strong><br>' + result.data.missing_optional_columns.join(', ');
          }
        }
        
        uploadResult.innerHTML = `
          <strong>${result.success ? 'Success!' : 'Error:'}</strong><br>
          ${result.message}
          ${errorDetails}
        `;
        
        if (result.success) {
          // Reset form
          uploadForm.reset();
          fileInfo.classList.remove('show');
          submitUpload.disabled = true;
          
          // Reload page after 2 seconds to show new data
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        }
      } catch (error) {
        console.error('Upload error:', error);
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'block';
        uploadResult.className = 'upload-result error';
        uploadResult.innerHTML = `
          <strong>Error:</strong><br>
          Failed to upload file. Please try again.<br>
          Error details: ${error.message}
        `;
        submitUpload.disabled = false;
      }
    } else {
      console.error('Submit upload button not found');
    }

    // Reset upload form when modal is closed
    if (closeUploadModal) {
      closeUploadModal.addEventListener('click', () => {
        console.log('Close upload modal clicked');
        closeModal(uploadModal);
        uploadForm.reset();
        fileInfo.classList.remove('show');
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'none';
        submitUpload.disabled = true;
      });
    } else {
      console.error('Close upload modal button not found');
    }

    if (cancelUpload) {
      cancelUpload.addEventListener('click', () => {
        console.log('Cancel upload clicked');
        closeModal(uploadModal);
        uploadForm.reset();
        fileInfo.classList.remove('show');
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'none';
        submitUpload.disabled = true;
      });
    } else {
      console.error('Cancel upload button not found');
    }

    if (uploadModal) {
      uploadModal.addEventListener('click', (e) => {
        if (e.target === uploadModal) {
          console.log('Upload modal background clicked');
          closeModal(uploadModal);
          uploadForm.reset();
          fileInfo.classList.remove('show');
          uploadProgress.style.display = 'none';
          uploadResult.style.display = 'none';
          submitUpload.disabled = true;
        }
      });
    } else {
      console.error('Upload modal not found');
    }

    // Bulk delete button enable/disable and show-all-checkboxes logic
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    function updateBulkDeleteBtn() {
      let checkedCount = 0;
      rowCheckboxes.forEach(cb => { if (cb.checked) checkedCount++; });
      if (checkedCount > 0) {
        bulkDeleteBtn.disabled = false;
        bulkDeleteForm.classList.add('show-all-checkboxes');
      } else {
        bulkDeleteBtn.disabled = true;
        bulkDeleteForm.classList.remove('show-all-checkboxes');
      }
      // Update selectAll checkbox state
      if (selectAll) {
        if (checkedCount === rowCheckboxes.length && rowCheckboxes.length > 0) {
          selectAll.checked = true;
          selectAll.indeterminate = false;
        } else if (checkedCount > 0) {
          selectAll.checked = false;
          selectAll.indeterminate = true;
        } else {
          selectAll.checked = false;
          selectAll.indeterminate = false;
        }
      }
    }
    rowCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateBulkDeleteBtn);
    });
    if (selectAll) {
      selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => { cb.checked = selectAll.checked; });
        updateBulkDeleteBtn();
      });
    }
    updateBulkDeleteBtn();
  </script>
</body>
</html> 