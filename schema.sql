-- EduMeet tutor system schema with demo seed data

CREATE DATABASE IF NOT EXISTS tutor_system;
USE tutor_system;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE `reports` (
  `reportID` int(11) NOT NULL,
  `reporterID` int(11) NOT NULL,
  `reportedID` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reports` (`reportID`, `reporterID`, `reportedID`, `reason`, `created_at`) VALUES
(1, 11, 3, 'Inappropriate behavior during session', '2025-12-21 17:51:31'),
(2, 12, 3, 'Session quality issue', '2025-12-22 10:55:03');

CREATE TABLE `requests` (
  `requestID` int(11) NOT NULL,
  `studentID` int(11) NOT NULL,
  `tutorID` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `lesson_time` varchar(100) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT NULL,
  `review` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `requests` (`requestID`, `studentID`, `tutorID`, `subject`, `lesson_time`, `status`, `created_at`, `rating`, `review`) VALUES
(1, 11, 3, 'Derivatives', '2025-12-20T03:20', 'accepted', '2025-12-21 17:18:20', 4, 'good'),
(2, 12, 3, 'Integral', '2025-12-21T16:20', 'accepted', '2025-12-22 19:37:19', 5, 'excellent'),
(3, 13, 3, 'Calculus', '2025-12-19T19:00', 'accepted', '2025-12-22 19:41:22', 3, 'average'),
(4, 11, 9, 'CPP', '2025-11-18T21:00', 'accepted', '2025-12-22 20:35:11', 3, 'not bad'),
(5, 12, 9, 'Java', '2025-12-11T17:00', 'accepted', '2025-12-22 20:35:17', 2, 'not good'),
(6, 13, 9, 'Python', '2025-11-29T18:00', 'accepted', '2025-12-22 20:35:23', 4, 'great'),
(7, 11, 6, 'Cell', '2025-12-10T19:00', 'accepted', '2025-12-22 20:35:28', 5, 'excellent'),
(8, 12, 6, 'Evolution', '2025-11-25T13:00', 'accepted', '2025-12-22 20:35:32', 5, 'great tutor'),
(9, 13, 6, 'Inheritance', '2025-10-20T14:00', 'accepted', '2025-12-22 20:35:36', 4, 'very good');

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `subjects` (`id`, `name`) VALUES
(4, 'Biology'),
(3, 'Chemistry'),
(5, 'English'),
(9, 'German'),
(6, 'History'),
(8, 'Literature'),
(1, 'Mathematics'),
(2, 'Physics'),
(7, 'Software/Coding');

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `userName` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','tutor','admin') NOT NULL DEFAULT 'student',
  `skills` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`userID`, `userName`, `email`, `password`, `role`, `skills`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$yqP8OsZohRe9JeriPeDntu97LVZyRh7fyYpS.EZOWBvhznllbGaqu', 'admin', NULL, '2025-12-11 23:36:59'),
(2, 'GermanTutor', 'germantutor@example.com', '$2a$12$35AFHMvAKQbL1nNN9vNAHOL5mAW3IsY8NHDJL7esBkiGWonSo.sqC', 'tutor', 'German', '2025-12-17 15:10:49'),
(3, 'MathTutor', 'mathtutor@example.com', '$2a$12$0hhXoVXmgsPmjls2Qi/2U.k8h.983mpdHI5K/Stm94SvlbRqkBNVi', 'tutor', 'Mathematics', '2025-12-22 10:40:41'),
(4, 'PhysicsTutor', 'physicstutor@example.com', '$2a$12$Ygkiy7v3kQvc4eQ80eTRZetiicuA5aXTrTu9EAlRbZcX1CD7FMjGO', 'tutor', 'Physics', '2025-12-17 15:06:48'),
(5, 'ChemistryTutor', 'chemistrytutor@example.com', '$2a$12$UUk.3LyhZiTJ2H9I5nNuzOvFopXwwj8Xovn5UVzBpnFyVdfzWkA66', 'tutor', 'Chemistry', '2025-12-17 15:08:01'),
(6, 'BiologyTutor', 'biologytutor@example.com', '$2a$12$j/4RpIATDtlO0z3cVDn2mOUuP5emyfQdRiOyY17JoZq6EuSGIwYCi', 'tutor', 'Biology', '2025-12-17 15:09:17'),
(7, 'EnglishTutor', 'englishtutor@example.com', '$2a$12$ME64moVTPCjDg2yvPSFBuetxzwMMX0J8U3Z9BrtpBWl5/BGJMNTmq', 'tutor', 'English', '2025-12-17 15:09:32'),
(8, 'HistoryTutor', 'historytutor@example.com', '$2a$12$X995mHzXSG5KnxXCmroWHO0lhJnDFlHlOSB7HXO3IVWbdPoJSj3wS', 'tutor', 'History', '2025-12-17 15:09:52'),
(9, 'SoftwareTutor', 'softwaretutor@example.com', '$2a$12$f6GThyYI87oVQzm5g0Gad.dyNUuUe123Faem2CaI1gxHCmF48v5O6', 'tutor', 'Software', '2025-12-17 15:10:09'),
(10, 'LiteratureTutor', 'literaturetutor@example.com', '$2a$12$age.rwV1hHYuJvvpeCXsdukvkT3TUtd0ndciFa3vKRR1WeFvwn2Q2', 'tutor', 'Literature', '2025-12-17 15:10:35'),
(11, 'Student1', 'student1@example.com', '$2a$12$KXotv.yRBAJZoQaB7GlCCOMuivYQo4FecnJyH8ej8fG.erlUol3iq', 'student', NULL, '2025-12-22 19:58:01'),
(12, 'Student2', 'student2@example.com', '$2a$12$IlEjKZuKcODi9a/cct/d.OG5S8S5aZ3PUquLY.cYAJM64GEyRTfm6', 'student', NULL, '2025-12-22 19:59:08'),
(13, 'Student3', 'student3@example.com', '$2a$12$zTe/aTg7snYtqR6zZKkC2uqCg9vAsPZZTdA0TPZ3r6CD33TLOQ2ma', 'student', NULL, '2025-12-22 19:59:55');

ALTER TABLE `reports`
  ADD PRIMARY KEY (`reportID`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`requestID`);

ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `reports`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `requests`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

COMMIT;
