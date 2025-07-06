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
$data_file = __DIR__ . '/research_capacity_data.csv';

// Handle form submission (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read all entries
    $entries = [];
    if (file_exists($data_file)) {
        $fp = fopen($data_file, 'r');
        while ($row = fgetcsv($fp)) {
            $entries[] = $row;
        }
        fclose($fp);
    }

    // Delete entry
    if (isset($_POST['delete']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        if (isset($entries[$index])) {
            array_splice($entries, $index, 1);
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Edit entry
    elseif (isset($_POST['edit']) && isset($_POST['index'])) {
        // Handled below (show edit form)
    }
    // Save edited entry
    elseif (isset($_POST['save_edit']) && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $date = $_POST['date'] ?? '';
        $name = $_POST['name'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $facilitators = $_POST['facilitators'] ?? '';
        $num_participants = $_POST['num_participants'] ?? '';
        $status = $_POST['status'] ?? '';
        if ($date && $name && $venue && $facilitators && $num_participants && $status) {
            $entries[$index] = [$date, $name, $venue, $facilitators, $num_participants, $status];
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Add new entry
    elseif (isset($_POST['add_entry'])) {
        $date = $_POST['date'] ?? '';
        $name = $_POST['name'] ?? '';
        $venue = $_POST['venue'] ?? '';
        $facilitators = $_POST['facilitators'] ?? '';
        $num_participants = $_POST['num_participants'] ?? '';
        $status = $_POST['status'] ?? '';
        if ($date && $name && $venue && $facilitators && $num_participants && $status) {
            $entries[] = [$date, $name, $venue, $facilitators, $num_participants, $status];
            $fp = fopen($data_file, 'w');
            foreach ($entries as $entry) {
                fputcsv($fp, $entry);
            }
            fclose($fp);
        }
    }
    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Read all entries
$entries = [];
if (file_exists($data_file)) {
    $fp = fopen($data_file, 'r');
    while ($row = fgetcsv($fp)) {
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
          <i class="fas fa-target"></i>
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
            <input type="hidden" name="index" id="editIndex">
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
        
        <div class="table-container">
          <table class="data-table" id="activitiesTable">
            <thead>
              <tr>
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
                  <td colspan="7">
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
                <?php foreach ($entries as $i => $entry): ?>
                <tr data-index="<?php echo $i; ?>">
                  <td data-label="Date">
                    <span class="date-info"><?php echo htmlspecialchars($entry[0]); ?></span>
                  </td>
                  <td data-label="Activity Name">
                    <div class="activity-title">
                      <h4><?php echo htmlspecialchars($entry[1]); ?></h4>
                    </div>
                  </td>
                  <td data-label="Venue">
                    <span class="venue-info"><?php echo htmlspecialchars($entry[2]); ?></span>
                  </td>
                  <td data-label="Facilitators">
                    <span class="facilitators-info"><?php echo htmlspecialchars($entry[3]); ?></span>
                  </td>
                  <td data-label="Participants">
                    <span class="participants-count"><?php echo htmlspecialchars($entry[4]); ?></span>
                  </td>
                  <td data-label="Status">
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry[5])); ?>">
                      <?php echo htmlspecialchars($entry[5]); ?>
                    </span>
                  </td>
                  <td data-label="Actions">
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-index="<?php echo $i; ?>" 
                              data-date="<?php echo htmlspecialchars($entry[0]); ?>"
                              data-name="<?php echo htmlspecialchars($entry[1]); ?>"
                              data-venue="<?php echo htmlspecialchars($entry[2]); ?>"
                              data-facilitators="<?php echo htmlspecialchars($entry[3]); ?>"
                              data-participants="<?php echo htmlspecialchars($entry[4]); ?>"
                              data-status="<?php echo htmlspecialchars($entry[5]); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this activity?');">
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
    .date-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .activity-title h4 {
      font-weight: 500;
      color: var(--text-primary);
      margin-bottom: 4px;
      line-height: 1.4;
    }
    
    .venue-info,
    .facilitators-info {
      color: var(--text-secondary);
      font-size: 0.875rem;
    }
    
    .participants-count {
      background: #f0f9ff;
      color: #0369a1;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
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
        const date = btn.dataset.date;
        const name = btn.dataset.name;
        const venue = btn.dataset.venue;
        const facilitators = btn.dataset.facilitators;
        const participants = btn.dataset.participants;
        const status = btn.dataset.status;

        document.getElementById('editIndex').value = index;
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

    // Upload button (placeholder)
    document.getElementById('uploadBtn').addEventListener('click', () => {
      alert('Upload functionality will be implemented here');
    });
  </script>
</body>
</html>