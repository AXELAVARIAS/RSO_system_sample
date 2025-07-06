<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Handle export request
if (isset($_POST['export'])) {
    $export_type = $_POST['export_type'] ?? 'comprehensive';
    $include_data = $_POST['include_data'] ?? [];
    
    // Redirect to appropriate export script
    if ($export_type === 'comprehensive') {
        header('Location: export_report.php');
        exit;
    } else {
        // For specific data types, we'll modify the export script
        $query_string = http_build_query([
            'type' => $export_type,
            'data' => $include_data
        ]);
        header('Location: export_report.php?' . $query_string);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Options - RSO Research Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/modern-theme.css">
    <link rel="stylesheet" href="../css/theme.css">
    <style>
        .export-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        
        .export-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .export-header h1 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .export-header p {
            color: var(--text-secondary);
        }
        
        .export-options {
            display: grid;
            gap: 30px;
        }
        
        .option-group {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-primary);
        }
        
        .option-group h3 {
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .radio-option:hover {
            background: var(--bg-hover);
        }
        
        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--btn-primary-bg);
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        
        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .checkbox-option:hover {
            background: var(--bg-hover);
        }
        
        .checkbox-option input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--btn-primary-bg);
        }
        
        .export-actions {
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
        
        .info-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="export-container">
        <div class="export-header">
            <h1><i class="fa-solid fa-download"></i> Export Research Report</h1>
            <p>Choose what data you'd like to export from the RSO Research Management System</p>
        </div>
        
        <form method="post" class="export-options">
            <div class="option-group">
                <h3><i class="fa-solid fa-file-export"></i> Export Type</h3>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="export_type" value="comprehensive" checked>
                        <div>
                            <strong>Comprehensive Report</strong>
                            <div class="info-text">Export all data with summary statistics and recent activity</div>
                        </div>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="export_type" value="specific">
                        <div>
                            <strong>Specific Data Types</strong>
                            <div class="info-text">Choose which specific data types to include</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="option-group" id="dataSelection" style="display: none;">
                <h3><i class="fa-solid fa-database"></i> Select Data Types</h3>
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="include_data[]" value="publications" checked>
                        <i class="fa-solid fa-book"></i>
                        Publications
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="include_data[]" value="ethics" checked>
                        <i class="fa-solid fa-shield-halved"></i>
                        Ethics Protocols
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="include_data[]" value="research" checked>
                        <i class="fa-solid fa-chart-line"></i>
                        Research Activities
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="include_data[]" value="kpi" checked>
                        <i class="fa-solid fa-bullseye"></i>
                        KPI Records
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="include_data[]" value="tools" checked>
                        <i class="fa-solid fa-database"></i>
                        Data Collection Tools
                    </label>
                </div>
            </div>
            
            <div class="export-actions">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <button type="submit" name="export" class="btn btn-primary">
                    <i class="fa-solid fa-download"></i>
                    Export Report
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Show/hide data selection based on export type
        document.querySelectorAll('input[name="export_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const dataSelection = document.getElementById('dataSelection');
                if (this.value === 'specific') {
                    dataSelection.style.display = 'block';
                } else {
                    dataSelection.style.display = 'none';
                }
            });
        });
        
        // Validate form before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const exportType = document.querySelector('input[name="export_type"]:checked').value;
            
            if (exportType === 'specific') {
                const checkedBoxes = document.querySelectorAll('input[name="include_data[]"]:checked');
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one data type to export.');
                    return false;
                }
            }
        });
    </script>
</body>
</html> 