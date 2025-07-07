# Excel Upload Functionality for Research Capacity Building Activities

## Overview
The Research Capacity Building Activities page now supports uploading data via CSV files. This allows users to bulk import research activities instead of adding them one by one.

## Features

### ✅ Supported Features
- **CSV File Upload**: Upload comma-separated value files
- **Template Download**: Download a sample CSV template with correct headers
- **Data Validation**: Validates required fields and data formats
- **Error Reporting**: Shows detailed error messages for failed imports
- **Success Feedback**: Displays import results with success/error counts

### 📋 Required CSV Headers
Your CSV file must include these exact headers (case-insensitive):
- `Date` - Activity date (YYYY-MM-DD format)
- `Activity Name` - Title of the activity
- `Venue` - Location where activity takes place
- `Facilitators` - Organizers or facilitators
- `Number of Participants` - Number of people attending
- `Status` - Activity status (Scheduled, In Progress, Completed, or Cancelled)

### 📁 File Format Support
- **CSV files** (.csv) - Fully supported
- **Excel files** (.xlsx) - Fully supported
- **Excel files** (.xls) - Basic support (text extraction)

## How to Use

### 1. Download Template
1. Click the "Upload Excel" button on the Research Capacity Building Activities page
2. In the upload modal, click "Download Template"
3. Open the downloaded CSV file in Excel or any spreadsheet application

### 2. Prepare Your Data
1. Fill in your data following the template format
2. Ensure all required fields are filled
3. Use YYYY-MM-DD format for dates
4. Use valid status values: Scheduled, In Progress, Completed, or Cancelled

### 3. Upload Your File
1. You can use Excel (.xlsx, .xls) or CSV format
2. Click "Upload Excel" button
3. Select your file
4. Click "Upload File"

### 4. Review Results
- Success message will show how many activities were imported
- Error messages will show any issues with specific rows
- Imported activities will appear in the main table

## Data Validation Rules

### Required Fields
- Date (must be valid date format)
- Activity Name (cannot be empty)
- Venue (cannot be empty)
- Facilitators (cannot be empty)

### Optional Fields
- Number of Participants (defaults to 0 if invalid)
- Status (defaults to "Scheduled" if invalid)

### Date Formats Supported
- YYYY-MM-DD (recommended)
- MM/DD/YYYY
- DD/MM/YYYY

### Status Values
- Scheduled
- In Progress
- Completed
- Cancelled

## Error Handling

The system provides detailed error messages for:
- Missing required headers
- Missing required fields
- Invalid date formats
- Database errors
- File format issues

## Technical Details

### Files Modified
- `php/Research  Capacity Buildings Activities.php` - Added upload modal and functionality
- `php/upload_excel.php` - New file for handling uploads
- `templates/research_capacity_template.csv` - Template file

### Database Integration
- Uses existing `research_capacity_activities` table
- Maintains data integrity with proper validation
- Supports all existing fields and constraints

### Security Features
- Session validation
- File type validation
- SQL injection prevention
- XSS protection

## Troubleshooting

### Common Issues

**"Missing required headers" error**
- Download the template and use the exact header names
- Check for extra spaces or typos in headers

**"Invalid date format" error**
- Use YYYY-MM-DD format (e.g., 2025-01-15)
- Avoid using text dates like "January 15, 2025"

**"No data found" error**
- Ensure your CSV file has data rows (not just headers)
- Check that cells are not empty

**Excel file not supported**
- Ensure your Excel file has data in the first sheet
- For .xls files, consider saving as .xlsx for better compatibility
- Make sure the file follows the required header format

### Getting Help
If you encounter issues:
1. Check the error messages for specific details
2. Download and use the template as a starting point
3. Ensure your data follows the validation rules
4. Contact system administrator for technical support

## Future Enhancements
- Enhanced .xls file parsing
- Bulk export functionality
- Advanced data validation rules
- Import preview before confirmation 