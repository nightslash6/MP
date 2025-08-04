
-- phpMyAdmin SQL Dump
-- version 5.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=3;

INSERT INTO `categories` (`category_id`, `category_name`, `category_description`, `created_at`) VALUES
(1, 'CyberSecurity', 'Questions related to Cyber Security topics.', '2025-06-29 13:49:06'),
(2, 'Forensics', 'Questions related to Forensics topics', '2025-07-03 14:59:22');

CREATE TABLE `levels` (
  `category_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `level_name` varchar(255) NOT NULL,
  `level_description` text DEFAULT NULL,
  `required_score` int(11) DEFAULT 40,
  `questions_count` int(11) DEFAULT 5,
  `unlock_previous_level` int(11) DEFAULT NULL,
  `badge_icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`,`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `levels` (`category_id`, `level_id`, `level_name`, `level_description`, `required_score`, `questions_count`, `unlock_previous_level`, `badge_icon`, `created_at`) VALUES
(1, 1, 'Beginner', 'Introduction to basic concepts.', 40, 5, NULL, 'beginner.png', '2025-06-29 13:49:06'),
(1, 2, 'Intermediate', 'Intermediate level questions.', 50, 7, 1, 'intermediate.png', '2025-06-29 13:49:06'),
(1, 3, 'Advanced', 'Challenging questions for advanced users.', 60, 10, 2, 'advanced.png', '2025-06-29 13:49:06'),
(2, 1, 'Beginner', 'Introduction to basic concepts.', 40, 5, NULL, 'beginner.png', '2025-07-10 06:00:36'),
(2, 2, 'Intermediate', 'Intermediate level questions.', 50, 7, 1, 'intermediate.png', '2025-07-10 06:00:36'),
(2, 3, 'Advanced', 'Challenging questions for advanced users.', 60, 10, 2, 'advanced.png', '2025-07-10 06:00:36');

CREATE TABLE `powerups` (
  `powerup_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` int(11) DEFAULT NULL,
  PRIMARY KEY (`powerup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=6;

INSERT INTO `powerups` (`powerup_id`, `name`, `description`, `cost`) VALUES
(1, '50/50', 'Removes two incorrect options from a multiple-choice question.', 20),
(2, 'Second Chance', 'Allows another attempt after a wrong answer.', 30),
(3, 'Skip Question', 'Lets you skip a difficult question.', 15),
(4, 'Time Freeze', 'Pauses the timer for 10 seconds.', 25),
(5, 'Reveal Hint', 'Shows a helpful hint for the current question.', 10);

CREATE TABLE `python` (
  `python_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  PRIMARY KEY (`python_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `python` (`python_id`, `topic`, `content`, `example`, `question`, `answer`) VALUES
(1, 'Strings', 'Strings are defined by enclosing the sequence of characters within either single quotes, double quotes, or triple quotes in a print() function.', 'print("Hello World")', 'Print a string.', NULL),
(2, 'Variables', 'Variables are symbolic names that act as containers for storing data values. Variables are created the moment you first assign a value to them using the assignment operator (=).', 'age = 30//name = "John"', 'Assign a variable, x ,a value and print it out.', NULL);

CREATE TABLE `python_subtopics` (
  `subtopic_id` int(11) NOT NULL,
  `python_id` int(11) NOT NULL,
  `subtopic_title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  PRIMARY KEY (`subtopic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `python_subtopics` (`subtopic_id`, `python_id`, `subtopic_title`, `content`, `example`, `question`, `answer`) VALUES
(1, 2, 'Variable Names', 'A variable can have a short name (like x and y) or a more descriptive name (age, carname, total_volume).//Rules for Python variables://A variable name must start with a letter or the underscore character//A variable name cannot start with a number//A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _ )//Variable names are case-sensitive (age, Age and AGE are three different variables)//A variable name cannot be any of the Python keywords.', 'Legal variable names://myvar = "John"//my_var = "John"//_my_var = "John"//myVar = "John"//MYVAR = "John"//myvar2 = "John"////Illegal variable names://2myvar = "John"//my-var = "John"//my var = "John"', NULL, NULL),
(2, 2, 'Assign Multiple Values', 'Python allows you to assign values to multiple variables in one line.', 'x, y, z = "Orange", "Banana", "Cherry"//print(x)//print(y)//print(z)', NULL, NULL);

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ') NOT NULL,
  `options` json DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT 1,
  `is_completed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`question_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=75;


INSERT INTO `questions` (`question_id`, `question_text`, `description`, `question_type`, `options`, `correct_answer`, `level_id`, `category_id`, `is_completed`) VALUES
(3, 'What is Cyber Security?', NULL, 'MCQ', '["Cyber Security provides security against malware", "Cyber Security provides security against cyber-terrorists", "Cyber Security protects a system from cyber attacks", "All of the mentioned"]', 'All of the mentioned', 1, 1, 0),
(4, 'What does cyber security protect?', NULL, 'MCQ', '["Cyber security protects criminals", "Cyber security protects internet-connected systems", "Cyber security protects hackers", "None of the mentioned"]', 'Cyber security protects internet-connected systems', 1, 1, 0),
(5, 'Which of the following is defined as an attempt to steal, spy, damage or destroy computer systems?', NULL, 'MCQ', '["Cyber attack", "Computer security", "Cryptography", "Digital hacking"]', 'Cyber attack', 1, 1, 0),
(6, 'What is the weakest link in cybersecurity?', NULL, 'MCQ', '["Weak encryption", "Humans", "Short passwords", "Firewalls"]', 'Humans', 1, 1, 0),
(7, 'Which of the following is a type of cyber security?', NULL, 'MCQ', '["Cloud Security", "Network Security", "Application Security", "All of the above"]', 'All of the above', 1, 1, 0),
(8, 'What does the term "firewall" refer to in cyber security?', NULL, 'MCQ', '["A protective suit worn by cybersecurity professionals", "A physical barrier around computer servers", "A security system that monitors and controls network traffic", "Software for preventing computer overheating"]', 'A security system that monitors and controls network traffic', 1, 1, 0),
(9, 'What is the primary function of a firewall?', NULL, 'MCQ', '["To speed up internet connection", "To monitor and control network traffic", "To store passwords", "To create backups"]', 'To monitor and control network traffic', 1, 1, 0),
(10, 'Which port is commonly used for HTTPS?', NULL, 'MCQ', '["80", "443", "8080", "3389"]', '443', 1, 1, 0),
(11, 'What does VPN stand for?', NULL, 'MCQ', '["Virtual Private Network", "Very Private Network", "Virtual Public Network", "Verified Private Network"]', 'Virtual Private Network', 1, 1, 0),
(12, 'What is malware?', NULL, 'MCQ', '["Good software", "Malicious software designed to harm computers", "A type of hardware", "A network protocol"]', 'Malicious software designed to harm computers', 1, 1, 0),
(13, 'What does AES stand for?', NULL, 'MCQ', '["Automated Encryption Strength", "Advanced Encryption Standard", "Algorithm Encrypted System", "Advanced Encryption Solution"]', 'Advanced Encryption Standard', 1, 1, 0),
(14, 'When is it ok to reuse a password?', NULL, 'MCQ', '["When you trust the website", "For unimportant accounts", "Never", "Once a year"]', 'Never', 1, 1, 0),
(15, 'What is the purpose of a CAPTCHA in online security?', NULL, 'MCQ', '["Detecting malware", "Authenticating users", "Preventing automated bots", "Encrypting data"]', 'Preventing automated bots', 1, 1, 0),
(16, 'What is two-factor authentication (2FA)?', NULL, 'MCQ', '["Using two passwords", "Using two devices", "Using two different methods to verify identity", "Using two usernames"]', 'Using two different methods to verify identity', 1, 1, 0),
(17, 'What type of software infects a machine, locks the files, then asks for money?', NULL, 'MCQ', '["Worm", "Trojan", "Ransomware", "Browser Hijacker"]', 'Ransomware', 1, 1, 0),
(18, 'Which protocol provides secure file transfer over SSH?', NULL, 'MCQ', '["FTP","FTPS","SFTP","TFTP"]', 'SFTP', 2, 1, 0),
(19, 'What port does SSH listen on by default?', NULL, 'MCQ', '["20","22","23","443"]', '22', 2, 1, 0),
(20, 'Which AES mode provides both confidentiality and integrity?', NULL, 'MCQ', '["CBC","ECB","GCM","OFB"]', 'GCM', 2, 1, 0),
(23, 'Which of the following Nmap options enables OS detection?', NULL, 'MCQ', '["-sV","-O","-A","-sS"]', '-O', 2, 1, 0),
(25, 'Which of the following best describes Fully Homomorphic Encryption (FHE)?', NULL, 'MCQ', '["Encryption that supports only addition on ciphertexts","Encryption that supports either addition or multiplication but not both","Encryption that allows arbitrary computation on encrypted data without decryption","A symmetric cipher hardened against quantum attacks"]', 'Encryption that allows arbitrary computation on encrypted data without decryption', 3, 1, 0),
(26, 'In a Zero Trust network, which principle is fundamental?', NULL, 'MCQ', '["Implicit trust for internal network traffic","Verification of every user or device, regardless of location","Relying on perimeter firewalls as the primary defense","Allowing lateral movement once inside the network"]', 'Verification of every user or device, regardless of location', 3, 1, 0),
(27, 'An attacker crafts inputs to bypass an ML-based intrusion detection system. This is known as:', NULL, 'MCQ', '["Data poisoning","Model inversion","Evasion attack","Membership inference"]', 'Evasion attack', 3, 1, 0),
(28, 'Which protocol is commonly used to securely connect to a remote server?', NULL, 'MCQ', '["HTTP","FTP","SSH","Telnet"]', 'SSH', 2, 1, 0),
(29, 'What is the main purpose of a firewall in a network?', NULL, 'MCQ', '["To store data","To block unauthorized access","To speed up the network","To manage passwords"]', 'To block unauthorized access', 2, 1, 0),
(30, 'Which of the following is considered a strong password?', NULL, 'MCQ', '["password123","qwerty","P@ssw0rd!","123456"]', 'P@ssw0rd!', 2, 1, 0),
(31, 'What is phishing primarily designed to do?', NULL, 'MCQ', '["Steal sensitive information","Speed up computers","Fix software bugs","Enhance graphics"]', 'Steal sensitive information', 2, 1, 0),
(32, 'Which type of malware is designed to replicate itself and spread to others?', NULL, 'MCQ', '["Worm","Adware","Spyware","Ransomware"]', 'Worm', 2, 1, 0),
(33, 'What is the primary function of a Security Information and Event Management (SIEM) system?', NULL, 'MCQ', '["Encrypt data","Monitor and analyze security events","Scan for viruses","Manage user passwords"]', 'Monitor and analyze security events', 3, 1, 0),
(34, 'Which attack exploits the trust between two communicating parties by intercepting traffic?', NULL, 'MCQ', '["Phishing","Man-in-the-middle","Denial of Service","SQL Injection"]', 'Man-in-the-middle', 3, 1, 0),
(35, 'What is the main goal of a penetration test?', NULL, 'MCQ', '["To fix software bugs","To evaluate security by simulating attacks","To increase bandwidth","To backup data"]', 'To evaluate security by simulating attacks', 3, 1, 0),
(36, 'Which protocol adds security to DNS queries?', NULL, 'MCQ', '["DNSSEC","HTTPS","SSL","SFTP"]', 'DNSSEC', 3, 1, 0),
(37, 'In cybersecurity, what does the principle of “least privilege” mean?', NULL, 'MCQ', '["Granting all users admin rights","Providing minimum access necessary","Allowing guest access","Disabling authentication"]', 'Providing minimum access necessary', 3, 1, 0),
(38, 'What is a common method for detecting anomalies in network traffic?', NULL, 'MCQ', '["Manual inspection","Signature-based detection","Anomaly-based detection","Password cracking"]', 'Anomaly-based detection', 3, 1, 0),
(39, 'Which type of cyber attack involves overwhelming a system with traffic to make it unavailable?', NULL, 'MCQ', '["Phishing","Denial of Service","Rootkit","Keylogger"]', 'Denial of Service', 3, 1, 0),
(47, 'What is the primary goal of digital forensics?', NULL, 'MCQ', '["To recover deleted files","To analyze digital evidence","To create malware","To hack systems"]', 'To analyze digital evidence', 1, 2, 0),
(48, 'Which device is commonly used to capture network traffic for forensic analysis?', NULL, 'MCQ', '["Router","Switch","Packet sniffer","Firewall"]', 'Packet sniffer', 1, 2, 0),
(49, 'What does the term \'chain of custody\' refer to in forensics?', NULL, 'MCQ', '["Sequence of evidence handling","Type of malware","Encryption method","File recovery technique"]', 'Sequence of evidence handling', 1, 2, 0),
(50, 'Which file system is often analyzed in digital forensics?', NULL, 'MCQ', '["NTFS","FAT32","EXT4","All of the above"]', 'All of the above', 1, 2, 0),
(51, 'What is the first step in a digital forensic investigation?', NULL, 'MCQ', '["Data analysis","Evidence collection","Reporting","System shutdown"]', 'Evidence collection', 1, 2, 0),
(52, 'Which tool is commonly used for disk imaging in forensics?', NULL, 'MCQ', '["Wireshark","FTK Imager","Nmap","Metasploit"]', 'FTK Imager', 2, 2, 0),
(53, 'What is the purpose of hashing in digital forensics?', NULL, 'MCQ', '["To encrypt data","To verify data integrity","To delete files","To speed up analysis"]', 'To verify data integrity', 2, 2, 0),
(54, 'Which type of evidence is considered volatile?', NULL, 'MCQ', '["Hard drive data","RAM data","Printed documents","Emails"]', 'RAM data', 2, 2, 0),
(55, 'What does the term \'live forensics\' mean?', NULL, 'MCQ', '["Analyzing data on a running system","Analyzing deleted files","Analyzing network traffic","Analyzing backups"]', 'Analyzing data on a running system', 2, 2, 0),
(56, 'Which file format is commonly used to store forensic images?', NULL, 'MCQ', '[".exe",".dd",".txt",".jpg"]', '.dd', 2, 2, 0),
(57, 'What is the role of metadata in digital forensics?', NULL, 'MCQ', '["To hide files","To provide information about data","To encrypt files","To delete data"]', 'To provide information about data', 2, 2, 0),
(58, 'Which law enforcement agency often handles digital forensic investigations in the US?', NULL, 'MCQ', '["FBI","CIA","NSA","DEA"]', 'FBI', 2, 2, 0),
(59, 'What is the difference between static and live forensics?', NULL, 'MCQ', '["Static analyzes powered-off systems, live analyzes running systems","Static analyzes running systems, live analyzes powered-off systems","Both analyze powered-off systems","Both analyze running systems"]', 'Static analyzes powered-off systems, live analyzes running systems', 3, 2, 0),
(60, 'Which forensic tool is used for memory analysis?', NULL, 'MCQ', '["Volatility","Wireshark","Nmap","FTK Imager"]', 'Volatility', 3, 2, 0),
(61, 'What is steganography in the context of digital forensics?', NULL, 'MCQ', '["Hiding data within other files","Encrypting data","Deleting data","Backing up data"]', 'Hiding data within other files', 3, 2, 0),
(62, 'Which technique is used to recover deleted files?', NULL, 'MCQ', '["File carving","Encryption","Hashing","Compression"]', 'File carving', 3, 2, 0),
(63, 'What is the significance of the write blocker in forensic investigations?', NULL, 'MCQ', '["Prevents modification of evidence","Speeds up data transfer","Encrypts data","Deletes malware"]', 'Prevents modification of evidence', 3, 2, 0),
(64, 'Which forensic process involves analyzing network traffic to detect intrusions?', NULL, 'MCQ', '["Network forensics","Disk imaging","File carving","Memory analysis"]', 'Network forensics', 3, 2, 0),
(65, 'What is the role of the hash value in verifying forensic evidence?', NULL, 'MCQ', '["To speed up analysis","To uniquely identify data","To encrypt data","To delete data"]', 'To uniquely identify data', 3, 2, 0),
(66, 'Which type of forensic analysis focuses on recovering data from mobile devices?', NULL, 'MCQ', '["Mobile forensics","Network forensics","Memory forensics","Disk forensics"]', 'Mobile forensics', 3, 2, 0),
(67, 'What is the primary challenge in cloud forensics?', NULL, 'MCQ', '["Data volatility","Data encryption","Data jurisdiction","All of the above"]', 'All of the above', 3, 2, 0),
(68, 'Which standard is commonly followed for forensic soundness?', NULL, 'MCQ', '["ISO/IEC 27037","ISO 9001","NIST SP 800-53","GDPR"]', 'ISO/IEC 27037', 3, 2, 0),
(70, 'testing 2', '', 'MCQ', '["1","2","3","4"]', '1', 1, 1, 0),
(71, 'testing 3', '', 'MCQ', '["1","2","3","4"]', '1', 3, 2, 0),
(72, 'Testing Today', '', 'MCQ', '["Right","Wrong","R1gjt","Wr0ng"]', 'Right', 3, 2, 0),
(73, 'testing 5', '', 'MCQ', '["tester","1","2","3"]', '1', 2, 2, 0),
(74, 'eee', '', 'MCQ', '["2222","23","21","22","2"]', '22', 1, 1, 0);

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(8) NOT NULL,
  `user_role` enum('student','admin') DEFAULT 'student',
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone_number` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=6;

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `user_role`, `password_hash`, `reset_token`, `reset_token_expiration`) VALUES
(1, 'Student', 'student@gmail.com', '99992222', 'student', 'passwordhash', NULL, NULL),
(2, 'ww', 'ww@gmail.com', '12345123', 'student', '$2y$10$HWf4oLpy6pECM0ATMvbitOsUpplbZ.nK1vh0m7w9rqfubRPB7KOoa', NULL, NULL),
(3, 'aa', 'aa@gmail.com', '10982929', 'student', '$2y$10$6Tb0szTi25mRwH2h06UlFOHlWwIbhIe8cfANvfXfHF9aSZ4/4OrJC', NULL, NULL),
(4, 'weih', '2302536D@student.tp.edu.sg', '88011122', 'admin', '$2y$10$8BpcRY7Oavrq2QB4RSbK5u1XDq3MPGvGWlAxM42wapwHg2VTIuxMO', '2bb0da73c8e793e3ebc4527eca3dd9189a23a77a0f30226efd084e2360a25e999343450f2f6b9698e2a30efa4213b01a2db1', '2025-07-16 00:17:17'),
(5, 'aaa', 'aaa@gmail.com', '10129128', 'student', '$2y$10$ceoU27UaiGORWTMKBMzs0.YJG8OwGARJlzLBx9kyzSajRTttxYc0a', NULL, NULL);

CREATE TABLE `user_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT 1,
  `question_id` int(11) DEFAULT NULL,
  `selected_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=5;

INSERT INTO `user_answers` (`answer_id`, `user_id`, `question_id`, `selected_answer`, `is_correct`, `points_earned`, `answered_at`) VALUES
(1, 1, 1, 'A', 1, 10, '2025-06-29 13:49:06'),
(2, 1, 2, 'C', 0, 0, '2025-06-29 13:49:06'),
(3, 2, 1, 'B', 0, 0, '2025-06-29 13:49:06'),
(4, 2, 3, 'D', 1, 10, '2025-06-29 13:49:06');

CREATE TABLE `user_points` (
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_points` (`user_id`, `points`) VALUES
(2, 25),
(3, 60),
(4, 275),
(5, 30);

CREATE TABLE `user_powerups` (
  `user_id` int(11) NOT NULL,
  `powerup_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  PRIMARY KEY (`user_id`,`powerup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_powerups` (`user_id`, `powerup_id`, `quantity`) VALUES
(2, 1, 0),
(2, 3, 0),
(3, 1, 0),
(3, 4, 0),
(3, 5, 0),
(4, 1, 0),
(4, 3, 0),
(5, 1, 0),
(5, 5, 1);

CREATE TABLE `user_progress` (
  `progress_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT 1,
  `level_id` int(11) DEFAULT NULL,
  `current_score` int(11) DEFAULT 0,
  `questions_answered` int(11) DEFAULT 0,
  `questions_correct` int(11) DEFAULT 0,
  `level_completed` tinyint(1) DEFAULT 0,
  `completion_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `unique_user_level` (`user_id`,`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=57;

INSERT INTO `user_progress` (`progress_id`, `user_id`, `level_id`, `current_score`, `questions_answered`, `questions_correct`, `level_completed`, `completion_time`, `created_at`) VALUES
(1, 1, 1, 45, 5, 4, 1, '2025-06-29 09:00:00', '2025-06-29 13:49:06'),
(2, 2, 1, 80, 5, 3, 0, '2025-06-29 09:28:01', '2025-06-29 13:49:06'),
(3, 1, 2, 10, 2, 1, 0, NULL, '2025-06-29 13:49:06'),
(5, 2, 2, 71, 7, 5, 1, '2025-06-29 10:22:20', '2025-06-29 15:28:37'),
(7, 3, 1, 80, 5, 3, 1, '2025-07-03 09:04:00', '2025-07-03 15:04:00'),
(8, 3, 2, 100, 7, 1, 1, NULL, '2025-07-03 15:04:45'),
(34, 3, 4, 100, 5, 5, 1, '2025-07-10 00:08:08', '2025-07-10 06:08:08'),
(36, 2, 4, 100, 5, 5, 1, '2025-07-10 12:17:32', '2025-07-10 18:17:32'),
(37, 2, 5, 100, 5, 5, 1, '2025-07-10 12:17:59', '2025-07-10 18:17:59'),
(44, 3, 5, 100, 5, 5, 1, '2025-07-10 22:25:35', '2025-07-11 04:25:35'),
(45, 5, 1, 100, 5, 5, 1, '2025-07-15 11:04:46', '2025-07-15 17:04:46'),
(46, 5, 4, 100, 5, 0, 1, NULL, '2025-07-17 16:38:07'),
(48, 4, 4, 100, 5, 5, 1, '2025-07-23 04:21:50', '2025-07-23 10:21:50'),
(50, 4, 5, 100, 5, 5, 1, '2025-07-23 04:55:41', '2025-07-23 10:55:41'),
(51, 4, 6, 100, 6, 6, 1, '2025-07-23 04:56:14', '2025-07-23 10:56:14'),
(52, 4, 1, 80, 5, 4, 1, '2025-07-23 04:57:09', '2025-07-23 10:57:09'),
(53, 4, 2, 86, 7, 6, 1, '2025-07-23 11:45:40', '2025-07-23 17:45:40');

-- Triggers
DELIMITER $$
CREATE TRIGGER `insert_user_points_after_user` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_points (user_id, points)
    VALUES (NEW.user_id, 0);
END
$$
DELIMITER ;

COMMIT;
