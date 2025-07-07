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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // Handle bulk delete
        if (isset($_POST['bulk_delete']) && !empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            $ids = array_map('intval', $_POST['selected_ids']);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("DELETE FROM data_collection_tools WHERE id IN ($placeholders)", $ids);
                $success_message = count($ids) . ' entries deleted successfully!';
            } else {
                $error_message = 'No entries selected for deletion.';
            }
        }
        // Handle delete
        elseif (isset($_POST['delete']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM data_collection_tools WHERE id = ?", [$id]);
            $success_message = 'Entry deleted successfully!';
        }
        // Handle edit save
        elseif (isset($_POST['save_edit']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $faculty = $_POST['faculty'] ?? '';
            $degree = $_POST['degree'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $title = $_POST['title'] ?? '';
            $ownership = $_POST['ownership'] ?? '';
            $presented = $_POST['presented'] ?? '';
            $published = $_POST['published'] ?? '';
            $journal = $_POST['journal'] ?? '';
            
            if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
                $db->query("UPDATE data_collection_tools SET researcher_name = ?, degree = ?, gender = ?, research_title = ?, role = ?, location = ?, submission_date = ?, research_area = ? WHERE id = ?", 
                    [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal, $id]);
                $success_message = 'Entry updated successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
        // Handle add
        else {
            $faculty = $_POST['faculty'] ?? '';
            $degree = $_POST['degree'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $title = $_POST['title'] ?? '';
            $ownership = $_POST['ownership'] ?? '';
            $presented = $_POST['presented'] ?? '';
            $published = $_POST['published'] ?? '';
            $journal = $_POST['journal'] ?? '';
            
            if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
                $db->query("INSERT INTO data_collection_tools (researcher_name, degree, gender, research_title, role, location, submission_date, research_area) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                    [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal]);
                $success_message = 'Entry added successfully!';
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
    $entries = $db->fetchAll("SELECT * FROM data_collection_tools ORDER BY submission_date DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load entries: ' . $e->getMessage();
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
        <a href="Data Collection Tools.php" class="nav-link active">
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
          <h1>Data Collection Tools</h1>
          <p>Manage research data collection tools and methodologies</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn">
            <i class="fas fa-plus"></i>
            Add New Entry
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
            <h3>Add New Data Collection Tool</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <div class="form-group">
              <label for="faculty">Faculty Name</label>
              <input type="text" id="faculty" name="faculty" required placeholder="Enter faculty name">
            </div>
            <div class="form-group">
              <label for="degree">Degree</label>
              <select id="degree" name="degree" required>
                <option value="">Select degree</option>
                <option value="Ph.D.">Ph.D.</option>
                <option value="M.S.">M.S.</option>
                <option value="M.A.">M.A.</option>
                <option value="B.S.">B.S.</option>
                <option value="B.A.">B.A.</option>
              </select>
            </div>
            <div class="form-group">
              <label for="sex">Sex</label>
              <select id="sex" name="sex" required>
                <option value="">Select sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="form-group">
              <label for="title">Research Title</label>
              <input type="text" id="title" name="title" required placeholder="Enter research title">
            </div>
            <div class="form-group">
              <label for="ownership">Ownership</label>
              <select id="ownership" name="ownership" required>
                <option value="">Select ownership</option>
                <option value="Author">Author</option>
                <option value="Co-Author">Co-Author</option>
                <option value="Contributor">Contributor</option>
              </select>
            </div>
            <div class="form-group">
              <label for="presented">Presented At</label>
              <input type="text" id="presented" name="presented" required placeholder="Enter presentation venue">
            </div>
            <div class="form-group">
              <label for="published">Published Date</label>
              <input type="date" id="published" name="published" required>
            </div>
            <div class="form-group">
              <label for="journal">Journal/Publication</label>
              <input type="text" id="journal" name="journal" required placeholder="Enter journal name">
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelAdd">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Entry</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Entry Modal -->
      <div class="modal" id="editModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Edit Data Collection Tool</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="" id="editForm">
            <input type="hidden" name="save_edit" value="1">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
              <label for="editFaculty">Faculty Name</label>
              <input type="text" id="editFaculty" name="faculty" required>
            </div>
            <div class="form-group">
              <label for="editDegree">Degree</label>
              <select id="editDegree" name="degree" required>
                <option value="Ph.D.">Ph.D.</option>
                <option value="M.S.">M.S.</option>
                <option value="M.A.">M.A.</option>
                <option value="B.S.">B.S.</option>
                <option value="B.A.">B.A.</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editSex">Sex</label>
              <select id="editSex" name="sex" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editTitle">Research Title</label>
              <input type="text" id="editTitle" name="title" required>
            </div>
            <div class="form-group">
              <label for="editOwnership">Ownership</label>
              <select id="editOwnership" name="ownership" required>
                <option value="Author">Author</option>
                <option value="Co-Author">Co-Author</option>
                <option value="Contributor">Contributor</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editPresented">Presented At</label>
              <input type="text" id="editPresented" name="presented" required>
            </div>
            <div class="form-group">
              <label for="editPublished">Published Date</label>
              <input type="date" id="editPublished" name="published" required>
            </div>
            <div class="form-group">
              <label for="editJournal">Journal/Publication</label>
              <input type="text" id="editJournal" name="journal" required>
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
        <div class="modal-content">
          <div class="modal-header">
            <h3>Upload Excel File</h3>
            <button class="modal-close" id="closeUploadModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" id="uploadExcelForm" enctype="multipart/form-data">
            <ul style="margin-bottom:1em;">
              <li>Upload an Excel file (.xls, .xlsx) or CSV file</li>
              <li>File should contain these columns (in any order):
                <ul style="margin-top:0.3em;">
                  <li><b>Faculty Name</b></li>
                  <li><b>Degree</b> <span style="color:#888;">(Ph.D., M.S., M.A., B.S., B.A.)</span></li>
                  <li><b>Sex</b> <span style="color:#888;">(Male, Female)</span></li>
                  <li><b>Research Title</b></li>
                  <li><b>Ownership</b> <span style="color:#888;">(Author, Co-Author, Contributor)</span></li>
                  <li><b>Presented At</b></li>
                  <li><b>Published Date</b> <span style="color:#888;">(YYYY-MM-DD)</span></li>
                  <li><b>Journal/Publication</b></li>
                </ul>
              </li>
              <li>First row should contain column headers</li>
              <li>Maximum file size: 5MB</li>
            </ul>
            <a href="download_template_data_collection_tools.php" target="_blank" style="margin-bottom:1em;display:inline-block;">Download Template</a>
            <div class="form-group" style="margin-top:1em;">
              <label for="excelFile" style="font-weight:600;">Select File</label>
              <input type="file" id="excelFile" name="excel_file" accept=".xls,.xlsx,.csv" required>
              <div id="fileInfo" style="margin-top:0.5em; font-size:0.97em; color:#ccc;"></div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelUpload">Cancel</button>
              <button type="submit" class="btn btn-primary">Upload File</button>
            </div>
            <div id="uploadResult" style="margin-top:1em;"></div>
            <div id="uploadLoading" style="margin-top:1em; display:none; text-align:center;">
              <i class="fas fa-spinner fa-spin" style="font-size:1.5em;"></i>
              <span style="margin-left:0.5em;">Uploading...</span>
            </div>
          </form>
        </div>
      </div>

      <!-- Data Table -->
      <div class="data-card">
        <div class="card-header">
          <div class="card-title">
            <i class="fas fa-database"></i>
            <h2>Data Collection Tools Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search tools..." id="searchInput">
          </div>
        </div>
        <form id="bulkDeleteForm" method="post" action="" onsubmit="return confirm('Are you sure you want to delete the selected entries?');">
          <div class="bulk-delete-bar">
            <div class="select-all-container">
              <input type="checkbox" id="selectAll" class="styled-checkbox">
              <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
            </div>
            <button type="submit" name="bulk_delete" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
          </div>
          <div class="table-container">
            <table class="data-table" id="toolsTable">
              <thead>
                <tr>
                  <th style="width:32px;"></th>
                  <th>Faculty Name</th>
                  <th>Degree</th>
                  <th>Sex</th>
                  <th>Research Title</th>
                  <th>Ownership</th>
                  <th>Presented At</th>
                  <th>Published Date</th>
                  <th>Journal/Publication</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr class="empty-state">
                    <td colspan="10">
                      <div class="empty-content">
                        <i class="fas fa-database"></i>
                        <h3>No data collection tools found</h3>
                        <p>Add your first data collection tool to get started</p>
                        <button class="btn btn-primary" id="addFirstBtn">
                          <i class="fas fa-plus"></i>
                          Add Tool
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $entry): ?>
                  <tr data-id="<?php echo $entry['id']; ?>">
                    <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                    <td data-label="Faculty Name">
                      <div class="faculty-info">
                        <strong><?php echo htmlspecialchars($entry['researcher_name']); ?></strong>
                      </div>
                    </td>
                    <td data-label="Degree">
                      <?php echo htmlspecialchars($entry['degree']); ?>
                    </td>
                    <td data-label="Sex">
                      <?php echo htmlspecialchars($entry['gender']); ?>
                    </td>
                    <td data-label="Research Title">
                      <div class="title-content">
                        <h4><?php echo htmlspecialchars($entry['research_title']); ?></h4>
                      </div>
                    </td>
                    <td data-label="Ownership">
                      <?php echo htmlspecialchars($entry['role']); ?>
                    </td>
                    <td data-label="Presented At">
                      <span class="presentation-info"><?php echo htmlspecialchars($entry['location']); ?></span>
                    </td>
                    <td data-label="Published Date">
                      <span class="date-info"><?php echo htmlspecialchars($entry['submission_date']); ?></span>
                    </td>
                    <td data-label="Journal/Publication">
                      <span class="journal-info"><?php echo htmlspecialchars($entry['research_area']); ?></span>
                    </td>
                    <td data-label="Actions">
                      <div class="action-buttons">
                        <button class="action-btn edit-btn" data-id="<?php echo $entry['id']; ?>" 
                                data-faculty="<?php echo htmlspecialchars($entry['researcher_name']); ?>"
                                data-degree="<?php echo htmlspecialchars($entry['degree']); ?>"
                                data-sex="<?php echo htmlspecialchars($entry['gender']); ?>"
                                data-title="<?php echo htmlspecialchars($entry['research_title']); ?>"
                                data-ownership="<?php echo htmlspecialchars($entry['role']); ?>"
                                data-presented="<?php echo htmlspecialchars($entry['location']); ?>"
                                data-published="<?php echo htmlspecialchars($entry['submission_date']); ?>"
                                data-journal="<?php echo htmlspecialchars($entry['research_area']); ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
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
          </div>
        </form>
      </div>
    </div>
  </main>

  <style>
    .faculty-info strong {
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .title-content h4 {
      font-weight: 500;
      color: var(--text-primary);
      margin-bottom: 4px;
      line-height: 1.4;
    }
    
    .presentation-info,
    .date-info,
    .journal-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
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
      display: inline-block;
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
      background: var(--primary-dark);
    }

    .upload-form-simple {
      padding: 0 1rem;
    }

    .file-label-simple {
      display: block;
      width: 100%;
      padding: 1rem;
      border: 2px dashed var(--border-color);
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      color: var(--text-secondary);
      transition: all 0.2s;
      margin-bottom: 1rem;
    }

    .file-label-simple:hover {
      border-color: var(--primary-color);
      color: var(--primary-color);
    }

    .file-label-simple input[type="file"] {
      display: none;
    }

    .file-info {
      margin-bottom: 1rem;
      padding: 0.75rem;
      background: var(--bg-secondary);
      border-radius: 6px;
      font-size: 0.875rem;
      color: var(--text-secondary);
    }

    .upload-progress {
      margin-bottom: 1rem;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: var(--bg-secondary);
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
      margin-bottom: 1rem;
      padding: 0.75rem;
      border-radius: 6px;
      font-size: 0.875rem;
    }

    .upload-result.success {
      background: var(--success-bg);
      color: var(--success-text);
      border: 1px solid var(--success-border);
    }

    .upload-result.error {
      background: var(--error-bg);
      color: var(--error-text);
      border: 1px solid var(--error-border);
    }

    .modal-footer.simple-footer {
      padding: 1rem;
      border-top: 1px solid var(--border-color);
      display: flex;
      gap: 0.75rem;
      justify-content: flex-end;
    }

    .template-link.prominent {
      background: var(--primary-color);
      color: #fff;
      font-weight: 600;
      font-size: 1em;
      padding: 0.6em 1.2em;
      border-radius: 6px;
      margin-left: 0.5em;
      text-decoration: none;
      display: inline-block;
      transition: background 0.2s;
    }
    .template-link.prominent:hover {
      background: var(--primary-dark);
      color: #fff;
    }
    .upload-instructions ol {
      padding-left: 1.2em;
      color: var(--text-primary);
      font-size: 1em;
    }
    .upload-instructions ul {
      margin: 0.3em 0 0.3em 1.2em;
      font-size: 0.97em;
    }
    .hint {
      color: var(--text-secondary);
      font-size: 0.92em;
      font-style: italic;
      margin-left: 0.3em;
    }
    .drag-area {
      border: 2px dashed var(--primary-color);
      background: var(--bg-secondary);
      border-radius: 10px;
      padding: 1.5em 0.5em;
      text-align: center;
      cursor: pointer;
      margin-bottom: 1em;
      transition: border-color 0.2s, background 0.2s;
    }
    .drag-area:hover, .drag-area:focus-within {
      border-color: var(--primary-dark);
      background: var(--primary-light);
    }
    .drag-text {
      font-size: 1.1em;
      color: var(--text-secondary);
      margin-top: 0.5em;
      display: inline-block;
    }
    .modal-header h2 {
      font-size: 1.4em;
      font-weight: 700;
      color: var(--primary-color);
      display: flex;
      align-items: center;
      gap: 0.5em;
    }
    .upload-instructions.card-style {
      background: var(--bg-secondary);
      border-radius: 10px;
      padding: 1.2em 1.2em 1em 1.2em;
      margin-bottom: 1.2em;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .upload-list {
      padding-left: 1.2em;
      margin-bottom: 0.7em;
      color: var(--text-primary);
      font-size: 1em;
    }
    .upload-list ul {
      margin: 0.3em 0 0.3em 1.2em;
      font-size: 0.97em;
    }
    .template-link.simple-link {
      color: var(--primary-color);
      font-weight: 500;
      text-decoration: underline;
      font-size: 1em;
      margin-top: 0.5em;
      display: inline-block;
    }
    .template-link.simple-link:hover {
      color: var(--primary-dark);
    }
    .file-label-simple {
      font-size: 1em;
      color: var(--text-primary);
    }
    .hint {
      color: var(--text-secondary);
      font-size: 0.92em;
      font-style: italic;
      margin-left: 0.3em;
    }
    .modal-header h3 {
      font-size: 1.2em;
      font-weight: 700;
      color: var(--primary-color);
      margin: 0;
    }

    /* Hide checkboxes by default */
    .data-table .styled-checkbox {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }

    /* Show checkbox on row hover */
    .data-table tr:hover .styled-checkbox,
    .data-table tr:focus-within .styled-checkbox {
      opacity: 1;
      pointer-events: auto;
    }

    /* Show checkbox if checked */
    .data-table .styled-checkbox:checked {
      opacity: 1;
      pointer-events: auto;
    }

    /* Show all checkboxes if table has .show-all-checkboxes (JS toggles this for bulk select) */
    .data-table.show-all-checkboxes .styled-checkbox {
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
      background-color: #232e3e;   /* dark background */
      border: 2px solid #4285f4;   /* blue border */
      border-radius: 6px;          /* rounded corners */
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

    /* Custom checkmark */
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
    const addBtn = document.getElementById('addBtn');
    const addFirstBtn = document.getElementById('addFirstBtn');
    const closeAddModal = document.getElementById('closeAddModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelAdd = document.getElementById('cancelAdd');
    const cancelEdit = document.getElementById('cancelEdit');

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

    // Close modal when clicking outside
    [addModal, editModal].forEach(modal => {
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
        const faculty = btn.dataset.faculty;
        const degree = btn.dataset.degree;
        const sex = btn.dataset.sex;
        const title = btn.dataset.title;
        const ownership = btn.dataset.ownership;
        const presented = btn.dataset.presented;
        const published = btn.dataset.published;
        const journal = btn.dataset.journal;

        document.getElementById('editId').value = id;
        document.getElementById('editFaculty').value = faculty;
        document.getElementById('editDegree').value = degree;
        document.getElementById('editSex').value = sex;
        document.getElementById('editTitle').value = title;
        document.getElementById('editOwnership').value = ownership;
        document.getElementById('editPresented').value = presented;
        document.getElementById('editPublished').value = published;
        document.getElementById('editJournal').value = journal;

        openModal(editModal);
      }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#toolsTable tbody tr');

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

    // Upload Excel AJAX logic (refactored for consistency)
    const uploadExcelForm = document.getElementById('uploadExcelForm');
    const uploadResult = document.getElementById('uploadResult');
    const uploadLoading = document.getElementById('uploadLoading');
    const excelFileInput = document.getElementById('excelFile');
    const fileInfo = document.getElementById('fileInfo');
    if (excelFileInput && fileInfo) {
      excelFileInput.addEventListener('change', function() {
        if (excelFileInput.files && excelFileInput.files.length > 0) {
          const file = excelFileInput.files[0];
          fileInfo.innerHTML = `<b>Selected file:</b> ${file.name}<br>Size: ${(file.size/1024/1024).toFixed(2)} MB<br>Type: ${file.type || 'N/A'}`;
        } else {
          fileInfo.textContent = '';
        }
      });
    }
    if (uploadExcelForm) {
      uploadExcelForm.addEventListener('submit', function(e) {
        e.preventDefault();
        uploadResult.textContent = '';
        uploadResult.style.display = 'none';
        uploadLoading.style.display = 'block';
        const formData = new FormData(uploadExcelForm);
        fetch('upload_excel_data_collection_tools.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          uploadLoading.style.display = 'none';
          uploadResult.style.display = 'block';
          if (data.success) {
            uploadResult.innerHTML = `
              <div style="background:#1e4620; color:#d4f8e8; border-radius:8px; padding:1em; display:flex; align-items:center; gap:0.7em; font-size:1.08em; border:1.5px solid #2ecc40;">
                <i class='fas fa-check-circle' style='font-size:1.5em; color:#2ecc40;'></i>
                <div><b>Success!</b> ${data.message}</div>
              </div>
            `;
            setTimeout(() => { window.location.reload(); }, 1800);
          } else {
            uploadResult.innerHTML = `
              <div style="background:#4d2323; color:#ffd6d6; border-radius:8px; padding:1em; display:flex; align-items:flex-start; gap:0.7em; font-size:1.08em; border:1.5px solid #e74c3c;">
                <i class='fas fa-exclamation-circle' style='font-size:1.5em; color:#e74c3c;'></i>
                <div><b>Error!</b> ${data.message}
                  ${(data.data && data.data.errors) ? '<ul style='margin:0.5em 0 0 1.2em; color:#ffd6d6;'>' + data.data.errors.map(e => '<li>' + e + '</li>').join('') + '</ul>' : ''}
                </div>
              </div>
            `;
          }
        })
        .catch(err => {
          uploadLoading.style.display = 'none';
          uploadResult.style.display = 'block';
          uploadResult.innerHTML = `
            <div style="background:#4d2323; color:#ffd6d6; border-radius:8px; padding:1em; display:flex; align-items:center; gap:0.7em; font-size:1.08em; border:1.5px solid #e74c3c;">
              <i class='fas fa-exclamation-circle' style='font-size:1.5em; color:#e74c3c;'></i>
              <div><b>Error!</b> Upload failed.</div>
            </div>
          `;
        });
      });
    }

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
        bulkDeleteBtn.disabled = checkedCount < 2;
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
      const bulkDeleteForm = document.getElementById('bulkDeleteForm');
      if (bulkDeleteForm) {
        if (checkedCount > 0) {
          bulkDeleteForm.classList.add('show-all-checkboxes');
        } else {
          bulkDeleteForm.classList.remove('show-all-checkboxes');
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
  </script>
</body>
</html> 