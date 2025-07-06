<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}
// File to store entries
$data_file = __DIR__ . '/ethics_reviewed_protocols.csv';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle delete
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $entries = [];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle edit save
    if (isset($_POST['save_edit']) && isset($_POST['index'])) {
        $entries = [];
        $header = ['No', 'Title', 'Department', 'Status', 'Action'];
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            $isFirstRow = true;
            while ($row = fgetcsv($fp)) {
                if ($isFirstRow) {
                    $isFirstRow = false; // skip header
                    continue;
                }
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        $no = $_POST['no'] ?? '';
        $title = $_POST['title'] ?? '';
        $department = $_POST['department'] ?? '';
        $status = $_POST['status'] ?? '';
        $action = $_POST['action'] ?? '';
        if ($no && $title && $department && $status && $action) {
            $entries[$index] = [$no, $title, $department, $status, $action];
            $fp = fopen($data_file, 'w');
            fputcsv($fp, $header);
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle add
    $no = $_POST['no'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $status = $_POST['status'] ?? '';
    $action = $_POST['action'] ?? '';
    if ($no && $title && $department && $status && $action) {
        $entry = [$no, $title, $department, $status, $action];
        $write_header = false;
        if (!file_exists($data_file) || filesize($data_file) === 0) {
            $write_header = true;
        }
        $fp = fopen($data_file, 'a');
        if ($write_header) {
            fputcsv($fp, ['No', 'Title', 'Department', 'Status', 'Action']);
        }
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['EP-2025-001', 'Impact of AI on Student Privacy in Educational Platforms', 'Computer Science Department', 'Approved', 'Full Ethics Clearance Granted'],
    ['EP-2025-002', 'Mental Health Survey Among University Students', 'Psychology Department', 'Under Review', 'Additional Documentation Requested'],
    ['EP-2025-003', 'Environmental Impact Assessment of Campus Operations', 'Environmental Science Department', 'Approved', 'Conditional Approval with Monitoring'],
    ['EP-2025-004', 'Social Media Usage Patterns Research', 'Sociology Department', 'Pending', 'Initial Review in Progress'],
];

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    $isFirstRow = true;
    while ($row = fgetcsv($fp)) {
        if ($isFirstRow) {
            $isFirstRow = false; // Skip the header row
            continue;
        }
        $entries[] = $row;
    }
    fclose($fp);
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
      
      <!-- Theme Toggle -->
      <button class="theme-toggle" title="Toggle Theme">
        <i class="fas fa-moon"></i>
      </button>
      
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
          <h1>Ethics Reviewed Protocols</h1>
          <p>Monitor research ethics compliance and protocol approvals</p>
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
            <input type="hidden" name="index" id="editIndex">
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
          <table class="data-table" id="protocolsTable">
            <thead>
              <tr>
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
                  <td colspan="6">
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
                <tr data-index="<?php echo $i; ?>">
                  <td class="protocol-no">
                    <span class="protocol-number"><?php echo htmlspecialchars($entry[0]); ?></span>
                  </td>
                  <td class="protocol-title">
                    <div class="title-content">
                      <h4><?php echo htmlspecialchars($entry[1]); ?></h4>
                    </div>
                  </td>
                  <td class="protocol-department">
                    <span class="department-name"><?php echo htmlspecialchars($entry[2]); ?></span>
                  </td>
                  <td class="protocol-status">
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry[3])); ?>">
                      <?php echo htmlspecialchars($entry[3]); ?>
                    </span>
                  </td>
                  <td class="protocol-action">
                    <span class="action-text"><?php echo htmlspecialchars($entry[4]); ?></span>
                  </td>
                  <td class="protocol-actions">
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-index="<?php echo $i; ?>" 
                              data-no="<?php echo htmlspecialchars($entry[0]); ?>"
                              data-title="<?php echo htmlspecialchars($entry[1]); ?>"
                              data-department="<?php echo htmlspecialchars($entry[2]); ?>"
                              data-status="<?php echo htmlspecialchars($entry[3]); ?>"
                              data-action="<?php echo htmlspecialchars($entry[4]); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this protocol?');">
                        <input type="hidden" name="index" value="<?php echo $i; ?>">
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
        const index = btn.dataset.index;
        const no = btn.dataset.no;
        const title = btn.dataset.title;
        const department = btn.dataset.department;
        const status = btn.dataset.status;
        const action = btn.dataset.action;

        document.getElementById('editIndex').value = index;
        document.getElementById('editNo').value = no;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDepartment').value = department;
        document.getElementById('editStatus').value = status;
        document.getElementById('editAction').value = action;

        openModal(editModal);
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

    // Upload button (placeholder)
    document.getElementById('uploadBtn').addEventListener('click', () => {
      alert('Upload functionality will be implemented here');
    });
  </script>
</body>
</html> 