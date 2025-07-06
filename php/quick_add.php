<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_type = $_POST['entry_type'] ?? '';
    
    try {
        switch ($entry_type) {
            case 'publication':
                $data = [
                    $_POST['date'] ?? '',
                    $_POST['author'] ?? '',
                    $_POST['title'] ?? '',
                    $_POST['journal'] ?? '',
                    $_POST['doi'] ?? '',
                    $_POST['status'] ?? 'Publication',
                    $_POST['scope'] ?? 'Local'
                ];
                
                $file = fopen(__DIR__ . '/publication_presentation.csv', 'a');
                fputcsv($file, $data);
                fclose($file);
                $success_message = 'Publication added successfully!';
                break;
                
            case 'research_activity':
                $data = [
                    $_POST['date'] ?? '',
                    $_POST['title'] ?? '',
                    $_POST['location'] ?? '',
                    $_POST['participants'] ?? '',
                    $_POST['participant_count'] ?? '',
                    $_POST['status'] ?? 'Completed'
                ];
                
                $file = fopen(__DIR__ . '/research_capacity_data.csv', 'a');
                fputcsv($file, $data);
                fclose($file);
                $success_message = 'Research activity added successfully!';
                break;
                
            case 'kpi':
                $data = [
                    $_POST['name'] ?? '',
                    $_POST['period'] ?? '',
                    $_POST['target'] ?? '',
                    $_POST['actual'] ?? '',
                    $_POST['unit'] ?? '',
                    $_POST['score'] ?? '',
                    $_POST['department'] ?? '',
                    $_POST['notes'] ?? ''
                ];
                
                $file = fopen(__DIR__ . '/kpi_records.csv', 'a');
                fputcsv($file, $data);
                fclose($file);
                $success_message = 'KPI record added successfully!';
                break;
                
            case 'ethics_protocol':
                $data = [
                    $_POST['number'] ?? '',
                    $_POST['title'] ?? '',
                    $_POST['department'] ?? '',
                    $_POST['status'] ?? 'Pending',
                    $_POST['action'] ?? 'Review'
                ];
                
                $file = fopen(__DIR__ . '/ethics_reviewed_protocols.csv', 'a');
                fputcsv($file, $data);
                fclose($file);
                $success_message = 'Ethics protocol added successfully!';
                break;
                
            case 'data_tool':
                $data = [
                    $_POST['name'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['department'] ?? '',
                    $_POST['status'] ?? 'Active',
                    $_POST['last_updated'] ?? date('Y-m-d')
                ];
                
                $file = fopen(__DIR__ . '/data_collection_tools.csv', 'a');
                fputcsv($file, $data);
                fclose($file);
                $success_message = 'Data collection tool added successfully!';
                break;
                
            default:
                $error_message = 'Invalid entry type selected.';
        }
    } catch (Exception $e) {
        $error_message = 'Error adding entry: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Add - RSO Research Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/modern-theme.css">
    <link rel="stylesheet" href="../css/theme.css">
    <style>
        .quick-add-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        
        .quick-add-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .quick-add-header h1 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .quick-add-header p {
            color: var(--text-secondary);
        }
        
        .entry-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        
        .entry-type-option {
            padding: 20px;
            border: 2px solid var(--border-primary);
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            background: var(--bg-secondary);
        }
        
        .entry-type-option:hover {
            border-color: var(--btn-primary-bg);
            background: var(--bg-hover);
        }
        
        .entry-type-option.selected {
            border-color: var(--btn-primary-bg);
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }
        
        .entry-type-option i {
            font-size: 2rem;
            margin-bottom: 12px;
            display: block;
        }
        
        .entry-type-option h3 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
        }
        
        .entry-type-option p {
            margin: 0;
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .form-container {
            display: none;
        }
        
        .form-container.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-primary);
            border-radius: 6px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--btn-primary-bg);
            box-shadow: 0 0 0 3px rgba(var(--btn-primary-bg-rgb), 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--btn-secondary-text);
        }
        
        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
        }
        
        .btn-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }
        
        .btn-primary:hover {
            background: var(--btn-primary-hover);
        }
        
        .alert {
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: var(--status-approved);
            color: var(--text-inverse);
        }
        
        .alert-error {
            background: var(--status-rejected);
            color: var(--text-inverse);
        }
        
        .required {
            color: var(--status-rejected);
        }
    </style>
</head>
<body>
    <div class="quick-add-container">
        <div class="quick-add-header">
            <h1><i class="fa-solid fa-plus"></i> Quick Add Entry</h1>
            <p>Quickly add new entries to any category in the RSO Research Management System</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" id="quickAddForm">
            <input type="hidden" name="entry_type" id="entryType" value="">
            
            <!-- Entry Type Selector -->
            <div class="entry-type-selector">
                <div class="entry-type-option" data-type="publication">
                    <i class="fa-solid fa-book"></i>
                    <h3>Publication</h3>
                    <p>Add a new publication or presentation</p>
                </div>
                <div class="entry-type-option" data-type="research_activity">
                    <i class="fa-solid fa-chart-line"></i>
                    <h3>Research Activity</h3>
                    <p>Add a research capacity building activity</p>
                </div>
                <div class="entry-type-option" data-type="kpi">
                    <i class="fa-solid fa-bullseye"></i>
                    <h3>KPI Record</h3>
                    <p>Add a new KPI measurement</p>
                </div>
                <div class="entry-type-option" data-type="ethics_protocol">
                    <i class="fa-solid fa-shield-halved"></i>
                    <h3>Ethics Protocol</h3>
                    <p>Add an ethics reviewed protocol</p>
                </div>
                <div class="entry-type-option" data-type="data_tool">
                    <i class="fa-solid fa-database"></i>
                    <h3>Data Tool</h3>
                    <p>Add a data collection tool</p>
                </div>
            </div>
            
            <!-- Publication Form -->
            <div class="form-container" id="publicationForm">
                <h3><i class="fa-solid fa-book"></i> Add Publication</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pub_date">Date <span class="required">*</span></label>
                        <input type="date" id="pub_date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="pub_author">Author <span class="required">*</span></label>
                        <input type="text" id="pub_author" name="author" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pub_title">Title <span class="required">*</span></label>
                    <input type="text" id="pub_title" name="title" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pub_journal">Journal/Conference</label>
                        <input type="text" id="pub_journal" name="journal">
                    </div>
                    <div class="form-group">
                        <label for="pub_doi">DOI/ISBN</label>
                        <input type="text" id="pub_doi" name="doi">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pub_status">Status</label>
                        <select id="pub_status" name="status">
                            <option value="Publication">Publication</option>
                            <option value="Presentation">Presentation</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pub_scope">Scope</label>
                        <select id="pub_scope" name="scope">
                            <option value="Local">Local</option>
                            <option value="National">National</option>
                            <option value="International">International</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Research Activity Form -->
            <div class="form-container" id="researchActivityForm">
                <h3><i class="fa-solid fa-chart-line"></i> Add Research Activity</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ra_date">Date <span class="required">*</span></label>
                        <input type="date" id="ra_date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="ra_title">Activity Title <span class="required">*</span></label>
                        <input type="text" id="ra_title" name="title" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ra_location">Location</label>
                        <input type="text" id="ra_location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="ra_participants">Participants</label>
                        <input type="text" id="ra_participants" name="participants">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ra_count">Participant Count</label>
                        <input type="number" id="ra_count" name="participant_count">
                    </div>
                    <div class="form-group">
                        <label for="ra_status">Status</label>
                        <select id="ra_status" name="status">
                            <option value="Completed">Completed</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Planned">Planned</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- KPI Form -->
            <div class="form-container" id="kpiForm">
                <h3><i class="fa-solid fa-bullseye"></i> Add KPI Record</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="kpi_name">KPI Name <span class="required">*</span></label>
                        <input type="text" id="kpi_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="kpi_period">Period <span class="required">*</span></label>
                        <input type="text" id="kpi_period" name="period" placeholder="e.g., Q1 2025" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="kpi_target">Target</label>
                        <input type="number" id="kpi_target" name="target" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="kpi_actual">Actual</label>
                        <input type="number" id="kpi_actual" name="actual" step="0.01">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="kpi_unit">Unit</label>
                        <input type="text" id="kpi_unit" name="unit">
                    </div>
                    <div class="form-group">
                        <label for="kpi_score">Score</label>
                        <input type="number" id="kpi_score" name="score" min="0" max="100" step="0.01">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="kpi_department">Department</label>
                        <input type="text" id="kpi_department" name="department">
                    </div>
                    <div class="form-group">
                        <label for="kpi_notes">Notes</label>
                        <input type="text" id="kpi_notes" name="notes">
                    </div>
                </div>
            </div>
            
            <!-- Ethics Protocol Form -->
            <div class="form-container" id="ethicsProtocolForm">
                <h3><i class="fa-solid fa-shield-halved"></i> Add Ethics Protocol</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ep_number">Protocol Number</label>
                        <input type="text" id="ep_number" name="number">
                    </div>
                    <div class="form-group">
                        <label for="ep_title">Title <span class="required">*</span></label>
                        <input type="text" id="ep_title" name="title" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ep_department">Department</label>
                        <input type="text" id="ep_department" name="department">
                    </div>
                    <div class="form-group">
                        <label for="ep_status">Status</label>
                        <select id="ep_status" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ep_action">Action</label>
                    <select id="ep_action" name="action">
                        <option value="Review">Review</option>
                        <option value="Approve">Approve</option>
                        <option value="Reject">Reject</option>
                        <option value="Request Revision">Request Revision</option>
                    </select>
                </div>
            </div>
            
            <!-- Data Tool Form -->
            <div class="form-container" id="dataToolForm">
                <h3><i class="fa-solid fa-database"></i> Add Data Collection Tool</h3>
                <div class="form-group">
                    <label for="dt_name">Tool Name <span class="required">*</span></label>
                    <input type="text" id="dt_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="dt_description">Description</label>
                    <textarea id="dt_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="dt_department">Department</label>
                        <input type="text" id="dt_department" name="department">
                    </div>
                    <div class="form-group">
                        <label for="dt_status">Status</label>
                        <select id="dt_status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Under Development">Under Development</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="dt_last_updated">Last Updated</label>
                    <input type="date" id="dt_last_updated" name="last_updated" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                    <i class="fa-solid fa-plus"></i>
                    Add Entry
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Entry type selection
        const entryTypeOptions = document.querySelectorAll('.entry-type-option');
        const entryTypeInput = document.getElementById('entryType');
        const formContainers = document.querySelectorAll('.form-container');
        const submitBtn = document.getElementById('submitBtn');
        
        entryTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                const selectedType = this.dataset.type;
                
                // Update visual selection
                entryTypeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                // Update hidden input
                entryTypeInput.value = selectedType;
                
                // Show/hide forms
                formContainers.forEach(container => {
                    container.classList.remove('active');
                });
                
                const targetForm = document.getElementById(selectedType + 'Form');
                if (targetForm) {
                    targetForm.classList.add('active');
                    submitBtn.style.display = 'inline-flex';
                }
            });
        });
        
        // Form validation
        document.getElementById('quickAddForm').addEventListener('submit', function(e) {
            if (!entryTypeInput.value) {
                e.preventDefault();
                alert('Please select an entry type first.');
                return false;
            }
        });
        
        // Auto-fill current date for date fields
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html> 