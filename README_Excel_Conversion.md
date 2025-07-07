# Excel to CSV Conversion Feature

## Overview

The Research Capacity Building Activities system now includes automatic Excel to CSV conversion functionality. This allows users to upload Excel files (.xls, .xlsx) directly, and the system will automatically convert them to CSV format for processing.

## How It Works

### 1. File Upload Process
- Users can upload Excel files (.xls, .xlsx) or CSV files directly
- The system detects the file type automatically
- Excel files are converted to CSV format internally
- CSV files are processed directly

### 2. Excel File Processing
- **XLSX files**: Uses ZIP archive extraction to read the XML-based format
- **XLS files**: Extracts readable text content from the binary format
- Both formats are converted to a standardized CSV format for processing

### 3. Data Validation
- Headers are mapped flexibly to accommodate variations in column names
- Required fields are validated before database insertion
- Date formats are automatically converted to YYYY-MM-DD format
- Status values are normalized to standard options

## Supported File Formats

### Input Formats
- **CSV** (.csv) - Processed directly
- **Excel 2007+** (.xlsx) - Automatically converted to CSV
- **Excel 97-2003** (.xls) - Automatically converted to CSV

### Required Headers
The system expects these headers (case-insensitive, with flexible naming):
- `Date` - Activity date
- `Activity Name` or `Activity` - Title of the activity
- `Venue` or `Location` - Where the activity takes place
- `Facilitators` or `Organizer` or `Organizers` - Who is running the activity
- `Number of Participants` or `Participants` or `Participant Count` - Number of attendees
- `Status` or `Activity Report Status` - Current status

## Template Files

### CSV Template
- Location: `templates/research_capacity_template.csv`
- Contains sample data with correct headers
- Can be opened in any text editor or spreadsheet application

### Excel Template
- Location: `templates/research_capacity_template.xlsx`
- Contains sample data with correct headers
- Can be opened in Excel, Google Sheets, or similar applications

## Technical Implementation

### Key Functions

#### `convertExcelToCSV($file_path)`
- Converts Excel files to data arrays
- Handles both XLSX and XLS formats
- Extracts data from the first worksheet

#### `createCSVContent($data)`
- Converts data arrays to CSV format
- Properly escapes quotes and special characters
- Handles commas and newlines in data

#### `readCSVContent($csv_content)`
- Parses CSV content back to data arrays
- Handles quoted fields and escaped characters
- Maintains data integrity

### Error Handling
- Validates file types before processing
- Checks for required headers
- Validates data formats (dates, numbers)
- Provides detailed error messages for troubleshooting

## Usage Instructions

### For Users
1. Download the appropriate template (CSV or Excel)
2. Fill in your data following the template format
3. Upload the file through the web interface
4. The system will automatically process and import your data

### For Developers
1. The conversion functions are in `php/upload_excel.php`
2. Test the conversion using `php/test_excel_conversion.php`
3. Templates are stored in the `templates/` directory

## Benefits

### For Users
- **Easier Upload**: No need to manually convert Excel files to CSV
- **Flexible Format**: Supports both Excel and CSV formats
- **Better Templates**: Excel templates are more user-friendly
- **Error Prevention**: Automatic format conversion reduces upload errors

### For System
- **Consistent Processing**: All files are processed as CSV internally
- **Better Compatibility**: Handles various Excel formats and versions
- **Data Integrity**: Proper escaping and parsing maintains data quality
- **Scalable**: Easy to extend for other file formats

## Troubleshooting

### Common Issues
1. **"No data found" error**: Ensure the Excel file has data in the first worksheet
2. **"Missing headers" error**: Check that your headers match the required format
3. **"Invalid date format" error**: Use YYYY-MM-DD format for dates

### Testing
- Use the test script: `php/test_excel_conversion.php`
- Check error logs for detailed conversion information
- Verify template files are accessible

## Future Enhancements

### Potential Improvements
- Support for multiple worksheets
- Better handling of complex Excel formatting
- Support for other spreadsheet formats (Google Sheets, LibreOffice)
- Real-time conversion preview
- Batch processing for multiple files

### Performance Optimizations
- Streaming processing for large files
- Caching of converted data
- Parallel processing for multiple uploads 