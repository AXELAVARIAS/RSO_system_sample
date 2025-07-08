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

// Fetch the next protocol number for auto-generation
function getNextProtocolNumber($db) {
    $year = date('Y');
    $prefix = 'EP-' . $year . '-';
    $last = $db->fetch("SELECT protocol_number FROM ethics_reviewed_protocols WHERE protocol_number LIKE ? ORDER BY id DESC LIMIT 1", [$prefix.'%']);
    if ($last && isset($last['protocol_number'])) {
        $lastNum = intval(substr($last['protocol_number'], strlen($prefix)));
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }
    return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
}

// Always set $next_protocol_no before any HTML output
$next_protocol_no = '';
try {
    $db = getDB();
    $next_protocol_no = getNextProtocolNumber($db);
} catch (Exception $e) {
    $next_protocol_no = '';
}

// Handle Add Entry form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_protocol'])) {
    $protocol_no = $_POST['protocol_no'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $status = $_POST['status'] ?? '';
    $action_taken = $_POST['action_taken'] ?? '';
    if ($protocol_no && $title && $department && $status && $action_taken) {
        try {
            $db = getDB();
            $db->query("INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)",
                [$protocol_no, $title, $department, $status, $action_taken]);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '1062') !== false) {
                $error_message = 'A protocol with this Protocol Number already exists.';
            } else {
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
    } else {
        $error_message = 'Please fill in all required fields.';
    }
}
// Handle Edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $status = $_POST['status'] ?? '';
    $action_taken = $_POST['action_taken'] ?? '';
    if ($title && $department && $status && $action_taken) {
        try {
            $db = getDB();
            $db->query("UPDATE ethics_reviewed_protocols SET title = ?, department = ?, status = ?, action_taken = ? WHERE id = ?",
                [$title, $department, $status, $action_taken, $id]);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please fill in all required fields.';
    }
}
// Handle Delete form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $db = getDB();
        $db->query("DELETE FROM ethics_reviewed_protocols WHERE id = ?", [$id]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}
// Handle Bulk Delete form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && !empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $ids = array_map('intval', $_POST['selected_ids']);
    if (!empty($ids)) {
        try {
            $db = getDB();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->query("DELETE FROM ethics_reviewed_protocols WHERE id IN ($placeholders)", $ids);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all entries from the database
$entries = [];
try {
    $db = getDB();
    $entries = $db->fetchAll("SELECT * FROM ethics_reviewed_protocols ORDER BY id DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load protocols: ' . $e->getMessage();
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
    .status-badge {
      display: inline-block;
      padding: 0.3em 1.2em;
      border-radius: 999px;
      font-size: 1em;
      font-weight: bold;
      letter-spacing: 1px;
      text-transform: uppercase;
      background: #eee;
      color: #222;
    }
    .status-pending {
      background: #ffc72c;
      color: #222;
    }
    .status-approved {
      background: #2ecc40;
      color: #fff;
    }
    .status-rejected {
      background: #e74c3c;
      color: #fff;
    }
    .status-under-review {
      background: #3498db;
      color: #fff;
    }
    .status-completed {
      background: #2ee9a6;
      color: #222;
    }
    #bulkDeleteForm .select-all-container {
      display: none;
    }
    #bulkDeleteForm.show-all-checkboxes .select-all-container {
      display: flex !important;
      align-items: center;
    }
    #bulkDeleteBtn {
      display: none;
    }
    #bulkDeleteForm.show-all-checkboxes #bulkDeleteBtn {
      display: inline-block;
    }
    /* Hide checkboxes by default */
    .data-table .styled-checkbox {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }
    /* Show checkbox on row hover or focus */
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
    #bulkDeleteForm.show-all-checkboxes .styled-checkbox {
      opacity: 1;
      pointer-events: auto;
    }
    .styled-checkbox {
      appearance: none;
      -webkit-appearance: none;
      background: none;
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
          <button class="btn btn-secondary" id="uploadExcelBtn">
            <i class="fas fa-file-excel"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn">
            <i class="fas fa-plus"></i>
            Add New Entry
          </button>
        </div>
      </div>
      <?php if ($error_message): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
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
          <form id="bulkDeleteForm" method="post" action="">
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
                    <td colspan="7" style="text-align:center; color:var(--text-secondary);">No protocols found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($entries as $entry): ?>
                    <tr>
                      <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                      <td data-label="Protocol No."><strong><?php echo htmlspecialchars($entry['protocol_number']); ?></strong></td>
                      <td data-label="Research Title"><div class="title-content"><h4><?php echo htmlspecialchars($entry['title']); ?></h4></div></td>
                      <td data-label="Department"><?php echo htmlspecialchars($entry['department']); ?></td>
                      <td data-label="Status">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry['status'])); ?>">
                          <?php echo htmlspecialchars($entry['status']); ?>
                        </span>
                      </td>
                      <td data-label="Action Taken"><?php echo htmlspecialchars($entry['action_taken']); ?></td>
                      <td data-label="Actions">
                        <div class="action-buttons">
                          <button class="action-btn edit-btn"
                            data-id="<?php echo $entry['id']; ?>"
                            data-protocol_no="<?php echo htmlspecialchars($entry['protocol_number']); ?>"
                            data-title="<?php echo htmlspecialchars($entry['title']); ?>"
                            data-department="<?php echo htmlspecialchars($entry['department']); ?>"
                            data-status="<?php echo htmlspecialchars($entry['status']); ?>"
                            data-action_taken="<?php echo htmlspecialchars($entry['action_taken']); ?>">
                            <i class="fas fa-edit"></i>
                          </button>
                          <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this protocol?');">
                            <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                            <button type="submit" name="delete" class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
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
            <input type="hidden" name="add_protocol" value="1">
            <div class="form-group">
              <label for="protocol_no">Protocol Number</label>
              <input type="text" id="protocol_no" name="protocol_no" value="<?php echo htmlspecialchars($next_protocol_no); ?>" readonly>
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
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="form-group">
              <label for="action_taken">Action Taken</label>
              <input type="text" id="action_taken" name="action_taken" required placeholder="Enter action taken">
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
              <label for="editProtocolNo">Protocol Number</label>
              <input type="text" id="editProtocolNo" name="protocol_no" readonly>
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
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editActionTaken">Action Taken</label>
              <input type="text" id="editActionTaken" name="action_taken" required>
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

    // Add Entry Modal functionality
    const addModal = document.getElementById('addModal');
    const addBtn = document.getElementById('addBtn');
    const closeAddModal = document.getElementById('closeAddModal');
    const cancelAdd = document.getElementById('cancelAdd');

    function openModal(modal) {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    function closeModal(modal) {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
    addBtn.addEventListener('click', () => openModal(addModal));
    closeAddModal.addEventListener('click', () => closeModal(addModal));
    cancelAdd.addEventListener('click', () => closeModal(addModal));
    addModal.addEventListener('click', (e) => {
      if (e.target === addModal) {
        closeModal(addModal);
      }
    });

    // Edit Entry Modal functionality
    const editModal = document.getElementById('editModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEdit = document.getElementById('cancelEdit');
    document.addEventListener('click', (e) => {
      if (e.target.closest('.edit-btn')) {
        const btn = e.target.closest('.edit-btn');
        document.getElementById('editId').value = btn.dataset.id;
        document.getElementById('editProtocolNo').value = btn.dataset.protocol_no;
        document.getElementById('editTitle').value = btn.dataset.title;
        document.getElementById('editDepartment').value = btn.dataset.department;
        document.getElementById('editStatus').value = btn.dataset.status;
        document.getElementById('editActionTaken').value = btn.dataset.action_taken;
        editModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
    });
    closeEditModal.addEventListener('click', () => {
      editModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
    cancelEdit.addEventListener('click', () => {
      editModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
    editModal.addEventListener('click', (e) => {
      if (e.target === editModal) {
        editModal.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    });

    // Bulk delete button enable/disable and select all logic
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

    // Upload Excel Modal functionality
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

    if (uploadExcelBtn) {
      uploadExcelBtn.addEventListener('click', () => openModal(uploadExcelModal));
      closeUploadExcelModal.addEventListener('click', () => closeModal(uploadExcelModal));
      cancelUploadExcel.addEventListener('click', () => closeModal(uploadExcelModal));
      uploadExcelModal.addEventListener('click', (e) => {
        if (e.target === uploadExcelModal) {
          closeModal(uploadExcelModal);
        }
      });
    }

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
        uploadResult.style.display = 'none';
      } else {
        fileInfo.innerHTML = '';
        submitUpload.disabled = true;
      }
    });

    submitUpload.addEventListener('click', async () => {
      const file = excelFile.files[0];
      if (!file) return;
      uploadProgress.style.display = 'block';
      uploadResult.style.display = 'none';
      submitUpload.disabled = true;
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

    function resetUploadForm() {
      excelFile.value = '';
      fileInfo.innerHTML = '';
      uploadProgress.style.display = 'none';
      uploadResult.style.display = 'none';
      progressFill.style.width = '0%';
      progressText.textContent = 'Uploading...';
      submitUpload.disabled = true;
    }
    closeUploadExcelModal.addEventListener('click', () => {
      closeModal(uploadExcelModal);
      resetUploadForm();
    });
    cancelUploadExcel.addEventListener('click', () => {
      closeModal(uploadExcelModal);
      resetUploadForm();
    });
    uploadExcelModal.addEventListener('click', (e) => {
      if (e.target === uploadExcelModal) {
        resetUploadForm();
      }
    });
  </script>
</body>
</html> 