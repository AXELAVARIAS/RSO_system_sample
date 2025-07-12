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
                  <li><b>Faculty Name</b></li>
                  <li><b>Degree</b> (Ph.D., M.S., M.A., B.S., B.A.)</li>
                  <li><b>Sex</b> (Male, Female)</li>
                  <li><b>Research Title</b></li>
                  <li><b>Ownership</b> (Author, Co-Author, Contributor)</li>
                  <li><b>Presented At</b></li>
                  <li><b>Published Date</b> (YYYY-MM-DD)</li>
                  <li><b>Journal/Publication</b></li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
              </ul>
              <div class="template-download-simple">
                <a href="download_template_data_collection_tools.php" class="template-link" download>Download Template</a>
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
            <i class="fas fa-database"></i>
            <h2>Data Collection Tools Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search tools..." id="searchInput">
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

    /* Upload Modal Styles */
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
    /* Table and data label font color fixes */
    .data-card {
      background: #e3f2fd !important;
    }
    .data-table th {
      background: #18813a !important;
      color: #fff !important;
      font-weight: 700 !important;
      border-bottom: 2px solid #b3e0ff !important;
    }
    .data-table td,
    .data-table tbody tr {
      background: #b3e0ff !important;
      color: #111 !important;
      font-weight: 600 !important;
    }
    .data-table td[data-label]::before {
      color: #111 !important;
      font-weight: 700 !important;
      letter-spacing: 0.5px;
    }
    /* Checkbox yellow styling */
    input[type="checkbox"].styled-checkbox, #selectAll.styled-checkbox {
      background-color: #fffbeb !important;
      border: 2px solid #ffb300 !important;
    }
    input[type="checkbox"].styled-checkbox:checked, #selectAll.styled-checkbox:checked {
      background-color: #ffb300 !important;
      border-color: #ffb300 !important;
    }
    input[type="checkbox"].styled-checkbox:checked::after, #selectAll.styled-checkbox:checked::after {
      border-color: #fff !important;
    }
    /* Info fields font color */
    .faculty-info strong,
    .title-content h4,
    .presentation-info,
    .date-info,
    .journal-info {
      color: #111 !important;
      font-weight: 600 !important;
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

        const response = await fetch('upload_excel_data_collection_tools.php', {
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
          if (!confirm('Are you sure you want to delete the selected entries?')) {
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