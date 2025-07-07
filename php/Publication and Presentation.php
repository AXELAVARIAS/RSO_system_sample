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
            <button class="profile-action theme-toggle" id="themeToggle" title="Toggle Theme">
              <i class="fas fa-moon"></i>
              <span>Dark Mode</span>
            </button>
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
                <tr data-id="<?php echo $entry['id']; ?>">
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
                      <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this publication?');">
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