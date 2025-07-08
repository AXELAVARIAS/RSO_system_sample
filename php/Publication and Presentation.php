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
          <h1>Publication and Presentation</h1>
          <p>Manage research publications, presentations, and academic outputs</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn">
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
          <form id="bulkDeleteForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return confirm('Are you sure you want to delete the selected publications?');">
            <div class="bulk-delete-bar">
              <div class="select-all-container">
                <input type="checkbox" id="selectAll" class="styled-checkbox">
                <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
              </div>
              <button type="submit" name="bulk_delete" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
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
                        <button class="btn btn-primary" id="addFirstBtn">
                          <i class="fas fa-plus"></i>
                          Add Publication
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $i => $entry): ?>
                  <tr data-id="<?php echo $entry['id']; ?>">
                    <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
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
          </form>
        </div>
      </div>
    </div>
  </main>

  <style>
    .date-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .author-info strong {
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .title-content h4 {
      font-weight: 500;
      color: var(--text-primary);
      margin-bottom: 4px;
      line-height: 1.4;
    }
    
    .journal-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
    }
    
    .impact-factor {
      /* Remove background and color styling for plain text */
      background: none;
      color: inherit;
      padding: 0;
      border-radius: 0;
      font-size: inherit;
      font-weight: inherit;
    }
    
    .citations-count {
      /* Remove background and color styling for plain text */
      background: none;
      color: inherit;
      padding: 0;
      border-radius: 0;
      font-size: inherit;
      font-weight: inherit;
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
    
    // Add form submission debugging
    bulkDeleteForm.addEventListener('submit', function(e) {
      const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
      if (selectedCheckboxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one publication to delete.');
        return false;
      }
      
      const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
      console.log('Submitting bulk delete with IDs:', selectedIds);
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