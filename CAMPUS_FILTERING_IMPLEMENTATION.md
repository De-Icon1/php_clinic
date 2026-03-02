# Campus Filtering Implementation - Security & Multi-Location Isolation

## Overview
Campus-based patient isolation is now **enforced** across the application. Patients from one campus are completely isolated from other campuses. Staff can only view, access, and manage patients from their assigned campus location.

## Implementation Strategy

### 1. Database Design
- **sendsignal table**: Contains `campus_id` column (added via migration)
- **his_docs table**: Contains `campus_id` column (associates staff with campus)
- **Session**: `$_SESSION['working_location_id']` set during login in index.php
- **Fallback logic**: If campus_id column doesn't exist, queries run unfiltered (backward compatibility)

### 2. Campus Filtering Logic (Used in All Pages)

```php
// Check if sendsignal table has campus_id column
$hascamp = 0;
$resCol = $mysqli->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id'");
if ($resCol) {
    $rowCol = $resCol->fetch_assoc();
    $hascamp = isset($rowCol['cnt']) ? (int)$rowCol['cnt'] : 0;
}

// Get user's campus from session
$campus_id = isset($_SESSION['working_location_id']) ? (int)$_SESSION['working_location_id'] : null;

// Apply filtering based on campus availability and user's campus
if ($hascamp && $campus_id) {
    // Campus filtering ENABLED: Filter by user's campus_id
    $query = "SELECT * FROM sendsignal WHERE campus_id = ? ...";
} else if ($hascamp && !$campus_id) {
    // Campus column exists but user has no campus - DENY ACCESS (safety measure)
    $query = "SELECT * FROM sendsignal WHERE 1=0";
} else {
    // Campus filtering DISABLED: Show all records (backward compatibility)
    $query = "SELECT * FROM sendsignal WHERE ...";
}
```

## Files with Campus Filtering Implemented

### 1. **his_admin_todays_visitn.php** (Nursing Patient Queue)
- **Location**: Nursing dashboard - lists today's patients awaiting vital signs
- **Filtering**: `campus_id` must match `$_SESSION['working_location_id']`
- **Protection**: Only shows patients from nurse's campus
- **Status Filter**: Added dropdown for "Pending", "Checked", "All" within campus context
- **Code**: Lines 157-189

### 2. **his_admin_todays_visit.php** (Patient Queue - General)
- **Location**: Patient queue view
- **Filtering**: `campus_id` must match user's working location
- **Protection**: Filters by date + campus_id
- **Code**: Lines 155-173

### 3. **his_admin_individual_patient.php** (Individual Patient List)
- **Location**: Admin dashboard - Individual patients
- **Filtering**: Uses INNER JOIN with sendsignal table; only shows individuals who have records in user's campus
- **Query**: `SELECT DISTINCT i.* FROM individual i INNER JOIN sendsignal s ON i.code = s.pat_code WHERE s.campus_id = ?`
- **Protection**: Staff can only see patients they've interacted with (have sendsignal records)
- **Code**: Lines 254-278

### 4. **his_admin_searchrecord.php** (Patient Search)
- **Location**: Records section - Search/find patients
- **Filtering**: Before redirecting to sendsignals, verifies patient has a record in this campus
- **Query**: `SELECT id FROM sendsignal WHERE pat_code = ? AND campus_id = ? LIMIT 1`
- **Protection**: Prevents searching/accessing patients from other campuses
- **Behavior**: Shows alert "Patient record not found in your campus. Access denied." if not found
- **Code**: Lines 8-42

### 5. **his_admin_sendsignals.php** (Patient Signal Processing)
- **Location**: Entry point when sending patient signals/records
- **Filtering**: Validates patient access by campus before processing
- **Query**: `SELECT id FROM sendsignal WHERE pat_code = ? AND campus_id = ? LIMIT 1`
- **Protection**: Denies patient access if they don't exist in campus
- **Behavior**: Dies with alert if access denied
- **Code**: Lines 5-31

### 6. **doc/his_doc_view_single_patient.php** (Doctor Patient View)
- **Location**: Doctor dashboard - View patient details
- **Filtering**: Patient query includes campus_id validation
- **Query**: `SELECT * FROM sendsignal WHERE pat_code=? AND campus_id=?`
- **Protection**: Doctors only see patients from their campus
- **Code**: Lines 548-572

## Security Guarantees

✅ **Cross-Campus Isolation**: Patients from Campus A are invisible to staff at Campus B
✅ **Access Denial**: Any attempt to access patient from wrong campus triggers alert and redirect
✅ **Backward Compatibility**: If campus_id column doesn't exist, system falls back to unfiltered mode
✅ **Safety Fallback**: If campus column exists but user has no campus assignment, system shows NO patients (safer than showing all)
✅ **Consistent Logic**: Same filtering pattern used across all patient-facing pages

## Configuration Requirements

### Prerequisites (One-Time Setup)
1. Ensure `campus_locations` table exists with id, name, location_name columns
2. Run migration to add `campus_id` column to sendsignal table:
   ```sql
   ALTER TABLE sendsignal ADD COLUMN campus_id INT NULL DEFAULT NULL AFTER id;
   ```
3. Run migration to add `campus_id` column to his_docs table:
   ```sql
   ALTER TABLE his_docs ADD COLUMN campus_id INT NULL;
   ```
4. Populate `his_docs.campus_id` for all staff members (assign each staff to their campus)

### Runtime Requirements
- **Login Process** (index.php): Must set `$_SESSION['working_location_id']` from his_docs.campus_id during login
- **Session**: Must start in config.php before any database queries

## Testing Campus Isolation

### Test Scenario 1: Patient Search Across Campuses
1. Login as staff from Campus A
2. Try to search for a patient code from Campus B
3. **Expected**: Alert "Patient record not found in your campus. Access denied."

### Test Scenario 2: Patient Queue View
1. Login as staff from Campus A (set campus_id = 1)
2. Go to nursing patient queue
3. **Expected**: Only patients with sendsignal.campus_id = 1 appear
4. Login as staff from Campus B (set campus_id = 2)
5. **Expected**: Different set of patients with sendsignal.campus_id = 2

### Test Scenario 3: Individual Patient List
1. Login as admin from Campus A
2. Go to Individual Patient list
3. **Expected**: Only individuals who have sendsignal records in Campus A
4. **Note**: Newly created individuals without sendsignal records won't appear until they're assigned to a visit

## Debugging & Monitoring

### Verify Campus Assignment
```sql
-- Check if staff has campus_id assigned
SELECT doc_number, doc_name, campus_id FROM his_docs WHERE doc_id = ?;

-- Check all patients in a campus
SELECT DISTINCT pat_code, Fullname, campus_id FROM sendsignal WHERE campus_id = ? ORDER BY Date DESC;
```

### Check Session Values
In any PHP page, verify session is set:
```php
echo "Working Location ID: " . isset($_SESSION['working_location_id']) ? $_SESSION['working_location_id'] : "NOT SET";
echo "Doctor ID: " . $_SESSION['doc_id'];
```

### Verify Campus Column Exists
```sql
-- Check if campus_id column exists in sendsignal
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='sendsignal' AND COLUMN_NAME='campus_id';
```

## Important Notes

1. **Individual Table**: Does NOT have campus_id column. Patient lists filter through sendsignal JOIN instead.
2. **Other Tables**: Family_individual, student, staff, antenatal, HMO tables also don't have campus_id. Same INNER JOIN pattern can be applied if needed.
3. **Backward Compatibility**: System checks if campus_id column exists before using it. If not present, queries run unfiltered.
4. **Session Dependency**: All filtering depends on `$_SESSION['working_location_id']` being set correctly during login.

## Future Enhancements

- Add campus_id directly to individual, family_individual, and other patient tables (denormalization) for better performance
- Create database views for filtered patient lists
- Add campus_id logging to audit trail
- Implement campus isolation in pharmacy and store inventory tables
- Add campus filter UI to dashboards for admin users (multi-campus admins)

---
**Last Updated**: March 2, 2026
**Status**: ✅ IMPLEMENTED - All critical patient-facing pages enforce campus isolation
