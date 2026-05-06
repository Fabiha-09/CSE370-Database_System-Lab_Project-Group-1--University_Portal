-- ============================================================
--  prerequisites_migration.sql
--  Run once against the `university_portal` database.
--  Safe to re-run: uses IF NOT EXISTS / INSERT IGNORE / etc.
-- ============================================================

-- ============================================================
-- Step 1: Add prereq_type column to Prerequisite table
-- ============================================================

ALTER TABLE `Prerequisite`
  ADD COLUMN IF NOT EXISTS `prereq_type` ENUM('HP','SP') NOT NULL DEFAULT 'HP';

-- ============================================================
-- Step 2: Add 6 missing courses to Course table
-- ============================================================

INSERT IGNORE INTO `Course`
  (`course_code`, `Semester_id`, `credit_hours`, `Enrollment_date`, `max_capacity`,
   `Start_date`, `End_date`, `Max_credits`, `dept_id`, `room_no`)
VALUES
  ('CSE250', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-04C'),
  ('CSE331', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-02C'),
  ('CSE471', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '09A-01C'),
  ('ENG091', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '09A-01C'),
  ('ENG101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '09A-02C'),
  ('ENG102', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '08A-05C');

-- INSERT IGNORE may null FK columns in some MariaDB configs; patch room_no
UPDATE `Course` SET `room_no` = '09A-01C' WHERE `course_code` = 'ENG091' AND `room_no` IS NULL;
UPDATE `Course` SET `room_no` = '09A-02C' WHERE `course_code` = 'ENG101' AND `room_no` IS NULL;
UPDATE `Course` SET `room_no` = '08A-05C' WHERE `course_code` = 'ENG102' AND `room_no` IS NULL;

-- ============================================================
-- Step 3: DELETE incorrect prerequisite rows
-- ============================================================

DELETE FROM `Prerequisite` WHERE
    (`course_code` = 'CSE330' AND `prereq_course_code` = 'CSE320')   -- was wrong; now needs MAT216
 OR (`course_code` = 'CSE420' AND `prereq_course_code` = 'CSE221')   -- was wrong; needs CSE321+CSE331+CSE340
 OR (`course_code` = 'CSE422' AND `prereq_course_code` = 'CSE420')   -- was wrong; needs CSE221
 OR (`course_code` = 'CSE423' AND `prereq_course_code` = 'CSE420')   -- was wrong; needs MAT216
 OR (`course_code` = 'MAT120' AND `prereq_course_code` = 'MAT101')   -- was wrong; needs MAT110
 OR (`course_code` = 'CSE470' AND `prereq_course_code` = 'CSE340');  -- was wrong; needs CSE370

-- ============================================================
-- Step 4: INSERT correct and new HP prerequisites
-- ============================================================

INSERT IGNORE INTO `Prerequisite` (`course_code`, `prereq_course_code`, `prereq_type`) VALUES
-- Program Core (White Boxes) - HP Chain
('CSE220', 'CSE230', 'HP'),   -- CSE230 alongside CSE111 for CSE220
('CSE331', 'CSE221', 'HP'),   -- NEW
('CSE422', 'CSE221', 'HP'),   -- CORRECTED
('CSE420', 'CSE321', 'HP'),   -- all 3 needed for CSE420
('CSE420', 'CSE331', 'HP'),
('CSE420', 'CSE340', 'HP'),
('CSE470', 'CSE370', 'HP'),   -- CORRECTED
('CSE471', 'CSE370', 'HP'),   -- NEW
-- Math Core (Blue Boxes) - HP Chain
('MAT120', 'MAT110', 'HP'),   -- CORRECTED
('MAT216', 'MAT120', 'HP'),   -- NEW
('CSE330', 'MAT216', 'HP'),   -- CORRECTED
('MAT215', 'MAT216', 'HP'),   -- NEW
('CSE423', 'MAT216', 'HP'),   -- CORRECTED
-- English & GenEd (Yellow Boxes) - HP Chain
('ENG101', 'ENG091', 'HP'),   -- NEW
('ENG102', 'ENG101', 'HP'),   -- NEW
('CSE250', 'ENG102', 'HP'),   -- NEW
('CSE250', 'PHY112', 'HP');   -- NEW (HP component; see also Soft_Prerequisite)

-- ============================================================
-- Step 5: Create Soft_Prerequisite table
-- ============================================================

CREATE TABLE IF NOT EXISTS `Soft_Prerequisite` (
  `course_code`    varchar(10) NOT NULL,
  `sp_course_code` varchar(10) NOT NULL,
  `note`           varchar(255) DEFAULT NULL,
  PRIMARY KEY (`course_code`, `sp_course_code`),
  KEY `sp_course_code` (`sp_course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Step 6: INSERT the one SP record
-- ============================================================

INSERT IGNORE INTO `Soft_Prerequisite` (`course_code`, `sp_course_code`, `note`) VALUES
('CSE250', 'PHY112', 'Recommended background: PHY112 — Physics for Computer Scientists');
