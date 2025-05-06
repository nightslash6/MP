SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create the project database
DROP DATABASE IF EXISTS `questiontest`;
CREATE DATABASE `questiontest`;
USE `questiontest`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `UserID` INT(11) NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(255) UNIQUE NOT NULL,
  `PhoneNumber` VARCHAR(8) UNIQUE NOT NULL,
  `PasswordHash` VARCHAR(255) NOT NULL,
  `Role` ENUM('Admin', 'Student') NOT NULL,
  `Department` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`Name`, `Email`, `PhoneNumber`, `PasswordHash`, `Role`) 
VALUES 
('Student', 'student@gmail.com', '99992222', 'passwordhash', 'Student');


CREATE TABLE IF NOT EXISTS `questions` (
    `QuestionID` INT(11) NOT NULL AUTO_INCREMENT,
    `QuestionText` TEXT NOT NULL,
    `Description` TEXT DEFAULT NULL,
    `QuestionType` ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
    `Options` JSON DEFAULT NULL,    
    `CorrectAnswer` TEXT DEFAULT NULL,
    `Is_Completed` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`QuestionID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `questions` (`QuestionText`, `Description`, `QuestionType`, `Options`, `CorrectAnswer`)
VALUES 
('What is the correct answer?', NULL, 'MCQ', '["Monkey", "Not This", "Not This", "Not This"]', 'Monkey');

INSERT INTO `questions` (`QuestionText`, `Description`, `QuestionType`, `Options`, `CorrectAnswer`)
VALUES 
('Cryptography can be easy, do you know what ROT13 is?', "cvpbPGS{arkg_gvzr_V'yy_gel_2_ebhaqf_bs_ebg13_nSkgmDJE}", 'LongAnswer', NULL, "picoCTF{next_time_I'll_try_2_rounds_of_rot13_aFxtzQWR}")

CREATE TABLE IF NOT EXISTS `questions` (
    `QuestionID` INT(11) NOT NULL AUTO_INCREMENT,
    `QuestionText` TEXT NOT NULL,
    `Description` TEXT DEFAULT NULL,
    `QuestionType` ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
    `Options` JSON DEFAULT NULL,    
    `CorrectAnswer` TEXT DEFAULT NULL,
    PRIMARY KEY (`QuestionID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
