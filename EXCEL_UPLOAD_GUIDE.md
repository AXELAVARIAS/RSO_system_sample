# Excel Upload Guide for Ethics Reviewed Protocols

## Overview
The Ethics Reviewed Protocols page now supports bulk upload of data via Excel files (.xlsx, .xls) or CSV files.

## How to Use

### 1. Access the Upload Feature
- Navigate to the Ethics Reviewed Protocols page
- Click the "Upload Excel" button in the top-right corner

### 2. Prepare Your File
Your Excel/CSV file should contain the following columns (in any order):
- **Protocol Number** (e.g., EP-2025-001)
- **Research Title** (full research title)
- **Department** (department name)
- **Status** (Approved, Under Review, Pending, Rejected)
- **Action Taken** (description of action taken)

### 3. File Requirements
- First row must contain column headers
- All fields are required
- Maximum file size: 10MB
- Supported formats: .xlsx, .xls, .csv

### 4. Upload Process
1. Click "Upload Excel" button
2. Click "Download Template" to get a sample file
3. Fill in your data following the template format
4. Click "Choose File" and select your Excel/CSV file
5. Click "Upload File" to process the data
6. Review the results and any error messages

### 5. Testing the Upload
- Use the "Load Sample Data" button to test with sample data
- Use the "Debug Info" button to check system configuration
- Check the browser console for detailed error information

## Sample Data Format

```csv
Protocol Number,Research Title,Department,Status,Action Taken
EP-2025-001,Study on Student Learning Patterns,Computer Science,Approved,Protocol approved after review
EP-2025-002,Social Media Impact Study,Psychology,Under Review,Currently under review
EP-2025-003,Environmental Assessment,Environmental Science,Pending,Waiting for documentation
```

## Status Values
- **Approved**: Protocol has been approved
- **Under Review**: Protocol is currently being reviewed
- **Pending**: Protocol is waiting for initial review
- **Rejected**: Protocol has been rejected

## Troubleshooting

### Common Issues
1. **File not uploading**: Check file size and format
2. **Data not importing**: Verify column headers match expected format
3. **Status not recognized**: Use exact status values (Approved, Under Review, Pending, Rejected)

### Debug Information
Click the "Debug Info" button to check:
- Session status
- PHP version and settings
- Database connection
- File upload permissions
- Required extensions

### Error Messages
- **"Invalid file format"**: Check column headers
- **"Missing required fields"**: Ensure all columns have data
- **"File too large"**: Reduce file size or split into smaller files

## Support
If you encounter issues:
1. Check the debug information
2. Verify your file format matches the template
3. Ensure all required fields are filled
4. Contact system administrator if problems persist 