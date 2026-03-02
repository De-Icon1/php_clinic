# Server Migration Guide: Campus Filtering Setup

## Problem
The patient queue fix works on localhost but not on the server because the **server database hasn't been migrated** to add the required `campus_id` columns.

## Solution: 3-Step Server Setup

### Step 1: Run Database Migrations ⚙️

**Option A: Using cPanel phpMyAdmin (Easiest)**
1. Login to your Plesk/cPanel control panel
2. Open **phpMyAdmin** → Select your **Hospital** database
3. Click the **SQL** tab
4. Copy and paste the entire contents of `SERVER_MIGRATION.sql`
5. Click **Go** to execute

**Option B: Using MySQL CLI**
```bash
mysql -u root -p Hospital < SERVER_MIGRATION.sql
```

**Option C: Manual SQL (if you can't copy/paste the whole file)**
```sql
-- Create campus_locations table
CREATE TABLE IF NOT EXISTS campus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    location_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add campus_id to his_docs (staff table)
ALTER TABLE his_docs ADD COLUMN campus_id INT NULL;

-- Add campus_id to sendsignal (patient queue)
ALTER TABLE sendsignal ADD COLUMN campus_id INT NULL DEFAULT NULL AFTER id;

-- Add sample campus locations
INSERT IGNORE INTO campus_locations (name, location_name) VALUES
('1', 'Main Campus'),
('2', 'Mini Campus');
```

### Step 2: Assign Staff to Campuses 👥

**CRITICAL:** Your staff need to be assigned to campuses for the system to work!

Run this SQL (customize the doc_id values for your setup):
```sql
-- Example: Assign doctors to Main Campus (campus_id = 1)
UPDATE his_docs SET campus_id = 1 WHERE doc_number IN ('DOC001', 'DOC002', 'DOC003');

-- Example: Assign doctors to Mini Campus (campus_id = 2)
UPDATE his_docs SET campus_id = 2 WHERE doc_number IN ('DOC004', 'DOC005');
```

**To find your staff member IDs:**
```sql
SELECT doc_id, doc_number, doc_name, doc_dept FROM his_docs LIMIT 20;
```

Then use their `doc_id` or `doc_number` in the UPDATE statement.

### Step 3: Verify Setup ✅

**Visit this verification page on your server:**
```
https://yourserver.com/clinic/server_migration_check.php
```

This page will show:
- ✅ Whether both columns exist
- ✅ Whether campus_locations table exists  
- ✅ Sample data from your tables
- ✅ Whether staff have campus_id assigned

**All items should show GREEN ✅**

## Testing the Fix

1. **Login as nursing staff** from Main Campus
   - Should have `campus_id = 1` in his_docs table
2. **Go to Records section** → Search for a patient
3. **Click "Send Signal"** to queue the patient
4. **Go to Nursing Dashboard** → "Today's Patient Visit"
5. **Patient should appear** in the nursing queue

## Troubleshooting

### Patients Still Not Showing?

**Check 1: Verify columns were created**
```sql
-- Show all columns in sendsignal
SHOW COLUMNS FROM sendsignal;

-- Show all columns in his_docs  
SHOW COLUMNS FROM his_docs;
```

Both should have a `campus_id` column visible.

**Check 2: Verify staff have campus assigned**
```sql
SELECT doc_id, doc_number, doc_name, campus_id FROM his_docs WHERE campus_id IS NOT NULL;
```

Should show at least one row with campus_id = 1, 2, etc.

**Check 3: Verify sendsignal records are being created**
```sql
SELECT id, pat_code, Fullname, Date, status, campus_id FROM sendsignal WHERE Date = CURDATE() ORDER BY id DESC LIMIT 10;
```

Should show records with `campus_id` populated when inserted from that staff's location.

**Check 4: Verify session is set correctly**
- Add this to `his_admin_todays_visitn.php` temporarily at the top:
```php
echo "Debug: working_location_id = " . (isset($_SESSION['working_location_id']) ? $_SESSION['working_location_id'] : 'NOT SET');
echo " | doc_id = " . (isset($_SESSION['doc_id']) ? $_SESSION['doc_id'] : 'NOT SET');
```
- After testing, remove this debug code

## Campus Structure

The system now works as follows:

| User's Campus | Can See Patients From | Cannot See |
|---------------|-----------------------|------------|
| Main Campus (1) | Main Campus patients | Mini Campus patients |
| Mini Campus (2) | Mini Campus patients | Main Campus patients |
| Not Assigned (NULL) | Unassigned patients (NULL) | Any assigned patients |

## Next Steps

1. ✅ Run the migration SQL on server
2. ✅ Assign your staff to campuses
3. ✅ Visit `server_migration_check.php` to verify
4. ✅ Test by sending a patient and checking the nursing queue
5. ✅ Remove `server_migration_check.php` from production when done (optional)

## Important Notes

- The migration is **safe** - it only adds new columns, doesn't delete anything
- Existing sendsignal records will have `campus_id = NULL` - this is fine
- Staff without a campus assigned will see `campus_id = NULL` patients
- Once all staff have campus assigned, those patients won't be visible

---
**Support**: If issues persist after following this guide, check the error logs on your Plesk server.
