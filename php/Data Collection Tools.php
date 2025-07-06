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
$data_file = __DIR__ . '/data_collection_tools.csv';

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
        $faculty = $_POST['faculty'] ?? '';
        $degree = $_POST['degree'] ?? '';
        $sex = $_POST['sex'] ?? '';
        $title = $_POST['title'] ?? '';
        $ownership = $_POST['ownership'] ?? '';
        $presented = $_POST['presented'] ?? '';
        $published = $_POST['published'] ?? '';
        $journal = $_POST['journal'] ?? '';
        if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
            $entries[$index] = [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal];
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
    $faculty = $_POST['faculty'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $title = $_POST['title'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $presented = $_POST['presented'] ?? '';
    $published = $_POST['published'] ?? '';
    $journal = $_POST['journal'] ?? '';
    if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
        $entry = [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['Dr. Sarah Johnson', 'Ph.D.', 'Female', 'Machine Learning Applications in Healthcare', 'Author', '05,15,25 - International AI Conference', '2025-04-20', 'Journal of Medical Informatics'],
    ['Prof. Michael Chen', 'Ph.D.', 'Male', 'Sustainable Energy Systems in Urban Planning', 'Co-Author', '04,22,25 - Green Cities Summit', '2025-03-15', 'Environmental Engineering Review'],
    ['Dr. Emily Rodriguez', 'Ph.D.', 'Female', 'Educational Technology Impact on Student Learning', 'Author', '03,10,25 - EdTech Innovation Forum', '2025-02-28', 'Educational Technology Research'],
];

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
            <input type="hidden" name="index" id="editIndex">
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
        
        <div class="table-container">
          <table class="data-table" id="toolsTable">
            <thead>
              <tr>
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
                  <td colspan="9">
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
                <?php foreach ($entries as $i => $entry): ?>
                <tr data-index="<?php echo $i; ?>">
                  <td data-label="Faculty Name">
                    <div class="faculty-info">
                      <strong><?php echo htmlspecialchars($entry[0]); ?></strong>
                    </div>
                  </td>
                  <td data-label="Degree">
                    <span class="degree-badge"><?php echo htmlspecialchars($entry[1]); ?></span>
                  </td>
                  <td data-label="Sex">
                    <span class="sex-badge"><?php echo htmlspecialchars($entry[2]); ?></span>
                  </td>
                  <td data-label="Research Title">
                    <div class="title-content">
                      <h4><?php echo htmlspecialchars($entry[3]); ?></h4>
                    </div>
                  </td>
                  <td data-label="Ownership">
                    <span class="ownership-badge ownership-<?php echo strtolower(str_replace(' ', '-', $entry[4])); ?>">
                      <?php echo htmlspecialchars($entry[4]); ?>
                    </span>
                  </td>
                  <td data-label="Presented At">
                    <span class="presentation-info"><?php echo htmlspecialchars($entry[5]); ?></span>
                  </td>
                  <td data-label="Published Date">
                    <span class="date-info"><?php echo htmlspecialchars($entry[6]); ?></span>
                  </td>
                  <td data-label="Journal/Publication">
                    <span class="journal-info"><?php echo htmlspecialchars($entry[7]); ?></span>
                  </td>
                  <td data-label="Actions">
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-index="<?php echo $i; ?>" 
                              data-faculty="<?php echo htmlspecialchars($entry[0]); ?>"
                              data-degree="<?php echo htmlspecialchars($entry[1]); ?>"
                              data-sex="<?php echo htmlspecialchars($entry[2]); ?>"
                              data-title="<?php echo htmlspecialchars($entry[3]); ?>"
                              data-ownership="<?php echo htmlspecialchars($entry[4]); ?>"
                              data-presented="<?php echo htmlspecialchars($entry[5]); ?>"
                              data-published="<?php echo htmlspecialchars($entry[6]); ?>"
                              data-journal="<?php echo htmlspecialchars($entry[7]); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this entry?');">
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
    .degree-badge {
      background: #e0f2fe;
      color: #0369a1;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .sex-badge {
      background: #f3e8ff;
      color: #7c3aed;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .ownership-badge {
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .ownership-author {
      background: #dcfce7;
      color: #166534;
    }
    
    .ownership-co-author {
      background: #fef3c7;
      color: #92400e;
    }
    
    .ownership-contributor {
      background: #dbeafe;
      color: #1e40af;
    }
    
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
        const faculty = btn.dataset.faculty;
        const degree = btn.dataset.degree;
        const sex = btn.dataset.sex;
        const title = btn.dataset.title;
        const ownership = btn.dataset.ownership;
        const presented = btn.dataset.presented;
        const published = btn.dataset.published;
        const journal = btn.dataset.journal;

        document.getElementById('editIndex').value = index;
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

    // Upload button (placeholder)
    document.getElementById('uploadBtn').addEventListener('click', () => {
      alert('Upload functionality will be implemented here');
    });
  </script>
</body>
</html> 