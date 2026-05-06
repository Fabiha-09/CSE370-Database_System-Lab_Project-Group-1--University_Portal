-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 05, 2026 at 05:18 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `university_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `AcademicRoutine`
--

CREATE TABLE `AcademicRoutine` (
    `Student_id` varchar(10) NOT NULL,
    `Courses` varchar(100) NOT NULL,
    `Exam_date` date DEFAULT NULL,
    `Exam_routine` text DEFAULT NULL,
    `Attribute` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AcademicRoutine`
--

INSERT INTO `AcademicRoutine` (`Student_id`, `Courses`, `Exam_date`, `Exam_routine`, `Attribute`) VALUES
('21201001', 'CSE370', '2026-04-04', 'Section 1  | 4:30-6:00 PM | 08A-03C', 'Midterm'),
('21201001', 'CSE420', '2026-04-02', 'Section 1  | 2:00-3:30 PM | 07A-05C', 'Midterm'),
('22301002', 'CSE221', '2026-03-31', 'Section 1  | 8:30-10:00 AM | 07A-03C', 'Midterm'),
('22301002', 'CSE370', '2026-04-04', 'Section 15 | 4:30-6:00 PM | 08A-04C', 'Midterm'),
('22301002', 'MAT120', '2026-04-06', 'Section 1  | 2:00-3:30 PM | 08A-02C', 'Midterm'),
('22301003', 'CSE370', '2026-04-04', 'Section 15 | 4:30-6:00 PM | 08A-04C', 'Midterm'),
('24101352', 'CSE221', '2026-03-31', 'Section 1  | 8:30-10:00 AM | 07A-03C', 'Midterm'),
('24101352', 'CSE370', '2026-04-04', 'Section 15 | 4:30-6:00 PM | 08A-04C', 'Midterm'),
('24101352', 'MAT120', '2026-04-06', 'Section 1  | 2:00-3:30 PM | 08A-02C', 'Midterm');

-- --------------------------------------------------------

--
-- Table structure for table `AnalyticsDashboard`
--

CREATE TABLE `AnalyticsDashboard` (
    `Course_Section` varchar(50) NOT NULL,
    `Highest_Grade` varchar(5) DEFAULT NULL,
    `Class_performance` varchar(100) DEFAULT NULL,
    `Lowest_Grade` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AnalyticsDashboard`
--

INSERT INTO `AnalyticsDashboard` (`Course_Section`, `Highest_Grade`, `Class_performance`, `Lowest_Grade`) VALUES
('BUS102-SP26', 'A', 'Excellent — High engagement', 'B+'),
('CSE221-SP26', 'A', 'Good — Strong algorithmic performance', 'B+'),
('CSE370-SP26', 'A', 'Good — Most students above B+', 'C'),
('CSE420-SP26', 'B+', 'Good — Active participation', 'B'),
('MAT120-SP26', 'A-', 'Average — Some students struggling', 'C+');

-- --------------------------------------------------------

--
-- Table structure for table `Classroom`
--

CREATE TABLE `Classroom` (
    `room_no` varchar(20) NOT NULL,
    `capacity` int(11) NOT NULL,
    `projector` tinyint(1) DEFAULT 0,
    `pcs` int(11) DEFAULT 0,
    `Equipment_List` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Classroom`
--

INSERT INTO `Classroom` (`room_no`, `capacity`, `projector`, `pcs`, `Equipment_List`) VALUES
('07A-01C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('07A-02C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('07A-03C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('07A-04C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('07A-05C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('08A-01C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('08A-02C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('08A-03C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('08A-04C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('08A-05C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('09A-01C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('09A-02C', 40, 1, 0, 'Projector,Whiteboard,AC'),
('09B-10L', 30, 0, 30, '30 PCs,AC'),
('09C-16T', 120, 1, 0, 'Projector,Mic,AC'),
('09E-20T', 120, 1, 0, 'Projector,Mic,AC'),
('10A-04C', 40, 1, 0, 'Projector,AC'),
('10B-12C', 40, 1, 0, 'Projector,AC'),
('10D-23C', 40, 1, 0, 'Projector,AC'),
('10E-25L', 30, 0, 30, '30 PCs,AC'),
('10E-26L', 30, 0, 30, '30 PCs,AC'),
('12A-01L', 30, 0, 30, '30 PCs,AC'),
('12A-02L', 30, 0, 30, '30 PCs,AC'),
('12A-07C', 40, 1, 0, 'Projector,AC'),
('12A-08C', 40, 1, 0, 'Projector,AC'),
('12A-09C', 40, 1, 0, 'Projector,AC');

-- --------------------------------------------------------

--
-- Table structure for table `Classroom_Lab_Room`
--

CREATE TABLE `Classroom_Lab_Room` (
    `lab_room` varchar(50) NOT NULL,
    `room_no` varchar(20) DEFAULT NULL,
    `Occupied_Slots` int(11) DEFAULT 0,
    `Free_Slots` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Classroom_Lab_Room`
--

INSERT INTO `Classroom_Lab_Room` (`lab_room`, `room_no`, `Occupied_Slots`, `Free_Slots`) VALUES
('BBS Finance Lab', '10E-26L', 1, 29),
('BBS MIS Lab', '10E-25L', 2, 28),
('CSE DB Lab', '12A-01L', 2, 28),
('CSE Network Lab', '12A-02L', 1, 29),
('CSE Programming Lab', '09B-10L', 3, 27);

-- --------------------------------------------------------

--
-- Table structure for table `Classroom_Theory_Room`
--

CREATE TABLE `Classroom_Theory_Room` (
    `theory_room` varchar(50) NOT NULL,
    `room_no` varchar(20) DEFAULT NULL,
    `Occupied_Slots` int(11) DEFAULT 0,
    `Free_Slots` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Classroom_Theory_Room`
--

INSERT INTO `Classroom_Theory_Room` (`theory_room`, `room_no`, `Occupied_Slots`, `Free_Slots`) VALUES
('Theory Room 07A-01C', '07A-01C', 10, 30),
('Theory Room 07A-02C', '07A-02C', 8, 32),
('Theory Room 07A-03C', '07A-03C', 7, 33),
('Theory Room 07A-04C', '07A-04C', 9, 31),
('Theory Room 07A-05C', '07A-05C', 10, 30),
('Theory Room 08A-01C', '08A-01C', 6, 34),
('Theory Room 08A-04C', '08A-04C', 8, 32),
('Theory Room 09A-01C', '09A-01C', 5, 35);

-- --------------------------------------------------------

--
-- Table structure for table `Clubs`
--

CREATE TABLE `Clubs` (
    `Club_Name` varchar(100) NOT NULL,
    `NO_of_members` int(11) DEFAULT 0,
    `Activities` text DEFAULT NULL,
    `Student_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Clubs`
--

INSERT INTO `Clubs` (`Club_Name`, `NO_of_members`, `Activities`, `Student_id`) VALUES
('BRACU Business Club', 50, 'Case competitions, seminars', '22101001'),
('BRACU Computer Club', 45, 'Programming contests, hackathons', '24101352'),
('BRACU Cultural Club', 55, 'Annual programs, Pohela Boishakh', '22101002'),
('BRACU Debate Club', 40, 'Debates, public speaking', '22301002'),
('BRACU Leadership Development Forum', 35, 'Leadership workshops, seminars', '21201001'),
('BRACU Photography Club', 35, 'Photo walks, exhibitions', '23301001'),
('BRACU Research for Development', 28, 'Research seminars, publications', '21201002'),
('BRACU Robotics Club', 25, 'Robot building, competitions', '22201001'),
('BRACU Science Club', 30, 'Science fairs, experiments', '22401001'),
('BRACU Sports Club', 60, 'Cricket, football, badminton', '23301002');

-- --------------------------------------------------------

--
-- Table structure for table `Complaint`
--

CREATE TABLE `Complaint` (
    `Complain_id` int(11) NOT NULL,
    `Statement` text DEFAULT NULL,
    `Issue_Type` varchar(100) DEFAULT NULL,
    `submitted_date` date DEFAULT NULL,
    `Student_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Complaint`
--

INSERT INTO `Complaint` (`Complain_id`, `Statement`, `Issue_Type`, `submitted_date`, `Student_id`) VALUES
(1, 'The AC in 08A-04C is not working.', 'Broken AC', '2026-03-10', '24101352'),
(2, 'Projector in 07A-05C shows distortion.', 'Projector not working', '2026-03-12', '22301002'),
(3, 'WiFi signal is very weak in Building 7.', 'WiFi problem', '2026-03-15', '22301003'),
(4, 'PC #12 in 09B-10L has keyboard issues.', 'Lab computer damaged', '2026-03-18', '21201001'),
(5, 'Several chairs in 07A-05C are broken.', 'Broken furniture', '2026-03-20', '21201002');

-- --------------------------------------------------------

--
-- Table structure for table `Course`
--

CREATE TABLE `Course` (
    `course_code` varchar(10) NOT NULL,
    `Semester_id` varchar(10) NOT NULL,
    `credit_hours` int(11) NOT NULL DEFAULT 3,
    `Enrollment_date` date DEFAULT NULL,
    `max_capacity` int(11) DEFAULT 40,
    `Start_date` date DEFAULT NULL,
    `End_date` date DEFAULT NULL,
    `Max_credits` int(11) DEFAULT 15,
    `dept_id` varchar(10) DEFAULT NULL,
    `room_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Course`
--

INSERT INTO `Course` (`course_code`, `Semester_id`, `credit_hours`, `Enrollment_date`, `max_capacity`, `Start_date`, `End_date`, `Max_credits`, `dept_id`, `room_no`) VALUES
('ACT201', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '09C-16T'),
('BIO101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '10A-04C'),
('BUS102', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '07A-01C'),
('BUS201', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '07A-01C'),
('CHE101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '09A-02C'),
('CSE101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-01C'),
('CSE110', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-03C'),
('CSE111', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-02C'),
('CSE220', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-01C'),
('CSE221', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-03C'),
('CSE230', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-01C'),
('CSE250', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-04C'),
('CSE320', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-01C'),
('CSE321', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-04C'),
('CSE330', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-01C'),
('CSE331', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-02C'),
('CSE340', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-05C'),
('CSE360', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-02C'),
('CSE370', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-03C'),
('CSE420', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-05C'),
('CSE421', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '09A-02C'),
('CSE422', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '07A-02C'),
('CSE423', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-04C'),
('CSE440', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-05C'),
('CSE460', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-01C'),
('CSE470', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '08A-02C'),
('CSE471', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'CSE', '09A-01C'),
('ECO101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ESS', '09C-16T'),
('ECO102', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ESS', '07A-04C'),
('ENG091', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '09A-01C'),
('ENG101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '09A-02C'),
('ENG102', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '08A-05C'),
('ENG111', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '08A-05C'),
('ENG115', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ENH', '09A-02C'),
('FIN201', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '07A-02C'),
('MAT101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '07A-01C'),
('MAT110', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '07A-02C'),
('MAT120', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '08A-02C'),
('MAT215', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '09A-02C'),
('MAT216', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '08A-01C'),
('MGT213', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '07A-03C'),
('MKT201', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'BBS', '07A-03C'),
('PHY111', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '07A-01C'),
('PHY112', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '07A-04C'),
('PSY101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'GenEd', '08A-03C'),
('SOC101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'ESS', '07A-03C'),
('STA101', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '09A-01C'),
('STA201', 'SP26', 3, '2026-01-15', 40, '2026-01-10', '2026-04-30', 15, 'MNS', '12A-09C');

-- --------------------------------------------------------

--
-- Table structure for table `Department`
--

CREATE TABLE `Department` (
    `dept_id` varchar(10) NOT NULL,
    `dept_name` varchar(100) NOT NULL,
    `total_no_students` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Department`
--

INSERT INTO `Department` (`dept_id`, `dept_name`, `total_no_students`) VALUES
('BBS', 'Business & Economics', 5),
('CSE', 'Computer Science & Engineering', 10),
('EEE', 'Electrical & Electronic Engineering', 5),
('ENH', 'English & Humanities', 2),
('ESS', 'Economics & Social Sciences', 2),
('GenEd', 'General Education', 2),
('MNS', 'Mathematics & Natural Sciences', 4);

-- --------------------------------------------------------

--
-- Table structure for table `Enrolled_In`
--

CREATE TABLE `Enrolled_In` (
    `Student_id` varchar(10) NOT NULL,
    `course_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Enrolled_In`
--

INSERT INTO `Enrolled_In` (`Student_id`, `course_code`) VALUES
('21201001', 'CSE370'),
('21201001', 'CSE420'),
('21201002', 'CSE340'),
('21201002', 'CSE370'),
('21201003', 'CSE320'),
('21201003', 'CSE330'),
('22101001', 'BUS102'),
('22101001', 'MGT213'),
('22101002', 'BUS102'),
('22201001', 'PHY111'),
('22201002', 'PHY111'),
('22301002', 'CSE221'),
('22301002', 'CSE370'),
('22301002', 'MAT120'),
('22301003', 'CSE330'),
('22301003', 'CSE370'),
('22401001', 'MAT215'),
('22401001', 'STA101'),
('23301001', 'CSE110'),
('23301001', 'MAT101'),
('23301002', 'CSE110'),
('24101352', 'CSE321'),
('24101352', 'CSE330'),
('24101352', 'CSE340');

-- --------------------------------------------------------

--
-- Table structure for table `Exam`
--

CREATE TABLE `Exam` (
    `Exam_id` int(11) NOT NULL,
    `Exam_type` varchar(20) NOT NULL,
    `Room_number` varchar(20) DEFAULT NULL,
    `Start_time` time DEFAULT NULL,
    `Exam_date` date DEFAULT NULL,
    `Student_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Exam`
--

INSERT INTO `Exam` (`Exam_id`, `Exam_type`, `Room_number`, `Start_time`, `Exam_date`, `Student_id`) VALUES
(1, 'Midterm', '08A-03C', '16:30:00', '2026-04-04', '24101352'),
(2, 'Midterm', '08A-04C', '16:30:00', '2026-04-04', '22301002'),
(3, 'Midterm', '08A-04C', '16:30:00', '2026-04-04', '22301003'),
(4, 'Midterm', '08A-03C', '16:30:00', '2026-04-04', '21201001'),
(5, 'Midterm', '08A-03C', '16:30:00', '2026-04-04', '21201002'),
(6, 'Midterm', '07A-03C', '08:30:00', '2026-03-31', '24101352'),
(7, 'Midterm', '07A-03C', '08:30:00', '2026-03-31', '22301002'),
(8, 'Midterm', '08A-02C', '14:00:00', '2026-04-06', '24101352'),
(9, 'Midterm', '08A-02C', '14:00:00', '2026-04-06', '22301002'),
(10, 'Final', '08A-04C', '09:00:00', '2026-06-15', '24101352'),
(11, 'Final', '08A-04C', '09:00:00', '2026-06-15', '22301002'),
(12, 'Final', '08A-04C', '09:00:00', '2026-06-15', '22301003');

-- --------------------------------------------------------

--
-- Table structure for table `Faculty`
--

CREATE TABLE `Faculty` (
    `faculty_id` varchar(10) NOT NULL,
    `faculty_type` varchar(30) DEFAULT NULL,
    `specialization` varchar(100) DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `dept_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Faculty`
--

INSERT INTO `Faculty` (`faculty_id`, `faculty_type`, `specialization`, `name`, `email`, `dept_id`) VALUES
('F001', 'Professor', 'Database Systems', 'Sayeedur Rahman', 'sayeedur@bracu.ac.bd', 'CSE'),
('F002', 'Assoc Prof', 'Algorithms', 'Amina Khatun', 'amina@bracu.ac.bd', 'CSE'),
('F003', 'Asst Prof', 'Programming', 'Tanvir Hossain', 'tanvir@bracu.ac.bd', 'CSE'),
('F004', 'Asst Prof', 'Software Engineering', 'Nadia Islam', 'nadia@bracu.ac.bd', 'CSE'),
('F005', 'Professor', 'Computer Networks', 'Kamal Uddin', 'kamal@bracu.ac.bd', 'CSE'),
('F006', 'Lecturer', 'Operating Systems', 'Raihan Ahmed', 'raihan@bracu.ac.bd', 'CSE'),
('F007', 'Professor', 'Artificial Intelligence', 'Farhan Hasan', 'farhan@bracu.ac.bd', 'CSE'),
('F008', 'Assoc Prof', 'Mathematics', 'Sumaiya Begum', 'sumaiya@bracu.ac.bd', 'MNS'),
('F009', 'Professor', 'Physics', 'Imtiaz Khan', 'imtiaz@bracu.ac.bd', 'MNS'),
('F010', 'Asst Prof', 'Business Admin', 'Rezaul Karim', 'rezaul@bracu.ac.bd', 'BBS'),
('F011', 'Professor', 'Economics', 'Shaheen Akter', 'shaheen@bracu.ac.bd', 'ESS'),
('F012', 'Lecturer', 'English Literature', 'Farhana Yasmin', 'farhana@bracu.ac.bd', 'ENH'),
('F013', 'Assoc Prof', 'Circuit Theory', 'Syed Mahmud', 'syed@bracu.ac.bd', 'EEE'),
('F014', 'Asst Prof', 'Statistics', 'Arif Billah', 'arif@bracu.ac.bd', 'MNS'),
('F015', 'Professor', 'Microbiology', 'Nasrin Sultana', 'nasrin@bracu.ac.bd', 'MNS');

-- --------------------------------------------------------

--
-- Table structure for table `Files`
--

CREATE TABLE `Files` (
    `Student_id` varchar(10) NOT NULL,
    `Complain_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Files`
--

INSERT INTO `Files` (`Student_id`, `Complain_id`) VALUES
('21201001', 4),
('21201002', 5),
('22301002', 2),
('22301003', 3),
('24101352', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Grade`
--

CREATE TABLE `Grade` (
    `Student_id` varchar(10) NOT NULL,
    `Course_code` varchar(10) NOT NULL,
    `Grade_Point` varchar(5) DEFAULT NULL,
    `Marks_obtained` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Grade`
--

INSERT INTO `Grade` (`Student_id`, `Course_code`, `Grade_Point`, `Marks_obtained`) VALUES
('21201001', 'CSE370', 'A-', 88.00),
('21201001', 'CSE420', 'B+', 74.00),
('21201002', 'CSE370', 'B+', 82.00),
('21201003', 'CSE320', 'C+', 62.00),
('22101001', 'BUS102', 'A-', 91.00),
('22201001', 'PHY111', 'B+', 76.00),
('22301002', 'CSE221', 'B+', 78.00),
('22301002', 'CSE370', 'A', 94.00),
('22301002', 'MAT120', 'A-', 85.00),
('22301003', 'CSE370', 'B', 72.00),
('22401001', 'STA101', 'A-', 89.00),
('23301001', 'CSE110', 'A', 95.00),
('24101352', 'CSE221', 'A-', 86.00),
('24101352', 'CSE370', 'A', 92.00),
('24101352', 'MAT120', 'B+', 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE `Messages` (
    `msg_id` int(11) NOT NULL,
    `sender_id` varchar(10) NOT NULL,
    `receiver_id` varchar(10) NOT NULL,
    `message_text` text NOT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Messages`
--

INSERT INTO `Messages` (`msg_id`, `sender_id`, `receiver_id`, `message_text`, `is_read`, `sent_at`) VALUES
(1, '22301002', '24101352', 'Hey! Do you have the notes for CSE370?', 1, '2026-05-05 15:06:48'),
(2, '24101352', '22301003', 'hey', 1, '2026-05-05 15:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `Note`
--

CREATE TABLE `Note` (
    `Title` varchar(200) NOT NULL,
    `Rating` decimal(3,2) DEFAULT 0.00,
    `File_Type` varchar(20) DEFAULT NULL,
    `Download` int(11) DEFAULT 0,
    `Upload_Date` date DEFAULT NULL,
    `Student_id` varchar(10) NOT NULL,
    `File_Path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Note`
--

INSERT INTO `Note` (`Title`, `Rating`, `File_Type`, `Download`, `Upload_Date`, `Student_id`, `File_Path`) VALUES
('cse331 assignment', 0.00, 'PDF', 0, '2026-05-05', '24101352', '../uploads/1777988910_cse331_assignment.pdf'),
('cse370 project', 0.00, 'PDF', 0, '2026-05-05', '22301002', '../uploads/1777990349_cse370_project.pdf'),
('My Actual PDF Upload', 0.00, 'PDF', 0, '2026-05-05', '24101352', '../uploads/1777988373_My_Actual_PDF_Upload.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `Prerequisite`
--

CREATE TABLE `Prerequisite` (
    `course_code` varchar(10) NOT NULL,
    `prereq_course_code` varchar(10) NOT NULL,
    `prereq_type` enum('HP','SP') NOT NULL DEFAULT 'HP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Prerequisite`
--

INSERT INTO `Prerequisite` (`course_code`, `prereq_course_code`, `prereq_type`) VALUES
('CSE111', 'CSE110', 'HP'),
('CSE220', 'CSE111', 'HP'),
('CSE220', 'CSE230', 'HP'),
('CSE221', 'CSE220', 'HP'),
('CSE230', 'CSE110', 'HP'),
('CSE320', 'CSE221', 'HP'),
('CSE321', 'CSE221', 'HP'),
('CSE331', 'CSE221', 'HP'),
('CSE340', 'CSE221', 'HP'),
('CSE360', 'CSE230', 'HP'),
('CSE370', 'CSE221', 'HP'),
('CSE420', 'CSE321', 'HP'),
('CSE420', 'CSE331', 'HP'),
('CSE420', 'CSE340', 'HP'),
('CSE421', 'CSE420', 'HP'),
('CSE422', 'CSE221', 'HP'),
('CSE440', 'CSE330', 'HP'),
('CSE460', 'CSE221', 'HP'),
('CSE470', 'CSE370', 'HP'),
('CSE471', 'CSE370', 'HP'),
('CSE250', 'ENG102', 'HP'),
('CSE250', 'PHY112', 'HP'),
('CSE330', 'MAT216', 'HP'),
('CSE423', 'MAT216', 'HP'),
('MAT110', 'MAT101', 'HP'),
('MAT120', 'MAT110', 'HP'),
('MAT215', 'MAT216', 'HP'),
('MAT216', 'MAT120', 'HP'),
('ENG101', 'ENG091', 'HP'),
('ENG102', 'ENG101', 'HP'),
('PHY112', 'PHY111', 'HP'),
('ECO102', 'ECO101', 'HP'),
('STA201', 'STA101', 'HP');

-- --------------------------------------------------------

--
-- Table structure for table `Soft_Prerequisite`
--

CREATE TABLE `Soft_Prerequisite` (
    `course_code` varchar(10) NOT NULL,
    `sp_course_code` varchar(10) NOT NULL,
    `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Soft_Prerequisite`
--

INSERT INTO `Soft_Prerequisite` (`course_code`, `sp_course_code`, `note`) VALUES
('CSE250', 'PHY112', 'Recommended background: PHY112 — Physics for Computer Scientists');

-- --------------------------------------------------------

--
-- Table structure for table `Reward_Points`
--

CREATE TABLE `Reward_Points` (
    `Reward_id` int(11) NOT NULL,
    `Rank` int(11) DEFAULT NULL,
    `Points_Awarded` int(11) DEFAULT 0,
    `Student_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Reward_Points`
--

INSERT INTO `Reward_Points` (`Reward_id`, `Rank`, `Points_Awarded`, `Student_id`) VALUES
(12, 0, 10, '24101352'),
(13, 0, 10, '24101352'),
(15, 0, 10, '22301002');

-- --------------------------------------------------------

--
-- Table structure for table `Section`
--

CREATE TABLE `Section` (
    `section_no` varchar(10) NOT NULL,
    `room_no` varchar(20) DEFAULT NULL,
    `date` date DEFAULT NULL,
    `time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Section`
--

INSERT INTO `Section` (`section_no`, `room_no`, `date`, `time`) VALUES
('1', '08A-03C', '2026-04-04', '16:30:00'),
('10', '08A-05C', '2026-04-04', '16:30:00'),
('11', '09A-01C', '2026-04-04', '16:30:00'),
('13', '09A-02C', '2026-04-04', '16:30:00'),
('2', '07A-01C', '2026-04-04', '16:30:00'),
('3', '08A-04C', '2026-04-04', '16:30:00'),
('4', '07A-02C', '2026-04-04', '16:30:00'),
('5', '07A-03C', '2026-04-04', '16:30:00'),
('6', '07A-04C', '2026-04-04', '16:30:00'),
('7', '07A-05C', '2026-04-04', '16:30:00'),
('8', '08A-01C', '2026-04-04', '16:30:00'),
('9', '08A-02C', '2026-04-04', '16:30:00'),
('S991', '08A-03C', '2026-05-04', '08:00:00'),
('S992', '08A-03C', '2026-05-06', '08:00:00'),
('S993', '09A-01C', '2026-05-05', '11:00:00'),
('S994', '09A-01C', '2026-05-07', '11:00:00'),
('S995', '07A-01C', '2026-05-04', '14:00:00'),
('S996', '07A-01C', '2026-05-06', '14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Section_Timing`
--

CREATE TABLE `Section_Timing` (
    `Timing` varchar(50) DEFAULT NULL,
    `section_no` varchar(10) NOT NULL,
    `Semester_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Section_Timing`
--

INSERT INTO `Section_Timing` (`Timing`, `section_no`, `Semester_id`) VALUES
('8:30 AM - 10:00 AM', '1', 'SP26'),
('4:30 PM - 6:00 PM', '10', 'SP26'),
('2:00 PM - 3:30 PM', '2', 'SP26');

-- --------------------------------------------------------

--
-- Table structure for table `Semester_Fees`
--

CREATE TABLE `Semester_Fees` (
    `Payment_id` int(11) NOT NULL,
    `Payment_date` date DEFAULT NULL,
    `Segment` varchar(50) DEFAULT NULL,
    `Amount` decimal(10,2) NOT NULL,
    `Student_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Semester_Fees`
--

INSERT INTO `Semester_Fees` (`Payment_id`, `Payment_date`, `Segment`, `Amount`, `Student_id`) VALUES
(1, '2026-01-12', 'Full Payment', 85000.00, '24101352'),
(2, NULL, 'Installment 1', 85000.00, '22301002'),
(3, NULL, 'Full Payment', 85000.00, '22301003'),
(4, '2026-01-10', 'Full Payment', 85000.00, '21201001'),
(5, NULL, 'Installment 2', 85000.00, '21201002'),
(6, '2026-01-14', 'Full Payment', 85000.00, '21201003'),
(7, '2026-01-11', 'Full Payment', 85000.00, '22101001'),
(8, '2026-01-13', 'Full Payment', 85000.00, '22101002'),
(9, NULL, 'Full Payment', 85000.00, '22201001'),
(10, '2026-01-15', 'Full Payment', 85000.00, '22201002');

-- --------------------------------------------------------

--
-- Table structure for table `Smart_Recommendation`
--

CREATE TABLE `Smart_Recommendation` (
    `Student_id` varchar(10) NOT NULL,
    `Courses_done` text DEFAULT NULL,
    `courses_not_done` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Smart_Recommendation`
--

INSERT INTO `Smart_Recommendation` (`Student_id`, `Courses_done`, `courses_not_done`) VALUES
('21201001', 'CSE110,CSE111,CSE220,CSE221,CSE320,CSE330', 'CSE420,CSE440'),
('21201002', 'CSE110,CSE111,CSE220,CSE221', 'CSE340,CSE370,CSE460'),
('22301002', 'CSE110,CSE111,CSE220,CSE221', 'CSE370,CSE422,CSE423'),
('22301003', 'CSE110,CSE111,CSE220', 'CSE221,CSE370,CSE330'),
('23301001', 'CSE110', 'CSE111,CSE220,CSE221,CSE370'),
('24101352', 'CSE110,CSE111,CSE220,CSE221', 'CSE370,CSE420,CSE421');

-- --------------------------------------------------------

--
-- Table structure for table `Student`
--

CREATE TABLE `Student` (
    `Student_id` varchar(10) NOT NULL,
    `DOB` date DEFAULT NULL,
    `Email` varchar(100) NOT NULL,
    `Fname` varchar(50) NOT NULL,
    `Lname` varchar(50) NOT NULL,
    `cgpa` decimal(4,2) DEFAULT 0.00,
    `Student_type` varchar(20) DEFAULT 'Undergrad',
    `Gender` varchar(10) DEFAULT NULL,
    `dept_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Student`
--

INSERT INTO `Student` (`Student_id`, `DOB`, `Email`, `Fname`, `Lname`, `cgpa`, `Student_type`, `Gender`, `dept_id`) VALUES
('20101001', '2001-07-07', 'imran.hossain@g.bracu.ac.bd', 'Imran', 'Hossain', 3.65, 'Undergrad', 'Male', 'CSE'),
('20101002', '2001-10-10', 'priya.sen@g.bracu.ac.bd', 'Priya', 'Sen', 3.45, 'Undergrad', 'Female', 'CSE'),
('21201001', '2002-03-15', 'rakib.hossain@g.bracu.ac.bd', 'Rakib', 'Hossain', 3.80, 'Undergrad', 'Male', 'CSE'),
('21201002', '2002-07-25', 'mitu.akter@g.bracu.ac.bd', 'Mitu', 'Akter', 3.60, 'Undergrad', 'Female', 'CSE'),
('21201003', '2002-09-10', 'nafis.rahman@g.bracu.ac.bd', 'Nafis', 'Rahman', 3.10, 'Undergrad', 'Male', 'CSE'),
('22101001', '2003-01-20', 'sadia.begum@g.bracu.ac.bd', 'Sadia', 'Begum', 3.90, 'Undergrad', 'Female', 'BBS'),
('22101002', '2003-04-18', 'arpon.das@g.bracu.ac.bd', 'Arpon', 'Das', 3.40, 'Undergrad', 'Male', 'BBS'),
('22101003', '2003-09-02', 'naim.ahmed@g.bracu.ac.bd', 'Naim', 'Ahmed', 3.25, 'Undergrad', 'Male', 'BBS'),
('22101004', '2003-11-10', 'maryam.haque@g.bracu.ac.bd', 'Maryam', 'Haque', 3.60, 'Undergrad', 'Female', 'BBS'),
('22101005', '2004-01-22', 'tanzim.rahman@g.bracu.ac.bd', 'Tanzim', 'Rahman', 3.40, 'Undergrad', 'Male', 'BBS'),
('22201001', '2003-06-30', 'tonni.khatun@g.bracu.ac.bd', 'Tonni', 'Khatun', 3.55, 'Undergrad', 'Female', 'EEE'),
('22201002', '2003-02-14', 'rifat.mahmud@g.bracu.ac.bd', 'Rifat', 'Mahmud', 3.30, 'Undergrad', 'Male', 'EEE'),
('22201003', '2002-12-05', 'shamim.khan@g.bracu.ac.bd', 'Shamim', 'Khan', 3.30, 'Undergrad', 'Male', 'EEE'),
('22201004', '2002-08-14', 'nabila.chowdhury@g.bracu.ac.bd', 'Nabila', 'Chowdhury', 3.55, 'Undergrad', 'Female', 'EEE'),
('22201005', '2002-10-20', 'asif.mahbub@g.bracu.ac.bd', 'Asif', 'Mahbub', 3.20, 'Undergrad', 'Male', 'EEE'),
('22301002', '2003-08-22', 'fabiha.islam@g.bracu.ac.bd', 'Fabiha', 'Islam', 3.70, 'Undergrad', 'Female', 'CSE'),
('22301003', '2003-11-03', 'shuvo.hasan@g.bracu.ac.bd', 'Shuvo', 'Hasan', 3.20, 'Undergrad', 'Male', 'CSE'),
('22401001', '2003-03-08', 'nusrat.jahan@g.bracu.ac.bd', 'Nusrat', 'Jahan', 3.85, 'Undergrad', 'Female', 'MNS'),
('22401002', '2001-12-01', 'tanisha.islam@g.bracu.ac.bd', 'Tanisha', 'Islam', 3.80, 'Undergrad', 'Female', 'MNS'),
('22401003', '2002-02-18', 'shahana.rahman@g.bracu.ac.bd', 'Shahana', 'Rahman', 3.65, 'Undergrad', 'Female', 'MNS'),
('22401004', '2002-05-09', 'arif.nasir@g.bracu.ac.bd', 'Arif', 'Nasir', 3.45, 'Undergrad', 'Male', 'MNS'),
('22501001', '2003-07-12', 'sania.karim@g.bracu.ac.bd', 'Sania', 'Karim', 3.70, 'Undergrad', 'Female', 'ENH'),
('22501002', '2003-08-23', 'rahim.uddin@g.bracu.ac.bd', 'Rahim', 'Uddin', 3.35, 'Undergrad', 'Male', 'ENH'),
('22601001', '2004-01-15', 'robi.ahmed@g.bracu.ac.bd', 'Robi', 'Ahmed', 3.55, 'Undergrad', 'Male', 'ESS'),
('22601002', '2004-03-29', 'mina.hasan@g.bracu.ac.bd', 'Mina', 'Hasan', 3.65, 'Undergrad', 'Female', 'ESS'),
('22701001', '2004-06-10', 'imam.sarker@g.bracu.ac.bd', 'Imam', 'Sarker', 3.50, 'Undergrad', 'Male', 'GenEd'),
('22701002', '2004-08-22', 'nabila.sultana@g.bracu.ac.bd', 'Nabila', 'Sultana', 3.45, 'Undergrad', 'Female', 'GenEd'),
('23301001', '2004-09-05', 'lamia.sharmin@g.bracu.ac.bd', 'Lamia', 'Sharmin', 3.75, 'Undergrad', 'Female', 'CSE'),
('23301002', '2004-12-25', 'sabbir.khan@g.bracu.ac.bd', 'Sabbir', 'Khan', 3.15, 'Undergrad', 'Male', 'CSE'),
('24101352', '2003-05-12', 'mohammad.faiyaz.mazumder@g.bracu.ac.bd', 'Mohammad Faiyaz', 'Mazumder', 3.50, 'Undergrad', 'Male', 'CSE');

-- --------------------------------------------------------

--
-- Table structure for table `Student_Phone`
--

CREATE TABLE `Student_Phone` (
    `Phone` varchar(20) NOT NULL,
    `Student_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Student_Phone`
--

INSERT INTO `Student_Phone` (`Phone`, `Student_id`) VALUES
('01711000001', '24101352'),
('01711000002', '22301002'),
('01711000003', '22301003'),
('01722000001', '21201001'),
('01722000002', '21201002'),
('01722000003', '21201003'),
('01733000001', '22101001'),
('01733000002', '22101002'),
('01744000001', '22201001'),
('01744000002', '22201002'),
('01755000001', '23301001'),
('01755000002', '23301002'),
('01766000001', '20101001'),
('01766000002', '20101002'),
('01777000001', '22401001');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AcademicRoutine`
--
ALTER TABLE `AcademicRoutine`
    ADD PRIMARY KEY (`Student_id`,`Courses`);

--
-- Indexes for table `AnalyticsDashboard`
--
ALTER TABLE `AnalyticsDashboard`
    ADD PRIMARY KEY (`Course_Section`);

--
-- Indexes for table `Classroom`
--
ALTER TABLE `Classroom`
    ADD PRIMARY KEY (`room_no`);

--
-- Indexes for table `Classroom_Lab_Room`
--
ALTER TABLE `Classroom_Lab_Room`
    ADD PRIMARY KEY (`lab_room`);

--
-- Indexes for table `Classroom_Theory_Room`
--
ALTER TABLE `Classroom_Theory_Room`
    ADD PRIMARY KEY (`theory_room`);

--
-- Indexes for table `Clubs`
--
ALTER TABLE `Clubs`
    ADD PRIMARY KEY (`Club_Name`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Complaint`
--
ALTER TABLE `Complaint`
    ADD PRIMARY KEY (`Complain_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Course`
--
ALTER TABLE `Course`
    ADD PRIMARY KEY (`course_code`),
    ADD KEY `dept_id` (`dept_id`),
    ADD KEY `room_no` (`room_no`);

--
-- Indexes for table `Department`
--
ALTER TABLE `Department`
    ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `Enrolled_In`
--
ALTER TABLE `Enrolled_In`
    ADD PRIMARY KEY (`Student_id`,`course_code`),
    ADD KEY `course_code` (`course_code`);

--
-- Indexes for table `Exam`
--
ALTER TABLE `Exam`
    ADD PRIMARY KEY (`Exam_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Faculty`
--
ALTER TABLE `Faculty`
    ADD PRIMARY KEY (`faculty_id`),
    ADD UNIQUE KEY `email` (`email`),
    ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `Files`
--
ALTER TABLE `Files`
    ADD PRIMARY KEY (`Student_id`,`Complain_id`),
    ADD KEY `Complain_id` (`Complain_id`);

--
-- Indexes for table `Grade`
--
ALTER TABLE `Grade`
    ADD PRIMARY KEY (`Student_id`,`Course_code`),
    ADD KEY `Course_code` (`Course_code`);

--
-- Indexes for table `Messages`
--
ALTER TABLE `Messages`
    ADD PRIMARY KEY (`msg_id`),
    ADD KEY `sender_id` (`sender_id`),
    ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `Note`
--
ALTER TABLE `Note`
    ADD PRIMARY KEY (`Title`,`Student_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Prerequisite`
--
ALTER TABLE `Prerequisite`
    ADD PRIMARY KEY (`course_code`,`prereq_course_code`),
    ADD KEY `prereq_course_code` (`prereq_course_code`);

--
-- Indexes for table `Soft_Prerequisite`
--
ALTER TABLE `Soft_Prerequisite`
    ADD PRIMARY KEY (`course_code`,`sp_course_code`),
    ADD KEY `sp_course_code` (`sp_course_code`);

--
-- Indexes for table `Reward_Points`
--
ALTER TABLE `Reward_Points`
    ADD PRIMARY KEY (`Reward_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Section`
--
ALTER TABLE `Section`
    ADD PRIMARY KEY (`section_no`),
    ADD KEY `room_no` (`room_no`);

--
-- Indexes for table `Section_Timing`
--
ALTER TABLE `Section_Timing`
    ADD PRIMARY KEY (`section_no`,`Semester_id`);

--
-- Indexes for table `Semester_Fees`
--
ALTER TABLE `Semester_Fees`
    ADD PRIMARY KEY (`Payment_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- Indexes for table `Smart_Recommendation`
--
ALTER TABLE `Smart_Recommendation`
    ADD PRIMARY KEY (`Student_id`);

--
-- Indexes for table `Student`
--
ALTER TABLE `Student`
    ADD PRIMARY KEY (`Student_id`),
    ADD UNIQUE KEY `Email` (`Email`),
    ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `Student_Phone`
--
ALTER TABLE `Student_Phone`
    ADD PRIMARY KEY (`Phone`,`Student_id`),
    ADD KEY `Student_id` (`Student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Complaint`
--
ALTER TABLE `Complaint`
    MODIFY `Complain_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Exam`
--
ALTER TABLE `Exam`
    MODIFY `Exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Messages`
--
ALTER TABLE `Messages`
    MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Reward_Points`
--
ALTER TABLE `Reward_Points`
    MODIFY `Reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Semester_Fees`
--
ALTER TABLE `Semester_Fees`
    MODIFY `Payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AcademicRoutine`
--
ALTER TABLE `AcademicRoutine`
    ADD CONSTRAINT `academicroutine_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Clubs`
--
ALTER TABLE `Clubs`
    ADD CONSTRAINT `clubs_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Complaint`
--
ALTER TABLE `Complaint`
    ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Course`
--
ALTER TABLE `Course`
    ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `Department` (`dept_id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `course_ibfk_2` FOREIGN KEY (`room_no`) REFERENCES `Classroom` (`room_no`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Enrolled_In`
--
ALTER TABLE `Enrolled_In`
    ADD CONSTRAINT `enrolled_in_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `enrolled_in_ibfk_2` FOREIGN KEY (`course_code`) REFERENCES `Course` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Exam`
--
ALTER TABLE `Exam`
    ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Faculty`
--
ALTER TABLE `Faculty`
    ADD CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `Department` (`dept_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Files`
--
ALTER TABLE `Files`
    ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`Complain_id`) REFERENCES `Complaint` (`Complain_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Grade`
--
ALTER TABLE `Grade`
    ADD CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `grade_ibfk_2` FOREIGN KEY (`Course_code`) REFERENCES `Course` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Messages`
--
ALTER TABLE `Messages`
    ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE,
    ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE;

--
-- Constraints for table `Note`
--
ALTER TABLE `Note`
    ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Prerequisite`
--
ALTER TABLE `Prerequisite`
    ADD CONSTRAINT `prerequisite_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `Course` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `prerequisite_ibfk_2` FOREIGN KEY (`prereq_course_code`) REFERENCES `Course` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Reward_Points`
--
ALTER TABLE `Reward_Points`
    ADD CONSTRAINT `reward_points_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Section`
--
ALTER TABLE `Section`
    ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`room_no`) REFERENCES `Classroom` (`room_no`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Section_Timing`
--
ALTER TABLE `Section_Timing`
    ADD CONSTRAINT `section_timing_ibfk_1` FOREIGN KEY (`section_no`) REFERENCES `Section` (`section_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Semester_Fees`
--
ALTER TABLE `Semester_Fees`
    ADD CONSTRAINT `semester_fees_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Smart_Recommendation`
--
ALTER TABLE `Smart_Recommendation`
    ADD CONSTRAINT `smart_recommendation_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Student`
--
ALTER TABLE `Student`
    ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `Department` (`dept_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Student_Phone`
--
ALTER TABLE `Student_Phone`
    ADD CONSTRAINT `student_phone_ibfk_1` FOREIGN KEY (`Student_id`) REFERENCES `Student` (`Student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;