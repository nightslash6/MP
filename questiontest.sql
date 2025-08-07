-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2025 at 07:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `questiontest`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_description`, `created_at`) VALUES
(1, 'CyberSecurity', 'Questions related to Cyber Security topics', '2025-06-29 05:49:06'),
(2, 'Forensics', 'Questions related to Forensics topics', '2025-07-03 06:59:22');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `flag` varchar(100) DEFAULT NULL,
  `solves` int(11) DEFAULT 0,
  `question` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `category`, `difficulty`, `description`, `flag`, `solves`, `question`) VALUES
(1, 'Simple SQLi', 'Web Exploitation', 'Easy', 'Bypass the login using SQL injection.', 'flag{sql_works}', 35, NULL),
(2, 'Steal the Session', 'Web Exploitation', 'Medium', NULL, NULL, 0, NULL),
(3, 'Admin Bypass', 'Web Exploitation', 'Hard', NULL, NULL, 0, NULL),
(4, 'CSRF Token Bypass', 'Web Exploitation', 'Easy', 'Craft a POST request without CSRF token.', 'cybersite{csrf_token_not_verified}', 5, 'The site uses CSRF protection. Can you find a way to submit a request without the token?'),
(5, 'Guess the ID', 'Web Exploitation', 'Easy', 'Use IDOR to access unauthorized data.', 'cybersite{predictable_ids_are_dangerous}', 94, 'User IDs are numeric and predictable. Can you access someone else\'s profile?'),
(6, 'Upload Madness', 'Web Exploitation', 'Easy', 'Upload a web shell bypassing file validation.', 'cybersite{webshell_uploaded}', 63, 'You can upload files, but not all types. Can you upload something malicious?'),
(7, 'Robot Spy', 'Web Exploitation', 'Easy', 'Access hidden directory listed in robots.txt.', 'cybersite{robots_txt_reveals_secret}', 74, 'Robots.txt isn’t meant for your eyes... but maybe it hides something interesting?'),
(8, 'View Source', 'Web Exploitation', 'Easy', 'Reveal flag in source code comment.', 'cybersite{always_view_source}', 87, 'Everything looks normal — but have you checked the source code of the page?'),
(9, 'Hidden Files', 'Web Exploitation', 'Easy', 'Access `.git/` or backup files for flag.', 'cybersite{.git_exposed}', 54, 'There might be leftover developer files or backups. Can you find and exploit them?'),
(10, 'Include Me', 'Web Exploitation', 'Easy', 'Exploit LFI to read sensitive files.', 'cybersite{lfi_pwned}', 96, 'Can you trick the site into loading local files on the server? (LFI Challenge)'),
(11, 'Auth Skip', 'Web Exploitation', 'Easy', 'Bypass login using SQL logic flaw.', 'cybersite{bypass_logic_flaw}', 250, 'Bypass the login logic using a simple trick — no credentials needed.'),
(12, 'JavaScript Ninja', 'Web Exploitation', 'Easy', 'Use browser dev tools to find JS variable.', 'cybersite{js_debugger_reveals_flag}', 123, 'Inspect the browser console and debug tools — maybe there\'s something hiding in JS?');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `category_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `level_name` varchar(255) NOT NULL,
  `level_description` text DEFAULT NULL,
  `required_score` int(11) DEFAULT 40,
  `questions_count` int(11) DEFAULT 5,
  `unlock_previous_level` int(11) DEFAULT NULL,
  `badge_icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`category_id`, `level_id`, `level_name`, `level_description`, `required_score`, `questions_count`, `unlock_previous_level`, `badge_icon`, `created_at`) VALUES
(1, 1, 'Beginner', 'Introduction to basic concepts.', 40, 5, NULL, 'beginner.png', '2025-06-29 05:49:06'),
(1, 2, 'Intermediate', 'Intermediate level questions.', 50, 7, 1, 'intermediate.png', '2025-06-29 05:49:06'),
(1, 3, 'Advanced', 'Challenging questions for advanced users.', 60, 10, 2, 'advanced.png', '2025-06-29 05:49:06'),
(2, 1, 'Beginner', 'Introduction to basic concepts.', 40, 5, NULL, 'beginner.png', '2025-07-09 22:00:36'),
(2, 2, 'Intermediate', 'Intermediate level questions.', 50, 7, 1, 'intermediate.png', '2025-07-09 22:00:36'),
(2, 3, 'Advanced', 'Challenging questions for advanced users.', 60, 10, 2, 'advanced.png', '2025-07-09 22:00:36');

-- --------------------------------------------------------

--
-- Table structure for table `my_crypto_questions`
--

CREATE TABLE `my_crypto_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ','ShortAnswer','LongAnswer') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `difficulty` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `my_crypto_questions`
--

INSERT INTO `my_crypto_questions` (`question_id`, `question_text`, `description`, `question_type`, `options`, `correct_answer`, `difficulty`) VALUES
(101, 'Decrypt this Caesar cipher (shift 3): Fdhvdu Flskhu lv vlpsoh.', NULL, 'ShortAnswer', NULL, 'CS{Caesar_Cipher_is_simple}', 'Beginner'),
(102, 'XOR the following hex strings: A1B2C3 XOR 1F2F3F = ? (Answer in uppercase hex without 0x)', NULL, 'ShortAnswer', NULL, 'CS{BEDDFC}', 'Intermediate'),
(103, 'What cipher replaces each letter with another letter a fixed number of positions away in the alphabet?', NULL, 'ShortAnswer', NULL, 'CS{Caesar_Cipher}', 'Beginner'),
(104, 'Which hashing algorithm outputs a 128-bit hash and starts with the characters \"MD\"?', NULL, 'ShortAnswer', NULL, 'CS{MD5}', 'Intermediate'),
(105, 'Given: p = 17, q = 11, find the modulus n = p * q.', NULL, 'ShortAnswer', NULL, 'CS{187}', 'Advanced'),
(106, 'What is the output of SHA256(\"hello\") in hex (first 6 chars only)?', 'Basic hashing test.', 'ShortAnswer', NULL, 'CS{2cf24d}', 'Intermediate'),
(107, 'Which classical cipher uses keyword-based shifting in a repeated manner?', NULL, 'ShortAnswer', NULL, 'CS{Vigenere}', 'Intermediate'),
(108, 'RSA uses which two keys for encryption and decryption?', NULL, 'MCQ', '{\"Option1\":\"Private and Public\",\"Option2\":\"Symmetric and Asymmetric\",\"Option3\":\"Hash and Salt\",\"Option4\":\"Prime and Composite\"}', 'CS{Private and Public}', 'Beginner'),
(109, 'Base64 decode this string: SGVsbG8gV29ybGQh', NULL, 'ShortAnswer', NULL, 'CS{Hello World!}', 'Beginner'),
(110, 'What does the \"E\" in AES stand for?', NULL, 'MCQ', '{\"Option1\":\"Encryption\",\"Option2\":\"Electronic\",\"Option3\":\"Essential\",\"Option4\":\"Efficient\"}', 'CS{Encryption}', 'Beginner');

-- --------------------------------------------------------

--
-- Table structure for table `my_forensics_questions`
--

CREATE TABLE `my_forensics_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ','ShortAnswer','LongAnswer') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `difficulty` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `my_forensics_questions`
--

INSERT INTO `my_forensics_questions` (`question_id`, `question_text`, `description`, `question_type`, `options`, `correct_answer`, `difficulty`) VALUES
(201, 'Which file signature corresponds to a PNG file?', NULL, 'ShortAnswer', NULL, 'CS{89504E47}', 'Beginner'),
(202, 'Which of the following timestamp types is affected when a file is opened?', NULL, 'MCQ', '{\"Option1\":\"Created\",\"Option2\":\"Modified\",\"Option3\":\"Accessed\",\"Option4\":\"Changed\"}', 'CS{Accessed}', 'Intermediate'),
(203, 'In Windows, which registry key stores info about USB devices?', NULL, 'ShortAnswer', NULL, 'CS{SYSTEM\\\\CurrentControlSet\\\\Enum\\\\USBSTOR}', 'Intermediate'),
(204, 'What memory analysis tool is commonly used with memory dumps and uses plugins like pslist, dlllist, and malfind?', NULL, 'ShortAnswer', NULL, 'CS{Volatility}', 'Beginner'),
(205, 'What header field in an email helps identify the original sender’s IP address?', NULL, 'ShortAnswer', NULL, 'CS{Received}', 'Advanced'),
(206, 'Which tool allows you to carve deleted files from disk images?', NULL, 'MCQ', '{\"Option1\":\"Autopsy\",\"Option2\":\"FTK Imager\",\"Option3\":\"Wireshark\",\"Option4\":\"IDA Pro\"}', 'CS{Autopsy}', 'Intermediate'),
(207, 'What type of file typically contains email evidence in forensic analysis?', NULL, 'MCQ', '{\"Option1\":\"PST\",\"Option2\":\"PCAP\",\"Option3\":\"DLL\",\"Option4\":\"MP4\"}', 'CS{PST}', 'Intermediate'),
(208, 'Which artifact would most likely show recent USB connections?', NULL, 'ShortAnswer', NULL, 'CS{Setupapi.dev.log}', 'Advanced'),
(209, 'You find a suspicious process in memory named svch0st.exe. What should be your first action?', NULL, 'MCQ', '{\"Option1\":\"Ignore it\",\"Option2\":\"Kill process\",\"Option3\":\"Verify process path\",\"Option4\":\"Restart system\"}', 'CS{Verify process path}', 'Advanced'),
(210, 'Which Windows file keeps track of recently opened files and apps?', NULL, 'ShortAnswer', NULL, 'CS{NTUSER.DAT}', 'Intermediate');

-- --------------------------------------------------------

--
-- Table structure for table `powerups`
--

CREATE TABLE `powerups` (
  `powerup_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `powerups`
--

INSERT INTO `powerups` (`powerup_id`, `name`, `description`, `cost`) VALUES
(1, '50/50', 'Removes two incorrect options from a multiple-choice question.', 20),
(2, 'Second Chance', 'Allows another attempt after a wrong answer.', 30),
(3, 'Skip Question', 'Lets you skip a difficult question.', 15),
(4, 'Time Freeze', 'Pauses the timer for 10 seconds.', 25),
(5, 'Reveal Hint', 'Shows a helpful hint for the current question.', 10);

-- --------------------------------------------------------

--
-- Table structure for table `python`
--

CREATE TABLE `python` (
  `python_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `python`
--

INSERT INTO `python` (`python_id`, `topic`, `content`, `example`, `question`, `answer`) VALUES
(1, 'Strings', 'Strings are defined by enclosing the sequence of characters within either single quotes, double quotes, or triple quotes in a print() function.', 'print(\"I love python!\")', 'Print a string.', ''),
(2, 'Variables', 'Variables are symbolic names that act as containers for storing data values. Variables are created the moment you first assign a value to them using the assignment operator (=).', 'age = 30\r\nname = \"John\"', 'Assign variable \"x\" a value and print it out.', ''),
(3, 'Conditions', 'Python supports the usual logical conditions from mathematics. \r\nEquals: a == b\r\nNot Equals: a != b\r\nLess than: a < b\r\nLess than or equal to: a <= b\r\nGreater than: a > b\r\nGreater than or equal to: a >= b\r\nThese conditions can be used in several ways, most commonly in \"if statements\" and loops.', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `python_subtopics`
--

CREATE TABLE `python_subtopics` (
  `subtopic_id` int(11) NOT NULL,
  `python_id` int(11) NOT NULL,
  `subtopic_title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `python_subtopics`
--

INSERT INTO `python_subtopics` (`subtopic_id`, `python_id`, `subtopic_title`, `content`, `example`, `question`, `answer`) VALUES
(1, 1, 'String Concatenation', 'To concatenate, or combine, two strings you can use the + operator.', 'Merge variable a with variable b into variable c:\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + b\r\nprint(c)\r\n\r\nTo add a space between them, add a \" \":\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + \" \" + b\r\nprint(c)', 'Using string concatenation, assign 4 variables and print \"I Love Python!\".', ''),
(2, 2, 'Variable Names', 'A variable can have a short name (like x and y) or a more descriptive name (age, carname, total_volume). \r\nRules for Python variables:\r\n-A variable name must start with a letter or the underscore character\r\n-A variable name cannot start with a number\r\n-A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _ )\r\n-Variable names are case-sensitive (age, Age and AGE are three different variables)\r\n-A variable name cannot be any of the Python keywords', 'Legal variable names:\r\nmyvar = \"John\"\r\nmy_var = \"John\"\r\nmyVar = \"John\"\r\nMYVAR = \"John\"\r\nmyvar2 = \"John\"\r\n\r\nIllegal variable names:\r\n2myvar = \"John\"\r\nmy-var = \"John\"\r\nmy var = \"John\"', '', ''),
(3, 2, 'Assign Multiple Values', 'Python allows you to assign values to multiple variables in one line.', 'x, y, z = \"Orange\", \"Banana\", \"Cherry\"\r\nprint(x)\r\nprint(y)\r\nprint(z)', '', ''),
(4, 3, 'If statements', 'An \"if statement\" is written by using the \"if\" keyword.', 'a = 33\r\nb = 200\r\nif b > a:\r\n   print(\"b is greater than a\")', '', ''),
(5, 3, 'Indentation', 'Python relies on indentation (whitespace at the beginning of a line) to define scope in the code. Other programming languages often use curly-brackets for this purpose.\r\nTo add indentation, you can press \"tab\" on your keyboard, or simply press the space bar three times. Indentation is normally added automatically upon pressing enter to proceed to the next line when writing \"if\" statements.', 'If statement, without indentation (will raise an error):\r\na = 33\r\nb = 200\r\nif b > a:\r\nprint(\"b is greater than a\") # you will get an error', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT 1,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question_text`, `description`, `question_type`, `options`, `correct_answer`, `level_id`, `category_id`, `is_completed`) VALUES
(75, 'What is Cyber Security?', NULL, 'MCQ', '[\"Cyber Security provides security against malware\", \"Cyber Security provides security against cyber-terrorists\", \"Cyber Security protects a system from cyber attacks\", \"All of the mentioned\"]', 'All of the mentioned', 1, 1, 0),
(76, 'What does cyber security protect?', NULL, 'MCQ', '[\"Cyber security protects criminals\", \"Cyber security protects internet-connected systems\", \"Cyber security protects hackers\", \"None of the mentioned\"]', 'Cyber security protects internet-connected systems', 1, 1, 0),
(77, 'Which of the following is defined as an attempt to steal, spy, damage or destroy computer systems?', NULL, 'MCQ', '[\"Cyber attack\", \"Computer security\", \"Cryptography\", \"Digital hacking\"]', 'Cyber attack', 1, 1, 0),
(78, 'What is the weakest link in cybersecurity?', NULL, 'MCQ', '[\"Weak encryption\", \"Humans\", \"Short passwords\", \"Firewalls\"]', 'Humans', 1, 1, 0),
(79, 'Which of the following is a type of cyber security?', NULL, 'MCQ', '[\"Cloud Security\", \"Network Security\", \"Application Security\", \"All of the above\"]', 'All of the above', 1, 1, 0),
(80, 'What does the term \"firewall\" refer to in cyber security?', NULL, 'MCQ', '[\"A protective suit worn by cybersecurity professionals\", \"A physical barrier around computer servers\", \"A security system that monitors and controls network traffic\", \"Software for preventing computer overheating\"]', 'A security system that monitors and controls network traffic', 1, 1, 0),
(81, 'What is the primary function of a firewall?', NULL, 'MCQ', '[\"To speed up internet connection\", \"To monitor and control network traffic\", \"To store passwords\", \"To create backups\"]', 'To monitor and control network traffic', 1, 1, 0),
(82, 'Which port is commonly used for HTTPS?', NULL, 'MCQ', '[\"80\", \"443\", \"8080\", \"3389\"]', '443', 1, 1, 0),
(83, 'What does VPN stand for?', NULL, 'MCQ', '[\"Virtual Private Network\", \"Very Private Network\", \"Virtual Public Network\", \"Verified Private Network\"]', 'Virtual Private Network', 1, 1, 0),
(84, 'What is malware?', NULL, 'MCQ', '[\"Good software\", \"Malicious software designed to harm computers\", \"A type of hardware\", \"A network protocol\"]', 'Malicious software designed to harm computers', 1, 1, 0),
(85, 'What does AES stand for?', NULL, 'MCQ', '[\"Automated Encryption Strength\", \"Advanced Encryption Standard\", \"Algorithm Encrypted System\", \"Advanced Encryption Solution\"]', 'Advanced Encryption Standard', 1, 1, 0),
(86, 'When is it ok to reuse a password?', NULL, 'MCQ', '[\"When you trust the website\", \"For unimportant accounts\", \"Never\", \"Once a year\"]', 'Never', 1, 1, 0),
(87, 'What is the purpose of a CAPTCHA in online security?', NULL, 'MCQ', '[\"Detecting malware\", \"Authenticating users\", \"Preventing automated bots\", \"Encrypting data\"]', 'Preventing automated bots', 1, 1, 0),
(88, 'What is two-factor authentication (2FA)?', NULL, 'MCQ', '[\"Using two passwords\", \"Using two devices\", \"Using two different methods to verify identity\", \"Using two usernames\"]', 'Using two different methods to verify identity', 1, 1, 0),
(89, 'What type of software infects a machine, locks the files, then asks for money?', NULL, 'MCQ', '[\"Worm\", \"Trojan\", \"Ransomware\", \"Browser Hijacker\"]', 'Ransomware', 1, 1, 0),
(90, 'Which protocol provides secure file transfer over SSH?', NULL, 'MCQ', '[\"FTP\",\"FTPS\",\"SFTP\",\"TFTP\"]', 'SFTP', 2, 1, 0),
(91, 'What port does SSH listen on by default?', NULL, 'MCQ', '[\"20\",\"22\",\"23\",\"443\"]', '22', 2, 1, 0),
(92, 'Which AES mode provides both confidentiality and integrity?', NULL, 'MCQ', '[\"CBC\",\"ECB\",\"GCM\",\"OFB\"]', 'GCM', 2, 1, 0),
(93, 'Which of the following Nmap options enables OS detection?', NULL, 'MCQ', '[\"-sV\",\"-O\",\"-A\",\"-sS\"]', '-O', 2, 1, 0),
(94, 'Which of the following best describes Fully Homomorphic Encryption (FHE)?', NULL, 'MCQ', '[\"Encryption that supports only addition on ciphertexts\",\"Encryption that supports either addition or multiplication but not both\",\"Encryption that allows arbitrary computation on encrypted data without decryption\",\"A symmetric cipher hardened against quantum attacks\"]', 'Encryption that allows arbitrary computation on encrypted data without decryption', 3, 1, 0),
(95, 'In a Zero Trust network, which principle is fundamental?', NULL, 'MCQ', '[\"Implicit trust for internal network traffic\",\"Verification of every user or device, regardless of location\",\"Relying on perimeter firewalls as the primary defense\",\"Allowing lateral movement once inside the network\"]', 'Verification of every user or device, regardless of location', 3, 1, 0),
(96, 'An attacker crafts inputs to bypass an ML-based intrusion detection system. This is known as:', NULL, 'MCQ', '[\"Data poisoning\",\"Model inversion\",\"Evasion attack\",\"Membership inference\"]', 'Evasion attack', 3, 1, 0),
(97, 'Which protocol is commonly used to securely connect to a remote server?', NULL, 'MCQ', '[\"HTTP\",\"FTP\",\"SSH\",\"Telnet\"]', 'SSH', 2, 1, 0),
(98, 'What is the main purpose of a firewall in a network?', NULL, 'MCQ', '[\"To store data\",\"To block unauthorized access\",\"To speed up the network\",\"To manage passwords\"]', 'To block unauthorized access', 2, 1, 0),
(99, 'Which of the following is considered a strong password?', NULL, 'MCQ', '[\"password123\",\"qwerty\",\"P@ssw0rd!\",\"123456\"]', 'P@ssw0rd!', 2, 1, 0),
(100, 'What is phishing primarily designed to do?', NULL, 'MCQ', '[\"Steal sensitive information\",\"Speed up computers\",\"Fix software bugs\",\"Enhance graphics\"]', 'Steal sensitive information', 2, 1, 0),
(101, 'Which type of malware is designed to replicate itself and spread to others?', NULL, 'MCQ', '[\"Worm\",\"Adware\",\"Spyware\",\"Ransomware\"]', 'Worm', 2, 1, 0),
(102, 'What is the primary function of a Security Information and Event Management (SIEM) system?', NULL, 'MCQ', '[\"Encrypt data\",\"Monitor and analyze security events\",\"Scan for viruses\",\"Manage user passwords\"]', 'Monitor and analyze security events', 3, 1, 0),
(103, 'Which attack exploits the trust between two communicating parties by intercepting traffic?', NULL, 'MCQ', '[\"Phishing\",\"Man-in-the-middle\",\"Denial of Service\",\"SQL Injection\"]', 'Man-in-the-middle', 3, 1, 0),
(104, 'What is the main goal of a penetration test?', NULL, 'MCQ', '[\"To fix software bugs\",\"To evaluate security by simulating attacks\",\"To increase bandwidth\",\"To backup data\"]', 'To evaluate security by simulating attacks', 3, 1, 0),
(105, 'Which protocol adds security to DNS queries?', NULL, 'MCQ', '[\"DNSSEC\",\"HTTPS\",\"SSL\",\"SFTP\"]', 'DNSSEC', 3, 1, 0),
(106, 'In cybersecurity, what does the principle of “least privilege” mean?', NULL, 'MCQ', '[\"Granting all users admin rights\",\"Providing minimum access necessary\",\"Allowing guest access\",\"Disabling authentication\"]', 'Providing minimum access necessary', 3, 1, 0),
(107, 'What is a common method for detecting anomalies in network traffic?', NULL, 'MCQ', '[\"Manual inspection\",\"Signature-based detection\",\"Anomaly-based detection\",\"Password cracking\"]', 'Anomaly-based detection', 3, 1, 0),
(108, 'Which type of cyber attack involves overwhelming a system with traffic to make it unavailable?', NULL, 'MCQ', '[\"Phishing\",\"Denial of Service\",\"Rootkit\",\"Keylogger\"]', 'Denial of Service', 3, 1, 0),
(109, 'What is the primary goal of digital forensics?', NULL, 'MCQ', '[\"To recover deleted files\",\"To analyze digital evidence\",\"To create malware\",\"To hack systems\"]', 'To analyze digital evidence', 1, 2, 0),
(110, 'Which device is commonly used to capture network traffic for forensic analysis?', NULL, 'MCQ', '[\"Router\",\"Switch\",\"Packet sniffer\",\"Firewall\"]', 'Packet sniffer', 1, 2, 0),
(111, 'What does the term \'chain of custody\' refer to in forensics?', NULL, 'MCQ', '[\"Sequence of evidence handling\",\"Type of malware\",\"Encryption method\",\"File recovery technique\"]', 'Sequence of evidence handling', 1, 2, 0),
(112, 'Which file system is often analyzed in digital forensics?', NULL, 'MCQ', '[\"NTFS\",\"FAT32\",\"EXT4\",\"All of the above\"]', 'All of the above', 1, 2, 0),
(113, 'What is the first step in a digital forensic investigation?', NULL, 'MCQ', '[\"Data analysis\",\"Evidence collection\",\"Reporting\",\"System shutdown\"]', 'Evidence collection', 1, 2, 0),
(114, 'Which tool is commonly used for disk imaging in forensics?', NULL, 'MCQ', '[\"Wireshark\",\"FTK Imager\",\"Nmap\",\"Metasploit\"]', 'FTK Imager', 2, 2, 0),
(115, 'What is the purpose of hashing in digital forensics?', NULL, 'MCQ', '[\"To encrypt data\",\"To verify data integrity\",\"To delete files\",\"To speed up analysis\"]', 'To verify data integrity', 2, 2, 0),
(116, 'Which type of evidence is considered volatile?', NULL, 'MCQ', '[\"Hard drive data\",\"RAM data\",\"Printed documents\",\"Emails\"]', 'RAM data', 2, 2, 0),
(117, 'What does the term \'live forensics\' mean?', NULL, 'MCQ', '[\"Analyzing data on a running system\",\"Analyzing deleted files\",\"Analyzing network traffic\",\"Analyzing backups\"]', 'Analyzing data on a running system', 2, 2, 0),
(118, 'Which file format is commonly used to store forensic images?', NULL, 'MCQ', '[\".exe\",\".dd\",\".txt\",\".jpg\"]', '.dd', 2, 2, 0),
(119, 'What is the role of metadata in digital forensics?', NULL, 'MCQ', '[\"To hide files\",\"To provide information about data\",\"To encrypt files\",\"To delete data\"]', 'To provide information about data', 2, 2, 0),
(120, 'Which law enforcement agency often handles digital forensic investigations in the US?', NULL, 'MCQ', '[\"FBI\",\"CIA\",\"NSA\",\"DEA\"]', 'FBI', 2, 2, 0),
(121, 'What is the difference between static and live forensics?', NULL, 'MCQ', '[\"Static analyzes powered-off systems, live analyzes running systems\",\"Static analyzes running systems, live analyzes powered-off systems\",\"Both analyze powered-off systems\",\"Both analyze running systems\"]', 'Static analyzes powered-off systems, live analyzes running systems', 3, 2, 0),
(122, 'Which forensic tool is used for memory analysis?', NULL, 'MCQ', '[\"Volatility\",\"Wireshark\",\"Nmap\",\"FTK Imager\"]', 'Volatility', 3, 2, 0),
(123, 'What is steganography in the context of digital forensics?', NULL, 'MCQ', '[\"Hiding data within other files\",\"Encrypting data\",\"Deleting data\",\"Backing up data\"]', 'Hiding data within other files', 3, 2, 0),
(124, 'Which technique is used to recover deleted files?', NULL, 'MCQ', '[\"File carving\",\"Encryption\",\"Hashing\",\"Compression\"]', 'File carving', 3, 2, 0),
(125, 'What is the significance of the write blocker in forensic investigations?', NULL, 'MCQ', '[\"Prevents modification of evidence\",\"Speeds up data transfer\",\"Encrypts data\",\"Deletes malware\"]', 'Prevents modification of evidence', 3, 2, 0),
(126, 'Which forensic process involves analyzing network traffic to detect intrusions?', NULL, 'MCQ', '[\"Network forensics\",\"Disk imaging\",\"File carving\",\"Memory analysis\"]', 'Network forensics', 3, 2, 0),
(127, 'What is the role of the hash value in verifying forensic evidence?', NULL, 'MCQ', '[\"To speed up analysis\",\"To uniquely identify data\",\"To encrypt data\",\"To delete data\"]', 'To uniquely identify data', 3, 2, 0),
(128, 'Which type of forensic analysis focuses on recovering data from mobile devices?', NULL, 'MCQ', '[\"Mobile forensics\",\"Network forensics\",\"Memory forensics\",\"Disk forensics\"]', 'Mobile forensics', 3, 2, 0),
(129, 'What is the primary challenge in cloud forensics?', NULL, 'MCQ', '[\"Data volatility\",\"Data encryption\",\"Data jurisdiction\",\"All of the above\"]', 'All of the above', 3, 2, 0),
(130, 'Which standard is commonly followed for forensic soundness?', NULL, 'MCQ', '[\"ISO/IEC 27037\",\"ISO 9001\",\"NIST SP 800-53\",\"GDPR\"]', 'ISO/IEC 27037', 3, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `solves`
--

CREATE TABLE `solves` (
  `user_id` int(11) DEFAULT NULL,
  `challenge_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(8) NOT NULL,
  `user_role` enum('student','admin') DEFAULT 'student',
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `user_role`, `password_hash`, `reset_token`, `reset_token_expiration`) VALUES
(1, 'admin101', 'admin@gmail.com', '98769876', 'admin', '$2y$10$PQTTnPSVX5V.JthPPzDqm.TIH8O/pVSrXgfQSDAnSIW.6zSh5UZoe', NULL, NULL),
(2, 'student', 'student@gmail.com', '90909090', 'student', '$2y$10$/LW2s5lFwBt47efdbQL.Wuo9ctTErw0DQLnSoZQ5UtxlnQ/HyxmbS', NULL, NULL),
(8, 'xuan', 'chiashuxuan76@gmail.com', '98989898', 'student', '$2y$10$jdl6EBOsR782eXgrIzdFOe8ywvAWvzptAe6.pilh1TKfbJ6go26GW', NULL, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `insert_user_points_after_user` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_points (user_id, points)
    VALUES (NEW.user_id, 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users1`
--

CREATE TABLE `users1` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `flag` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users1`
--

INSERT INTO `users1` (`id`, `username`, `email`, `flag`) VALUES
(1, 'admin', 'admin@cybersite.local', 'cybersite{predictable_ids_are_dangerous}'),
(2, 'user1', 'user1@cybersite.local', NULL),
(3, 'user2', 'user2@cybersite.local', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

CREATE TABLE `user_answers` (
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `question_id` int(11) DEFAULT NULL,
  `selected_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_answers`
--

INSERT INTO `user_answers` (`answer_id`, `user_id`, `question_id`, `selected_answer`, `is_correct`, `points_earned`, `answered_at`) VALUES
(1, 1, 1, 'A', 1, 10, '2025-06-29 05:49:06'),
(2, 1, 2, 'C', 0, 0, '2025-06-29 05:49:06'),
(3, 2, 1, 'B', 0, 0, '2025-06-29 05:49:06'),
(4, 2, 3, 'D', 1, 10, '2025-06-29 05:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`user_id`, `points`) VALUES
(2, 25),
(3, 60),
(4, 275),
(5, 30),
(6, 0),
(7, 0),
(8, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_powerups`
--

CREATE TABLE `user_powerups` (
  `user_id` int(11) NOT NULL,
  `powerup_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_powerups`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `level_id` int(11) DEFAULT NULL,
  `current_score` int(11) DEFAULT 0,
  `questions_answered` int(11) DEFAULT 0,
  `questions_correct` int(11) DEFAULT 0,
  `level_completed` tinyint(1) DEFAULT 0,
  `completion_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`progress_id`, `user_id`, `level_id`, `current_score`, `questions_answered`, `questions_correct`, `level_completed`, `completion_time`, `created_at`) VALUES
(1, 1, 1, 45, 5, 4, 1, '2025-06-29 01:00:00', '2025-06-29 05:49:06'),
(2, 2, 1, 80, 5, 3, 0, '2025-06-29 01:28:01', '2025-06-29 05:49:06'),
(3, 1, 2, 10, 2, 1, 0, NULL, '2025-06-29 05:49:06'),
(5, 2, 2, 71, 7, 5, 1, '2025-06-29 02:22:20', '2025-06-29 07:28:37'),
(7, 3, 1, 80, 5, 3, 1, '2025-07-03 01:04:00', '2025-07-03 07:04:00'),
(8, 3, 2, 100, 7, 1, 1, NULL, '2025-07-03 07:04:45'),
(34, 3, 4, 100, 5, 5, 1, '2025-07-09 16:08:08', '2025-07-09 22:08:08'),
(36, 2, 4, 100, 5, 5, 1, '2025-07-10 04:17:32', '2025-07-10 10:17:32'),
(37, 2, 5, 100, 5, 5, 1, '2025-07-10 04:17:59', '2025-07-10 10:17:59'),
(44, 3, 5, 100, 5, 5, 1, '2025-07-10 14:25:35', '2025-07-10 20:25:35'),
(45, 5, 1, 100, 5, 5, 1, '2025-07-15 03:04:46', '2025-07-15 09:04:46'),
(46, 5, 4, 100, 5, 0, 1, NULL, '2025-07-17 08:38:07'),
(48, 4, 4, 100, 5, 5, 1, '2025-07-22 20:21:50', '2025-07-23 02:21:50'),
(50, 4, 5, 100, 5, 5, 1, '2025-07-22 20:55:41', '2025-07-23 02:55:41'),
(51, 4, 6, 100, 6, 6, 1, '2025-07-22 20:56:14', '2025-07-23 02:56:14'),
(52, 4, 1, 80, 5, 4, 1, '2025-07-22 20:57:09', '2025-07-23 02:57:09'),
(53, 4, 2, 86, 7, 6, 1, '2025-07-23 03:45:40', '2025-07-23 09:45:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`category_id`,`level_id`);

--
-- Indexes for table `my_crypto_questions`
--
ALTER TABLE `my_crypto_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `my_forensics_questions`
--
ALTER TABLE `my_forensics_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `powerups`
--
ALTER TABLE `powerups`
  ADD PRIMARY KEY (`powerup_id`);

--
-- Indexes for table `python`
--
ALTER TABLE `python`
  ADD PRIMARY KEY (`python_id`);

--
-- Indexes for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  ADD PRIMARY KEY (`subtopic_id`),
  ADD KEY `python_id` (`python_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `solves`
--
ALTER TABLE `solves`
  ADD UNIQUE KEY `user_id` (`user_id`,`challenge_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `users1`
--
ALTER TABLE `users1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`answer_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_powerups`
--
ALTER TABLE `user_powerups`
  ADD PRIMARY KEY (`user_id`,`powerup_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_user_level` (`user_id`,`level_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `my_crypto_questions`
--
ALTER TABLE `my_crypto_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `my_forensics_questions`
--
ALTER TABLE `my_forensics_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `powerups`
--
ALTER TABLE `powerups`
  MODIFY `powerup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `python`
--
ALTER TABLE `python`
  MODIFY `python_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  MODIFY `subtopic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  ADD CONSTRAINT `python_subtopics_ibfk_1` FOREIGN KEY (`python_id`) REFERENCES `python` (`python_id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
