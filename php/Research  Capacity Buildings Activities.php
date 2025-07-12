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

// Handle GET messages from upload
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

// Handle form submission (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // Bulk delete entries
        if (isset($_POST['bulk_delete']) && !empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            $ids = array_map('intval', $_POST['selected_ids']);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("DELETE FROM research_capacity_activities WHERE id IN ($placeholders)", $ids);
                $success_message = count($ids) . ' activities deleted successfully!';
            } else {
                $error_message = 'No activities selected for deletion.';
            }
        }
        // Delete entry (updated to match ethics protocol page)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            try {
                $db = getDB();
                $db->query("DELETE FROM research_capacity_activities WHERE id = ?", [$id]);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
        // Save edited entry
        elseif (isset($_POST['save_edit']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $date = $_POST['date'] ?? '';
            $name = $_POST['name'] ?? '';
            $venue = $_POST['venue'] ?? '';
            $facilitators = $_POST['facilitators'] ?? '';
            $num_participants = (int)($_POST['num_participants'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if ($date && $name && $venue && $facilitators && $status) {
                $db->query("UPDATE research_capacity_activities SET activity_date = ?, activity_title = ?, venue = ?, organizer = ?, participants_count = ?, status = ? WHERE id = ?", 
                    [$date, $name, $venue, $facilitators, $num_participants, $status, $id]);
                $success_message = 'Activity updated successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
        // Add new entry
        elseif (isset($_POST['add_entry'])) {
            $date = $_POST['date'] ?? '';
            $name = $_POST['name'] ?? '';
            $venue = $_POST['venue'] ?? '';
            $facilitators = $_POST['facilitators'] ?? '';
            $num_participants = (int)($_POST['num_participants'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if ($date && $name && $venue && $facilitators && $status) {
                $db->query("INSERT INTO research_capacity_activities (activity_date, activity_title, venue, organizer, participants_count, status) VALUES (?, ?, ?, ?, ?, ?)", 
                    [$date, $name, $venue, $facilitators, $num_participants, $status]);
                $success_message = 'Activity added successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
    
    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Read all entries from database
$entries = [];
try {
    $db = getDB();
    $entries = $db->fetchAll("SELECT * FROM research_capacity_activities ORDER BY activity_date DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load activities: ' . $e->getMessage();
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
        <a href="Research  Capacity Buildings Activities.php" class="nav-link active">
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
        <a href="KPI records.php" class="nav-link">
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
          <h1>Research Capacity Building Activities</h1>
          <p>Track and manage research capacity building initiatives and training programs</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn">
            <i class="fas fa-plus"></i>
            Add New Activity
          </button>
        </div>
      </div>

      <!-- Messages -->
      <?php if ($success_message): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($error_message): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <!-- Add Entry Modal -->
      <div class="modal" id="addModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New Research Activity</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <input type="hidden" name="add_entry" value="1">
            <div class="form-group">
              <label for="date">Date</label>
              <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
              <label for="name">Activity Name</label>
              <input type="text" id="name" name="name" required placeholder="Enter activity name">
            </div>
            <div class="form-group">
              <label for="venue">Venue</label>
              <input type="text" id="venue" name="venue" required placeholder="Enter venue">
            </div>
            <div class="form-group">
              <label for="facilitators">Facilitators</label>
              <input type="text" id="facilitators" name="facilitators" required placeholder="Enter facilitators">
            </div>
            <div class="form-group">
              <label for="num_participants">Number of Participants</label>
              <input type="number" id="num_participants" name="num_participants" required placeholder="Enter number of participants">
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" name="status" required>
                <option value="">Select status</option>
                <option value="Completed">Completed</option>
                <option value="In Progress">In Progress</option>
                <option value="Scheduled">Scheduled</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelAdd">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Activity</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Entry Modal -->
      <div class="modal" id="editModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Edit Research Activity</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="" id="editForm">
            <input type="hidden" name="save_edit" value="1">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
              <label for="editDate">Date</label>
              <input type="date" id="editDate" name="date" required>
            </div>
            <div class="form-group">
              <label for="editName">Activity Name</label>
              <input type="text" id="editName" name="name" required>
            </div>
            <div class="form-group">
              <label for="editVenue">Venue</label>
              <input type="text" id="editVenue" name="venue" required>
            </div>
            <div class="form-group">
              <label for="editFacilitators">Facilitators</label>
              <input type="text" id="editFacilitators" name="facilitators" required>
            </div>
            <div class="form-group">
              <label for="editNumParticipants">Number of Participants</label>
              <input type="number" id="editNumParticipants" name="num_participants" required>
            </div>
            <div class="form-group">
              <label for="editStatus">Status</label>
              <select id="editStatus" name="status" required>
                <option value="Completed">Completed</option>
                <option value="In Progress">In Progress</option>
                <option value="Scheduled">Scheduled</option>
                <option value="Cancelled">Cancelled</option>
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
                  <li><b>Date</b> (YYYY-MM-DD)</li>
                  <li><b>Activity Name</b></li>
                  <li><b>Venue</b></li>
                  <li><b>Facilitators</b></li>
                  <li><b>Number of Participants</b></li>
                  <li><b>Status</b> (Scheduled, In Progress, Completed, Cancelled)</li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
              </ul>
              <div class="template-download-simple">
                <a href="download_template.php" class="template-link" download>Download Template</a>
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
            <i class="fas fa-chart-line"></i>
            <h2>Research Capacity Activities Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search activities..." id="searchInput">
          </div>
        </div>
        
        <form id="bulkDeleteForm" method="post" action="">
          <div class="bulk-delete-bar">
            <div class="select-all-container">
              <input type="checkbox" id="selectAll" class="styled-checkbox">
              <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
            </div>
            <button type="submit" name="bulk_delete" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
          </div>
          <table class="data-table" id="activitiesTable">
            <thead>
              <tr>
                <th style="width:32px;"></th>
                <th>Date</th>
                <th>Activity Name</th>
                <th>Venue</th>
                <th>Facilitators</th>
                <th>Participants</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($entries)): ?>
                <tr class="empty-state">
                  <td colspan="8">
                    <div class="empty-content">
                      <i class="fas fa-chart-line"></i>
                      <h3>No research activities found</h3>
                      <p>Add your first research capacity building activity to get started</p>
                      <button class="btn btn-primary" id="addFirstBtn">
                        <i class="fas fa-plus"></i>
                        Add Activity
                      </button>
                    </div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($entries as $entry): ?>
                <tr data-id="<?php echo $entry['id']; ?>">
                  <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                  <td data-label="Date">
                    <span class="date-info"><?php echo htmlspecialchars($entry['activity_date']); ?></span>
                  </td>
                  <td data-label="Activity Name">
                    <div class="activity-title">
                      <h4><?php echo htmlspecialchars($entry['activity_title']); ?></h4>
                    </div>
                  </td>
                  <td data-label="Venue">
                    <span class="venue-info"><?php echo htmlspecialchars($entry['venue']); ?></span>
                  </td>
                  <td data-label="Facilitators">
                    <span class="facilitators-info"><?php echo htmlspecialchars($entry['organizer']); ?></span>
                  </td>
                  <td data-label="Participants">
                    <span class="participants-count"><?php echo htmlspecialchars($entry['participants_count']); ?></span>
                  </td>
                  <td data-label="Status">
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry['status'])); ?>">
                      <?php echo htmlspecialchars($entry['status']); ?>
                    </span>
                  </td>
                  <td data-label="Actions">
                    <div class="action-buttons">
                      <button type="button" class="action-btn edit-btn" data-id="<?php echo $entry['id']; ?>" 
                              data-date="<?php echo htmlspecialchars($entry['activity_date']); ?>"
                              data-name="<?php echo htmlspecialchars($entry['activity_title']); ?>"
                              data-venue="<?php echo htmlspecialchars($entry['venue']); ?>"
                              data-facilitators="<?php echo htmlspecialchars($entry['organizer']); ?>"
                              data-participants="<?php echo htmlspecialchars($entry['participants_count']); ?>"
                              data-status="<?php echo htmlspecialchars($entry['status']); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this activity?');">
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
  </main>

  <style>
    .date-info {
      color: #18813a !important;
      font-weight: 600 !important;
    }
    
    .activity-title h4 {
      font-weight: 500;
      color: #18813a !important;
      margin-bottom: 4px;
      line-height: 1.4;
    }
    
    .venue-info,
    .facilitators-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
    }
    
    .participants-count {
      /* Remove background and color styling for plain text */
      background: none;
      color: inherit;
      padding: 0;
      border-radius: 0;
      font-size: inherit;
      font-weight: inherit;
    }

    /* Simplified Upload Modal */
    .upload-modal-simple {
      max-width: 400px;
      min-width: 0;
      width: 100%;
      padding: 0;
      background: #e3f2fd; /* Match .data-card */
      border-radius: 16px;
      box-shadow: 0 6px 24px 0 rgba(0,119,182,0.12);
      border: 1.5px solid #b3e0ff;
    }
    .upload-modal-simple .modal-header {
      background: #ff9800;
      color: #fff;
      border-radius: 16px 16px 0 0;
      padding: 20px 24px 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1.5px solid #b3e0ff;
    }
    .upload-modal-simple .modal-header h3 {
      font-size: 1.2rem;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }
    .upload-modal-simple .modal-close {
      background: none;
      border: none;
      color: #fff;
      font-size: 1.3rem;
      cursor: pointer;
      border-radius: 6px;
      padding: 2px 6px;
      transition: background 0.2s, color 0.2s;
    }
    .upload-modal-simple .modal-close:hover {
      background: #fff3cd;
      color: #ff9800;
    }
    .upload-modal-simple .modal-body {
      background: #e3f2fd;
      padding: 0 24px 18px 24px;
      border-radius: 0 0 16px 16px;
    }
    .upload-simple-instructions {
      background: #fff;
      border-radius: 10px;
      padding: 1.2rem 1.2rem 0.5rem 1.2rem;
      font-size: 1rem;
      color: #145c2c;
      margin-bottom: 1rem;
      border: 1px solid #b3e0ff;
      box-shadow: 0 2px 8px 0 rgba(0,119,182,0.06);
    }
    .upload-simple-instructions p strong {
      color: #18813a;
    }
    .upload-simple-instructions ul {
      color: #145c2c;
      font-size: 0.97rem;
      margin: 0.5rem 0 0.5rem 1.2rem;
      padding-left: 1.2rem;
    }
    .upload-simple-instructions ul ul {
      font-size: 0.95rem;
      margin: 0.2rem 0 0.2rem 1.2rem;
    }
    .upload-simple-instructions li b {
      color: #18813a;
      font-weight: 600;
    }
    .upload-simple-instructions li {
      margin-bottom: 0.2em;
    }
    .template-download-simple {
      margin: 1rem 0 0.5rem 0;
    }
    .template-link {
      color: #ff9800;
      text-decoration: underline;
      font-size: 0.98rem;
      font-weight: 500;
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
    }
    .template-link:hover {
      color: #f57c00;
      text-decoration: none;
    }
    .upload-form-simple {
      padding: 0 1.2rem 1.2rem 1.2rem;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
    }
    .file-label-simple {
      font-weight: 600;
      color: #18813a;
      font-size: 1rem;
      margin-bottom: 0.2rem;
    }
    #excelFile {
      border: 1px solid #b3e0ff;
      border-radius: 6px;
      padding: 0.5rem 0.75rem;
      font-size: 0.97rem;
      background: #fff;
      color: #145c2c;
      width: 100%;
      margin-bottom: 0.2rem;
    }
    #excelFile:focus {
      outline: 2px solid #ff9800;
      border-color: #ff9800;
    }
    .file-info {
      margin-top: 0.2rem;
      padding: 0.4rem 0.7rem;
      background: #b3e0ff;
      border-radius: 4px;
      font-size: 0.93rem;
      color: #145c2c;
      width: 100%;
      word-break: break-all;
    }
    .upload-progress {
      margin-top: 0.5rem;
      background: #e3f2fd;
      border-radius: 6px;
      padding: 0.5rem 0.7rem;
      box-shadow: 0 1px 4px 0 rgba(0,119,182,0.06);
    }
    .progress-bar {
      background: #b3e0ff;
      border-radius: 6px;
      height: 8px;
      width: 100%;
      margin-bottom: 0.3rem;
      overflow: hidden;
    }
    .progress-fill {
      background: linear-gradient(90deg, #ff9800 0%, #f57c00 100%);
      height: 100%;
      width: 0%;
      border-radius: 6px;
      transition: width 0.3s;
    }
    .progress-text {
      font-size: 0.93rem;
      color: #18813a;
      font-weight: 600;
      text-align: center;
    }
    .upload-result {
      margin-top: 0.5rem;
      padding: 0.7rem 1rem;
      border-radius: 6px;
      font-size: 0.97rem;
      font-weight: 500;
      background: #fff3cd;
      color: #18813a;
      border: 1px solid #ffe082;
      box-shadow: 0 1px 4px 0 rgba(255,193,7,0.06);
    }
    .upload-result.error {
      background: #ffebee;
      color: #e53935;
      border: 1px solid #ffcdd2;
    }
    .upload-result.success {
      background: #e8f5e9;
      color: #18813a;
      border: 1px solid #b9f6ca;
    }
    .simple-footer {
      padding: 1rem 1.2rem 1rem 1.2rem;
      border-top: 1px solid #b3e0ff;
      background: #e3f2fd;
      border-radius: 0 0 16px 16px;
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
    }
    .simple-footer .btn-primary {
      background: #ff9800;
      color: #000;
      font-weight: 700;
      border-radius: 8px;
      border: none;
      box-shadow: 0 2px 8px 0 rgba(255,152,0,0.10);
      transition: background 0.2s, color 0.2s;
    }
    .simple-footer .btn-primary:hover {
      background: #f57c00;
      color: #fff;
    }
    .simple-footer .btn-secondary {
      background: #b3e0ff;
      color: #000;
      font-weight: 700;
      border-radius: 8px;
      border: none;
      box-shadow: 0 2px 8px 0 rgba(179,224,255,0.10);
      transition: background 0.2s, color 0.2s;
    }
    .simple-footer .btn-secondary:hover {
      background: #90caf9;
      color: #18813a;
    }
    @media (max-width: 600px) {
      .upload-modal-simple {
        max-width: 98vw;
        min-width: 0;
        padding: 0;
      }
      .upload-simple-instructions, .upload-form-simple {
        padding-left: 0.7rem;
        padding-right: 0.7rem;
      }
      .simple-footer {
        padding-left: 0.7rem;
        padding-right: 0.7rem;
      }
    }
    .bulk-delete-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    .select-all-container {
      display: none;
    }
    .select-all-container.visible {
      display: flex;
    }

    /* Modern custom checkbox styles */
    input[type="checkbox"].styled-checkbox, #selectAll.styled-checkbox {
      appearance: none;
      -webkit-appearance: none;
      background-color: #fffbeb !important;
      border: 2px solid #ffb300 !important;
      width: 22px;
      height: 22px;
      border-radius: 6px;
      display: inline-block;
      position: relative;
      cursor: pointer;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      vertical-align: middle;
      margin: 0;
    }
    input[type="checkbox"].styled-checkbox:checked, #selectAll.styled-checkbox:checked {
      background-color: #ffb300 !important;
      border-color: #ffb300 !important;
    }
    input[type="checkbox"].styled-checkbox:checked::after, #selectAll.styled-checkbox:checked::after {
      content: '';
      position: absolute;
      left: 6px;
      top: 2px;
      width: 6px;
      height: 12px;
      border: solid #fff !important;
      border-width: 0 3px 3px 0 !important;
      border-radius: 1px;
      transform: rotate(45deg);
      transition: border-color 0.2s;
    }
    input[type="checkbox"].styled-checkbox:focus, #selectAll.styled-checkbox:focus {
      box-shadow: 0 0 0 2px var(--btn-primary-bg, #3b82f6);
    }
    /* Hide default checkmark for indeterminate state, show custom style */
    #selectAll.styled-checkbox:indeterminate {
      background-color: #ffb300 !important;
      border-color: #ffb300 !important;
    }
    #selectAll.styled-checkbox:indeterminate::after {
      content: '';
      position: absolute;
      left: 4px;
      top: 9px;
      width: 14px;
      height: 3px;
      background: #fff;
      border-radius: 2px;
    }
    /* Make label clickable and align nicely */
    .select-all-container label {
      cursor: pointer;
      user-select: none;
      margin-bottom: 0;
      margin-left: 0.5em;
      font-size: 1em;
      color: var(--text-primary);
    }
    /* Slightly increase row checkbox spacing */
    .data-table td:first-child, .data-table th:first-child {
      text-align: center;
      width: 36px;
    }

    /* Hide row checkboxes by default, show on row hover or if any checked */
    .data-table .row-checkbox {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }
    .data-table tr:hover .row-checkbox,
    .data-table.show-all-checkboxes .row-checkbox {
      opacity: 1;
      pointer-events: auto;
    }
    /* Always show select-all checkbox above the table */
    .select-all-container .styled-checkbox {
      opacity: 1 !important;
      pointer-events: auto !important;
    }

    /* Table readability fix for UC theme */
    .data-table th {
      color: #1976d2 !important;
      font-weight: 700 !important;
      background: #e3f2fd !important;
    }
    /* Make all table data cell text and info fields black for maximum readability */
    .data-table td,
    .date-info,
    .venue-info,
    .facilitators-info,
    .participants-count,
    .activity-title h4 {
      color: #111 !important;
    }
    .data-table tr {
      background: #e3f2fd !important;
    }
    .data-table tr.empty-state td {
      color: #1976d2 !important;
      background: #e3f2fd !important;
    }

    /* UC Table Theme for Research Capacity Activities Overview */
    .data-table th {
      background: #18813a !important;
      color: #fff !important;
      font-weight: 700 !important;
      border-bottom: 2px solid #b3e0ff !important;
    }
    .data-table td {
      background: #f4fbff !important;
      color: #145c2c !important;
      font-weight: 600 !important;
      border-bottom: 1px solid #b3e0ff !important;
    }
    .data-table tr:nth-child(even) td {
      background: #e3f2fd !important;
    }
    .data-table tr:hover td {
      background: #d0e7f9 !important;
      color: #18813a !important;
    }

    /* Match Research Capacity Activities Overview card background to Recent Updates */
    .data-card {
      background: #e3f2fd !important;
    }

    .venue-info {
      color: #000 !important;
      font-weight: 600 !important;
    }
    .facilitators-info {
      color: #000 !important;
      font-weight: 600 !important;
    }

    /* Make all data-label text black for maximum readability */
    .data-table td[data-label],
    .data-table th[data-label] {
      color: #111 !important;
    }

    /* Make data-labels in responsive/mobile view visible */
    .data-table td[data-label]::before {
      color: #111 !important;
      font-weight: 700 !important;
      letter-spacing: 0.5px;
    }

  </style>
  <style>
    :root {
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
    .profile-btn .fa-chevron-down, .profile-btn .fa-chevron-down {
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
    /* Hide theme toggle switch/button */
    .theme-switch, .theme-toggle {
      display: none !important;
    }
    
    /* Page actions button text color - make it black */
    .page-actions .btn {
      color: #000 !important;
    }
    .page-actions .btn-primary {
      color: #000 !important;
    }
    .page-actions .btn-secondary {
      color: #000 !important;
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

    // Theme toggle within profile dropdown
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (window.themeManager) {
          window.themeManager.toggleTheme();
          // Update the button text based on current theme
          const currentTheme = window.themeManager.getCurrentTheme();
          const icon = themeToggle.querySelector('i');
          const text = themeToggle.querySelector('span');
          
          if (currentTheme === 'dark') {
            icon.className = 'fas fa-sun';
            text.textContent = 'Light Mode';
          } else {
            icon.className = 'fas fa-moon';
            text.textContent = 'Dark Mode';
          }
        }
      });
    }

    // Initialize theme toggle button state
    document.addEventListener('DOMContentLoaded', () => {
      if (window.themeManager && themeToggle) {
        const currentTheme = window.themeManager.getCurrentTheme();
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        
        if (currentTheme === 'dark') {
          icon.className = 'fas fa-sun';
          text.textContent = 'Light Mode';
        } else {
          icon.className = 'fas fa-moon';
          text.textContent = 'Dark Mode';
        }
      }
    });

    // Modal functionality
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const uploadModal = document.getElementById('uploadModal');
    const addBtn = document.getElementById('addBtn');
    const addFirstBtn = document.getElementById('addFirstBtn');
    const uploadBtn = document.getElementById('uploadBtn');
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
    uploadBtn.addEventListener('click', () => openModal(uploadModal));
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
        const date = btn.dataset.date;
        const name = btn.dataset.name;
        const venue = btn.dataset.venue;
        const facilitators = btn.dataset.facilitators;
        const participants = btn.dataset.participants;
        const status = btn.dataset.status;

        document.getElementById('editId').value = id;
        document.getElementById('editDate').value = date;
        document.getElementById('editName').value = name;
        document.getElementById('editVenue').value = venue;
        document.getElementById('editFacilitators').value = facilitators;
        document.getElementById('editNumParticipants').value = participants;
        document.getElementById('editStatus').value = status;

        openModal(editModal);
      }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#activitiesTable tbody tr');

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
    const excelFile = document.getElementById('excelFile');
    const fileInfo = document.getElementById('fileInfo');
    const submitUpload = document.getElementById('submitUpload');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadResult = document.getElementById('uploadResult');
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');

    // File selection handler
    excelFile.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        fileInfo.innerHTML = `
          <strong>Selected file:</strong> ${file.name}<br>
          <strong>Size:</strong> ${fileSize} MB<br>
          <strong>Type:</strong> ${file.type || 'Unknown'}
        `;
        submitUpload.disabled = false;
        
        // Hide any previous results
        uploadResult.style.display = 'none';
      } else {
        fileInfo.innerHTML = '';
        submitUpload.disabled = true;
      }
    });

    // Upload submission handler
    submitUpload.addEventListener('click', async () => {
      const file = excelFile.files[0];
      if (!file) return;

      // Show progress
      uploadProgress.style.display = 'block';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
      
      // Simulate progress
      let progress = 0;
      const progressInterval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        progressFill.style.width = progress + '%';
      }, 200);

      try {
        const formData = new FormData();
        formData.append('excel_file', file);

        const response = await fetch('upload_excel_research_capacity.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();
        
        clearInterval(progressInterval);
        progressFill.style.width = '100%';
        progressText.textContent = 'Upload complete!';

        // Show result
        setTimeout(() => {
          uploadProgress.style.display = 'none';
          uploadResult.style.display = 'block';
          
          if (result.success) {
            uploadResult.className = 'upload-result success';
            uploadResult.innerHTML = `
              <i class="fas fa-check-circle"></i>
              <strong>Success!</strong> ${result.message}
              ${result.data.errors && result.data.errors.length > 0 ? 
                `<br><br><strong>Errors:</strong><br>${result.data.errors.join('<br>')}` : ''}
            `;
            
            // Reload page after successful upload to show new data
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            uploadResult.className = 'upload-result error';
            uploadResult.innerHTML = `
              <i class="fas fa-exclamation-circle"></i>
              <strong>Error:</strong> ${result.message}
            `;
          }
          
          submitUpload.disabled = false;
        }, 500);

      } catch (error) {
        clearInterval(progressInterval);
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'block';
        uploadResult.className = 'upload-result error';
        uploadResult.innerHTML = `
          <i class="fas fa-exclamation-circle"></i>
          <strong>Error:</strong> Upload failed. Please try again.
        `;
        submitUpload.disabled = false;
      }
    });

    // Reset upload form when modal is closed
    function resetUploadForm() {
      excelFile.value = '';
      fileInfo.innerHTML = '';
      uploadProgress.style.display = 'none';
      uploadResult.style.display = 'none';
      progressFill.style.width = '0%';
      progressText.textContent = 'Uploading...';
      submitUpload.disabled = true;
    }

    // Add reset to close handlers
    closeUploadModal.addEventListener('click', () => {
      closeModal(uploadModal);
      resetUploadForm();
    });
    
    cancelUpload.addEventListener('click', () => {
      closeModal(uploadModal);
      resetUploadForm();
    });

    // Bulk delete button enable/disable
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    const selectAllContainer = document.querySelector('.select-all-container');
    function updateBulkDeleteBtn() {
      let checkedCount = 0;
      rowCheckboxes.forEach(cb => { if (cb.checked) checkedCount++; });
      if (checkedCount > 0) {
        bulkDeleteBtn.style.display = '';
        bulkDeleteBtn.disabled = false;
      } else {
        bulkDeleteBtn.style.display = 'none';
        bulkDeleteBtn.disabled = true;
      }
      // Show select-all if at least 1 is checked OR select-all is checked/indeterminate
      if (selectAllContainer) {
        if (
          checkedCount > 0 ||
          (selectAll && (selectAll.checked || selectAll.indeterminate))
        ) {
          selectAllContainer.classList.add('visible');
        } else {
          selectAllContainer.classList.remove('visible');
        }
      }
      // Show all checkboxes if any are checked
      const dataTable = document.getElementById('activitiesTable');
      if (dataTable) {
        if (checkedCount > 0) {
          dataTable.classList.add('show-all-checkboxes');
        } else {
          dataTable.classList.remove('show-all-checkboxes');
        }
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

    // Bulk delete form submit handler
    document.addEventListener('DOMContentLoaded', function() {
      const bulkDeleteForm = document.getElementById('bulkDeleteForm');
      if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', function(e) {
          const checkboxes = bulkDeleteForm.querySelectorAll('.row-checkbox:checked');
          if (checkboxes.length === 0) {
            // Prevent form submission if nothing is selected
            e.preventDefault();
            return false;
          }
          // Show confirmation only if at least one is checked
          if (!confirm('Are you sure you want to delete the selected activities?')) {
            e.preventDefault();
            return false;
          }
          // Otherwise, allow form to submit
        });
      }
    });

  </script>
</body>
</html>