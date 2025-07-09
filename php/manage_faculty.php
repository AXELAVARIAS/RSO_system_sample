<?php
session_start();
require_once '../database/config.php';
// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: loginpage.php');
    exit;
}
// Check admin login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: loginpage.php');
    exit;
}
// manage_faculty.php - Admin management of faculty accounts

// --- Faculty and RSO Accounts Management (SQL version) ---
$faculty = [];
$rso_accounts = [];
try {
    $db = getDB();
    $faculty = $db->fetchAll("SELECT * FROM users WHERE user_type = 'faculty' ORDER BY full_name");
    $rso_accounts = $db->fetchAll("SELECT * FROM users WHERE user_type = 'rso' ORDER BY full_name");
} catch (Exception $e) {
    $users_error_message = 'Failed to load user accounts: ' . $e->getMessage();
}

// Handle delete requests (SQL version)
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_email = $_GET['delete'];
    try {
        $db = getDB();
        $db->query("DELETE FROM users WHERE email = ? AND user_type = 'faculty'", [$delete_email]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#faculty-accounts');
        exit;
    } catch (Exception $e) {
        $users_error_message = 'Failed to delete faculty account: ' . $e->getMessage();
    }
}

if (isset($_GET['delete_rso']) && !empty($_GET['delete_rso'])) {
    $delete_email = $_GET['delete_rso'];
    try {
        $db = getDB();
        $db->query("DELETE FROM users WHERE email = ? AND user_type = 'rso'", [$delete_email]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#faculty-accounts');
        exit;
    } catch (Exception $e) {
        $users_error_message = 'Failed to delete RSO account: ' . $e->getMessage();
    }
}

// Handle activate/deactivate requests
if (isset($_POST['toggle_active']) && isset($_POST['toggle_email'])) {
    $toggle_email = $_POST['toggle_email'];
    try {
        $db = getDB();
        // Get current status
        $user = $db->fetch("SELECT is_active FROM users WHERE email = ? AND user_type = 'faculty'", [$toggle_email]);
        if ($user) {
            $new_status = ($user['is_active'] ?? 1) ? 0 : 1;
            $db->query("UPDATE users SET is_active = ? WHERE email = ? AND user_type = 'faculty'", [$new_status, $toggle_email]);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '#faculty-accounts');
        exit;
    } catch (Exception $e) {
        $users_error_message = 'Failed to update account status: ' . $e->getMessage();
    }
}

// Update backend logic to accept status from dropdown
if (isset($_POST['set_active_status']) && isset($_POST['toggle_email']) && isset($_POST['new_status'])) {
    $toggle_email = $_POST['toggle_email'];
    $new_status = ($_POST['new_status'] === '1') ? 1 : 0;
    try {
        $db = getDB();
        $db->query("UPDATE users SET is_active = ? WHERE email = ? AND user_type = 'faculty'", [$new_status, $toggle_email]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#faculty-accounts');
        exit;
    } catch (Exception $e) {
        $users_error_message = 'Failed to update account status: ' . $e->getMessage();
    }
}

// Update backend logic to accept status from dropdown for RSO accounts
if (isset($_POST['set_active_status_rso']) && isset($_POST['toggle_email_rso']) && isset($_POST['new_status_rso'])) {
    $toggle_email = $_POST['toggle_email_rso'];
    $new_status = ($_POST['new_status_rso'] === '1') ? 1 : 0;
    try {
        $db = getDB();
        $db->query("UPDATE users SET is_active = ? WHERE email = ? AND user_type = 'rso'", [$new_status, $toggle_email]);
        header('Location: ' . $_SERVER['PHP_SELF'] . '#rso-accounts');
        exit;
    } catch (Exception $e) {
        $users_error_message = 'Failed to update RSO account status: ' . $e->getMessage();
    }
}

// Data Collection Tools Management (SQL version)
$dct_entries = [];
try {
    $db = getDB();
    $dct_entries = $db->fetchAll("SELECT * FROM data_collection_tools ORDER BY submission_date DESC");
} catch (Exception $e) {
    $dct_error_message = 'Failed to load data collection tools: ' . $e->getMessage();
}

// Handle DCT add, edit, delete (SQL version)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        // Add new entry
        if (isset($_POST['add_dct'])) {
            $faculty = $_POST['dct_faculty'] ?? '';
            $degree = $_POST['dct_degree'] ?? '';
            $sex = $_POST['dct_sex'] ?? '';
            $title = $_POST['dct_title'] ?? '';
            $ownership = $_POST['dct_ownership'] ?? '';
            $presented = $_POST['dct_presented'] ?? '';
            $published = $_POST['dct_published'] ?? '';
            $journal = $_POST['dct_journal'] ?? '';
            if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
                $db->query("INSERT INTO data_collection_tools (researcher_name, degree, gender, research_title, role, location, submission_date, research_area) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#data-tools');
                exit;
            }
        }
        // Edit entry
        if (isset($_POST['save_dct_edit']) && isset($_POST['dct_id'])) {
            $id = (int)$_POST['dct_id'];
            $faculty = $_POST['faculty'] ?? '';
            $degree = $_POST['degree'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $title = $_POST['title'] ?? '';
            $ownership = $_POST['ownership'] ?? '';
            $presented = $_POST['presented'] ?? '';
            $published = $_POST['published'] ?? '';
            $journal = $_POST['journal'] ?? '';
            if ($faculty && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
                $db->query("UPDATE data_collection_tools SET researcher_name = ?, degree = ?, gender = ?, research_title = ?, role = ?, location = ?, submission_date = ?, research_area = ? WHERE id = ?",
                    [$faculty, $degree, $sex, $title, $ownership, $presented, $published, $journal, $id]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#data-tools');
                exit;
            }
        }
        // Delete entry
        if (isset($_POST['delete_dct']) && isset($_POST['dct_id'])) {
            $id = (int)$_POST['dct_id'];
            $db->query("DELETE FROM data_collection_tools WHERE id = ?", [$id]);
            header('Location: ' . $_SERVER['PHP_SELF'] . '#data-tools');
            exit;
        }
    } catch (Exception $e) {
        $dct_error_message = 'Database error: ' . $e->getMessage();
    }
}

// --- KPI Records Management (SQL version) ---
$kpi_entries = [];
try {
    $db = getDB();
    $kpi_entries = $db->fetchAll("SELECT * FROM kpi_records ORDER BY id DESC");
} catch (Exception $e) {
    $kpi_error_message = 'Failed to load KPI records: ' . $e->getMessage();
}

// Handle KPI add, edit, delete (SQL version)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        // Add new entry
        if (isset($_POST['add_kpi'])) {
            $faculty = $_POST['kpi_faculty'] ?? '';
            $period = $_POST['kpi_period'] ?? '';
            $publications = $_POST['kpi_publications'] ?? '';
            $trainings = $_POST['kpi_trainings'] ?? '';
            $presentations = $_POST['kpi_presentations'] ?? '';
            $score = $_POST['kpi_score'] ?? '';
            $performance = $_POST['kpi_performance'] ?? '';
            if ($faculty && $period && $publications && $trainings && $presentations && $score && $performance) {
                $db->query("INSERT INTO kpi_records (faculty_name, quarter, publications_count, research_projects_count, presentations_count, performance_score, performance_rating) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$faculty, $period, $publications, $trainings, $presentations, $score, $performance]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#kpi-records');
                exit;
            }
        }
        // Edit entry
        if (isset($_POST['save_kpi_edit']) && isset($_POST['kpi_id'])) {
            $id = (int)$_POST['kpi_id'];
            $faculty = $_POST['faculty'] ?? '';
            $period = $_POST['period'] ?? '';
            $publications = $_POST['publications'] ?? '';
            $trainings = $_POST['trainings'] ?? '';
            $presentations = $_POST['presentations'] ?? '';
            $score = $_POST['score'] ?? '';
            $performance = $_POST['performance'] ?? '';
            if ($faculty && $period && $publications && $trainings && $presentations && $score && $performance) {
                $db->query("UPDATE kpi_records SET faculty_name = ?, quarter = ?, publications_count = ?, research_projects_count = ?, presentations_count = ?, performance_score = ?, performance_rating = ? WHERE id = ?",
                    [$faculty, $period, $publications, $trainings, $presentations, $score, $performance, $id]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#kpi-records');
                exit;
            }
        }
        // Delete entry
        if (isset($_POST['delete_kpi']) && isset($_POST['kpi_id'])) {
            $id = (int)$_POST['kpi_id'];
            $db->query("DELETE FROM kpi_records WHERE id = ?", [$id]);
            header('Location: ' . $_SERVER['PHP_SELF'] . '#kpi-records');
            exit;
        }
    } catch (Exception $e) {
        $kpi_error_message = 'Database error: ' . $e->getMessage();
    }
}

// --- Ethics Reviewed Protocols Management (SQL version) ---
$ethics_entries = [];
try {
    $db = getDB();
    $ethics_entries = $db->fetchAll("SELECT * FROM ethics_reviewed_protocols ORDER BY id DESC");
} catch (Exception $e) {
    $ethics_error_message = 'Failed to load protocols: ' . $e->getMessage();
}

// Handle Ethics add, edit, delete (SQL version)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        // Add new entry
        if (isset($_POST['add_ethics'])) {
            $no = $_POST['ethics_no'] ?? '';
            $title = $_POST['ethics_title'] ?? '';
            $department = $_POST['ethics_department'] ?? '';
            $status = $_POST['ethics_status'] ?? '';
            $remarks = $_POST['ethics_remarks'] ?? '';
            if ($no && $title && $department && $status && $remarks) {
                $db->query("INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)",
                    [$no, $title, $department, $status, $remarks]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#ethics-protocols');
                exit;
            }
        }
        // Edit entry
        if (isset($_POST['save_ethics_edit']) && isset($_POST['ethics_id'])) {
            $id = (int)$_POST['ethics_id'];
            $no = $_POST['no'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $status = $_POST['status'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            if ($no && $title && $department && $status && $remarks) {
                $db->query("UPDATE ethics_reviewed_protocols SET protocol_number = ?, title = ?, department = ?, status = ?, action_taken = ? WHERE id = ?",
                    [$no, $title, $department, $status, $remarks, $id]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#ethics-protocols');
                exit;
            }
        }
        // Delete entry
        if (isset($_POST['delete_ethics']) && isset($_POST['ethics_id'])) {
            $id = (int)$_POST['ethics_id'];
            $db->query("DELETE FROM ethics_reviewed_protocols WHERE id = ?", [$id]);
            header('Location: ' . $_SERVER['PHP_SELF'] . '#ethics-protocols');
            exit;
        }
    } catch (Exception $e) {
        $ethics_error_message = 'Database error: ' . $e->getMessage();
    }
}

// --- Publication and Presentation Management (SQL version) ---
$pub_entries = [];
try {
    $db = getDB();
    $pub_entries = $db->fetchAll("SELECT * FROM publication_presentations ORDER BY application_date DESC");
} catch (Exception $e) {
    $pub_error_message = 'Failed to load publications: ' . $e->getMessage();
}

// Handle Publication add, edit, delete (SQL version)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        // Add new entry
        if (isset($_POST['add_pub'])) {
            $date = $_POST['pub_date'] ?? '';
            $faculty = $_POST['pub_faculty'] ?? '';
            $title = $_POST['pub_title'] ?? '';
            $department = $_POST['pub_department'] ?? '';
            $subsidy = $_POST['pub_subsidy'] ?? '';
            $status = $_POST['pub_status'] ?? '';
            $locality = $_POST['pub_locality'] ?? '';
            if ($date && $faculty && $title && $department && $subsidy && $status && $locality) {
                $db->query("INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$date, $faculty, $title, $department, $subsidy, $status, $locality]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#publication-presentation');
                exit;
            }
        }
        // Edit entry
        if (isset($_POST['save_pub_edit']) && isset($_POST['pub_id'])) {
            $id = (int)$_POST['pub_id'];
            $date = $_POST['date'] ?? '';
            $faculty = $_POST['faculty'] ?? '';
            $title = $_POST['title'] ?? '';
            $department = $_POST['department'] ?? '';
            $subsidy = $_POST['subsidy'] ?? '';
            $status = $_POST['status'] ?? '';
            $locality = $_POST['locality'] ?? '';
            if ($date && $faculty && $title && $department && $subsidy && $status && $locality) {
                $db->query("UPDATE publication_presentations SET application_date = ?, author_name = ?, paper_title = ?, department = ?, research_subsidy = ?, status = ?, scope = ? WHERE id = ?",
                    [$date, $faculty, $title, $department, $subsidy, $status, $locality, $id]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#publication-presentation');
                exit;
            }
        }
        // Delete entry
        if (isset($_POST['delete_pub']) && isset($_POST['pub_id'])) {
            $id = (int)$_POST['pub_id'];
            $db->query("DELETE FROM publication_presentations WHERE id = ?", [$id]);
            header('Location: ' . $_SERVER['PHP_SELF'] . '#publication-presentation');
            exit;
        }
    } catch (Exception $e) {
        $pub_error_message = 'Database error: ' . $e->getMessage();
    }
}

// --- Research Capacity Building Activities Management (SQL version) ---
$rcb_entries = [];
try {
    $db = getDB();
    $rcb_entries = $db->fetchAll("SELECT * FROM research_capacity_activities ORDER BY activity_date DESC");
} catch (Exception $e) {
    $rcb_error_message = 'Failed to load research capacity activities: ' . $e->getMessage();
}

// Handle RCB add, edit, delete (SQL version)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        // Add new entry
        if (isset($_POST['add_rcb'])) {
            $date = $_POST['rcb_date'] ?? '';
            $name = $_POST['rcb_name'] ?? '';
            $venue = $_POST['rcb_venue'] ?? '';
            $facilitators = $_POST['rcb_facilitators'] ?? '';
            $num_participants = $_POST['rcb_num_participants'] ?? '';
            $status = $_POST['rcb_status'] ?? '';
            if ($date && $name && $venue && $facilitators && $num_participants && $status) {
                $db->query("INSERT INTO research_capacity_activities (activity_date, activity_title, venue, organizer, participants_count, status) VALUES (?, ?, ?, ?, ?, ?)",
                    [$date, $name, $venue, $facilitators, $num_participants, $status]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#research-capacity');
                exit;
            }
        }
        // Edit entry
        if (isset($_POST['save_rcb_edit']) && isset($_POST['rcb_id'])) {
            $id = (int)$_POST['rcb_id'];
            $date = $_POST['date'] ?? '';
            $name = $_POST['name'] ?? '';
            $venue = $_POST['venue'] ?? '';
            $facilitators = $_POST['facilitators'] ?? '';
            $num_participants = $_POST['num_participants'] ?? '';
            $status = $_POST['status'] ?? '';
            if ($date && $name && $venue && $facilitators && $num_participants && $status) {
                $db->query("UPDATE research_capacity_activities SET activity_date = ?, activity_title = ?, venue = ?, organizer = ?, participants_count = ?, status = ? WHERE id = ?",
                    [$date, $name, $venue, $facilitators, $num_participants, $status, $id]);
                header('Location: ' . $_SERVER['PHP_SELF'] . '#research-capacity');
                exit;
            }
        }
        // Delete entry
        if (isset($_POST['delete_rcb']) && isset($_POST['rcb_id'])) {
            $id = (int)$_POST['rcb_id'];
            $db->query("DELETE FROM research_capacity_activities WHERE id = ?", [$id]);
            header('Location: ' . $_SERVER['PHP_SELF'] . '#research-capacity');
            exit;
        }
    } catch (Exception $e) {
        $rcb_error_message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Manage Faculty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/theme.css">
    <style>
        body {
            background: var(--bg-primary);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--text-primary);
        }
        .custom-sidebar {
            width: 230px;
            min-height: 100vh;
            background: var(--bg-secondary);
            color: var(--text-primary);
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-primary);
        }
        .custom-sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            padding: 32px 0 24px 0;
            text-align: center;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--text-primary);
        }
        .custom-sidebar nav {
            flex: 1;
        }
        .custom-sidebar .nav-link {
            color: var(--text-secondary);
            padding: 14px 32px;
            font-size: 1.08rem;
            border-left: 4px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
        }
        .custom-sidebar .nav-link.active, .custom-sidebar .nav-link:hover {
            color: var(--text-primary);
            background: var(--bg-tertiary);
            border-left: 4px solid var(--btn-primary-bg);
        }
        .custom-sidebar .sidebar-footer {
            padding: 18px 32px;
            border-top: 1px solid var(--border-primary);
            font-size: 0.97rem;
            color: var(--text-secondary);
        }
        .custom-topbar {
            margin-left: 230px;
            height: 64px;
            background: var(--bg-header);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 101;
            border-bottom: 1px solid var(--border-primary);
        }
        .custom-topbar .page-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .custom-topbar .search-bar {
            margin-left: auto;
            margin-right: 18px;
        }
        .custom-topbar .search-bar input {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-primary);
        }
        .custom-topbar .search-bar input:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .custom-topbar .search-bar input::placeholder {
            color: var(--input-placeholder);
        }
        .custom-topbar .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
        }
        .custom-topbar .user-info img {
            width: 36px; height: 36px; border-radius: 50%;
        }
        .dashboard-content {
            margin-left: 230px;
            padding: 32px 24px 24px 24px;
        }
        .welcome-card {
            background: linear-gradient(90deg, var(--btn-primary-bg) 0%, var(--btn-primary-hover) 100%);
            color: var(--text-inverse);
            border-radius: 14px;
            box-shadow: var(--shadow-md);
            padding: 32px 32px 24px 32px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .welcome-card .welcome-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .welcome-card .welcome-desc {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
        @media (max-width: 991.98px) {
            .custom-sidebar, .custom-topbar, .dashboard-content { margin-left: 0 !important; }
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        .card-custom {
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            background: var(--bg-card);
            padding: 0;
            border: 1px solid var(--border-primary);
        }
        .card-custom .card-header {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-primary);
        }
        .card-custom .card-body {
            padding: 24px;
            background: var(--bg-card);
        }
        .table {
            background: var(--bg-card);
            color: var(--text-primary);
        }
        .table thead th {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-weight: 600;
            border-bottom: 1px solid var(--border-primary);
        }
        .table tbody tr {
            background: var(--bg-card);
        }
        .table tbody tr:hover {
            background: var(--bg-tertiary);
        }
        .table tbody td {
            border-top: 1px solid var(--border-primary);
            color: var(--text-primary);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background: var(--bg-secondary);
        }
        .table-striped tbody tr:nth-of-type(odd):hover {
            background: var(--bg-tertiary);
        }
        .table-warning {
            background: rgba(255, 193, 7, 0.1) !important;
        }
        [data-theme="dark"] .table-warning {
            background: rgba(255, 193, 7, 0.2) !important;
        }
        .btn-custom {
            background: var(--btn-primary-bg);
            color: var(--text-inverse);
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn-custom:hover { 
            background: var(--btn-primary-hover); 
            color: var(--text-inverse); 
        }
        .btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-primary);
        }
        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
            color: var(--text-primary);
        }
        .btn-danger {
            background: var(--btn-danger-bg);
            color: var(--text-inverse);
        }
        .btn-danger:hover {
            background: var(--btn-danger-hover);
            color: var(--text-inverse);
        }
        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-primary);
        }
        .form-control:focus {
            background: var(--input-bg);
            border-color: var(--input-focus-border);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .form-control::placeholder {
            color: var(--input-placeholder);
        }
        .form-select {
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-primary);
        }
        .form-select:focus {
            background: var(--input-bg);
            border-color: var(--input-focus-border);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .alert {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            color: var(--text-primary);
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--status-approved);
            color: var(--status-approved);
        }
        [data-theme="dark"] .alert-success {
            background: rgba(16, 185, 129, 0.2);
        }
        html { scroll-behavior: smooth; }
        #faculty-accounts, #data-tools, #kpi-records, #ethics-protocols, #publication-presentation, #research-capacity {
            scroll-margin-top: 90px;
        }
        .nav-link.active {
            background: var(--bg-tertiary) !important;
            color: var(--text-primary) !important;
            border-left: 4px solid var(--btn-primary-bg);
            font-weight: bold;
        }

        /* Theme toggle button styling */
        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 15px;
        }
        .theme-toggle:hover {
            background: var(--bg-secondary);
            border-color: var(--border-secondary);
        }
        .theme-toggle i {
            font-size: 1.1rem;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }
        .theme-toggle:hover i {
            color: var(--text-primary);
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.custom-sidebar .nav-link');
        function setActiveLinkByHash() {
            links.forEach(link => link.classList.remove('active'));
            const hash = window.location.hash || '#faculty-accounts';
            const active = Array.from(links).find(link => link.getAttribute('href') === hash);
            if (active) active.classList.add('active');
        }
        setActiveLinkByHash();
        window.addEventListener('hashchange', setActiveLinkByHash);
        const sections = [
            'faculty-accounts', 'data-tools', 'kpi-records', 'ethics-protocols', 'publication-presentation', 'research-capacity'
        ].map(id => document.getElementById(id));
        window.addEventListener('scroll', function() {
            let found = false;
            for (let i = sections.length - 1; i >= 0; i--) {
                const sec = sections[i];
                if (sec && window.scrollY + 100 >= sec.offsetTop) {
                    links.forEach(link => link.classList.remove('active'));
                    const active = Array.from(links).find(link => link.getAttribute('href') === '#' + sec.id);
                    if (active) active.classList.add('active');
                    found = true;
                    break;
                }
            }
            if (!found) setActiveLinkByHash();
        });

        // Search filter functionality
        const searchInput = document.querySelector('.custom-topbar .search-bar input[type="search"]');
        if (searchInput) {
            function filterTables() {
                const query = searchInput.value.trim().toLowerCase();
                const tables = document.querySelectorAll('.dashboard-content table');
                
                tables.forEach(table => {
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    let hasVisibleRows = false;
                    
                    rows.forEach(row => {
                        const cells = Array.from(row.querySelectorAll('td'));
                        let rowMatches = false;
                        
                        // Check if any cell in the row contains the search query
                        cells.forEach(cell => {
                            const cellText = cell.textContent.toLowerCase();
                            if (cellText.includes(query)) {
                                rowMatches = true;
                            }
                        });
                        
                        // Show/hide the row based on match
                        if (query === '' || rowMatches) {
                            row.style.display = '';
                            hasVisibleRows = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Show "no results" message if no rows match
                    const noResultsRow = tbody.querySelector('.no-results-row');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                    
                    if (query !== '' && !hasVisibleRows) {
                        const colSpan = tbody.querySelector('tr') ? tbody.querySelector('tr').cells.length : 1;
                        const noResults = document.createElement('tr');
                        noResults.className = 'no-results-row';
                        noResults.innerHTML = `<td colspan="${colSpan}" class="text-center text-muted">No results found for "${query}"</td>`;
                        tbody.appendChild(noResults);
                    }
                });
            }
            
            // Filter on input
            searchInput.addEventListener('input', filterTables);
            
            // Prevent form submission on Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
            
            // Prevent form submission on search button click
            const searchBtn = searchInput.parentElement.querySelector('button[type="submit"]');
            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                });
            }
        }

        // Save scroll position before submitting any edit form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Only for edit/save forms (not logout, not search)
                if (
                    this.querySelector('[name="save_dct_edit"]') ||
                    this.querySelector('[name="save_kpi_edit"]') ||
                    this.querySelector('[name="save_ethics_edit"]') ||
                    this.querySelector('[name="save_pub_edit"]') ||
                    this.querySelector('[name="save_rcb_edit"]')
                ) {
                    sessionStorage.setItem('scrollY', window.scrollY);
                }
            });
        });
        // Restore scroll position after reload
        if (sessionStorage.getItem('scrollY')) {
            window.scrollTo({ top: parseInt(sessionStorage.getItem('scrollY'), 10), behavior: 'auto' });
            sessionStorage.removeItem('scrollY');
        }

        // Scroll to editing row if in edit mode
        const urlParams = new URLSearchParams(window.location.search);
        const editParams = [
            { param: 'dct_edit', prefix: 'edit-row-dct-' },
            { param: 'kpi_edit', prefix: 'edit-row-kpi-' },
            { param: 'ethics_edit', prefix: 'edit-row-ethics-' },
            { param: 'pub_edit', prefix: 'edit-row-pub-' },
            { param: 'rcb_edit', prefix: 'edit-row-rcb-' }
        ];
        for (const {param, prefix} of editParams) {
            if (urlParams.has(param)) {
                const idx = urlParams.get(param);
                const row = document.getElementById(prefix + idx);
                if (row) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    });
    </script>
</head>
<body>
    <div class="custom-sidebar">
        <div class="logo d-flex align-items-center justify-content-center" style="gap: 12px;">
            <img src="../pics/rso-bg.png" alt="Logo" style="height:36px;width:36px;object-fit:contain;">
            <span>RSO Admin</span>
        </div>
        <nav class="nav flex-column">
            <a href="#faculty-accounts" class="nav-link"><i class="fa fa-users me-2"></i>User Accounts</a>
            <a href="#data-tools" class="nav-link"><i class="fa fa-database me-2"></i>Data Collection Tool</a>
            <a href="#kpi-records" class="nav-link"><i class="fa fa-chart-bar me-2"></i>KPI Records</a>
            <a href="#ethics-protocols" class="nav-link"><i class="fa fa-file-alt me-2"></i>Ethics Reviewed Protocols</a>
            <a href="#publication-presentation" class="nav-link"><i class="fa fa-book me-2"></i>Publication and Presentation</a>
            <a href="#research-capacity" class="nav-link"><i class="fa fa-chalkboard-teacher me-2"></i>Research Capacity Building Activities</a>
        </nav>
        <div class="sidebar-footer mt-auto">
            <div><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'Admin'); ?></div>
            <form method="post" class="mt-2">
                <button type="submit" name="logout" class="btn btn-sm btn-light w-100">Logout</button>
            </form>
        </div>
    </div>
    <div class="custom-topbar">
        <div class="page-title"><i class="fa fa-cogs me-2"></i>Admin Dashboard</div>
        <form class="search-bar d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
            <button class="btn btn-custom" type="submit"><i class="fa fa-search"></i></button>
        </form>
        <div class="user-info ms-3">
            <span><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'Admin'); ?></span>
        </div>
        <!-- Theme Toggle -->
        <button class="theme-toggle" title="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>
    </div>
    <div class="dashboard-content">
        <div class="welcome-card">
            <div>
                <div class="welcome-title">Welcome, <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'Admin'); ?>!</div>
                <div class="welcome-desc">Manage faculty accounts and research data efficiently from your custom admin dashboard.</div>
            </div>
            <div style="font-size:2.5rem;opacity:0.2;"><i class="fa fa-user-shield"></i></div>
        </div>
        <!-- Tab Navigation and tab-content removed, restore all sections as cards -->
        <div class="dashboard-grid">
            <div class="card-custom" id="faculty-accounts">
                <div class="card-header"><i class="fa fa-users me-2"></i>Faculty Accounts</div>
                <div class="card-body">
                    <?php if (!empty($users_error_message)): ?>
                        <div class="alert alert-error mb-2"><?php echo htmlspecialchars($users_error_message); ?></div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>User Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($faculty) === 0): ?>
                                    <tr><td colspan="6" class="text-center">No faculty accounts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($faculty as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                                            <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                                            <td>
                                                <form method="post" action="" style="display:inline;">
                                                    <input type="hidden" name="toggle_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                    <input type="hidden" name="set_active_status" value="1">
                                                    <select name="new_status" class="form-select form-select-sm" style="width:110px;display:inline-block;" onchange="this.form.submit()">
                                                        <option value="1" <?php if (isset($user['is_active']) && $user['is_active']) echo 'selected'; ?>>Active</option>
                                                        <option value="0" <?php if (isset($user['is_active']) && !$user['is_active']) echo 'selected'; ?>>Inactive</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <a class="btn btn-danger btn-sm" href="?delete=<?php echo urlencode($user['email']); ?>" onclick="return confirm('Delete this faculty account?');"><i class="fa fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-custom" id="rso-accounts">
                <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#6a82fb 0%,#fc5c7d 100%)!important;"><i class="fa fa-user-shield me-2"></i>RSO Accounts</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Department</th>
                                    <th>User Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rso_accounts) === 0): ?>
                                    <tr><td colspan="6" class="text-center">No RSO accounts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($rso_accounts as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                                            <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                                            <td>
                                                <form method="post" action="" style="display:inline;">
                                                    <input type="hidden" name="toggle_email_rso" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                    <input type="hidden" name="set_active_status_rso" value="1">
                                                    <select name="new_status_rso" class="form-select form-select-sm" style="width:110px;display:inline-block;" onchange="this.form.submit()">
                                                        <option value="1" <?php if (isset($user['is_active']) && $user['is_active']) echo 'selected'; ?>>Active</option>
                                                        <option value="0" <?php if (isset($user['is_active']) && !$user['is_active']) echo 'selected'; ?>>Inactive</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <a class="btn btn-danger btn-sm" href="?delete_rso=<?php echo urlencode($user['email']); ?>" onclick="return confirm('Delete this RSO account?');"><i class="fa fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-custom mt-4" id="data-tools">
            <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#43cea2 0%,#185a9d 100%)!important;"><i class="fa fa-database me-2"></i>Data Collection Tools</div>
            <div class="card-body">
                <!-- Add Data Collection Tool Form -->
                <form method="post" class="row g-2 mb-3">
                    <div class="col-md-2"><input type="text" name="dct_faculty" class="form-control" placeholder="Faculty Name" required></div>
                    <div class="col-md-1"><input type="text" name="dct_degree" class="form-control" placeholder="Degree" required></div>
                    <div class="col-md-1"><input type="text" name="dct_sex" class="form-control" placeholder="Sex" required></div>
                    <div class="col-md-2"><input type="text" name="dct_title" class="form-control" placeholder="Research Title" required></div>
                    <div class="col-md-1"><input type="text" name="dct_ownership" class="form-control" placeholder="Ownership" required></div>
                    <div class="col-md-1"><input type="text" name="dct_presented" class="form-control" placeholder="Presented" required></div>
                    <div class="col-md-2"><input type="text" name="dct_published" class="form-control" placeholder="Published" required></div>
                    <div class="col-md-1"><input type="text" name="dct_journal" class="form-control" placeholder="Journal" required></div>
                    <div class="col-md-1"><button type="submit" name="add_dct" class="btn btn-custom w-100">Add</button></div>
                </form>
                <?php if (!empty($dct_error_message)): ?>
                    <div class="alert alert-error mb-2"><?php echo htmlspecialchars($dct_error_message); ?></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
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
                            <?php foreach ($dct_entries as $entry): ?>
                                <?php if (isset($_GET['dct_edit']) && $_GET['dct_edit'] == $entry['id']): ?>
                                    <tr class="table-warning">
                                        <form method="post" action="">
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry['researcher_name']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="degree" value="<?php echo htmlspecialchars($entry['degree']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="sex" value="<?php echo htmlspecialchars($entry['gender']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry['research_title']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="ownership" value="<?php echo htmlspecialchars($entry['role']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="presented" value="<?php echo htmlspecialchars($entry['location']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="published" value="<?php echo htmlspecialchars($entry['submission_date']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="journal" value="<?php echo htmlspecialchars($entry['research_area']); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <input type="hidden" name="dct_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="save_dct_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php#data-tools" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry['researcher_name']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['degree']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['research_title']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['role']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['location']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['submission_date']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['research_area']); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?dct_edit=<?php echo $entry['id']; ?>#data-tools" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="dct_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="delete_dct" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-custom mt-4" id="kpi-records">
            <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#f7971e 0%,#ffd200 100%)!important;"><i class="fa fa-chart-bar me-2"></i>Manage KPI Records</div>
            <div class="card-body">
                <!-- Add KPI Record Form -->
                <form method="post" class="row g-2 mb-3">
                    <div class="col-md-2"><input type="text" name="kpi_faculty" class="form-control" placeholder="Faculty Name" required></div>
                    <div class="col-md-1"><input type="text" name="kpi_period" class="form-control" placeholder="Period" required></div>
                    <div class="col-md-1"><input type="number" name="kpi_publications" class="form-control" placeholder="Publications" min="0" required></div>
                    <div class="col-md-1"><input type="number" name="kpi_trainings" class="form-control" placeholder="Trainings" min="0" required></div>
                    <div class="col-md-1"><input type="number" name="kpi_presentations" class="form-control" placeholder="Presentations" min="0" required></div>
                    <div class="col-md-1"><input type="number" name="kpi_score" class="form-control" placeholder="Score" min="0" max="10" step="0.1" required></div>
                    <div class="col-md-2">
                        <select name="kpi_performance" class="form-select" required>
                            <option value="Excellent">Excellent</option>
                            <option value="Very Good">Very Good</option>
                            <option value="Good">Good</option>
                            <option value="Satisfactory">Satisfactory</option>
                            <option value="Needs Improvement">Needs Improvement</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="submit" name="add_kpi" class="btn btn-custom w-100">Add</button></div>
                </form>
                <?php if (!empty($kpi_error_message)): ?>
                    <div class="alert alert-error mb-2"><?php echo htmlspecialchars($kpi_error_message); ?></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
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
                            <?php foreach ($kpi_entries as $entry): ?>
                                <?php if (isset($_GET['kpi_edit']) && $_GET['kpi_edit'] == $entry['id']): ?>
                                    <tr class="table-warning" id="edit-row-kpi-<?php echo $entry['id']; ?>">
                                        <form method="post" action="">
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry['faculty_name']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="period" value="<?php echo htmlspecialchars($entry['quarter']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="publications" min="0" value="<?php echo htmlspecialchars($entry['publications_count']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="trainings" min="0" value="<?php echo htmlspecialchars($entry['research_projects_count']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="presentations" min="0" value="<?php echo htmlspecialchars($entry['presentations_count']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="score" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($entry['performance_score']); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <select name="performance" class="form-select form-select-sm" required>
                                                    <option value="Excellent" <?php if ($entry['performance_rating']==='Excellent') echo 'selected'; ?>>Excellent</option>
                                                    <option value="Very Good" <?php if ($entry['performance_rating']==='Very Good') echo 'selected'; ?>>Very Good</option>
                                                    <option value="Good" <?php if ($entry['performance_rating']==='Good') echo 'selected'; ?>>Good</option>
                                                    <option value="Satisfactory" <?php if ($entry['performance_rating']==='Satisfactory') echo 'selected'; ?>>Satisfactory</option>
                                                    <option value="Needs Improvement" <?php if ($entry['performance_rating']==='Needs Improvement') echo 'selected'; ?>>Needs Improvement</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="hidden" name="kpi_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="save_kpi_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php#kpi-records" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry['faculty_name']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['quarter']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['publications_count']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['research_projects_count']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['presentations_count']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['performance_score']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['performance_rating']); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?kpi_edit=<?php echo $entry['id']; ?>#kpi-records" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="kpi_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="delete_kpi" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-custom mt-4" id="ethics-protocols">
            <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#ff5858 0%,#f09819 100%)!important;"><i class="fa fa-file-alt me-2"></i>Manage Ethics Reviewed Protocols</div>
            <div class="card-body">
                <!-- Add Ethics Protocol Form -->
                <form method="post" class="row g-2 mb-3">
                    <div class="col-md-2"><input type="text" name="ethics_no" class="form-control" placeholder="No." required></div>
                    <div class="col-md-2"><input type="text" name="ethics_title" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-2"><input type="text" name="ethics_department" class="form-control" placeholder="Department" required></div>
                    <div class="col-md-2">
                        <select name="ethics_status" class="form-select" required>
                            <option value="Approved">Approved</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2"><input type="text" name="ethics_remarks" class="form-control" placeholder="Remarks" required></div>
                    <div class="col-md-2"><button type="submit" name="add_ethics" class="btn btn-custom w-100">Add</button></div>
                </form>
                <?php if (!empty($ethics_error_message)): ?>
                    <div class="alert alert-error mb-2"><?php echo htmlspecialchars($ethics_error_message); ?></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ethics_entries as $entry): ?>
                                <?php if (isset($_GET['ethics_edit']) && $_GET['ethics_edit'] == $entry['id']): ?>
                                    <tr class="table-warning" id="edit-row-ethics-<?php echo $entry['id']; ?>">
                                        <form method="post" action="">
                                            <td><input type="text" name="no" value="<?php echo htmlspecialchars($entry['protocol_number']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry['title']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="department" value="<?php echo htmlspecialchars($entry['department']); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="Approved" <?php if ($entry['status']==='Approved') echo 'selected'; ?>>Approved</option>
                                                    <option value="Under Review" <?php if ($entry['status']==='Under Review') echo 'selected'; ?>>Under Review</option>
                                                    <option value="Pending" <?php if ($entry['status']==='Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="Rejected" <?php if ($entry['status']==='Rejected') echo 'selected'; ?>>Rejected</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="remarks" value="<?php echo htmlspecialchars($entry['action_taken']); ?>" class="form-control form-control-sm" placeholder="Remarks" required></td>
                                            <td>
                                                <input type="hidden" name="ethics_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="save_ethics_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php#ethics-protocols" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry['protocol_number']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['department']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['status']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['action_taken']); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?ethics_edit=<?php echo $entry['id']; ?>#ethics-protocols" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="ethics_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="delete_ethics" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-custom mt-4" id="publication-presentation">
            <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#43cea2 0%,#185a9d 100%)!important;"><i class="fa fa-book me-2"></i>Manage Publication and Presentation</div>
            <div class="card-body">
                <!-- Add Publication/Presentation Form -->
                <form method="post" class="row g-2 mb-3">
                    <div class="col-md-2"><input type="date" name="pub_date" class="form-control" required></div>
                    <div class="col-md-2"><input type="text" name="pub_faculty" class="form-control" placeholder="Faculty" required></div>
                    <div class="col-md-2"><input type="text" name="pub_title" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-1"><input type="text" name="pub_department" class="form-control" placeholder="Department" required></div>
                    <div class="col-md-1"><input type="text" name="pub_subsidy" class="form-control" placeholder="Subsidy" required></div>
                    <div class="col-md-2">
                        <select name="pub_status" class="form-select" required>
                            <option value="">Select status</option>
                            <option value="Draft">Draft</option>
                            <option value="Submitted">Submitted</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Accepted">Accepted</option>
                            <option value="Published">Published</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select name="pub_locality" class="form-select" required>
                            <option value="">Select scope</option>
                            <option value="Local">Local</option>
                            <option value="International">International</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="submit" name="add_pub" class="btn btn-custom w-100">Add</button></div>
                </form>
                <?php if (!empty($pub_error_message)): ?>
                    <div class="alert alert-error mb-2"><?php echo htmlspecialchars($pub_error_message); ?></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Faculty</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Subsidy</th>
                                <th>Status</th>
                                <th>Locality</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pub_entries as $entry): ?>
                                <?php if (isset($_GET['pub_edit']) && $_GET['pub_edit'] == $entry['id']): ?>
                                    <tr class="table-warning" id="edit-row-pub-<?php echo $entry['id']; ?>">
                                        <form method="post" action="">
                                            <td><input type="date" name="date" value="<?php echo htmlspecialchars($entry['application_date']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry['author_name']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry['paper_title']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="department" value="<?php echo htmlspecialchars($entry['department']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="subsidy" value="<?php echo htmlspecialchars($entry['research_subsidy']); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="Draft" <?php if ($entry['status']==='Draft') echo 'selected'; ?>>Draft</option>
                                                    <option value="Submitted" <?php if ($entry['status']==='Submitted') echo 'selected'; ?>>Submitted</option>
                                                    <option value="Under Review" <?php if ($entry['status']==='Under Review') echo 'selected'; ?>>Under Review</option>
                                                    <option value="Accepted" <?php if ($entry['status']==='Accepted') echo 'selected'; ?>>Accepted</option>
                                                    <option value="Published" <?php if ($entry['status']==='Published') echo 'selected'; ?>>Published</option>
                                                    <option value="Rejected" <?php if ($entry['status']==='Rejected') echo 'selected'; ?>>Rejected</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="locality" class="form-select form-select-sm" required>
                                                    <option value="Local" <?php if ($entry['scope']==='Local') echo 'selected'; ?>>Local</option>
                                                    <option value="International" <?php if ($entry['scope']==='International') echo 'selected'; ?>>International</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="hidden" name="pub_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="save_pub_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php#publication-presentation" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry['application_date']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['author_name']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['paper_title']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['department']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['research_subsidy']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['status']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['scope']); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?pub_edit=<?php echo $entry['id']; ?>#publication-presentation" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="pub_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="delete_pub" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-custom mt-4" id="research-capacity">
            <div class="card-header bg-gradient" style="background:linear-gradient(90deg,#11998e 0%,#38ef7d 100%)!important;"><i class="fa fa-chalkboard-teacher me-2"></i>Manage Research Capacity Building Activities</div>
            <div class="card-body">
                <!-- Add Research Capacity Activity Form -->
                <form method="post" class="row g-2 mb-3">
                    <div class="col-md-2"><input type="date" name="rcb_date" class="form-control" required></div>
                    <div class="col-md-2"><input type="text" name="rcb_name" class="form-control" placeholder="Activity Name" required></div>
                    <div class="col-md-2"><input type="text" name="rcb_venue" class="form-control" placeholder="Venue" required></div>
                    <div class="col-md-2"><input type="text" name="rcb_facilitators" class="form-control" placeholder="Facilitators" required></div>
                    <div class="col-md-2"><input type="number" name="rcb_num_participants" class="form-control" placeholder="No. of Participants" min="0" required></div>
                    <div class="col-md-1">
                        <select name="rcb_status" class="form-select" required>
                            <option value="Completed">Completed</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button type="submit" name="add_rcb" class="btn btn-custom w-100">Add</button></div>
                </form>
                <?php if (!empty($rcb_error_message)): ?>
                    <div class="alert alert-error mb-2"><?php echo htmlspecialchars($rcb_error_message); ?></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity Name</th>
                                <th>Venue</th>
                                <th>Facilitators/Participants</th>
                                <th>No. of Participants</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rcb_entries as $entry): ?>
                                <?php if (isset($_GET['rcb_edit']) && $_GET['rcb_edit'] == $entry['id']): ?>
                                    <tr class="table-warning" id="edit-row-rcb-<?php echo $entry['id']; ?>">
                                        <form method="post" action="">
                                            <td><input type="date" name="date" value="<?php echo htmlspecialchars($entry['activity_date']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($entry['activity_title']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="venue" value="<?php echo htmlspecialchars($entry['venue']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="facilitators" value="<?php echo htmlspecialchars($entry['organizer']); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="num_participants" min="0" value="<?php echo htmlspecialchars($entry['participants_count']); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="Completed" <?php if ($entry['status']==='Completed') echo 'selected'; ?>>Completed</option>
                                                    <option value="In Progress" <?php if ($entry['status']==='In Progress') echo 'selected'; ?>>In Progress</option>
                                                    <option value="Scheduled" <?php if ($entry['status']==='Scheduled') echo 'selected'; ?>>Scheduled</option>
                                                    <option value="Cancelled" <?php if ($entry['status']==='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="hidden" name="rcb_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="save_rcb_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php#research-capacity" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry['activity_date']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['activity_title']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['venue']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['organizer']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['participants_count']); ?></td>
                                        <td><?php echo htmlspecialchars($entry['status']); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?rcb_edit=<?php echo $entry['id']; ?>#research-capacity" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="rcb_id" value="<?php echo $entry['id']; ?>">
                                                <button type="submit" name="delete_rcb" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?');"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 