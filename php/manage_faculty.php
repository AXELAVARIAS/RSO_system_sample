<?php
session_start();
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
$users_file = 'users.csv';
$delete_message = '';

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_email = $_GET['delete'];
    if (file_exists($users_file)) {
        $rows = [];
        $deleted = false;
        $file = fopen($users_file, 'r');
        while (($data = fgetcsv($file)) !== false) {
            if ($data[0] === $delete_email && $data[2] === 'faculty') {
                $deleted = true;
                continue; // skip this row
            }
            $rows[] = $data;
        }
        fclose($file);
        $file = fopen($users_file, 'w');
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        $delete_message = $deleted ? 'Faculty account deleted.' : 'Account not found.';
    }
}

// PHP: Handle RSO delete request
if (isset($_GET['delete_rso']) && !empty($_GET['delete_rso'])) {
    $delete_email = $_GET['delete_rso'];
    if (file_exists($users_file)) {
        $rows = [];
        $deleted = false;
        $file = fopen($users_file, 'r');
        while (($data = fgetcsv($file)) !== false) {
            if ($data[0] === $delete_email && $data[2] === 'rso') {
                $deleted = true;
                continue; // skip this row
            }
            $rows[] = $data;
        }
        fclose($file);
        $file = fopen($users_file, 'w');
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        $delete_message = $deleted ? 'RSO account deleted.' : 'Account not found.';
    }
}

// Read all faculty users
$faculty = [];
if (file_exists($users_file)) {
    $file = fopen($users_file, 'r');
    while (($data = fgetcsv($file)) !== false) {
        if (isset($data[2]) && $data[2] === 'faculty') {
            $faculty[] = $data;
        }
    }
    fclose($file);
}

// PHP: Read all RSO users
$rso_accounts = [];
if (file_exists($users_file)) {
    $file = fopen($users_file, 'r');
    while (($data = fgetcsv($file)) !== false) {
        if (isset($data[2]) && $data[2] === 'rso') {
            $rso_accounts[] = $data;
        }
    }
    fclose($file);
}

// Data Collection Tools Management
$dct_file = 'data_collection_tools.csv';
$dct_entries = [];
if (file_exists($dct_file)) {
    $fp = fopen($dct_file, 'r');
    while ($row = fgetcsv($fp)) {
        $dct_entries[] = $row;
    }
    fclose($fp);
}

// Handle DCT delete
if (isset($_POST['delete_dct']) && isset($_POST['dct_index'])) {
    $index = (int)$_POST['dct_index'];
    if (isset($dct_entries[$index])) {
        array_splice($dct_entries, $index, 1);
        $fp = fopen($dct_file, 'w');
        foreach ($dct_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle DCT edit save
if (isset($_POST['save_dct_edit']) && isset($_POST['dct_index'])) {
    $index = (int)$_POST['dct_index'];
    $faculty_name = $_POST['faculty'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $title = $_POST['title'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $presented = $_POST['presented'] ?? '';
    $published = $_POST['published'] ?? '';
    $journal = $_POST['journal'] ?? '';
    if ($faculty_name && $degree && $sex && $title && $ownership && $presented && $published && $journal) {
        $dct_entries[$index] = [$faculty_name, $degree, $sex, $title, $ownership, $presented, $published, $journal];
        $fp = fopen($dct_file, 'w');
        foreach ($dct_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$dct_edit_index = isset($_GET['dct_edit']) ? (int)$_GET['dct_edit'] : null;

// --- KPI Records Management ---
$kpi_file = 'kpi_records.csv';
$kpi_entries = [];
if (file_exists($kpi_file)) {
    $fp = fopen($kpi_file, 'r');
    while ($row = fgetcsv($fp)) {
        $kpi_entries[] = $row;
    }
    fclose($fp);
}
$kpi_edit_index = isset($_GET['kpi_edit']) ? (int)$_GET['kpi_edit'] : null;
$kpi_edit_entry = ($kpi_edit_index !== null && isset($kpi_entries[$kpi_edit_index])) ? $kpi_entries[$kpi_edit_index] : null;
if (isset($_POST['delete_kpi']) && isset($_POST['kpi_index'])) {
    $index = (int)$_POST['kpi_index'];
    if (isset($kpi_entries[$index])) {
        array_splice($kpi_entries, $index, 1);
        $fp = fopen($kpi_file, 'w');
        foreach ($kpi_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
if (isset($_POST['save_kpi_edit']) && isset($_POST['kpi_index'])) {
    $index = (int)$_POST['kpi_index'];
    $faculty = $_POST['faculty'] ?? '';
    $period = $_POST['period'] ?? '';
    $publications = $_POST['publications'] ?? '';
    $trainings = $_POST['trainings'] ?? '';
    $presentations = $_POST['presentations'] ?? '';
    $score = $_POST['score'] ?? '';
    $performance = $_POST['performance'] ?? '';
    if ($faculty && $period && $publications && $trainings && $presentations && $score && $performance) {
        $kpi_entries[$index] = [$faculty, $period, $publications, $trainings, $presentations, $score, $performance];
        $fp = fopen($kpi_file, 'w');
        foreach ($kpi_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Ethics Reviewed Protocols Management ---
$ethics_file = 'ethics_reviewed_protocols.csv';
$ethics_entries = [];
if (file_exists($ethics_file)) {
    $fp = fopen($ethics_file, 'r');
    while ($row = fgetcsv($fp)) {
        $ethics_entries[] = $row;
    }
    fclose($fp);
}
$ethics_edit_index = isset($_GET['ethics_edit']) ? (int)$_GET['ethics_edit'] : null;
$ethics_edit_entry = ($ethics_edit_index !== null && isset($ethics_entries[$ethics_edit_index])) ? $ethics_entries[$ethics_edit_index] : null;
if (isset($_POST['delete_ethics']) && isset($_POST['ethics_index'])) {
    $index = (int)$_POST['ethics_index'];
    if (isset($ethics_entries[$index])) {
        array_splice($ethics_entries, $index, 1);
        $fp = fopen($ethics_file, 'w');
        foreach ($ethics_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
if (isset($_POST['save_ethics_edit']) && isset($_POST['ethics_index'])) {
    $index = (int)$_POST['ethics_index'];
    $no = $_POST['no'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $status = $_POST['status'] ?? '';
    $action = $_POST['action'] ?? '';
    if ($no && $title && $department && $status && $action) {
        $ethics_entries[$index] = [$no, $title, $department, $status, $action];
        $fp = fopen($ethics_file, 'w');
        foreach ($ethics_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Publication and Presentation Management ---
$pub_file = 'publication_presentation.csv';
$pub_entries = [];
if (file_exists($pub_file)) {
    $fp = fopen($pub_file, 'r');
    while ($row = fgetcsv($fp)) {
        $pub_entries[] = $row;
    }
    fclose($fp);
}
$pub_edit_index = isset($_GET['pub_edit']) ? (int)$_GET['pub_edit'] : null;
$pub_edit_entry = ($pub_edit_index !== null && isset($pub_entries[$pub_edit_index])) ? $pub_entries[$pub_edit_index] : null;
if (isset($_POST['delete_pub']) && isset($_POST['pub_index'])) {
    $index = (int)$_POST['pub_index'];
    if (isset($pub_entries[$index])) {
        array_splice($pub_entries, $index, 1);
        $fp = fopen($pub_file, 'w');
        foreach ($pub_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
if (isset($_POST['save_pub_edit']) && isset($_POST['pub_index'])) {
    $index = (int)$_POST['pub_index'];
    $date = $_POST['date'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $title = $_POST['title'] ?? '';
    $department = $_POST['department'] ?? '';
    $subsidy = $_POST['subsidy'] ?? '';
    $status = $_POST['status'] ?? '';
    $locality = $_POST['locality'] ?? '';
    if ($date && $faculty && $title && $department && $subsidy && $status && $locality) {
        $pub_entries[$index] = [$date, $faculty, $title, $department, $subsidy, $status, $locality];
        $fp = fopen($pub_file, 'w');
        foreach ($pub_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Research Capacity Building Activities Management ---
$rcb_file = 'research_capacity_data.csv';
$rcb_entries = [];
if (file_exists($rcb_file)) {
    $fp = fopen($rcb_file, 'r');
    while ($row = fgetcsv($fp)) {
        $rcb_entries[] = $row;
    }
    fclose($fp);
}
$rcb_edit_index = isset($_GET['rcb_edit']) ? (int)$_GET['rcb_edit'] : null;
$rcb_edit_entry = ($rcb_edit_index !== null && isset($rcb_entries[$rcb_edit_index])) ? $rcb_entries[$rcb_edit_index] : null;
if (isset($_POST['delete_rcb']) && isset($_POST['rcb_index'])) {
    $index = (int)$_POST['rcb_index'];
    if (isset($rcb_entries[$index])) {
        array_splice($rcb_entries, $index, 1);
        $fp = fopen($rcb_file, 'w');
        foreach ($rcb_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
if (isset($_POST['save_rcb_edit']) && isset($_POST['rcb_index'])) {
    $index = (int)$_POST['rcb_index'];
    $date = $_POST['date'] ?? '';
    $name = $_POST['name'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $facilitators = $_POST['facilitators'] ?? '';
    $num_participants = $_POST['num_participants'] ?? '';
    $status = $_POST['status'] ?? '';
    if ($date && $name && $venue && $facilitators && $num_participants && $status) {
        $rcb_entries[$index] = [$date, $name, $venue, $facilitators, $num_participants, $status];
        $fp = fopen($rcb_file, 'w');
        foreach ($rcb_entries as $entry) {
            fputcsv($fp, $entry);
        }
        fclose($fp);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
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
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .custom-sidebar {
            width: 230px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3557 0%, #274472 100%);
            color: #fff;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
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
        }
        .custom-sidebar nav {
            flex: 1;
        }
        .custom-sidebar .nav-link {
            color: #b8c6e0;
            padding: 14px 32px;
            font-size: 1.08rem;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .custom-sidebar .nav-link.active, .custom-sidebar .nav-link:hover {
            color: #fff;
            background: #22335a;
            border-left: 4px solid #4fc3f7;
        }
        .custom-sidebar .sidebar-footer {
            padding: 18px 32px;
            border-top: 1px solid #2c4066;
            font-size: 0.97rem;
            color: #b8c6e0;
        }
        .custom-topbar {
            margin-left: 230px;
            height: 64px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(30,53,87,0.06);
            display: flex;
            align-items: center;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 101;
        }
        .custom-topbar .page-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #22335a;
        }
        .custom-topbar .search-bar {
            margin-left: auto;
            margin-right: 18px;
        }
        .custom-topbar .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .custom-topbar .user-info img {
            width: 36px; height: 36px; border-radius: 50%;
        }
        .dashboard-content {
            margin-left: 230px;
            padding: 32px 24px 24px 24px;
        }
        .welcome-card {
            background: linear-gradient(90deg, #4fc3f7 0%, #1976d2 100%);
            color: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(30,53,87,0.07);
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
            box-shadow: 0 2px 12px rgba(30,53,87,0.07);
            background: #fff;
            padding: 0;
        }
        .card-custom .card-header {
            background: #22335a;
            color: #fff;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 18px 24px;
        }
        .card-custom .card-body {
            padding: 24px;
        }
        .table thead th {
            background: #f4f7fa;
            color: #22335a;
            font-weight: 600;
        }
        .btn-custom {
            background: #4fc3f7;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn-custom:hover { background: #1976d2; color: #fff; }
        html { scroll-behavior: smooth; }
        #faculty-accounts, #data-tools, #kpi-records, #ethics-protocols, #publication-presentation, #research-capacity {
            scroll-margin-top: 90px;
        }
        .nav-link.active {
            background: #22335a !important;
            color: #fff !important;
            border-left: 4px solid #4fc3f7;
            font-weight: bold;
        }
        .highlight-search {
            background: yellow;
            color: #222;
            border-radius: 3px;
            padding: 0 2px;
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

        // Search highlight
        const searchInput = document.querySelector('.custom-topbar .search-bar input[type="search"]');
        if (searchInput) {
            let matchIndex = 0;
            function scrollToMatch(idx) {
                const matches = document.querySelectorAll('.highlight-search');
                if (matches.length) {
                    if (idx >= matches.length) idx = 0;
                    if (idx < 0) idx = matches.length - 1;
                    matches[idx].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    matchIndex = idx;
                }
            }
            function highlightAndScroll() {
                const query = searchInput.value.trim();
                // Remove old highlights
                document.querySelectorAll('.highlight-search').forEach(span => {
                    const parent = span.parentNode;
                    parent.replaceChild(document.createTextNode(span.textContent), span);
                    parent.normalize();
                });
                if (!query) return;
                // Highlight matches in all tables
                const tables = document.querySelectorAll('.dashboard-content table');
                tables.forEach(table => {
                    Array.from(table.tBodies).forEach(tbody => {
                        Array.from(tbody.rows).forEach(row => {
                            Array.from(row.cells).forEach(cell => {
                                highlightText(cell, query);
                            });
                        });
                    });
                });
                matchIndex = 0;
                scrollToMatch(matchIndex);
            }
            searchInput.addEventListener('input', function() {
                highlightAndScroll();
            });
            // Scroll to next match on Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const matches = document.querySelectorAll('.highlight-search');
                    if (matches.length) {
                        matchIndex = (matchIndex + 1) % matches.length;
                        scrollToMatch(matchIndex);
                    }
                }
            });
            // Scroll to next match on search button click
            const searchBtn = searchInput.parentElement.querySelector('button[type="submit"]');
            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const matches = document.querySelectorAll('.highlight-search');
                    if (matches.length) {
                        matchIndex = (matchIndex + 1) % matches.length;
                        scrollToMatch(matchIndex);
                    }
                });
            }
        }
        function highlightText(element, query) {
            if (!query) return;
            const regex = new RegExp('('+escapeRegExp(query)+')', 'gi');
            // Only highlight text nodes
            Array.from(element.childNodes).forEach(node => {
                if (node.nodeType === 3 && node.textContent.match(regex)) {
                    const html = node.textContent.replace(regex, '<span class="highlight-search">$1</span>');
                    const temp = document.createElement('span');
                    temp.innerHTML = html;
                    while (temp.firstChild) {
                        element.insertBefore(temp.firstChild, node);
                    }
                    element.removeChild(node);
                }
            });
        }
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
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
    </div>
    <div class="dashboard-content">
        <div class="welcome-card">
            <div>
                <div class="welcome-title">Welcome, <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? 'Admin'); ?>!</div>
                <div class="welcome-desc">Manage faculty accounts and research data efficiently from your custom admin dashboard.</div>
            </div>
            <div style="font-size:2.5rem;opacity:0.2;"><i class="fa fa-user-shield"></i></div>
        </div>
        <div class="dashboard-grid">
            <div class="card-custom" id="faculty-accounts">
                <div class="card-header"><i class="fa fa-users me-2"></i>Faculty Accounts</div>
                <div class="card-body">
                    <?php if ($delete_message): ?>
                        <div class="alert alert-success text-center"> <?php echo htmlspecialchars($delete_message); ?> </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($faculty) === 0): ?>
                                    <tr><td colspan="3" class="text-center">No faculty accounts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($faculty as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user[0]); ?></td>
                                            <td><?php echo htmlspecialchars($user[2]); ?></td>
                                            <td><a class="btn btn-danger btn-sm" href="?delete=<?php echo urlencode($user[0]); ?>" onclick="return confirm('Delete this faculty account?');"><i class="fa fa-trash"></i> Delete</a></td>
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
                                    <th>User Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rso_accounts) === 0): ?>
                                    <tr><td colspan="3" class="text-center">No RSO accounts found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($rso_accounts as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user[0]); ?></td>
                                            <td><?php echo htmlspecialchars($user[2]); ?></td>
                                            <td><a class="btn btn-danger btn-sm" href="?delete_rso=<?php echo urlencode($user[0]); ?>" onclick="return confirm('Delete this RSO account?');"><i class="fa fa-trash"></i> Delete</a></td>
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
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name of Faculty</th>
                                <th>Degree</th>
                                <th>Sex</th>
                                <th>Research Title</th>
                                <th>Ownership</th>
                                <th>Date & Venue Presented</th>
                                <th>Date Published</th>
                                <th>Journal Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dct_entries as $i => $entry): ?>
                                <?php if ($dct_edit_index === $i): ?>
                                    <tr class="table-warning">
                                        <form method="post" action="">
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry[0]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="degree" value="<?php echo htmlspecialchars($entry[1]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="sex" value="<?php echo htmlspecialchars($entry[2]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry[3]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="ownership" value="<?php echo htmlspecialchars($entry[4]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="presented" value="<?php echo htmlspecialchars($entry[5]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="published" value="<?php echo htmlspecialchars($entry[6]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="journal" value="<?php echo htmlspecialchars($entry[7]); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <input type="hidden" name="dct_index" value="<?php echo $i; ?>">
                                                <button type="submit" name="save_dct_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[5]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[6]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[7]); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?dct_edit=<?php echo $i; ?>" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="dct_index" value="<?php echo $i; ?>">
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
                            <?php foreach ($kpi_entries as $i => $entry): ?>
                                <?php if ($kpi_edit_index === $i): ?>
                                    <tr class="table-warning" id="edit-row-kpi-<?php echo $i; ?>">
                                        <form method="post" action="">
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry[0]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="period" value="<?php echo htmlspecialchars($entry[1]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="publications" min="0" value="<?php echo htmlspecialchars($entry[2]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="trainings" min="0" value="<?php echo htmlspecialchars($entry[3]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="presentations" min="0" value="<?php echo htmlspecialchars($entry[4]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="score" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($entry[5]); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <select name="performance" class="form-select form-select-sm" required>
                                                    <option value="Excellent" <?php if ($entry[6]==='Excellent') echo 'selected'; ?>>Excellent</option>
                                                    <option value="Very Good" <?php if ($entry[6]==='Very Good') echo 'selected'; ?>>Very Good</option>
                                                    <option value="Good" <?php if ($entry[6]==='Good') echo 'selected'; ?>>Good</option>
                                                    <option value="Satisfactory" <?php if ($entry[6]==='Satisfactory') echo 'selected'; ?>>Satisfactory</option>
                                                    <option value="Needs Improvement" <?php if ($entry[6]==='Needs Improvement') echo 'selected'; ?>>Needs Improvement</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="hidden" name="kpi_index" value="<?php echo $i; ?>">
                                                <button type="submit" name="save_kpi_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[5]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[6]); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?kpi_edit=<?php echo $i; ?>" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="kpi_index" value="<?php echo $i; ?>">
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
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ethics_entries as $i => $entry): ?>
                                <?php if ($ethics_edit_index === $i): ?>
                                    <tr class="table-warning" id="edit-row-ethics-<?php echo $i; ?>">
                                        <form method="post" action="">
                                            <td><input type="text" name="no" value="<?php echo htmlspecialchars($entry[0]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry[1]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="department" value="<?php echo htmlspecialchars($entry[2]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="status" value="<?php echo htmlspecialchars($entry[3]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="action" value="<?php echo htmlspecialchars($entry[4]); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <input type="hidden" name="ethics_index" value="<?php echo $i; ?>">
                                                <button type="submit" name="save_ethics_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?ethics_edit=<?php echo $i; ?>" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="ethics_index" value="<?php echo $i; ?>">
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
                            <?php foreach ($pub_entries as $i => $entry): ?>
                                <?php if ($pub_edit_index === $i): ?>
                                    <tr class="table-warning" id="edit-row-pub-<?php echo $i; ?>">
                                        <form method="post" action="">
                                            <td><input type="date" name="date" value="<?php echo htmlspecialchars($entry[0]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="faculty" value="<?php echo htmlspecialchars($entry[1]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="title" value="<?php echo htmlspecialchars($entry[2]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="department" value="<?php echo htmlspecialchars($entry[3]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="subsidy" value="<?php echo htmlspecialchars($entry[4]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="status" value="<?php echo htmlspecialchars($entry[5]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="locality" value="<?php echo htmlspecialchars($entry[6]); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <input type="hidden" name="pub_index" value="<?php echo $i; ?>">
                                                <button type="submit" name="save_pub_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[5]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[6]); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?pub_edit=<?php echo $i; ?>" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="pub_index" value="<?php echo $i; ?>">
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
                            <?php foreach ($rcb_entries as $i => $entry): ?>
                                <?php if ($rcb_edit_index === $i): ?>
                                    <tr class="table-warning" id="edit-row-rcb-<?php echo $i; ?>">
                                        <form method="post" action="">
                                            <td><input type="date" name="date" value="<?php echo htmlspecialchars($entry[0]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($entry[1]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="venue" value="<?php echo htmlspecialchars($entry[2]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="facilitators" value="<?php echo htmlspecialchars($entry[3]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="number" name="num_participants" min="0" value="<?php echo htmlspecialchars($entry[4]); ?>" class="form-control form-control-sm" required></td>
                                            <td><input type="text" name="status" value="<?php echo htmlspecialchars($entry[5]); ?>" class="form-control form-control-sm" required></td>
                                            <td>
                                                <input type="hidden" name="rcb_index" value="<?php echo $i; ?>">
                                                <button type="submit" name="save_rcb_edit" class="btn btn-custom btn-sm"><i class="fa fa-save"></i> Save</button>
                                                <a href="manage_faculty.php" class="btn btn-secondary btn-sm">Cancel</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($entry[0]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[1]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[2]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[3]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[4]); ?></td>
                                        <td><?php echo htmlspecialchars($entry[5]); ?></td>
                                        <td>
                                            <a href="manage_faculty.php?rcb_edit=<?php echo $i; ?>" class="btn btn-custom btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="rcb_index" value="<?php echo $i; ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 