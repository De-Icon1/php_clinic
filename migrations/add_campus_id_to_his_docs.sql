-- Add campus_id column to his_docs to associate staff with a campus/location
ALTER TABLE his_docs
ADD COLUMN campus_id INT NULL,
ADD INDEX idx_his_docs_campus_id (campus_id);

-- Optionally: add a foreign key constraint if campus_locations.id is primary key
-- ALTER TABLE his_docs
-- ADD CONSTRAINT fk_his_docs_campus FOREIGN KEY (campus_id) REFERENCES campus_locations(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- NOTE: After running this migration, you may want to populate campus_id values for existing his_docs rows.
-- Example to set campus_id based on a known mapping of working_location name stored elsewhere:
-- UPDATE his_docs h
-- JOIN campus_locations c ON c.name = h.working_location_name
-- SET h.campus_id = c.id
-- WHERE h.campus_id IS NULL;
