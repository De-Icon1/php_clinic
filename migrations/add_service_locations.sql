-- Migration: add_service_locations.sql
-- Inserts core service locations (Doctor, Nursing, Laboratory) into campus_locations if missing

INSERT INTO campus_locations (name)
SELECT 'Doctor' WHERE NOT EXISTS (SELECT 1 FROM campus_locations WHERE name = 'Doctor');

INSERT INTO campus_locations (name)
SELECT 'Nursing' WHERE NOT EXISTS (SELECT 1 FROM campus_locations WHERE name = 'Nursing');

INSERT INTO campus_locations (name)
SELECT 'Laboratory' WHERE NOT EXISTS (SELECT 1 FROM campus_locations WHERE name = 'Laboratory');
