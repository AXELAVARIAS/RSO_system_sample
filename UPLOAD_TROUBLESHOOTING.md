# Upload Troubleshooting Guide

## Quick Fixes

### 1. Check File Format
- **Supported formats**: `.xls`, `.xlsx`, `.csv`
- **Required columns** (case-insensitive):
  - Protocol Number
  - Research Title
  - Department
  - Status
  - Action Taken
- **File size**: Maximum 10MB

### 2. Template Format
Your CSV/Excel file should look like this:
```csv
Protocol Number,Research Title,Department,Status,Action Taken
EP-2025-001,Study on Student Learning Patterns,Computer Science,Approved,Protocol approved with minor revisions
EP-2025-002,Faculty Development Research,Education,Under Review,Under committee review
```

### 3. Common Issues

#### No Success/Error Message
1. **Check browser console** (F12 → Console tab)
2. **Click "Debug Info" button** in upload modal
3. **Check file format** matches requirements

#### "Invalid file format" Error
- Ensure column headers match exactly (spaces, spelling)
- Check for hidden characters or BOM in CSV files
- Verify file is not corrupted

#### "No data found" Error
- Ensure file has at least 2 rows (header + data)
- Check for empty rows at the beginning
- Verify file encoding (UTF-8 recommended)

#### "Database error" Message
- Check database connection
- Verify table `ethics_reviewed_protocols` exists
- Check user permissions

## Debug Tools

### 1. Debug Info Button
Click the "Debug Info" button in the upload modal to see:
- Session status
- Server configuration
- Database connection
- File permissions
- PHP extensions

### 2. Test Script
Visit `php/test_upload_handler.php` to test the upload handler directly.

### 3. Browser Console
Open F12 → Console to see detailed error messages and request/response data.

## Server Requirements

### PHP Extensions
- `zip` - For Excel file processing
- `json` - For API responses
- `mbstring` - For string handling
- `fileinfo` - For file type detection

### File Permissions
- Upload directory must be writable
- Temporary directory must be writable

### PHP Settings
- `upload_max_filesize` ≥ 10M
- `post_max_size` ≥ 10M
- `memory_limit` ≥ 128M
- `max_execution_time` ≥ 30s

## Testing Steps

1. **Use the test template**: `test_ethics_template.csv`
2. **Check debug info**: Click "Debug Info" button
3. **Monitor console**: Watch for error messages
4. **Verify response**: Check network tab for response details

## Getting Help

If issues persist:
1. Check browser console for errors
2. Use debug tools to identify the problem
3. Verify file format matches requirements
4. Check server logs for PHP errors
5. Ensure all server requirements are met 