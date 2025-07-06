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
$data_file = __DIR__ . '/kpi_records.csv';

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
        if (file_exists($data_file)) {
            $fp = fopen($data_file, 'r');
            while ($row = fgetcsv($fp)) {
                $entries[] = $row;
            }
            fclose($fp);
        }
        $index = (int)$_POST['index'];
        $faculty_name = $_POST['faculty_name'] ?? '';
        $period = $_POST['period'] ?? '';
        $publications = $_POST['publications'] ?? '';
        $trainings = $_POST['trainings'] ?? '';
        $presentations = $_POST['presentations'] ?? '';
        $kpi_score = $_POST['kpi_score'] ?? '';
        $performance = $_POST['performance'] ?? '';
        if ($faculty_name && $period && $publications && $trainings && $presentations && $kpi_score && $performance) {
            $entries[$index] = [$faculty_name, $period, $publications, $trainings, $presentations, $kpi_score, $performance];
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    // Handle add
    $faculty_name = $_POST['faculty_name'] ?? '';
    $period = $_POST['period'] ?? '';
    $publications = $_POST['publications'] ?? '';
    $trainings = $_POST['trainings'] ?? '';
    $presentations = $_POST['presentations'] ?? '';
    $kpi_score = $_POST['kpi_score'] ?? '';
    $performance = $_POST['performance'] ?? '';
    if ($faculty_name && $period && $publications && $trainings && $presentations && $kpi_score && $performance) {
        $entry = [$faculty_name, $period, $publications, $trainings, $presentations, $kpi_score, $performance];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries (optional, can be removed or edited)
$default_entries = [
    ['Dr. Sarah Johnson', 'Q1 2025', '3', '2', '1', '95', 'Excellent'],
    ['Prof. Michael Chen', 'Q1 2025', '2', '1', '2', '88', 'Good'],
    ['Dr. Emily Rodriguez', 'Q1 2025', '4', '3', '2', '99', 'Outstanding'],
];

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    $is_first_row = true;
    while ($row = fgetcsv($fp)) {
        if ($is_first_row) {
            $is_first_row = false;
            continue; // Skip header row
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
              <label for="trainings">Trainings</label>
              <input type="number" id="trainings" name="trainings" required placeholder="Enter number of trainings">
            </div>
            <div class="form-group">
              <label for="presentations">Presentations</label>
              <input type="number" id="presentations" name="presentations" required placeholder="Enter number of presentations">
            </div>
            <div class="form-group">
              <label for="kpi_score">KPI Score</label>
              <input type="number" id="kpi_score" name="kpi_score" required placeholder="Enter KPI score">
            </div>
            <div class="form-group">
              <label for="performance">Performance</label>
              <input type="text" id="performance" name="performance" required placeholder="Enter performance">
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
            <input type="hidden" name="index" id="editIndex">
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
              <label for="editTrainings">Trainings</label>
              <input type="number" id="editTrainings" name="trainings" required>
            </div>
            <div class="form-group">
              <label for="editPresentations">Presentations</label>
              <input type="number" id="editPresentations" name="presentations" required>
            </div>
            <div class="form-group">
              <label for="editKpiScore">KPI Score</label>
              <input type="number" id="editKpiScore" name="kpi_score" required>
            </div>
            <div class="form-group">
              <label for="editPerformance">Performance</label>
              <input type="text" id="editPerformance" name="performance" required>
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
            <i class="fas fa-bullseye"></i>
            <h2>KPI Records Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search KPIs..." id="searchInput">
          </div>
        </div>
        
        <div class="table-container">
          <table class="data-table" id="kpiTable">
            <thead>
              <tr>
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
              <?php if (empty($entries)): ?>
                <tr class="empty-state">
                  <td colspan="8">
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
                <tr data-index="<?php echo $i; ?>">
                  <td data-label="Faculty Name">
                    <div class="kpi-name">
                      <strong><?php echo htmlspecialchars($entry[0] ?? ''); ?></strong>
                    </div>
                  </td>
                  <td data-label="Period">
                    <span class="period-info"><?php echo htmlspecialchars($entry[1] ?? ''); ?></span>
                  </td>
                  <td data-label="Publications">
                    <span class="target-value"><?php echo htmlspecialchars($entry[2] ?? ''); ?></span>
                  </td>
                  <td data-label="Trainings">
                    <span class="actual-value"><?php echo htmlspecialchars($entry[3] ?? ''); ?></span>
                  </td>
                  <td data-label="Presentations">
                    <?php echo htmlspecialchars($entry[4] ?? ''); ?>
                  </td>
                  <td data-label="KPI Score">
                    <?php echo htmlspecialchars($entry[5] ?? ''); ?>
                  </td>
                  <td data-label="Performance">
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry[6] ?? '')); ?>">
                      <?php echo htmlspecialchars($entry[6] ?? ''); ?>
                    </span>
                  </td>
                  <td data-label="Actions">
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-index="<?php echo $i; ?>" 
                              data-faculty-name="<?php echo htmlspecialchars($entry[0] ?? ''); ?>"
                              data-period="<?php echo htmlspecialchars($entry[1] ?? ''); ?>"
                              data-publications="<?php echo htmlspecialchars($entry[2] ?? ''); ?>"
                              data-trainings="<?php echo htmlspecialchars($entry[3] ?? ''); ?>"
                              data-presentations="<?php echo htmlspecialchars($entry[4] ?? ''); ?>"
                              data-kpi-score="<?php echo htmlspecialchars($entry[5] ?? ''); ?>"
                              data-performance="<?php echo htmlspecialchars($entry[6] ?? ''); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this KPI record?');">
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
        const facultyName = btn.dataset.facultyName;
        const period = btn.dataset.period;
        const publications = btn.dataset.publications;
        const trainings = btn.dataset.trainings;
        const presentations = btn.dataset.presentations;
        const kpiScore = btn.dataset.kpiScore;
        const performance = btn.dataset.performance;

        document.getElementById('editIndex').value = index;
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

    // Upload button (placeholder)
    document.getElementById('uploadBtn').addEventListener('click', () => {
      alert('Upload functionality will be implemented here');
    });
  </script>
</body>
</html> 