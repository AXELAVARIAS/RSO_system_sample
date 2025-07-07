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
            $db->query("DELETE FROM ethics_reviewed_protocols WHERE id = ?", [$id]);
            $success_message = 'Protocol deleted successfully!';
        }
        // Handle edit save
        elseif (isset($_POST['save_edit']) && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $no = $_POST['no'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $status = $_POST['status'] ?? '';
            $action = $_POST['action'] ?? '';
            
            if ($no && $title && $department && $status && $action) {
                $db->query("UPDATE ethics_reviewed_protocols SET protocol_number = ?, title = ?, department = ?, status = ?, action_taken = ? WHERE id = ?", 
                    [$no, $title, $department, $status, $action, $id]);
                $success_message = 'Protocol updated successfully!';
            } else {
                $error_message = 'Please fill in all required fields.';
            }
        }
        // Handle add
        else {
            $no = $_POST['no'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $status = $_POST['status'] ?? '';
            $action = $_POST['action'] ?? '';
            
            if ($no && $title && $department && $status && $action) {
                $db->query("INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)", 
                    [$no, $title, $department, $status, $action]);
                $success_message = 'Protocol added successfully!';
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
    $entries = $db->fetchAll("SELECT * FROM ethics_reviewed_protocols ORDER BY protocol_number DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load protocols: ' . $e->getMessage();
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
  <title>Ethics Reviewed Protocols</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/ethics-reviewed-protocols-new.css">
  <link rel="stylesheet" href="../css/theme.css">
  <link rel="stylesheet" href="../css/modern-theme.css">
  <style>
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

    /* Upload Progress Styles */
    .upload-progress {
      margin-top: 1rem;
      padding: 1rem;
      background: var(--bg-tertiary);
      border-radius: 8px;
      text-align: center;
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
      background: var(--btn-primary-bg);
      width: 0%;
      transition: width 0.3s ease;
    }
    
    .progress-text {
      font-size: 0.9rem;
      color: var(--text-secondary);
      font-weight: 500;
    }
    
    /* Upload Result Styles */
    .upload-result {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 8px;
      font-size: 0.95rem;
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
    }
    
    .upload-result.success {
      background: #1e4620;
      color: #d4f8e8;
      border: 1.5px solid #2ecc40;
    }
    
    .upload-result.error {
      background: #4d2323;
      color: #ffd6d6;
      border: 1.5px solid #e74c3c;
    }
    
    .upload-result.info {
      background: #2c3e50;
      color: #ecf0f1;
      border: 1.5px solid #34495e;
    }
    
    .upload-result i {
      font-size: 1.2em;
      margin-top: 0.1em;
    }
    
    .upload-result.success i {
      color: #2ecc40;
    }
    
    .upload-result.error i {
      color: #e74c3c;
    }

    /* Simplified Upload Modal Styles */
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
      padding: 1.2rem 1.2rem 0.5rem 1.2rem;
      font-size: 1rem;
      color: var(--text-primary);
    }
    
    .upload-simple-instructions ul {
      margin: 0.5rem 0 0.5rem 1.2rem;
      padding-left: 1.2rem;
      font-size: 0.97rem;
      color: var(--text-secondary);
    }
    
    .upload-simple-instructions ul ul {
      margin: 0.2rem 0 0.2rem 1.2rem;
      font-size: 0.95rem;
    }
    
    .template-download-simple {
      margin: 1rem 0 0.5rem 0;
    }
    
    .template-link {
      color: var(--btn-primary-bg);
      text-decoration: underline;
      font-size: 0.98rem;
      font-weight: 500;
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
    }
    
    .template-link:hover {
      text-decoration: none;
      color: var(--btn-primary-hover);
    }
    
    .upload-form-simple {
      padding: 0 1.2rem 1.2rem 1.2rem;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
    }
    
    .file-label-simple {
      font-weight: 500;
      color: var(--text-primary);
      font-size: 1rem;
      margin-bottom: 0.2rem;
    }
    
    #excelFile {
      border: 1px solid var(--border-primary);
      border-radius: 6px;
      padding: 0.5rem 0.75rem;
      font-size: 0.97rem;
      background: var(--bg-secondary);
      color: var(--text-primary);
      width: 100%;
      margin-bottom: 0.2rem;
    }
    
    #excelFile:focus {
      outline: 2px solid var(--btn-primary-bg);
      border-color: var(--btn-primary-bg);
    }
    
    .file-info {
      margin-top: 0.2rem;
      padding: 0.4rem 0.7rem;
      background: var(--bg-tertiary);
      border-radius: 4px;
      font-size: 0.93rem;
      color: var(--text-secondary);
      width: 100%;
      word-break: break-all;
    }
    
    .simple-footer {
      padding: 1rem 1.2rem 1rem 1.2rem;
      border-top: 1px solid var(--border-color);
      background: var(--bg-secondary);
      border-radius: 0 0 12px 12px;
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
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
  </style>
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
        <a href="Ethicss Reviewed Protocols.php" class="nav-link active">
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
          <h1>Ethics Reviewed Protocols</h1>
          <p>Monitor research ethics compliance and protocol approvals</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-primary" id="addBtn">
            <i class="fas fa-plus"></i>
            Add New Entry
          </button>
          <button class="btn btn-secondary" id="uploadExcelBtn" style="margin-left: 0.5em;">
            <i class="fas fa-file-excel"></i>
            Upload Excel
          </button>
        </div>
      </div>

      <!-- Add Entry Modal -->
      <div class="modal" id="addModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New Ethics Protocol</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <div class="form-group">
              <label for="no">Protocol Number</label>
              <input type="text" id="no" name="no" required placeholder="e.g., EP-2025-001">
            </div>
            <div class="form-group">
              <label for="title">Research Title</label>
              <input type="text" id="title" name="title" required placeholder="Enter research title">
            </div>
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" id="department" name="department" required placeholder="Enter department name">
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" name="status" required>
                <option value="">Select status</option>
                <option value="Approved">Approved</option>
                <option value="Under Review">Under Review</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="form-group">
              <label for="action">Action Taken</label>
              <input type="text" id="action" name="action" required placeholder="Enter action taken">
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
            <h3>Edit Ethics Protocol</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="" id="editForm">
            <input type="hidden" name="save_edit" value="1">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
              <label for="editNo">Protocol Number</label>
              <input type="text" id="editNo" name="no" required>
            </div>
            <div class="form-group">
              <label for="editTitle">Research Title</label>
              <input type="text" id="editTitle" name="title" required>
            </div>
            <div class="form-group">
              <label for="editDepartment">Department</label>
              <input type="text" id="editDepartment" name="department" required>
            </div>
            <div class="form-group">
              <label for="editStatus">Status</label>
              <select id="editStatus" name="status" required>
                <option value="Approved">Approved</option>
                <option value="Under Review">Under Review</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editAction">Action Taken</label>
              <input type="text" id="editAction" name="action" required>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Upload Excel Modal -->
      <div class="modal" id="uploadExcelModal">
        <div class="modal-content upload-modal-simple">
          <div class="modal-header">
            <h3>Upload Excel File</h3>
            <button class="modal-close" id="closeUploadExcelModal">
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
                  <li><b>Protocol Number</b></li>
                  <li><b>Research Title</b></li>
                  <li><b>Department</b></li>
                  <li><b>Status</b> (Approved, Under Review, Pending, Rejected)</li>
                  <li><b>Action Taken</b></li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
              </ul>
              <div class="template-download-simple">
                <a href="download_template_ethics_reviewed_protocols.php" class="template-link" download>Download Template</a>
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
            <button type="button" class="btn btn-secondary" id="cancelUploadExcel">Cancel</button>
            <button type="button" class="btn btn-primary" id="submitUpload" disabled>Upload File</button>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="data-card">
        <div class="card-header">
          <div class="card-title">
            <i class="fas fa-shield-alt"></i>
            <h2>Ethics Protocols Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search protocols..." id="searchInput">
          </div>
        </div>
        
        <div class="table-container">
          <form id="bulkDeleteForm" method="post" action="" onsubmit="return confirm('Are you sure you want to delete the selected protocols?');">
            <div class="bulk-delete-bar">
              <div class="select-all-container">
                <input type="checkbox" id="selectAll" class="styled-checkbox">
                <label for="selectAll" style="margin-left: 0.4em; font-size: 0.97em; cursor:pointer;">Select All</label>
              </div>
              <button type="submit" name="bulk_delete" class="btn btn-danger" id="bulkDeleteBtn" disabled style="margin-bottom: 1rem;">Delete Selected</button>
            </div>
            <table class="data-table" id="protocolsTable">
              <thead>
                <tr>
                  <th style="width:32px;"></th>
                  <th>Protocol No.</th>
                  <th>Research Title</th>
                  <th>Department</th>
                  <th>Status</th>
                  <th>Action Taken</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr class="empty-state">
                    <td colspan="7">
                      <div class="empty-content">
                        <i class="fas fa-inbox"></i>
                        <h3>No protocols found</h3>
                        <p>Add your first ethics protocol to get started</p>
                        <button class="btn btn-primary" id="addFirstBtn">
                          <i class="fas fa-plus"></i>
                          Add Protocol
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $i => $entry): ?>
                  <tr data-id="<?php echo $entry['id']; ?>">
                    <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                    <td class="protocol-no">
                      <span class="protocol-number"><?php echo htmlspecialchars($entry['protocol_number']); ?></span>
                    </td>
                    <td class="protocol-title">
                      <div class="title-content">
                        <h4><?php echo htmlspecialchars($entry['title']); ?></h4>
                      </div>
                    </td>
                    <td class="protocol-department">
                      <span class="department-name"><?php echo htmlspecialchars($entry['department']); ?></span>
                    </td>
                    <td class="protocol-status">
                      <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry['status'])); ?>">
                        <?php echo htmlspecialchars($entry['status']); ?>
                      </span>
                    </td>
                    <td class="protocol-action">
                      <span class="action-text"><?php echo htmlspecialchars($entry['action_taken']); ?></span>
                    </td>
                    <td class="protocol-actions">
                      <div class="action-buttons">
                        <button class="action-btn edit-btn" data-id="<?php echo $entry['id']; ?>" 
                                data-no="<?php echo htmlspecialchars($entry['protocol_number']); ?>"
                                data-title="<?php echo htmlspecialchars($entry['title']); ?>"
                                data-department="<?php echo htmlspecialchars($entry['department']); ?>"
                                data-status="<?php echo htmlspecialchars($entry['status']); ?>"
                                data-action="<?php echo htmlspecialchars($entry['action_taken']); ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this protocol?');">
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
    closeEditModal.addEventListener('click', () => closeModal(editModal));
    cancelAdd.addEventListener('click', () => closeModal(addModal));
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
        const no = btn.dataset.no;
        const title = btn.dataset.title;
        const department = btn.dataset.department;
        const status = btn.dataset.status;
        const action = btn.dataset.action;

        document.getElementById('editId').value = id;
        document.getElementById('editNo').value = no;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDepartment').value = department;
        document.getElementById('editStatus').value = status;
        document.getElementById('editAction').value = action;

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
    const tableRows = document.querySelectorAll('#protocolsTable tbody tr');

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

    // Upload functionality
    const uploadExcelBtn = document.getElementById('uploadExcelBtn');
    const uploadExcelModal = document.getElementById('uploadExcelModal');
    const closeUploadExcelModal = document.getElementById('closeUploadExcelModal');
    const cancelUploadExcel = document.getElementById('cancelUploadExcel');
    const excelFile = document.getElementById('excelFile');
    const fileInfo = document.getElementById('fileInfo');
    const submitUpload = document.getElementById('submitUpload');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadResult = document.getElementById('uploadResult');
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');

    // Upload modal functionality
    if (uploadExcelBtn) {
      uploadExcelBtn.addEventListener('click', () => openModal(uploadExcelModal));
      closeUploadExcelModal.addEventListener('click', () => closeModal(uploadExcelModal));
      cancelUploadExcel.addEventListener('click', () => closeModal(uploadExcelModal));
      
      // Close modal when clicking outside
      uploadExcelModal.addEventListener('click', (e) => {
        if (e.target === uploadExcelModal) {
          closeModal(uploadExcelModal);
        }
      });
    }

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

        const response = await fetch('upload_excel_ethics_reviewed_protocols.php', {
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
    closeUploadExcelModal.addEventListener('click', () => {
      closeModal(uploadExcelModal);
      resetUploadForm();
    });
    
    cancelUploadExcel.addEventListener('click', () => {
      closeModal(uploadExcelModal);
      resetUploadForm();
    });

    // Also reset when clicking outside modal
    uploadExcelModal.addEventListener('click', (e) => {
      if (e.target === uploadExcelModal) {
        resetUploadForm();
      }
    });
  </script>
</body>
</html> 