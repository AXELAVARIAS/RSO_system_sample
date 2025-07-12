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
            $db->query("DELETE FROM publication_presentations WHERE id = ?", [$id]);
            $success_message = 'Publication deleted successfully!';
        }
        // Handle bulk delete
        elseif (isset($_POST['bulk_delete']) && isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            $selected_ids = array_map('intval', $_POST['selected_ids']);
            if (!empty($selected_ids)) {
                $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                $db->query("DELETE FROM publication_presentations WHERE id IN ($placeholders)", $selected_ids);
                $success_message = count($selected_ids) . ' publication(s) deleted successfully!';
            } else {
                $error_message = 'No publications selected for deletion.';
            }
        }
        // Handle edit save
        elseif (isset($_POST['save_edit']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $date = $_POST['date'] ?? '';
            $author = $_POST['author'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $subsidy = $_POST['subsidy'] ?? '';
            $status = $_POST['status'] ?? '';
            $local_international = $_POST['local_international'] ?? '';
            
            if ($date && $author && $title && $department && $subsidy && $status && $local_international) {
                $db->query("UPDATE publication_presentations SET application_date = ?, author_name = ?, paper_title = ?, department = ?, research_subsidy = ?, status = ?, scope = ? WHERE id = ?", 
                    [$date, $author, $title, $department, $subsidy, $status, $local_international, $id]);
                $success_message = 'Publication updated successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
        // Handle add
        else {
            $date = $_POST['date'] ?? '';
            $author = $_POST['author'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $subsidy = $_POST['subsidy'] ?? '';
            $status = $_POST['status'] ?? '';
            $local_international = $_POST['local_international'] ?? '';
            
            if ($date && $author && $title && $department && $subsidy && $status && $local_international) {
                $db->query("INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                    [$date, $author, $title, $department, $subsidy, $status, $local_international]);
                $success_message = 'Publication added successfully!';
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
    $entries = $db->fetchAll("SELECT * FROM publication_presentations ORDER BY application_date DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load publications: ' . $e->getMessage();
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
  <title>Publication and Presentation</title>
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
        <a href="Publication and Presentation.php" class="nav-link active">
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
          <h1>Publication and Presentation</h1>
          <p>Manage research publications, presentations, and academic outputs</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn" type="button">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn" type="button">
            <i class="fas fa-plus"></i>
            Add New Publication
          </button>
        </div>
      </div>

      <!-- Add Entry Modal -->
      <div class="modal" id="addModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New Publication</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <div class="form-group">
              <label for="date">Date OF Application</label>
              <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
              <label for="author">Name(s) of faculty/research worker</label>
              <input type="text" id="author" name="author" required placeholder="Enter name(s)">
            </div>
            <div class="form-group">
              <label for="title">Title of Paper</label>
              <input type="text" id="title" name="title" required placeholder="Enter title of paper">
            </div>
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" id="department" name="department" required placeholder="Enter department">
            </div>
            <div class="form-group">
              <label for="subsidy">Research Subsidy</label>
              <input type="text" id="subsidy" name="subsidy" required placeholder="Enter research subsidy">
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" name="status" required>
                <option value="">Select status</option>
                <option value="Draft">Draft</option>
                <option value="Submitted">Submitted</option>
                <option value="Under Review">Under Review</option>
                <option value="Accepted">Accepted</option>
                <option value="Published">Published</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="form-group">
              <label for="local_international">Local/International</label>
              <select id="local_international" name="local_international" required>
                <option value="">Select scope</option>
                <option value="Local">Local</option>
                <option value="International">International</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelAdd">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Publication</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Entry Modal -->
      <div class="modal" id="editModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Edit Publication</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="" id="editForm">
            <input type="hidden" name="save_edit" value="1">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
              <label for="editDate">Date OF Application</label>
              <input type="date" id="editDate" name="date" required>
            </div>
            <div class="form-group">
              <label for="editAuthor">Name(s) of faculty/research worker</label>
              <input type="text" id="editAuthor" name="author" required>
            </div>
            <div class="form-group">
              <label for="editTitle">Title of Paper</label>
              <input type="text" id="editTitle" name="title" required>
            </div>
            <div class="form-group">
              <label for="editDepartment">Department</label>
              <input type="text" id="editDepartment" name="department" required>
            </div>
            <div class="form-group">
              <label for="editSubsidy">Research Subsidy</label>
              <input type="text" id="editSubsidy" name="subsidy" required>
            </div>
            <div class="form-group">
              <label for="editStatus">Status</label>
              <select id="editStatus" name="status" required>
                <option value="Draft">Draft</option>
                <option value="Submitted">Submitted</option>
                <option value="Under Review">Under Review</option>
                <option value="Accepted">Accepted</option>
                <option value="Published">Published</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editLocalInternational">Local/International</label>
              <select id="editLocalInternational" name="local_international" required>
                <option value="Local">Local</option>
                <option value="International">International</option>
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
                  <li><b>Date OF Application</b> (YYYY-MM-DD, DD/MM/YYYY, or MM/DD/YYYY) - <strong>Required</strong></li>
                  <li><b>Name(s) of faculty/research worker</b> (or Faculty Name, Author Name) - <strong>Required</strong></li>
                  <li><b>Title of Paper</b> (or Research Title, Paper Title) - <strong>Required</strong></li>
                  <li><b>Research Subsidy</b> (or Funding, Grant Amount, Ownership) - <strong>Required</strong></li>
                  <li><b>Department</b> (or Faculty, School, College) - <em>Optional (default: "Not Specified")</em></li>
                  <li><b>Status</b> (Draft, Submitted, Under Review, Accepted, Published, Rejected) - <em>Optional (default: "Draft")</em></li>
                  <li><b>Local/International</b> (Local, International) - <em>Optional (default: "Local")</em></li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
                <li><strong>Note:</strong> The system will automatically map common column name variations</li>
              </ul>
              <div class="template-download-simple">
                <a href="download_template_publications.php" class="template-link" download>Download Template</a>
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
            <i class="fas fa-book"></i>
            <h2>Publications Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search publications..." id="searchInput">
          </div>
        </div>
        
        <div class="table-container">
          <div class="bulk-delete-bar">
            <div class="select-all-container">
              <input type="checkbox" id="selectAll" class="styled-checkbox">
              <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
            </div>
            <button type="button" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
          </div>
          <table class="data-table" id="publicationsTable">
              <thead>
                <tr>
                  <th style="width:32px;"></th>
                  <th>Date OF Application</th>
                  <th>Name(s) of faculty/research worker</th>
                  <th>Title of Paper</th>
                  <th>Department</th>
                  <th>Research Subsidy</th>
                  <th>Status</th>
                  <th>Local/International</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr class="empty-state">
                    <td colspan="9">
                      <div class="empty-content">
                        <i class="fas fa-book"></i>
                        <h3>No publications found</h3>
                        <p>Add your first publication to get started</p>
                        <button class="btn btn-primary" id="addFirstBtn" type="button">
                          <i class="fas fa-plus"></i>
                          Add Publication
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $i => $entry): ?>
                  <tr data-id="<?php echo $entry['id']; ?>">
                    <td><input type="checkbox" class="row-checkbox styled-checkbox" value="<?php echo $entry['id']; ?>"></td>
                    <td data-label="Date OF Application">
                      <span class="date-info"><?php echo htmlspecialchars($entry['application_date']); ?></span>
                    </td>
                    <td data-label="Name(s) of faculty/research worker">
                      <div class="author-info">
                        <strong><?php echo htmlspecialchars($entry['author_name']); ?></strong>
                      </div>
                    </td>
                    <td data-label="Title of Paper">
                      <div class="title-content">
                        <h4><?php echo htmlspecialchars($entry['paper_title']); ?></h4>
                      </div>
                    </td>
                    <td data-label="Department">
                      <span class="journal-info"><?php echo htmlspecialchars($entry['department']); ?></span>
                    </td>
                    <td data-label="Research Subsidy">
                      <span class="impact-factor"><?php echo htmlspecialchars($entry['research_subsidy']); ?></span>
                    </td>
                    <td data-label="Status">
                      <span class="citations-count"><?php echo htmlspecialchars($entry['status']); ?></span>
                    </td>
                    <td data-label="Local/International">
                      <span class="citations-count"><?php echo htmlspecialchars($entry['scope']); ?></span>
                    </td>
                    <td data-label="Actions">
                      <div class="action-buttons">
                        <button class="action-btn edit-btn" data-id="<?php echo $entry['id']; ?>" 
                                data-date="<?php echo htmlspecialchars($entry['application_date']); ?>"
                                data-author="<?php echo htmlspecialchars($entry['author_name']); ?>"
                                data-title="<?php echo htmlspecialchars($entry['paper_title']); ?>"
                                data-department="<?php echo htmlspecialchars($entry['department']); ?>"
                                data-subsidy="<?php echo htmlspecialchars($entry['research_subsidy']); ?>"
                                data-status="<?php echo htmlspecialchars($entry['status']); ?>"
                                data-local-international="<?php echo htmlspecialchars($entry['scope']); ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="action-btn delete-btn" data-id="<?php echo $entry['id']; ?>">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
        </div>
      </div>
    </div>
  </main>

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
    /* Checkbox yellow styling - bigger size */
    input[type="checkbox"].styled-checkbox, #selectAll.styled-checkbox {
      appearance: none;
      -webkit-appearance: none;
      width: 24px !important;
      height: 24px !important;
      background-color: #fff3cd !important;
      border: 3px solid #ffc107 !important;
      border-radius: 6px;
      cursor: pointer;
      position: relative;
      transition: all 0.2s ease;
    }
    
    input[type="checkbox"].styled-checkbox:checked, #selectAll.styled-checkbox:checked {
      background-color: #ffc107 !important;
      border-color: #ffc107 !important;
    }
    
    input[type="checkbox"].styled-checkbox:checked::after, #selectAll.styled-checkbox:checked::after {
      content: '';
      position: absolute;
      left: 7px;
      top: 3px;
      width: 6px;
      height: 12px;
      border: solid #fff !important;
      border-width: 0 3px 3px 0 !important;
      transform: rotate(45deg);
    }
    
    input[type="checkbox"].styled-checkbox:hover, #selectAll.styled-checkbox:hover {
      background-color: #fff8e1 !important;
      border-color: #ffb300 !important;
      transform: scale(1.05);
    }
    
    input[type="checkbox"].styled-checkbox:checked:hover, #selectAll.styled-checkbox:checked:hover {
      background-color: #ffb300 !important;
      border-color: #ffb300 !important;
    }
    
    /* Hide checkboxes by default, show on hover */
    .data-table .row-checkbox {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
    
    /* Show checkbox when row is hovered */
    .data-table tr:hover .row-checkbox {
      opacity: 1;
      pointer-events: auto;
    }
    
    /* Always show checkbox if it's checked */
    .data-table .row-checkbox:checked {
      opacity: 1;
      pointer-events: auto;
    }
    /* Always show select-all checkbox above the table */
    .select-all-container .styled-checkbox {
      opacity: 1 !important;
      pointer-events: auto !important;
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
    /* Info fields font color */
    .author-info,
    .title-content h4,
    .department-info,
    .subsidy-info,
    .date-info,
    .scope-info {
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
        const date = btn.dataset.date;
        const author = btn.dataset.author;
        const title = btn.dataset.title;
        const department = btn.dataset.department;
        const subsidy = btn.dataset.subsidy;
        const status = btn.dataset.status;
        const localInternational = btn.dataset.localInternational || btn.dataset['local-international'];

        document.getElementById('editId').value = id;
        document.getElementById('editDate').value = date;
        document.getElementById('editAuthor').value = author;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDepartment').value = department;
        document.getElementById('editSubsidy').value = subsidy;
        document.getElementById('editStatus').value = status;
        document.getElementById('editLocalInternational').value = localInternational;

        openModal(editModal);
      }
    });

    // Individual delete functionality
    document.addEventListener('click', (e) => {
      if (e.target.closest('.delete-btn')) {
        const btn = e.target.closest('.delete-btn');
        const id = btn.dataset.id;
        
        if (confirm('Are you sure you want to delete this publication?')) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>';
          
          const idInput = document.createElement('input');
          idInput.type = 'hidden';
          idInput.name = 'id';
          idInput.value = id;
          
          const deleteInput = document.createElement('input');
          deleteInput.type = 'hidden';
          deleteInput.name = 'delete';
          deleteInput.value = '1';
          
          form.appendChild(idInput);
          form.appendChild(deleteInput);
          document.body.appendChild(form);
          form.submit();
        }
      }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#publicationsTable tbody tr');

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

    uploadBtn.addEventListener('click', () => openModal(uploadModal));

    // File selection handling
    excelFileInput.addEventListener('change', (e) => {
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
      } else {
        fileInfo.classList.remove('show');
        submitUpload.disabled = true;
      }
    });

    // Upload form submission
    submitUpload.addEventListener('click', async () => {
      const formData = new FormData(uploadForm);
      const file = excelFileInput.files[0];
      
      if (!file) {
        alert('Please select a file first.');
        return;
      }

      // Show progress
      uploadProgress.style.display = 'block';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
      
      try {
        const response = await fetch('upload_excel_publications.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
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
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'block';
        uploadResult.className = 'upload-result error';
        uploadResult.innerHTML = `
          <strong>Error:</strong><br>
          Failed to upload file. Please try again.
        `;
        submitUpload.disabled = false;
      }
    });

    // Reset upload form when modal is closed
    closeUploadModal.addEventListener('click', () => {
      closeModal(uploadModal);
      uploadForm.reset();
      fileInfo.classList.remove('show');
      uploadProgress.style.display = 'none';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
    });

    cancelUpload.addEventListener('click', () => {
      closeModal(uploadModal);
      uploadForm.reset();
      fileInfo.classList.remove('show');
      uploadProgress.style.display = 'none';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
    });

    uploadModal.addEventListener('click', (e) => {
      if (e.target === uploadModal) {
        closeModal(uploadModal);
        uploadForm.reset();
        fileInfo.classList.remove('show');
        uploadProgress.style.display = 'none';
        uploadResult.style.display = 'none';
        submitUpload.disabled = true;
      }
    });

    // Bulk delete button enable/disable and show-all-checkboxes logic
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
      const dataTable = document.getElementById('publicationsTable');
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
    
    // Bulk delete functionality
    bulkDeleteBtn.addEventListener('click', function() {
      const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
      if (selectedCheckboxes.length === 0) {
        alert('Please select at least one publication to delete.');
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${selectedCheckboxes.length} publication(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>';
        
        const bulkDeleteInput = document.createElement('input');
        bulkDeleteInput.type = 'hidden';
        bulkDeleteInput.name = 'bulk_delete';
        bulkDeleteInput.value = '1';
        form.appendChild(bulkDeleteInput);
        
        selectedCheckboxes.forEach(cb => {
          const idInput = document.createElement('input');
          idInput.type = 'hidden';
          idInput.name = 'selected_ids[]';
          idInput.value = cb.value;
          form.appendChild(idInput);
        });
        
        document.body.appendChild(form);
        form.submit();
      }
    });
    
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