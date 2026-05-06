-- Migration: add email columns to patient/staff/student tables
-- Run this once in your MySQL database (adjust table names if needed)

ALTER TABLE `staff` ADD COLUMN `email` VARCHAR(120) NULL AFTER `phone`;
ALTER TABLE `student` ADD COLUMN `email` VARCHAR(120) NULL AFTER `phone`;
ALTER TABLE `individual` ADD COLUMN `email` VARCHAR(120) NULL AFTER `phone`;

-- Optional: verify columns
-- DESCRIBE staff;
-- DESCRIBE student;
-- DESCRIBE individual;
