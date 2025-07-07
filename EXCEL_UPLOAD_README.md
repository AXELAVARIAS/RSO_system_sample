# Excel Upload Functionality for Research Capacity Building Activities

## Overview
The Research Capacity Building Activities page now supports bulk data import through Excel file uploads. Users can upload Excel (.xls, .xlsx) or CSV files to automatically add multiple activities to the system.

## Features

### Supported File Formats
- **Excel 2007+ (.xlsx)** - Modern Excel format
- **Excel 97-2003 (.xls)** - Legacy Excel format  
- **CSV (.csv)** - Comma-separated values

### File Requirements
- **Maximum file size**: 5MB
- **Required columns** (case-insensitive):
  - Date
  - Activity Name
  - Venue
  - Facilitators
  - Number of Participants
  - Status

### Data Validation
The system validates:
- Required fields are not empty
- Date format (converts common formats to YYYY-MM-DD)
- Participant count is a valid number
- Status is one of: Scheduled, In Progress, Completed, Cancelled

## How to Use

### 1. Access the Upload Feature
- Navigate to "Research Capacity Building Activities" page
- Click the "Upload Excel" button in the top-right corner

### 2. Prepare Your File
- Download the template using the "Download Template" button
- Fill in your data following the template format
- Save as Excel (.xlsx, .xls) or CSV format

### 3. Upload Your File
- Click "Choose File" and select your prepared file
- Review the file information displayed
- Click "Upload File" to process

### 4. Review Results
- The system will show upload progress
- Success/error messages will be displayed
- The page will automatically refresh to show new data

## File Format Example

```csv
Date,Activity Name,Venue,Facilitators,Number of Participants,Status
2024-01-15,Research Methodology Workshop,Conference Room A,Dr. John Smith,25,Completed
2024-01-20,Data Analysis Training,Computer Lab 101,Prof. Jane Doe,15,In Progress
2024-01-25,Academic Writing Seminar,Auditorium,Dr. Robert Johnson,40,Scheduled
```

## Error Handling

### Common Issues
1. **Invalid file format**: Ensure file is .xls, .xlsx, or .csv
2. **Missing required fields**: All columns must be present
3. **Invalid date format**: Use YYYY-MM-DD or common formats
4. **File too large**: Keep files under 5MB
5. **Empty rows**: Rows with no data are automatically skipped

### Error Messages
- Clear error messages explain what went wrong
- Row-specific errors show which data rows had issues
- Partial imports are supported (valid rows are imported)

## Technical Details

### Files Modified/Created
- `php/Research  Capacity Buildings Activities.php` - Main page with upload UI
- `php/upload_excel_research_capacity.php` - Upload handler
- `php/download_template.php` - Template download handler
- `css/theme.css` - Added alert color variables

### Security Features
- File type validation (extension + MIME type)
- File size limits
- SQL injection prevention through prepared statements
- Session-based authentication required

### Database Integration
- Data is inserted into `research_capacity_activities` table
- Automatic timestamp handling
- Duplicate prevention through unique constraints

## Troubleshooting

### Upload Fails
1. Check file format and size
2. Ensure all required columns are present
3. Verify date formats are valid
4. Check server error logs for detailed messages

### Partial Import
- Review error messages for specific row issues
- Fix data format issues and re-upload
- Valid rows are still imported even if some fail

### Performance
- Large files may take time to process
- Progress bar shows upload status
- Server timeout is set to handle large files

## Future Enhancements
- Support for additional file formats
- Batch processing for very large files
- Data preview before import
- Export functionality for existing data
- Template customization options 