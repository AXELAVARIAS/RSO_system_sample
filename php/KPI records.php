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

// Handle Delete KPI Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && !empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $ids = array_map('intval', $_POST['selected_ids']);
    if (!empty($ids)) {
        try {
            $db = getDB();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->query("DELETE FROM kpi_records WHERE id IN ($placeholders)", $ids);
            // Do not set $success_message for delete
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'No KPI records selected for deletion.';
    }
}

// Handle Add New KPI Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_kpi'])) {
    $faculty_name = trim($_POST['faculty_name'] ?? '');
    $quarter = trim($_POST['quarter'] ?? '');
    $publications_count = intval($_POST['publications_count'] ?? 0);
    $trainings_count = intval($_POST['trainings_count'] ?? 0);
    $presentations_count = intval($_POST['presentations_count'] ?? 0);
    $performance_score = floatval($_POST['performance_score'] ?? 0);
    $performance_rating = trim($_POST['performance_rating'] ?? 'Fair');

    if ($faculty_name && $quarter) {
        try {
            $db = getDB();
            $db->query("INSERT INTO kpi_records (faculty_name, quarter, publications_count, research_projects_count, presentations_count, performance_score, performance_rating) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $faculty_name, $quarter, $publications_count, $trainings_count, $presentations_count, $performance_score, $performance_rating
            ]);
            // Redirect to avoid resubmission on refresh
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please fill in all required fields.';
    }
}

// Handle Edit KPI Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kpi']) && isset($_POST['kpi_id'])) {
    $kpi_id = intval($_POST['kpi_id']);
    $faculty_name = trim($_POST['faculty_name'] ?? '');
    $quarter = trim($_POST['quarter'] ?? '');
    $publications_count = intval($_POST['publications_count'] ?? 0);
    $trainings_count = intval($_POST['trainings_count'] ?? 0);
    $presentations_count = intval($_POST['presentations_count'] ?? 0);
    $performance_score = floatval($_POST['performance_score'] ?? 0);
    $performance_rating = trim($_POST['performance_rating'] ?? 'Fair');
    if ($kpi_id > 0 && $faculty_name && $quarter) {
        try {
            $db = getDB();
            $db->query("UPDATE kpi_records SET faculty_name=?, quarter=?, publications_count=?, research_projects_count=?, presentations_count=?, performance_score=?, performance_rating=? WHERE id=?", [
                $faculty_name, $quarter, $publications_count, $trainings_count, $presentations_count, $performance_score, $performance_rating, $kpi_id
            ]);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Please fill in all required fields.';
    }
}

// Handle Delete Single KPI Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_kpi_id'])) {
    $delete_id = intval($_POST['delete_kpi_id']);
    if ($delete_id > 0) {
        try {
            $db = getDB();
            $db->query("DELETE FROM kpi_records WHERE id = ?", [$delete_id]);
            // Optionally set $success_message
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Invalid KPI record ID.';
    }
}

// Fetch KPI records from database
$kpi_entries = [];
try {
    $db = getDB();
    $kpi_entries = $db->fetchAll("SELECT * FROM kpi_records ORDER BY created_at DESC");
} catch (Exception $e) {
    $error_message = 'Failed to load KPI records: ' . $e->getMessage();
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
        <button type="button" class="profile-btn" id="profileBtn">
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
          <p>Monitor faculty KPI performance and achievements</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" id="uploadBtn" type="button">
            <i class="fas fa-upload"></i>
            Upload Excel
          </button>
          <button class="btn btn-primary" id="addBtn" type="button">
            <i class="fas fa-plus"></i>
            Add New KPI Record
          </button>
        </div>
      </div>

      <?php if ($success_message): ?>
        <div class="custom-alert custom-alert-success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      <?php if ($error_message): ?>
        <div class="custom-alert custom-alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
<style>
.custom-alert {
  margin: 24px 0 16px 0;
  padding: 16px 24px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border: 1.5px solid transparent;
  letter-spacing: 0.01em;
  max-width: 100%;
}
.custom-alert-success {
  background: #173c32;
  color: #2ee59d;
  border-color: #2ee59d;
}
.custom-alert-danger {
  background: #3c1717;
  color: #ff6b6b;
  border-color: #ff6b6b;
}
  /* Bulk delete styles copied from Data Collection Tools */
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
  .data-table.show-all-checkboxes .styled-checkbox {
    opacity: 1;
    pointer-events: auto;
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
</style>

      <!-- Add KPI Modal -->
      <div class="modal" id="addModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Add New KPI Record</h3>
            <button class="modal-close" id="closeAddModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <input type="hidden" name="add_kpi" value="1">
            <div class="form-group">
              <label for="faculty_name">Faculty Name</label>
              <input type="text" id="faculty_name" name="faculty_name" required placeholder="Enter faculty name">
            </div>
            <div class="form-group">
              <label for="quarter">Period/Quarter</label>
              <input type="text" id="quarter" name="quarter" required placeholder="e.g. Q1 2025">
            </div>
            <div class="form-group">
              <label for="publications_count">Publications</label>
              <input type="number" id="publications_count" name="publications_count" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label for="trainings_count">Trainings</label>
              <input type="number" id="trainings_count" name="trainings_count" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label for="presentations_count">Presentations</label>
              <input type="number" id="presentations_count" name="presentations_count" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label for="performance_score">KPI Score</label>
              <input type="number" step="0.01" id="performance_score" name="performance_score" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label for="performance_rating">Performance</label>
              <select id="performance_rating" name="performance_rating" required>
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
              <button type="submit" class="btn btn-primary">Add KPI Record</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit KPI Modal -->
      <div class="modal" id="editModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Edit KPI Record</h3>
            <button class="modal-close" id="closeEditModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <form class="modal-form" method="post" action="">
            <input type="hidden" name="edit_kpi" value="1">
            <input type="hidden" id="edit_kpi_id" name="kpi_id">
            <div class="form-group">
              <label for="edit_faculty_name">Faculty Name</label>
              <input type="text" id="edit_faculty_name" name="faculty_name" required>
            </div>
            <div class="form-group">
              <label for="edit_quarter">Period/Quarter</label>
              <input type="text" id="edit_quarter" name="quarter" required>
            </div>
            <div class="form-group">
              <label for="edit_publications_count">Publications</label>
              <input type="number" id="edit_publications_count" name="publications_count" min="0" required>
            </div>
            <div class="form-group">
              <label for="edit_trainings_count">Trainings</label>
              <input type="number" id="edit_trainings_count" name="trainings_count" min="0" required>
            </div>
            <div class="form-group">
              <label for="edit_presentations_count">Presentations</label>
              <input type="number" id="edit_presentations_count" name="presentations_count" min="0" required>
            </div>
            <div class="form-group">
              <label for="edit_performance_score">KPI Score</label>
              <input type="number" step="0.01" id="edit_performance_score" name="performance_score" min="0" required>
            </div>
            <div class="form-group">
              <label for="edit_performance_rating">Performance</label>
              <select id="edit_performance_rating" name="performance_rating" required>
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
                  <li><b>Faculty Name</b></li>
                  <li><b>Period</b> (e.g. Q1 2025)</li>
                  <li><b>Publications</b></li>
                  <li><b>Trainings</b></li>
                  <li><b>Presentations</b></li>
                  <li><b>KPI Score</b></li>
                  <li><b>Performance Rating</b></li>
                </ul>
                <li>First row should contain column headers</li>
                <li>Maximum file size: 5MB</li>
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

      <!-- Data Table Card -->
      <div class="data-card">
        <div class="card-header">
          <div class="card-title">
            <i class="fas fa-bullseye"></i>
            <h2>KPI Performance Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search KPI records..." id="searchInput">
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
            <table class="data-table" id="kpiTable">
              <thead>
                <tr>
                  <th style="width:32px;"></th>
                  <th>Faculty Name</th>
                  <th>Period</th>
                  <th>Publications</th>
                  <th>Trainings</th>
                  <th>Presentations</th>
                  <th>KPI Score</th>
                  <th>Performance</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($kpi_entries)): ?>
                  <tr class="empty-state">
                    <td colspan="9" style="text-align:center;">No KPI records found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($kpi_entries as $entry): ?>
                    <tr data-id="<?php echo $entry['id']; ?>" data-faculty="<?php echo htmlspecialchars($entry['faculty_name']); ?>" data-quarter="<?php echo htmlspecialchars($entry['quarter']); ?>" data-publications="<?php echo htmlspecialchars($entry['publications_count']); ?>" data-trainings="<?php echo htmlspecialchars($entry['research_projects_count']); ?>" data-presentations="<?php echo htmlspecialchars($entry['presentations_count']); ?>" data-score="<?php echo htmlspecialchars($entry['performance_score']); ?>" data-rating="<?php echo htmlspecialchars($entry['performance_rating']); ?>">
                      <td><input type="checkbox" class="row-checkbox styled-checkbox" name="selected_ids[]" value="<?php echo $entry['id']; ?>"></td>
                      <td><?php echo htmlspecialchars($entry['faculty_name']); ?></td>
                      <td><?php echo htmlspecialchars($entry['quarter']); ?></td>
                      <td><?php echo htmlspecialchars($entry['publications_count']); ?></td>
                      <td><?php echo htmlspecialchars($entry['research_projects_count']); ?></td>
                      <td><?php echo htmlspecialchars($entry['presentations_count']); ?></td>
                      <td><?php echo htmlspecialchars($entry['performance_score']); ?></td>
                      <td><?php echo htmlspecialchars($entry['performance_rating']); ?></td>
                      <td>
                        <div class="action-buttons">
                          <button class="action-btn edit-btn" type="button"><i class="fas fa-edit"></i></button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </form>
        <?php if (!empty($kpi_entries)): ?>
          <?php foreach ($kpi_entries as $entry): ?>
            <form method="post" action="" style="display:none;" id="delete-form-<?php echo $entry['id']; ?>">
              <input type="hidden" name="delete_kpi_id" value="<?php echo $entry['id']; ?>">
            </form>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    // Modal functionality for Add KPI
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
      if (e.target === addModal) closeModal(addModal);
    });

    // Search functionality for KPI table
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#kpiTable tbody tr');
    searchInput.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase();
      tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  </script>
  <script src="../js/theme.js"></script>
  <script>
    // Profile menu toggle
    const profileMenu = document.getElementById('profileMenu');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('open');
      });
    }
    document.addEventListener('click', (e) => {
      if (!profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
      }
    });
  </script>
  <script>
// Modal functionality for Upload Excel
const uploadModal = document.getElementById('uploadModal');
const uploadBtn = document.getElementById('uploadBtn');
const closeUploadModal = document.getElementById('closeUploadModal');
const cancelUpload = document.getElementById('cancelUpload');

function openModal(modal) {
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal(modal) {
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
}
uploadBtn.addEventListener('click', () => openModal(uploadModal));
closeUploadModal.addEventListener('click', () => closeModal(uploadModal));
cancelUpload.addEventListener('click', () => closeModal(uploadModal));
uploadModal.addEventListener('click', (e) => {
  if (e.target === uploadModal) closeModal(uploadModal);
});

// Upload logic
const uploadForm = document.getElementById('uploadForm');
const excelFileInput = document.getElementById('excelFile');
const fileInfo = document.getElementById('fileInfo');
const uploadProgress = document.getElementById('uploadProgress');
const uploadResult = document.getElementById('uploadResult');
const submitUpload = document.getElementById('submitUpload');

excelFileInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (file) {
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    fileInfo.innerHTML = `<strong>Selected file:</strong> ${file.name}<br><strong>Size:</strong> ${fileSize} MB<br><strong>Type:</strong> ${file.type || 'Unknown'}`;
    fileInfo.classList.add('show');
    submitUpload.disabled = false;
  } else {
    fileInfo.classList.remove('show');
    submitUpload.disabled = true;
  }
});

submitUpload.addEventListener('click', async () => {
  const formData = new FormData(uploadForm);
  const file = excelFileInput.files[0];
  if (!file) {
    alert('Please select a file first.');
    return;
  }
  uploadProgress.style.display = 'block';
  uploadResult.style.display = 'none';
  submitUpload.disabled = true;
  try {
    const response = await fetch('upload_excel_kpi_records.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();
    uploadProgress.style.display = 'none';
    uploadResult.style.display = 'block';
    uploadResult.className = `upload-result ${result.success ? 'success' : 'error'}`;
    let errorDetails = '';
    if (result.data) {
      if (result.data.errors && result.data.errors.length > 0) {
        errorDetails += '<br><br><strong>Row Errors:</strong><br>' + result.data.errors.join('<br>');
      }
    }
    uploadResult.innerHTML = `<strong>${result.success ? 'Success!' : 'Error:'}</strong><br>${result.message}${errorDetails}`;
    if (result.success) {
      uploadForm.reset();
      fileInfo.classList.remove('show');
      submitUpload.disabled = true;
      setTimeout(() => { window.location.reload(); }, 2000);
    }
  } catch (error) {
    uploadProgress.style.display = 'none';
    uploadResult.style.display = 'block';
    uploadResult.className = 'upload-result error';
    uploadResult.innerHTML = `<strong>Error:</strong><br>Failed to upload file. Please try again.`;
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
</script>
<script>
// Bulk delete button enable/disable and select all logic (copied and adapted from Data Collection Tools)
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
  const bulkDeleteForm = document.getElementById('bulkDeleteForm');
  if (bulkDeleteForm) {
    if (checkedCount > 0) {
      bulkDeleteForm.classList.add('show-all-checkboxes');
    } else {
      bulkDeleteForm.classList.remove('show-all-checkboxes');
    }
  }
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
document.addEventListener('DOMContentLoaded', function() {
  const bulkDeleteForm = document.getElementById('bulkDeleteForm');
  if (bulkDeleteForm) {
    bulkDeleteForm.addEventListener('submit', function(e) {
      const checkboxes = bulkDeleteForm.querySelectorAll('.row-checkbox:checked');
      if (checkboxes.length === 0) {
        e.preventDefault();
        return false;
      }
      if (!confirm('Are you sure you want to delete the selected KPI records?')) {
        e.preventDefault();
        return false;
      }
    });
  }
});
</script>
<script>
// Edit Modal functionality
const editModal = document.getElementById('editModal');
const closeEditModal = document.getElementById('closeEditModal');
const cancelEdit = document.getElementById('cancelEdit');
const editKpiId = document.getElementById('edit_kpi_id');
const editFacultyName = document.getElementById('edit_faculty_name');
const editQuarter = document.getElementById('edit_quarter');
const editPublicationsCount = document.getElementById('edit_publications_count');
const editTrainingsCount = document.getElementById('edit_trainings_count');
const editPresentationsCount = document.getElementById('edit_presentations_count');
const editPerformanceScore = document.getElementById('edit_performance_score');
const editPerformanceRating = document.getElementById('edit_performance_rating');

function openEditModal(row) {
  editKpiId.value = row.getAttribute('data-id');
  editFacultyName.value = row.getAttribute('data-faculty');
  editQuarter.value = row.getAttribute('data-quarter');
  editPublicationsCount.value = row.getAttribute('data-publications');
  editTrainingsCount.value = row.getAttribute('data-trainings');
  editPresentationsCount.value = row.getAttribute('data-presentations');
  editPerformanceScore.value = row.getAttribute('data-score');
  editPerformanceRating.value = row.getAttribute('data-rating');
  editModal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeEditModalFunc() {
  editModal.style.display = 'none';
  document.body.style.overflow = 'auto';
}
closeEditModal.addEventListener('click', closeEditModalFunc);
cancelEdit.addEventListener('click', closeEditModalFunc);
editModal.addEventListener('click', (e) => {
  if (e.target === editModal) closeEditModalFunc();
});
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const row = this.closest('tr');
    openEditModal({
      getAttribute: (attr) => {
        switch(attr) {
          case 'data-id': return row.querySelector('input.row-checkbox').value;
          case 'data-faculty': return row.children[1].textContent.trim();
          case 'data-quarter': return row.children[2].textContent.trim();
          case 'data-publications': return row.children[3].textContent.trim();
          case 'data-trainings': return row.children[4].textContent.trim();
          case 'data-presentations': return row.children[5].textContent.trim();
          case 'data-score': return row.children[6].textContent.trim();
          case 'data-rating': return row.children[7].textContent.trim();
        }
      }
    });
  });
});
</script>
<script>
// In the table, change the delete button to submit the correct form by JS
document.querySelectorAll('.delete-btn').forEach((btn, idx) => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');
    if (confirm('Are you sure you want to delete this KPI record?')) {
      document.getElementById('delete-form-' + id).submit();
    }
  });
});
</script>
</body>
</html> 