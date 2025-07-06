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
$data_file = __DIR__ . '/publication_presentation.csv';

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
        $date = $_POST['date'] ?? '';
        $author = $_POST['author'] ?? '';
        $title = $_POST['title'] ?? '';
        $journal = $_POST['journal'] ?? '';
        $doi = $_POST['doi'] ?? '';
        $impact_factor = $_POST['impact_factor'] ?? '';
        $citations = $_POST['citations'] ?? '';
        if ($date && $author && $title && $journal && $doi && $impact_factor && $citations) {
            $entries[$index] = [$date, $author, $title, $journal, $doi, $impact_factor, $citations];
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
    $date = $_POST['date'] ?? '';
    $author = $_POST['author'] ?? '';
    $title = $_POST['title'] ?? '';
    $journal = $_POST['journal'] ?? '';
    $doi = $_POST['doi'] ?? '';
    $impact_factor = $_POST['impact_factor'] ?? '';
    $citations = $_POST['citations'] ?? '';
    if ($date && $author && $title && $journal && $doi && $impact_factor && $citations) {
        $entry = [$date, $author, $title, $journal, $doi, $impact_factor, $citations];
        $fp = fopen($data_file, 'a');
        fputcsv($fp, $entry);
        fclose($fp);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Predefined entries
$default_entries = [
    ['2025-01-15', 'Dr. Sarah Johnson', 'Machine Learning Applications in Healthcare', 'Journal of Medical Informatics', '10.1000/abc123', '3.45', '25'],
    ['2025-02-20', 'Prof. Michael Chen', 'Sustainable Energy Systems in Urban Planning', 'Environmental Engineering Review', '10.1000/def456', '2.78', '18'],
    ['2025-03-10', 'Dr. Emily Rodriguez', 'Educational Technology Impact on Student Learning', 'Educational Technology Research', '10.1000/ghi789', '4.12', '32'],
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
              <label for="date">Publication Date</label>
              <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
              <label for="author">Author</label>
              <input type="text" id="author" name="author" required placeholder="Enter author name">
            </div>
            <div class="form-group">
              <label for="title">Publication Title</label>
              <input type="text" id="title" name="title" required placeholder="Enter publication title">
            </div>
            <div class="form-group">
              <label for="journal">Journal/Conference</label>
              <input type="text" id="journal" name="journal" required placeholder="Enter journal or conference name">
            </div>
            <div class="form-group">
              <label for="doi">DOI</label>
              <input type="text" id="doi" name="doi" required placeholder="Enter DOI">
            </div>
            <div class="form-group">
              <label for="impact_factor">Impact Factor</label>
              <input type="number" id="impact_factor" name="impact_factor" step="0.01" required placeholder="Enter impact factor">
            </div>
            <div class="form-group">
              <label for="citations">Citations</label>
              <input type="number" id="citations" name="citations" required placeholder="Enter number of citations">
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
            <input type="hidden" name="index" id="editIndex">
            <div class="form-group">
              <label for="editDate">Publication Date</label>
              <input type="date" id="editDate" name="date" required>
            </div>
            <div class="form-group">
              <label for="editAuthor">Author</label>
              <input type="text" id="editAuthor" name="author" required>
            </div>
            <div class="form-group">
              <label for="editTitle">Publication Title</label>
              <input type="text" id="editTitle" name="title" required>
            </div>
            <div class="form-group">
              <label for="editJournal">Journal/Conference</label>
              <input type="text" id="editJournal" name="journal" required>
            </div>
            <div class="form-group">
              <label for="editDoi">DOI</label>
              <input type="text" id="editDoi" name="doi" required>
            </div>
            <div class="form-group">
              <label for="editImpactFactor">Impact Factor</label>
              <input type="number" id="editImpactFactor" name="impact_factor" step="0.01" required>
            </div>
            <div class="form-group">
              <label for="editCitations">Citations</label>
              <input type="number" id="editCitations" name="citations" required>
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
            <i class="fas fa-book"></i>
            <h2>Publications Overview</h2>
          </div>
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search publications..." id="searchInput">
          </div>
        </div>
        
        <div class="table-container">
          <table class="data-table" id="publicationsTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Author</th>
                <th>Title</th>
                <th>Journal/Conference</th>
                <th>DOI</th>
                <th>Impact Factor</th>
                <th>Citations</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($entries)): ?>
                <tr class="empty-state">
                  <td colspan="8">
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
                <tr data-index="<?php echo $i; ?>">
                  <td data-label="Date">
                    <span class="date-info"><?php echo htmlspecialchars($entry[0]); ?></span>
                  </td>
                  <td data-label="Author">
                    <div class="author-info">
                      <strong><?php echo htmlspecialchars($entry[1]); ?></strong>
                    </div>
                  </td>
                  <td data-label="Title">
                    <div class="title-content">
                      <h4><?php echo htmlspecialchars($entry[2]); ?></h4>
                    </div>
                  </td>
                  <td data-label="Journal/Conference">
                    <span class="journal-info"><?php echo htmlspecialchars($entry[3]); ?></span>
                  </td>
                  <td data-label="DOI">
                    <span class="doi-info"><?php echo htmlspecialchars($entry[4]); ?></span>
                  </td>
                  <td data-label="Impact Factor">
                    <span class="impact-factor"><?php echo htmlspecialchars($entry[5]); ?></span>
                  </td>
                  <td data-label="Citations">
                    <span class="citations-count"><?php echo htmlspecialchars($entry[6]); ?></span>
                  </td>
                  <td data-label="Actions">
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-index="<?php echo $i; ?>" 
                              data-date="<?php echo htmlspecialchars($entry[0]); ?>"
                              data-author="<?php echo htmlspecialchars($entry[1]); ?>"
                              data-title="<?php echo htmlspecialchars($entry[2]); ?>"
                              data-journal="<?php echo htmlspecialchars($entry[3]); ?>"
                              data-doi="<?php echo htmlspecialchars($entry[4]); ?>"
                              data-impact-factor="<?php echo htmlspecialchars($entry[5]); ?>"
                              data-citations="<?php echo htmlspecialchars($entry[6]); ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this publication?');">
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
    
    .doi-info {
      font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
      font-size: 0.75rem;
      color: #0369a1;
      background: #f0f9ff;
      padding: 2px 6px;
      border-radius: 4px;
    }
    
    .impact-factor {
      background: #fef3c7;
      color: #92400e;
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .citations-count {
      background: #dcfce7;
      color: #166534;
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
        const author = btn.dataset.author;
        const title = btn.dataset.title;
        const journal = btn.dataset.journal;
        const doi = btn.dataset.doi;
        const impactFactor = btn.dataset.impactFactor;
        const citations = btn.dataset.citations;

        document.getElementById('editIndex').value = index;
        document.getElementById('editDate').value = date;
        document.getElementById('editAuthor').value = author;
        document.getElementById('editTitle').value = title;
        document.getElementById('editJournal').value = journal;
        document.getElementById('editDoi').value = doi;
        document.getElementById('editImpactFactor').value = impactFactor;
        document.getElementById('editCitations').value = citations;

        openModal(editModal);
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

    // Upload button (placeholder)
    document.getElementById('uploadBtn').addEventListener('click', () => {
      alert('Upload functionality will be implemented here');
    });
  </script>
</body>
</html> 