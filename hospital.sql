-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 02:48 PM
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
-- Database: `hospital`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_type`
--

CREATE TABLE `account_type` (
  `id` int(11) NOT NULL,
  `typecode` varchar(20) NOT NULL,
  `typename` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_type`
--

INSERT INTO `account_type` (`id`, `typecode`, `typename`) VALUES
(1, '1', 'INCOME ACCOUNT'),
(2, '2', 'EXPENSE ACCOUNT'),
(3, '7', 'BANK ACCOUNT');

-- --------------------------------------------------------

--
-- Table structure for table `campus_locations`
--

CREATE TABLE `campus_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campus_locations`
--

INSERT INTO `campus_locations` (`id`, `name`) VALUES
(3, 'Ayetoro Health Centre'),
(2, 'Ibogun Campus Health Centre'),
(4, 'Main Campus Health Centre'),
(5, 'Main store'),
(1, 'Mini Campus Health Centre');

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

CREATE TABLE `card` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `card`
--

INSERT INTO `card` (`id`, `name`, `amount`) VALUES
(1, 'INDIVIDUAL CARD', '1200'),
(2, 'FAMILY CARD', '2500'),
(3, 'COMPANY CARD', '5000'),
(4, 'STAFF CARD', '1000'),
(5, 'STUDENT CARD', '1000'),
(19, 'CHOOSE CARD', '0');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `details` varchar(100) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `officer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_pay`
--

CREATE TABLE `cart_pay` (
  `id` int(11) NOT NULL,
  `date` varchar(200) NOT NULL,
  `bank` varchar(50) NOT NULL,
  `account` varchar(20) NOT NULL,
  `amount` varchar(100) NOT NULL,
  `mode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashbook`
--

CREATE TABLE `cashbook` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `transcode` varchar(100) NOT NULL,
  `time` varchar(100) NOT NULL,
  `details` varchar(100) NOT NULL,
  `cash` varchar(100) NOT NULL,
  `bank` varchar(100) NOT NULL,
  `cashspent` varchar(100) NOT NULL,
  `cashbank` varchar(100) NOT NULL,
  `officer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashbook`
--

INSERT INTO `cashbook` (`id`, `date`, `transcode`, `time`, `details`, `cash`, `bank`, `cashspent`, `cashbank`, `officer`) VALUES
(421, '2025-07-11', '', '03:06 am', 'OLABANJI OLUWASEYI', '', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(422, '2025-07-11', '', '03:06 am', 'OLABANJI OLUWASEYI', '', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(423, '2025-07-11', 'INV4373', '03:12 am', 'OLABANJI OLUWASEYI', '', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(424, '2025-07-11', 'INV4373', '03:12 am', 'OLABANJI OLUWASEYI', '', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(425, '2025-07-24', 'INV2147', '08:51 pm', '', '', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(426, '2025-07-28', 'Ph7765', '02:30 am', 'OLADAYO IDUNNU', '2,000.00', '0', '0', '0', 'OLABANJI OLUWASEUN'),
(427, '2025-09-26', 'IND44577', '04:29 pm', 'OLATUNBOSUN OYINLOLA MICHAEL', '', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE'),
(428, '2025-09-26', 'IND44577', '04:29 pm', 'OLATUNBOSUN OYINLOLA MICHAEL', '', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE'),
(429, '2025-10-22', 'S4310', '12:14 pm', 'Adeyemi  Nurudeen Adewale', '480.00', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE'),
(430, '2025-10-22', 'IND44577', '12:24 pm', 'OLATUNBOSUN OYINLOLA MICHAEL', '160.00', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE'),
(431, '2025-10-22', 'IND44577', '12:24 pm', 'OLATUNBOSUN OYINLOLA MICHAEL', '160.00', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE'),
(432, '2025-10-23', 'S4310', '11:51 am', 'Adeyemi  Nurudeen Adewale', '720.00', '0', '0', '0', 'ADEYEMI NURUDEEN ADEWALE');

-- --------------------------------------------------------

--
-- Table structure for table `chart`
--

CREATE TABLE `chart` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `typecode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart`
--

INSERT INTO `chart` (`id`, `code`, `name`, `typecode`) VALUES
(1, '0007999979', 'PETTY CASH', '7'),
(2, '0007999975', 'STANBIC IBTC', '7'),
(3, '0003553565', 'STERLING BANK', '7'),
(4, '0024458796', 'GTB BANK', '7'),
(5, '03033554525', 'ZENITH BANK', '7'),
(6, '8067498518', 'OPAY ACCOUNT', '7');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `regdate` date NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `regdate`, `code`, `name`) VALUES
(6, '2025-04-11', 'C63825', 'FCMB'),
(7, '2025-04-11', 'C65899', 'ZENITH BANK'),
(8, '2025-04-11', 'C63981', 'DANGOTE CEMENT');

-- --------------------------------------------------------

--
-- Table structure for table `company_individual`
--

CREATE TABLE `company_individual` (
  `id` int(11) NOT NULL,
  `fcode` varchar(6) NOT NULL,
  `Surname` varchar(100) NOT NULL,
  `Firstname` varchar(100) NOT NULL,
  `Lastname` varchar(100) NOT NULL,
  `reg_date` date NOT NULL,
  `code` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `age` varchar(20) NOT NULL,
  `address` varchar(100) NOT NULL,
  `marrital` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nok` varchar(100) NOT NULL,
  `nok_phone` varchar(20) NOT NULL,
  `picture` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_individual`
--

INSERT INTO `company_individual` (`id`, `fcode`, `Surname`, `Firstname`, `Lastname`, `reg_date`, `code`, `dob`, `age`, `address`, `marrital`, `phone`, `nok`, `nok_phone`, `picture`) VALUES
(2, 'C65899', 'MR ABIODUN', 'SHERIF', 'SEUN', '2025-04-11', 'C658996557', '1962-07-12', '56', 'OKANLAWON STREET JIBOLA ABEOKUTA', 'Married', '08077564465', 'MRS ADELABU', '0908876754', 'team-4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis`
--

CREATE TABLE `diagnosis` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosis`
--

INSERT INTO `diagnosis` (`id`, `name`, `amount`) VALUES
(1, 'COUGH', '100'),
(2, 'COLD', '100'),
(3, 'MALARIA', '0'),
(4, 'HYPERTENSION', '0'),
(5, 'TYPHOID', '5000');

-- --------------------------------------------------------

--
-- Table structure for table `doc_diagnosis`
--

CREATE TABLE `doc_diagnosis` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `doc_id` varchar(20) NOT NULL,
  `diagnosis` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_lab`
--

CREATE TABLE `doc_lab` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `doc_id` varchar(100) NOT NULL,
  `lab` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doc_lab`
--

INSERT INTO `doc_lab` (`id`, `date`, `doc_id`, `lab`) VALUES
(3, '2025-10-13', '', 'PCV'),
(4, '2025-10-15', '', 'MALARIA PARASITE'),
(5, '2025-10-22', '', 'MALARIA PARASITE,MALARIA PARASITE');

-- --------------------------------------------------------

--
-- Table structure for table `doc_procedure`
--

CREATE TABLE `doc_procedure` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `doc_id` varchar(20) NOT NULL,
  `procedures` varchar(300) NOT NULL,
  `Total` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_scanlab`
--

CREATE TABLE `doc_scanlab` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `doc_id` varchar(20) NOT NULL,
  `scanlab` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doc_scanlab`
--

INSERT INTO `doc_scanlab` (`id`, `date`, `doc_id`, `scanlab`) VALUES
(16, '2025-10-15', '', 'PRENANCY SCAN'),
(17, '2025-10-22', '', 'HEAD SCAN,HEAD SCAN');

-- --------------------------------------------------------

--
-- Table structure for table `drug`
--

CREATE TABLE `drug` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` varchar(10) NOT NULL,
  `amount` int(20) NOT NULL,
  `category` varchar(100) NOT NULL,
  `pharmacy_location_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drug`
--

INSERT INTO `drug` (`id`, `name`, `quantity`, `amount`, `category`, `pharmacy_location_id`) VALUES
(1, 'PARACETAMOL 500G', '260', 40, 'Tab', NULL),
(5, 'CHLOROQUINE', '400', 150, 'TAB', NULL),
(6, 'SHANTOX', '50', 100, 'Tab', NULL),
(13, 'AMOXILIN 500G', '200', 100, 'Tab', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drugchart`
--

CREATE TABLE `drugchart` (
  `id` int(10) NOT NULL,
  `patid` varchar(10) NOT NULL,
  `Fullname` varchar(200) NOT NULL,
  `session` varchar(20) NOT NULL,
  `date` varchar(20) NOT NULL,
  `drug` varchar(100) NOT NULL,
  `dose` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `comment` varchar(300) NOT NULL,
  `posted` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drugchart`
--

INSERT INTO `drugchart` (`id`, `patid`, `Fullname`, `session`, `date`, `drug`, `dose`, `time`, `comment`, `posted`) VALUES
(4, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Morning', '2025-06-15', 'PARACETAMOL', '500mg', '7:15AM', 'The Patient is Responding Speedily to Treatment', ''),
(5, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Morning', '2025-06-15', 'PARACETAMOL', '500mg', '7:15AM', 'The Patient is Responding Speedily to Treatment', ''),
(6, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Morning', '2025-06-15', 'PARACETAMOL', '500mg', '7:15AM', 'The Patient is Responding Speedily to Treatment', ''),
(7, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Morning', '2025-06-17', 'CHLOROQUEEN', '10ML', '9:45AM', 'GOOD RECOVERY', ' '),
(8, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Afternoon', '2025-06-17', 'PARACETAMOL', '500mg', '3PM', 'GOOD RECOVERY', 'CV6YOS');

-- --------------------------------------------------------

--
-- Table structure for table `drug_ayetoro`
--

CREATE TABLE `drug_ayetoro` (
  `id` int(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` varchar(10) DEFAULT NULL,
  `amount` int(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drug_ibogun`
--

CREATE TABLE `drug_ibogun` (
  `id` int(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` varchar(10) DEFAULT NULL,
  `amount` int(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drug_minicampus`
--

CREATE TABLE `drug_minicampus` (
  `id` int(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` varchar(10) DEFAULT NULL,
  `amount` int(20) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drug_prescription`
--

CREATE TABLE `drug_prescription` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `patid` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `drug` varchar(20) NOT NULL,
  `qnt` varchar(300) NOT NULL,
  `const` varchar(200) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `total` varchar(100) NOT NULL,
  `totdrug` varchar(100) NOT NULL,
  `amount` varchar(100) NOT NULL,
  `cate` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drug_prescription`
--

INSERT INTO `drug_prescription` (`id`, `date`, `patid`, `name`, `drug`, `qnt`, `const`, `duration`, `total`, `totdrug`, `amount`, `cate`) VALUES
(70, '2025-10-16', '', ';', 'SHANTOX', '1', '12hly', '1/7', '2', '2Tab', '200', 'Tab');

-- --------------------------------------------------------

--
-- Table structure for table `family`
--

CREATE TABLE `family` (
  `id` int(11) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `reg_date` datetime(6) NOT NULL,
  `code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family`
--

INSERT INTO `family` (`id`, `surname`, `firstname`, `middlename`, `reg_date`, `code`) VALUES
(1, 'OLABANJI', 'OLUWASEUN', 'ADENIJI', '2025-04-10 00:00:00.000000', 'F57054'),
(2, 'ALOYSIUS ', 'ABIODUN', 'OKE', '2025-04-10 00:00:00.000000', 'F56155'),
(3, 'ADEROLOYE', 'OLUWASEYI', 'MARGRET', '2025-04-09 00:00:00.000000', 'F56147'),
(4, 'AYANDEKO', 'FEMI', 'A', '2025-04-12 00:00:00.000000', 'F56574');

-- --------------------------------------------------------

--
-- Table structure for table `family_individual`
--

CREATE TABLE `family_individual` (
  `id` int(11) NOT NULL,
  `fcode` varchar(20) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `reg_date` date NOT NULL,
  `code` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `age` varchar(10) NOT NULL,
  `address` varchar(200) NOT NULL,
  `marital` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nok` varchar(50) NOT NULL,
  `nok_contact` varchar(20) NOT NULL,
  `picture` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_individual`
--

INSERT INTO `family_individual` (`id`, `fcode`, `surname`, `firstname`, `middlename`, `reg_date`, `code`, `dob`, `age`, `address`, `marital`, `phone`, `nok`, `nok_contact`, `picture`) VALUES
(1, 'F57054', 'OLAYIWOLA', 'MATHEW', 'JONES', '2025-04-10', 'F570545246', '2002-10-15', '38', '10 IYANA IPAJA BUS STOP LAGOS', 'Married', '08067498518', 'MRS ADETUTU', '09067498518', 'logo.jpg'),
(6, 'F53332', 'MR ABIODUN', 'SHERIF', 'SEUN', '2025-04-11', 'F533325226', '1982-11-25', '43', 'OKANLAWON STREET JIBOLA ABEOKUTA', 'Married', '08077564465', 'MRS ADELABU', '0908876754', 'team-1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `his_accounts`
--

CREATE TABLE `his_accounts` (
  `acc_id` int(200) NOT NULL,
  `acc_name` varchar(200) DEFAULT NULL,
  `acc_desc` text DEFAULT NULL,
  `acc_type` varchar(200) DEFAULT NULL,
  `acc_number` varchar(200) DEFAULT NULL,
  `acc_amount` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_accounts`
--

INSERT INTO `his_accounts` (`acc_id`, `acc_name`, `acc_desc`, `acc_type`, `acc_number`, `acc_amount`) VALUES
(1, 'Individual Retirement Account', '<p>IRA&rsquo;s are simply an account where you stash your money for retirement. The concept is pretty simple, your account balance is not taxed UNTIL you withdraw, at which point you pay the taxes there. This allows you to grow your account with interest without taxes taking away from the balance. The net result is you earn more money.</p>', 'Payable Account', '518703294', '25000'),
(2, 'Equity Bank', '<p>Find <em>bank account</em> stock <em>images</em> in HD and millions of other royalty-free stock photos, illustrations and vectors in the Shutterstock collection. Thousands of new</p>', 'Receivable Account', '753680912', '12566'),
(3, 'Test Account Name', '<p>This is a demo test</p>', 'Payable Account', '620157843', '1100');

-- --------------------------------------------------------

--
-- Table structure for table `his_admin`
--

CREATE TABLE `his_admin` (
  `ad_id` int(20) NOT NULL,
  `ad_fname` varchar(200) DEFAULT NULL,
  `ad_lname` varchar(200) DEFAULT NULL,
  `ad_email` varchar(200) DEFAULT NULL,
  `ad_pwd` varchar(200) DEFAULT NULL,
  `ad_dpic` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_admin`
--

INSERT INTO `his_admin` (`ad_id`, `ad_fname`, `ad_lname`, `ad_email`, `ad_pwd`, `ad_dpic`) VALUES
(1, 'System', 'Administrator', 'admin@mail.com', '4c7f5919e957f354d57243d37f223cf31e9e7181', 'doc-icon.png');

-- --------------------------------------------------------

--
-- Table structure for table `his_assets`
--

CREATE TABLE `his_assets` (
  `asst_id` int(20) NOT NULL,
  `asst_name` varchar(200) DEFAULT NULL,
  `asst_desc` longtext DEFAULT NULL,
  `asst_vendor` varchar(200) DEFAULT NULL,
  `asst_status` varchar(200) DEFAULT NULL,
  `asst_dept` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `his_docs`
--

CREATE TABLE `his_docs` (
  `doc_id` int(20) NOT NULL,
  `doc_fname` varchar(200) DEFAULT NULL,
  `doc_lname` varchar(200) DEFAULT NULL,
  `doc_email` varchar(200) DEFAULT NULL,
  `doc_pwd` varchar(200) DEFAULT NULL,
  `doc_dept` varchar(200) DEFAULT NULL,
  `doc_number` varchar(200) DEFAULT NULL,
  `doc_dpic` varchar(200) DEFAULT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_docs`
--

INSERT INTO `his_docs` (`doc_id`, `doc_fname`, `doc_lname`, `doc_email`, `doc_pwd`, `doc_dept`, `doc_number`, `doc_dpic`, `status`) VALUES
(13, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Records', 'CV6YO', '', 'ACTIVE'),
(14, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Nursing', 'CV6YOS', '', 'ACTIVE'),
(15, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Administrator', 'CV6YOF', '', 'ACTIVE'),
(21, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Laboratory', 'CV6YOL', '', 'ACTIVE'),
(23, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyei.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Cashier', 'CV6YOC', '', 'ACTIVE'),
(24, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Doctor', 'CV6YOD', '', 'ACTIVE'),
(25, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@oouagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Pharmacy', 'CV6YOP', '', 'ACTIVE'),
(26, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@OOUagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Scan', 'CV6YOSC', '', 'ACTIVE'),
(27, 'ADEYEMI', 'NURUDEEN ADEWALE', 'adeyemi.nurudeen@OOUagoiwoye.edu.ng', '8b3b577bdb423d1f313ad1f7c8b0b60ae401762d', 'Vice Chancellor', 'CV6YOVC', '', 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `his_equipments`
--

CREATE TABLE `his_equipments` (
  `eqp_id` int(20) NOT NULL,
  `eqp_code` varchar(200) DEFAULT NULL,
  `eqp_name` varchar(200) DEFAULT NULL,
  `eqp_vendor` varchar(200) DEFAULT NULL,
  `eqp_desc` longtext DEFAULT NULL,
  `eqp_dept` varchar(200) DEFAULT NULL,
  `eqp_status` varchar(200) DEFAULT NULL,
  `eqp_qty` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_equipments`
--

INSERT INTO `his_equipments` (`eqp_id`, `eqp_code`, `eqp_name`, `eqp_vendor`, `eqp_desc`, `eqp_dept`, `eqp_status`, `eqp_qty`) VALUES
(2, '178640239', 'TestTubes', 'Casio', '<p>Testtubes are used to perform lab tests--</p>', 'Laboratory', 'Functioning', '700000'),
(3, '052367981', 'Surgical Robot', 'Nexus', '<p>Surgical Robots aid in surgey process.</p>', 'Surgical | Theatre', 'Functioning', '100');

-- --------------------------------------------------------

--
-- Table structure for table `his_laboratory`
--

CREATE TABLE `his_laboratory` (
  `lab_id` int(20) NOT NULL,
  `lab_pat_name` varchar(200) DEFAULT NULL,
  `lab_pat_ailment` varchar(200) DEFAULT NULL,
  `lab_pat_number` varchar(200) DEFAULT NULL,
  `lab_pat_tests` longtext DEFAULT NULL,
  `lab_pat_results` longtext DEFAULT NULL,
  `lab_number` varchar(200) DEFAULT NULL,
  `lab_date_rec` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `his_medical_records`
--

CREATE TABLE `his_medical_records` (
  `mdr_id` int(20) NOT NULL,
  `mdr_number` varchar(200) DEFAULT NULL,
  `mdr_pat_name` varchar(200) DEFAULT NULL,
  `mdr_pat_adr` varchar(200) DEFAULT NULL,
  `mdr_pat_age` varchar(200) DEFAULT NULL,
  `mdr_pat_ailment` varchar(200) DEFAULT NULL,
  `mdr_pat_number` varchar(200) DEFAULT NULL,
  `mdr_pat_prescr` longtext DEFAULT NULL,
  `mdr_date_rec` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_medical_records`
--

INSERT INTO `his_medical_records` (`mdr_id`, `mdr_number`, `mdr_pat_name`, `mdr_pat_adr`, `mdr_pat_age`, `mdr_pat_ailment`, `mdr_pat_number`, `mdr_pat_prescr`, `mdr_date_rec`) VALUES
(1, 'ZNXI4', 'John Doe', '12 900 Los Angeles', '35', 'Malaria', 'RAV6C', '<ul><li>Combination of atovaquone and proguanil (Malarone)</li><li>Quinine sulfate (Qualaquin) with doxycycline (Vibramycin, Monodox, others)</li><li>Mefloquine.</li><li>Primaquine phosphate.</li></ul>', '2020-01-11 15:03:05.9839'),
(2, 'MIA9P', 'Cynthia Connolly', '9 Hill Haven Drive', '22', 'Demo Test', '3Z14K', NULL, '2022-10-18 17:07:46.7306'),
(3, 'F1ZHQ', 'Michael White', '60 Radford Street', '30', 'Demo Test', 'DCRI8', NULL, '2022-10-18 17:08:35.7938'),
(4, 'ZLN0Q', 'Lawrence Bischof', '82 Bryan Street', '32', 'Demo Test', 'ISL1E', '<ol><li>sample</li><li>sampl</li><li>sample</li><li>sample</li></ol>', '2022-10-20 17:22:15.7034');

-- --------------------------------------------------------

--
-- Table structure for table `his_patients`
--

CREATE TABLE `his_patients` (
  `pat_id` int(20) NOT NULL,
  `pat_fname` varchar(200) DEFAULT NULL,
  `pat_lname` varchar(200) DEFAULT NULL,
  `pat_dob` varchar(200) DEFAULT NULL,
  `pat_age` varchar(200) DEFAULT NULL,
  `pat_number` varchar(200) DEFAULT NULL,
  `pat_addr` varchar(200) DEFAULT NULL,
  `pat_phone` varchar(200) DEFAULT NULL,
  `pat_type` varchar(200) DEFAULT NULL,
  `pat_date_joined` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `pat_ailment` varchar(200) DEFAULT NULL,
  `pat_discharge_status` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_patients`
--

INSERT INTO `his_patients` (`pat_id`, `pat_fname`, `pat_lname`, `pat_dob`, `pat_age`, `pat_number`, `pat_addr`, `pat_phone`, `pat_type`, `pat_date_joined`, `pat_ailment`, `pat_discharge_status`) VALUES
(8, 'Michael', 'White', '02/02/1992', '30', 'DCRI8', '60 Radford Street', '1458887854', 'InPatient', '2022-10-18 16:28:51.469431', 'Demo Test', NULL),
(9, 'Lawrence', 'Bischof', '01/19/1990', '32', 'ISL1E', '82 Bryan Street', '7412225698', 'InPatient', '2022-10-18 16:53:26.210951', 'Demo Test', NULL),
(10, 'Cynthia', 'Connolly', '10/11/2000', '22', '3Z14K', '9 Hill Haven Drive', '1478885458', 'InPatient', '2022-10-18 16:54:53.104490', 'Demo Test', NULL),
(11, 'Helen', 'Macdougall', '01/01/1980', '42', 'KU8W4', '28 Holly Street', '1458889655', 'OutPatient', '2022-10-20 17:26:45.256878', 'Test Test', NULL),
(12, 'Christine', 'Moore', '11/06/1994', '28', '4TLG0', '117 Bleecker Street', '7412569698', 'InPatient', '2022-10-22 10:38:30.937516', 'Demo Test', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `his_patient_transfers`
--

CREATE TABLE `his_patient_transfers` (
  `t_id` int(20) NOT NULL,
  `t_hospital` varchar(200) DEFAULT NULL,
  `t_date` varchar(200) DEFAULT NULL,
  `t_pat_name` varchar(200) DEFAULT NULL,
  `t_pat_number` varchar(200) DEFAULT NULL,
  `t_status` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_patient_transfers`
--

INSERT INTO `his_patient_transfers` (`t_id`, `t_hospital`, `t_date`, `t_pat_name`, `t_pat_number`, `t_status`) VALUES
(1, 'Kenyatta National Hospital', '2020-01-11', 'Mart Developers', '9KXPM', 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `his_payrolls`
--

CREATE TABLE `his_payrolls` (
  `pay_id` int(20) NOT NULL,
  `pay_number` varchar(200) DEFAULT NULL,
  `pay_doc_name` varchar(200) DEFAULT NULL,
  `pay_doc_number` varchar(200) DEFAULT NULL,
  `pay_doc_email` varchar(200) DEFAULT NULL,
  `pay_emp_salary` varchar(200) DEFAULT NULL,
  `pay_date_generated` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4),
  `pay_status` varchar(200) DEFAULT NULL,
  `pay_descr` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_payrolls`
--

INSERT INTO `his_payrolls` (`pay_id`, `pay_number`, `pay_doc_name`, `pay_doc_number`, `pay_doc_email`, `pay_emp_salary`, `pay_date_generated`, `pay_status`, `pay_descr`) VALUES
(2, 'HUT1B', 'Henry Doe', 'N8TI0', 'henryd@hms.org', '7555', '2022-10-20 17:14:18.3708', 'Paid', '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a,</p>'),
(3, 'T294L', 'Bryan Arreola', 'YDS7L', 'bryan@mail.com', '15500', '2022-10-20 17:14:50.5588', NULL, '<p>demo demo demo demo demo</p>'),
(4, '3UOXY', 'Jessica Spencer', '5VIFT', 'jessica@mail.com', '4150', '2022-10-22 11:04:36.9626', NULL, '<p>This is a demo payroll description for test!!</p>');

-- --------------------------------------------------------

--
-- Table structure for table `his_pharmaceuticals`
--

CREATE TABLE `his_pharmaceuticals` (
  `phar_id` int(20) NOT NULL,
  `phar_name` varchar(200) DEFAULT NULL,
  `phar_bcode` varchar(200) DEFAULT NULL,
  `phar_desc` longtext DEFAULT NULL,
  `phar_qty` varchar(200) DEFAULT NULL,
  `phar_cat` varchar(200) DEFAULT NULL,
  `phar_vendor` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_pharmaceuticals`
--

INSERT INTO `his_pharmaceuticals` (`phar_id`, `phar_name`, `phar_bcode`, `phar_desc`, `phar_qty`, `phar_cat`, `phar_vendor`) VALUES
(1, 'Paracetamol', '134057629', '<ul><li><strong>Paracetamol</strong>, also known as <strong>acetaminophen</strong> and <strong>APAP</strong>, is a medication used to treat <a href=\"https://en.wikipedia.org/wiki/Pain\">pain</a> and <a href=\"https://en.wikipedia.org/wiki/Fever\">fever</a>. It is typically used for mild to moderate pain relief. There is mixed evidence for its use to relieve fever in children.&nbsp; It is often sold in combination with other medications, such as in many <a href=\"https://en.wikipedia.org/wiki/Cold_medication\">cold medications</a> Paracetamol is also used for severe pain, such as <a href=\"https://en.wikipedia.org/wiki/Cancer_pain\">cancer pain</a> and pain after surgery, in combination with <a href=\"https://en.wikipedia.org/wiki/Opioid_analgesic\">opioid pain medication</a>. It is typically used either by mouth or <a href=\"https://en.wikipedia.org/wiki/Rectally\">rectally</a>, but is also available by <a href=\"https://en.wikipedia.org/wiki/Intravenous\">injection into a vein</a>. Effects last between two and four hours.</li><li>Paracetamol is generally safe at recommended doses.The recommended maximum daily dose for an adult is three to four grams. Higher doses may lead to toxicity, including <a href=\"https://en.wikipedia.org/wiki/Liver_failure\">liver failure</a> Serious skin rashes may rarely occur. It appears to be safe during <a href=\"https://en.wikipedia.org/wiki/Pregnancy\">pregnancy</a> and when <a href=\"https://en.wikipedia.org/wiki/Breastfeeding\">breastfeeding</a>.In those with liver disease, it may still be used, but in lower doses. It is classified as a mild <a href=\"https://en.wikipedia.org/wiki/Analgesic\">analgesic</a>. It does not have significant <a href=\"https://en.wikipedia.org/wiki/Anti-inflammatory\">anti-inflammatory</a> activity. How it works is not entirely clear.</li><li>Paracetamol was first made in 1877. It is the most commonly used medication for pain and fever in both the United States and Europe. It is on the <a href=\"https://en.wikipedia.org/wiki/World_Health_Organization%27s_List_of_Essential_Medicines\">World Health Organization&#39;s List of Essential Medicines</a>, the safest and most effective medicines needed in a <a href=\"https://en.wikipedia.org/wiki/Health_system\">health system</a>.<a href=\"https://en.wikipedia.org/wiki/Paracetamol#cite_note-WHO21st-24\">[24]</a> Paracetamol is available as a <a href=\"https://en.wikipedia.org/wiki/Generic_medication\">generic medication</a> with trade names including <a href=\"https://en.wikipedia.org/wiki/Tylenol_(brand)\">Tylenol</a> and <a href=\"https://en.wikipedia.org/wiki/Panadol_(brand)\">Panadol</a>, among others.The wholesale price in the <a href=\"https://en.wikipedia.org/wiki/Developing_world\">developing world</a> is less than US$0.01 per dose. In the United States, it costs about US$0.04 per dose. In 2019, it was the 17th most prescribed medication in the United States, with more than 29&nbsp;million prescriptions.</li></ul>', '500', 'Antipyretics', 'Dawa Limited Kenya'),
(2, 'Aspirin', '452760813', '<ul><li><strong>Aspirin</strong>, also known as <strong>acetylsalicylic acid</strong> (<strong>ASA</strong>), is a <a href=\"https://en.wikipedia.org/wiki/Medication\">medication</a> used to reduce <a href=\"https://en.wikipedia.org/wiki/Pain\">pain</a>, <a href=\"https://en.wikipedia.org/wiki/Fever\">fever</a>, or <a href=\"https://en.wikipedia.org/wiki/Inflammation\">inflammation</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Specific inflammatory conditions which aspirin is used to treat include <a href=\"https://en.wikipedia.org/wiki/Kawasaki_disease\">Kawasaki disease</a>, <a href=\"https://en.wikipedia.org/wiki/Pericarditis\">pericarditis</a>, and <a href=\"https://en.wikipedia.org/wiki/Rheumatic_fever\">rheumatic fever</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Aspirin given shortly after a <a href=\"https://en.wikipedia.org/wiki/Myocardial_infarction\">heart attack</a> decreases the risk of death.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Aspirin is also used long-term to help prevent further heart attacks, <a href=\"https://en.wikipedia.org/wiki/Ischaemic_stroke\">ischaemic strokes</a>, and <a href=\"https://en.wikipedia.org/wiki/Thrombus\">blood clots</a> in people at high risk.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> It may also decrease the risk of certain types of <a href=\"https://en.wikipedia.org/wiki/Cancer\">cancer</a>, particularly <a href=\"https://en.wikipedia.org/wiki/Colorectal_cancer\">colorectal cancer</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-6\">[6]</a> For pain or fever, effects typically begin within 30 minutes.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Aspirin is a <a href=\"https://en.wikipedia.org/wiki/Nonsteroidal_anti-inflammatory_drug\">nonsteroidal anti-inflammatory drug</a> (NSAID) and works similarly to other NSAIDs but also suppresses the normal functioning of <a href=\"https://en.wikipedia.org/wiki/Platelet\">platelets</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a></li><li>One common <a href=\"https://en.wikipedia.org/wiki/Adverse_effect\">adverse effect</a> is an <a href=\"https://en.wikipedia.org/wiki/Upset_stomach\">upset stomach</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> More significant side effects include <a href=\"https://en.wikipedia.org/wiki/Stomach_ulcer\">stomach ulcers</a>, <a href=\"https://en.wikipedia.org/wiki/Stomach_bleeding\">stomach bleeding</a>, and worsening <a href=\"https://en.wikipedia.org/wiki/Asthma\">asthma</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Bleeding risk is greater among those who are older, drink <a href=\"https://en.wikipedia.org/wiki/Alcohol_(drug)\">alcohol</a>, take other NSAIDs, or are on other <a href=\"https://en.wikipedia.org/wiki/Anticoagulants\">blood thinners</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> Aspirin is not recommended in the last part of <a href=\"https://en.wikipedia.org/wiki/Pregnancy\">pregnancy</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> It is not generally recommended in children with <a href=\"https://en.wikipedia.org/wiki/Infection\">infections</a> because of the risk of <a href=\"https://en.wikipedia.org/wiki/Reye_syndrome\">Reye syndrome</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a> High doses may result in <a href=\"https://en.wikipedia.org/wiki/Tinnitus\">ringing in the ears</a>.<a href=\"https://en.wikipedia.org/wiki/Aspirin#cite_note-AHSF2016-5\">[5]</a></li></ul>', '500', 'Analgesics', 'Cosmos Kenya Limited'),
(3, 'Test Pharma', '465931288', '<p>This is a demo test.&nbsp;This is a demo test.&nbsp;This is a demo test.</p>', '36', 'Antibiotics', 'Cosmos Pharmaceutical Limited');

-- --------------------------------------------------------

--
-- Table structure for table `his_pharmaceuticals_categories`
--

CREATE TABLE `his_pharmaceuticals_categories` (
  `pharm_cat_id` int(20) NOT NULL,
  `pharm_cat_name` varchar(200) DEFAULT NULL,
  `pharm_cat_vendor` varchar(200) DEFAULT NULL,
  `pharm_cat_desc` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_pharmaceuticals_categories`
--

INSERT INTO `his_pharmaceuticals_categories` (`pharm_cat_id`, `pharm_cat_name`, `pharm_cat_vendor`, `pharm_cat_desc`) VALUES
(1, 'Antipyretics', 'Cosmos Kenya Limited', '<ul><li>An <strong>antipyretic</strong> (<a href=\"https://en.wikipedia.org/wiki/Help:IPA/English\">/ËŒ&aelig;ntipaÉªËˆrÉ›tÉªk/</a>, from <em>anti-</em> &#39;against&#39; and <em><a href=\"https://en.wiktionary.org/wiki/pyretic\">pyretic</a></em> &#39;feverish&#39;) is a substance that reduces <a href=\"https://en.wikipedia.org/wiki/Fever\">fever</a>. Antipyretics cause the <a href=\"https://en.wikipedia.org/wiki/Hypothalamus\">hypothalamus</a> to override a <a href=\"https://en.wikipedia.org/wiki/Prostaglandin\">prostaglandin</a>-induced increase in <a href=\"https://en.wikipedia.org/wiki/Thermoregulation\">temperature</a>. The body then works to lower the temperature, which results in a reduction in fever.</li><li>Most antipyretic medications have other purposes. The most common antipyretics in the United States are <a href=\"https://en.wikipedia.org/wiki/Ibuprofen\">ibuprofen</a> and <a href=\"https://en.wikipedia.org/wiki/Aspirin\">aspirin</a>, which are <a href=\"https://en.wikipedia.org/wiki/Nonsteroidal_anti-inflammatory_drugs\">nonsteroidal anti-inflammatory drugs</a> (NSAIDs) used primarily as <a href=\"https://en.wikipedia.org/wiki/Analgesics\">analgesics</a> (pain relievers), but which also have antipyretic properties; and <a href=\"https://en.wikipedia.org/wiki/Acetaminophen\">acetaminophen</a> (paracetamol), an analgesic with weak anti-inflammatory properties.<a href=\"https://en.wikipedia.org/wiki/Antipyretic#cite_note-2\">[2]</a></li></ul>'),
(2, 'Analgesics', 'Dawa Limited Kenya', '<ul><li><p>An <strong>analgesic</strong> or <strong>painkiller</strong> is any member of the group of <a href=\"https://en.wikipedia.org/wiki/Pharmaceutical_drug\">drugs</a> used to achieve analgesia, relief from <a href=\"https://en.wikipedia.org/wiki/Pain\">pain</a>.</p><p>Analgesic drugs act in various ways on the <a href=\"https://en.wikipedia.org/wiki/Peripheral_nervous_system\">peripheral</a> and <a href=\"https://en.wikipedia.org/wiki/Central_nervous_system\">central</a> nervous systems. They are distinct from <a href=\"https://en.wikipedia.org/wiki/Anesthetic\">anesthetics</a>, which temporarily affect, and in some instances completely eliminate, <a href=\"https://en.wikipedia.org/wiki/Sense\">sensation</a>. Analgesics include <a href=\"https://en.wikipedia.org/wiki/Paracetamol\">paracetamol</a> (known in North America as <a href=\"https://en.wikipedia.org/wiki/Acetaminophen\">acetaminophen</a> or simply APAP), the <a href=\"https://en.wikipedia.org/wiki/Nonsteroidal_anti-inflammatory_drug\">nonsteroidal anti-inflammatory drugs</a> (NSAIDs) such as the <a href=\"https://en.wikipedia.org/wiki/Salicylate\">salicylates</a>, and <a href=\"https://en.wikipedia.org/wiki/Opioid\">opioid</a> drugs such as <a href=\"https://en.wikipedia.org/wiki/Morphine\">morphine</a> and <a href=\"https://en.wikipedia.org/wiki/Oxycodone\">oxycodone</a>.</p></li></ul>'),
(3, 'Antibiotics', 'Cosmos Kenya Limited', '<p>Antibiotics</p>');

-- --------------------------------------------------------

--
-- Table structure for table `his_prescriptions`
--

CREATE TABLE `his_prescriptions` (
  `pres_id` int(200) NOT NULL,
  `pres_pat_name` varchar(200) DEFAULT NULL,
  `pres_pat_age` varchar(200) DEFAULT NULL,
  `pres_pat_number` varchar(200) DEFAULT NULL,
  `pres_number` varchar(200) DEFAULT NULL,
  `pres_pat_addr` varchar(200) DEFAULT NULL,
  `pres_pat_type` varchar(200) DEFAULT NULL,
  `pres_date` timestamp(4) NOT NULL DEFAULT current_timestamp(4) ON UPDATE current_timestamp(4),
  `pres_pat_ailment` varchar(200) DEFAULT NULL,
  `pres_ins` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_prescriptions`
--

INSERT INTO `his_prescriptions` (`pres_id`, `pres_pat_name`, `pres_pat_age`, `pres_pat_number`, `pres_number`, `pres_pat_addr`, `pres_pat_type`, `pres_date`, `pres_pat_ailment`, `pres_ins`) VALUES
(3, 'Mart Developers', '23', '6P8HJ', 'J9DC6', '127001 LocalHost', 'InPatient', '2020-01-11 12:32:39.6963', 'Fever', '<ul><li><a href=\"https://www.medicalnewstoday.com/articles/179211.php\">Non-steroidal anti-inflammatory drugs</a> (NSAIDs) such as <a href=\"https://www.medicalnewstoday.com/articles/161255.php\">aspirin</a> or ibuprofen can help bring a fever down. These are available to purchase over-the-counter or <a target=\"_blank\" href=\"https://amzn.to/2qp3d0b\">online</a>. However, a mild fever may be helping combat the bacterium or virus that is causing the infection. It may not be ideal to bring it down.</li><li>If the fever has been caused by a bacterial infection, the doctor may prescribe an <a href=\"https://www.medicalnewstoday.com/articles/10278.php\">antibiotic</a>.</li><li>If a fever has been caused by a cold, which is caused by a viral infection, NSAIDs may be used to relieve uncomfortable symptoms. Antibiotics have no effect against viruses and will not be prescribed by your doctor for a viral infection.</li></ul>'),
(4, 'John Doe', '30', 'RAV6C', 'HZQ8J', '12 900 NYE', 'OutPatient', '2020-01-11 13:08:46.7368', 'Malaria', '<ul><li>Combination of atovaquone and proguanil (Malarone)</li><li>Quinine sulfate (Qualaquin) with doxycycline (Vibramycin, Monodox, others)</li><li>Mefloquine.</li><li>Primaquine phosphate.</li></ul>'),
(5, 'Lorem Ipsum', '10', '7EW0L', 'HQC3D', '12 9001 Machakos', 'OutPatient', '2020-01-13 12:19:30.3702', 'Flu', '<ul><li><a href=\"https://www.google.com/search?client=firefox-b-e&amp;sxsrf=ACYBGNRW3vlJoag6iJInWVOTtTG_HUTedA:1578917913108&amp;q=flu+decongestant&amp;stick=H4sIAAAAAAAAAOMQFeLQz9U3SK5MTlbiBLGMktNzcnYxMRosYhVIyylVSElNzs9LTy0uScwrAQBMMnd5LgAAAA&amp;sa=X&amp;ved=2ahUKEwjRhNzKx4DnAhUcBGMBHYs1A24Q0EAwFnoECAwQHw\">Decongestant</a></li><li>Relieves nasal congestion, swelling and runny nose.</li><li><a href=\"https://www.google.com/search?client=firefox-b-e&amp;sxsrf=ACYBGNRW3vlJoag6iJInWVOTtTG_HUTedA:1578917913108&amp;q=flu+cough+medicine&amp;stick=H4sIAAAAAAAAAOMQFeLQz9U3SK5MTlbiBLEM89IsLHYxMRosYhVKyylVSM4vTc9QyE1NyUzOzEsFAA_gu9IwAAAA&amp;sa=X&amp;ved=2ahUKEwjRhNzKx4DnAhUcBGMBHYs1A24Q0EAwFnoECAwQIA\">Cough medicine</a></li><li>Blocks the cough reflex. Some may thin and loosen mucus, making it easier to clear from the airways.</li><li><a href=\"https://www.google.com/search?client=firefox-b-e&amp;sxsrf=ACYBGNRW3vlJoag6iJInWVOTtTG_HUTedA:1578917913108&amp;q=flu+nonsteroidal+anti-inflammatory+drug&amp;stick=H4sIAAAAAAAAAOMQFeLQz9U3SK5MTlYCs0yzCit3MTEaLGJVT8spVcjLzysuSS3Kz0xJzFFIzCvJ1M3MS8tJzM1NLMkvqlRIKSpNBwByUiYhRAAAAA&amp;sa=X&amp;ved=2ahUKEwjRhNzKx4DnAhUcBGMBHYs1A24Q0EAwFnoECAwQIQ\">Nonsteroidal anti-inflammatory drug</a></li><li>Relieves pain, decreases inflammation and reduces fever.</li><li><a href=\"https://www.google.com/search?client=firefox-b-e&amp;sxsrf=ACYBGNRW3vlJoag6iJInWVOTtTG_HUTedA:1578917913108&amp;q=flu+analgesic&amp;stick=H4sIAAAAAAAAAOMQFeLQz9U3SK5MTlZiB7EqDSx3MTEaLGLlTcspVUjMS8xJTy3OTAYAbecS9ikAAAA&amp;sa=X&amp;ved=2ahUKEwjRhNzKx4DnAhUcBGMBHYs1A24Q0EAwFnoECAwQIg\">Analgesic</a></li><li>Relieves pain.</li><li><a href=\"https://www.google.com/search?client=firefox-b-e&amp;sxsrf=ACYBGNRW3vlJoag6iJInWVOTtTG_HUTedA:1578917913108&amp;q=flu+antiviral+drug&amp;stick=H4sIAAAAAAAAAOMQFeLQz9U3SK5MTlYCs1KMC0x2MTEaLGIVSsspVUjMK8ksyyxKzFFIKSpNBwDBFxlOLwAAAA&amp;sa=X&amp;ved=2ahUKEwjRhNzKx4DnAhUcBGMBHYs1A24Q0EAwFnoECAwQIw\">Antiviral drug</a></li><li>Reduces viruses&#39; ability to replicate.</li></ul>'),
(6, 'Christine Moore', '28', '4TLG0', '09Y2P', '117 Bleecker Street', 'InPatient', '2022-10-22 10:57:10.7496', 'Demo Test', '<ol><li>This is a demo prescription.</li><li>This is a second demo prescription.</li><li>And this one&#39;s third!</li></ol>');

-- --------------------------------------------------------

--
-- Table structure for table `his_pwdresets`
--

CREATE TABLE `his_pwdresets` (
  `id` int(20) NOT NULL,
  `email` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `his_surgery`
--

CREATE TABLE `his_surgery` (
  `s_id` int(200) NOT NULL,
  `s_number` varchar(200) DEFAULT NULL,
  `s_doc` varchar(200) DEFAULT NULL,
  `s_pat_number` varchar(200) DEFAULT NULL,
  `s_pat_name` varchar(200) DEFAULT NULL,
  `s_pat_ailment` varchar(200) DEFAULT NULL,
  `s_pat_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `s_pat_status` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_surgery`
--

INSERT INTO `his_surgery` (`s_id`, `s_number`, `s_doc`, `s_pat_number`, `s_pat_name`, `s_pat_ailment`, `s_pat_date`, `s_pat_status`) VALUES
(2, '8KQWD', 'Martin Mbithi', 'RAV6C', 'John Doe', 'Malaria', '2020-01-13 08:50:10.649889', 'Successful'),
(3, '7K18R', 'Bryan Arreola', '3Z14K', 'Cynthia Connolly', 'Demo Test', '2022-10-18 17:26:44.053571', 'Successful'),
(4, 'ECF62', 'Bryan Arreola', '4TLG0', 'Christine Moore', 'Demo Test', '2022-10-22 11:03:33.765255', 'Successful');

-- --------------------------------------------------------

--
-- Table structure for table `his_vendor`
--

CREATE TABLE `his_vendor` (
  `v_id` int(20) NOT NULL,
  `v_number` varchar(200) DEFAULT NULL,
  `v_name` varchar(200) DEFAULT NULL,
  `v_adr` varchar(200) DEFAULT NULL,
  `v_mobile` varchar(200) DEFAULT NULL,
  `v_email` varchar(200) DEFAULT NULL,
  `v_phone` varchar(200) DEFAULT NULL,
  `v_desc` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_vendor`
--

INSERT INTO `his_vendor` (`v_id`, `v_number`, `v_name`, `v_adr`, `v_mobile`, `v_email`, `v_phone`, `v_desc`) VALUES
(1, '6ISKC', 'Cosmos Pharmaceutical Limited', 'P.O. Box 41433, GPO 00100 Nairobi, Kenya', '', 'info@cosmospharmaceuticallimited.com', '+254(20)550700-9', '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc,</p>');

-- --------------------------------------------------------

--
-- Table structure for table `his_vitals`
--

CREATE TABLE `his_vitals` (
  `id` int(20) NOT NULL,
  `date` varchar(10) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `vit_pat_number` varchar(200) DEFAULT NULL,
  `vit_bodytemp` varchar(200) DEFAULT NULL,
  `vit_heartpulse` varchar(200) DEFAULT NULL,
  `vit_resprate` varchar(200) DEFAULT NULL,
  `vit_bloodpress` varchar(200) DEFAULT NULL,
  `vit_daterec` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `picture` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `his_vitals`
--

INSERT INTO `his_vitals` (`id`, `date`, `fullname`, `vit_pat_number`, `vit_bodytemp`, `vit_heartpulse`, `vit_resprate`, `vit_bloodpress`, `vit_daterec`, `picture`) VALUES
(8, '', 'TEMITAYO BLESSED OTITE', 'IND45592', '45°C', '50P', '30R', '20mmHg', '2025-05-01 12:05:38.000000', 'testimonial-4.jpg'),
(9, '', 'TEMITAYO BLESSED OTITE', 'IND45592', '45°C', '50P', '30R', '20mmHg', '2025-05-01 12:05:38.000000', 'testimonial-4.jpg'),
(10, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(11, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(12, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(13, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(14, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(15, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(16, '', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '40P', '70R', '80mmHg', '2025-06-07 18:17:43.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(17, '2025-08-30', 'OMOSEYIN OLUKAYODE KUNLE', 'IND437', '45°C', '50P', '70R', '20mmHg', '2025-08-30 10:53:00.395663', ''),
(18, '2025-08-30', 'TEMITAYO BLESSED OTITE', 'IND45592', '45°C', '50P', '30R', '80mmHg', '2025-08-30 10:52:38.373974', 'testimonial-4.jpg'),
(19, '2025-08-30', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '30°C', '50P', '30R', '80mmHg', '2025-08-30 10:52:19.268750', 'ADEBAYO ALIU Olamilekan.jpg'),
(27, '2025-10-13', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '45°C', '54P', '65R', '35mmHg', '0000-00-00 00:00:00.000000', 'we.png'),
(28, '2025-10-14', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '65P', '37R', '120mmHg', '0000-00-00 00:00:00.000000', 'de icon.jpg'),
(29, '2025-10-14', 'ADEBOWALE SAMUEL LAWAL', 'IND45523', '60°C', '75P', '37R', '120mmHg', '0000-00-00 00:00:00.000000', 'ADEBAYO ALIU Olamilekan.jpg'),
(30, '2025-10-14', 'Adeyemi Nurudeen Adewale', 'ST43173', '60°C', '75P', '37R', '120mmHg', '0000-00-00 00:00:00.000000', 'de icon.jpg'),
(31, '2025-10-15', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '75P', '37R', '120mmHg', '0000-00-00 00:00:00.000000', 'de icon.jpg'),
(32, '2025-10-15', 'Adeyemi Nurudeen ', 'ST43173', '60°C', '65P', '37R', '120mmHg', '2025-10-15 13:56:27.000000', 'de icon.jpg'),
(33, '2025-10-16', 'OLATUNBOSUN OYINLOLA ', 'IND44577', '60°C', '65P', '37R', '120mmHg', '2025-10-16 11:13:31.000000', 'we.png'),
(34, '2025-10-16', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '75P', '37R', '120mmHg', '2025-10-16 13:59:35.000000', 'de icon.jpg'),
(35, '2025-10-20', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '60°C', '75P', '37R', '120mmHg', '2025-10-20 09:27:11.000000', 'we.png'),
(36, '2025-10-21', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '60°C', '75P', '37R', '120mmHg', '2025-10-21 15:32:02.000000', 'we.png'),
(37, '2025-10-22', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '75P', '37R', '120mmHg', '2025-10-22 09:13:23.000000', 'de icon.jpg'),
(38, '2025-10-22', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '60°C', '75P', '37R', '120mmHg', '2025-10-22 09:41:52.000000', 'we.png'),
(39, '2025-10-22', 'Adeyemi Nurudeen Adewale', 'ST4376', '60°C', '65P', '37R', '120mmHg', '2025-10-22 14:22:19.000000', 'de icon.jpg'),
(40, '2025-10-23', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '60°C', '65P', '37R', '120mmHg', '2025-10-23 08:52:27.000000', 'we.png'),
(41, '2025-10-23', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '75P', '37R', '120mmHg', '2025-10-23 10:21:30.000000', 'de icon.jpg'),
(42, '2025-10-23', 'Adeyemi Nurudeen Adewale', 'ST4376', '70°C', '75P', '37R', '120mmHg', '2025-10-23 10:43:51.000000', 'de icon.jpg'),
(43, '2025-10-27', 'OLATUNBOSUN OYINLOLA MICHAEL', 'IND44577', '60°C', '75P', '37R', '120mmHg', '2025-10-27 08:25:03.000000', 'we.png'),
(44, '2025-11-27', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '75P', '37R', '120mmHg', '2025-11-27 10:50:08.000000', 'de icon.jpg'),
(45, '2025-11-29', 'Adeyemi  Nurudeen Adewale', 'S4310', '60°C', '65P', '37R', '120mmHg', '2025-11-29 13:38:21.000000', 'de icon.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `hmo`
--

CREATE TABLE `hmo` (
  `id` int(11) NOT NULL,
  `regdate` date NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hmo`
--

INSERT INTO `hmo` (`id`, `regdate`, `code`, `name`) VALUES
(7, '2025-04-11', 'H69', 'HAZARD MANZA'),
(8, '2025-04-11', 'H612', 'HEALTH CARE'),
(9, '2025-04-11', 'H615', 'LIFE TREASURE');

-- --------------------------------------------------------

--
-- Table structure for table `hmocompany`
--

CREATE TABLE `hmocompany` (
  `id` int(11) NOT NULL,
  `regdate` date NOT NULL,
  `Hcode` varchar(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hmocompany`
--

INSERT INTO `hmocompany` (`id`, `regdate`, `Hcode`, `code`, `name`) VALUES
(1, '2025-04-13', 'H612', 'H612660', 'FCMB'),
(2, '2025-04-13', 'H612', 'H612397', 'FACCOMPUTERS');

-- --------------------------------------------------------

--
-- Table structure for table `hmocompany_individual`
--

CREATE TABLE `hmocompany_individual` (
  `id` int(11) NOT NULL,
  `hcode` varchar(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `Surname` varchar(100) NOT NULL,
  `Firstname` varchar(100) NOT NULL,
  `Lastname` varchar(100) NOT NULL,
  `reg_date` date NOT NULL,
  `dob` date NOT NULL,
  `age` varchar(10) NOT NULL,
  `address` varchar(200) NOT NULL,
  `marrital` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nok` varchar(100) NOT NULL,
  `nok_phone` varchar(20) NOT NULL,
  `picture` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hmocompany_individual`
--

INSERT INTO `hmocompany_individual` (`id`, `hcode`, `code`, `Surname`, `Firstname`, `Lastname`, `reg_date`, `dob`, `age`, `address`, `marrital`, `phone`, `nok`, `nok_phone`, `picture`) VALUES
(1, 'H612660', 'AJANI', 'MATHEW', 'LAWRENCE', '2025-04-13', '0000-00-00', '1982-06-15', '42', 'IBEJU LEKKI LAGOS', 'Married', '0805586785', 'MRS AJANI', '0809978655', 'user.jpg'),
(4, 'H612397', 'H6123975', 'OGODU', 'WALE', 'OMOGA', '2025-02-04', '2024-01-30', '5', 'no 5 unity estate', 'Married', '07033545545', 'MRS ADELABU', '07033545545', 'ABIOLA MOSHOOD_01.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `individual`
--

CREATE TABLE `individual` (
  `id` int(11) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `reg_date` date NOT NULL,
  `dob` date NOT NULL,
  `age` varchar(10) NOT NULL,
  `address` varchar(200) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nok` varchar(200) NOT NULL,
  `nok_contact` varchar(20) NOT NULL,
  `marrital` varchar(100) NOT NULL,
  `picture` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `individual`
--

INSERT INTO `individual` (`id`, `surname`, `firstname`, `middlename`, `code`, `reg_date`, `dob`, `age`, `address`, `phone`, `nok`, `nok_contact`, `marrital`, `picture`) VALUES
(1, 'ADETUTU', 'TOMIWA', 'LAT', 'IND42246', '2025-04-09', '1990-01-24', '35', 'no 5 unity estate IJEBU-ODE OGUN STATE', '09032077469', 'ADETUTU FATIMAT', '0908875664', '', ''),
(2, 'OMOSEYIN', 'OLUKAYODE', 'KUNLE', 'IND437', '2025-04-09', '1987-10-13', '38', 'no 5 unity estate IJEBU-ODE OGUN STATE', '0705576887', 'MRS ADEWALE MOSHOOD', '070886755', '', ''),
(3, 'TEMITAYO', 'BLESSED', 'OTITE', 'IND45592', '2025-04-10', '1955-02-15', '52', 'LAGOS IBADAN EXPRESS WAY', '0805566764', 'MRS OTITE', '07066758766', 'Married', 'testimonial-4.jpg'),
(4, 'ADEBOWALE', 'SAMUEL', 'LAWAL', 'IND45523', '2025-04-28', '1987-06-09', '35', 'no 5 unity estate', '0706657666', 'MRS ADETUTU', '07033545545', 'Single', 'ADEBAYO ALIU Olamilekan.jpg'),
(5, 'OLATUNBOSUN', 'OYINLOLA', 'MICHAEL', 'IND44577', '2025-08-30', '2022-01-10', '10', 'NO 3 ETI-ITALE IJEBU-ODE', '07033545545', 'MRS ADEWALE MOSHOOD', '07033545545', 'Single', 'we.png');

-- --------------------------------------------------------

--
-- Table structure for table `injectionchart`
--

CREATE TABLE `injectionchart` (
  `id` int(10) NOT NULL,
  `patid` varchar(20) NOT NULL,
  `Fullname` varchar(200) NOT NULL,
  `session` varchar(20) NOT NULL,
  `date` varchar(20) NOT NULL,
  `drug` varchar(200) NOT NULL,
  `type` varchar(20) NOT NULL,
  `dose` varchar(20) NOT NULL,
  `time` varchar(10) NOT NULL,
  `comment` varchar(300) NOT NULL,
  `posted` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `injectionchart`
--

INSERT INTO `injectionchart` (`id`, `patid`, `Fullname`, `session`, `date`, `drug`, `type`, `dose`, `time`, `comment`, `posted`) VALUES
(7, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', 'Morning', '2025-06-15', 'MALARIA PARASITE', 'IV', '10ml', '8:00am', 'The Patient is Responding Speedily to Treatment', ''),
(8, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', 'Morning', '2025-06-15', 'MALARIA PARASITE', 'IV', '10ml', '8:00am', 'The Patient is Responding Speedily to Treatment', ''),
(9, 'IND45592', 'TEMITAYO BLESSED OTITE', 'Morning', '2025-06-15', 'GENTAMIZINE', 'IV', '10ml', '8:00am', 'The Patient is Responding Speedily to Treatment', '');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `invno` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `details` varchar(50) NOT NULL,
  `time` varchar(20) NOT NULL,
  `amount` varchar(20) NOT NULL,
  `total` varchar(50) NOT NULL,
  `officer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`id`, `date`, `invno`, `name`, `details`, `time`, `amount`, `total`, `officer`) VALUES
(5, '2025-07-11', '', 'OLABANJI OLUWASEYI', 'FBS', '03:06 am', '5000', '5000', 'OLABANJI OLUWASEUN'),
(6, '2025-07-11', '', 'OLABANJI OLUWASEYI', 'FAMILY CARD', '03:06 am', '2500', '2500', 'OLABANJI OLUWASEUN'),
(7, '2025-07-11', 'INV4373', 'OLABANJI OLUWASEYI', 'FBS', '03:12 am', '5000', '5000', 'OLABANJI OLUWASEUN'),
(8, '2025-07-11', 'INV4373', 'OLABANJI OLUWASEYI', 'PELVIC SCAN', '03:12 am', '3000', '3000', 'OLABANJI OLUWASEUN'),
(9, '2025-07-24', 'INV2147', '', 'COMPANY CARD', '08:51 pm', '5000', '5000', 'OLABANJI OLUWASEUN');

-- --------------------------------------------------------

--
-- Table structure for table `journal`
--

CREATE TABLE `journal` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `acc_code` varchar(100) NOT NULL,
  `acc_name` varchar(100) NOT NULL,
  `credit` varchar(100) NOT NULL,
  `debit` varchar(100) NOT NULL,
  `name` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `journal`
--

INSERT INTO `journal` (`id`, `date`, `acc_code`, `acc_name`, `credit`, `debit`, `name`) VALUES
(177, '2025-07-11', '100', 'Cash Account', '0', '', 0),
(178, '2025-07-11', '101', 'Sales Account', '', '0', 0),
(179, '2025-07-11', '100', 'Cash Account', '0', '', 0),
(180, '2025-07-11', '101', 'Sales Account', '', '0', 0),
(181, '2025-07-11', '100', 'Cash Account', '0', '', 0),
(182, '2025-07-11', '101', 'Sales Account', '', '0', 0),
(183, '2025-07-11', '100', 'Cash Account', '0', '', 0),
(184, '2025-07-11', '101', 'Sales Account', '', '0', 0),
(185, '2025-07-24', '100', 'Cash Account', '0', '', 0),
(186, '2025-07-24', '101', 'Sales Account', '', '0', 0),
(187, '2025-07-28', '100', 'Cash Account', '0', '2,000.00', 0),
(188, '2025-07-28', '101', 'Sales Account', '2,000.00', '0', 0),
(189, '2025-09-26', '100', 'Cash Account', '0', '', 0),
(190, '2025-09-26', '101', 'Sales Account', '', '0', 0),
(191, '2025-09-26', '100', 'Cash Account', '0', '', 0),
(192, '2025-09-26', '101', 'Sales Account', '', '0', 0),
(193, '2025-10-22', '100', 'Cash Account', '0', '480.00', 0),
(194, '2025-10-22', '101', 'Sales Account', '480.00', '0', 0),
(195, '2025-10-22', '100', 'Cash Account', '0', '160.00', 0),
(196, '2025-10-22', '101', 'Sales Account', '160.00', '0', 0),
(197, '2025-10-22', '100', 'Cash Account', '0', '160.00', 0),
(198, '2025-10-22', '101', 'Sales Account', '160.00', '0', 0),
(199, '2025-10-23', '100', 'Cash Account', '0', '720.00', 0),
(200, '2025-10-23', '101', 'Sales Account', '720.00', '0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lab`
--

CREATE TABLE `lab` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab`
--

INSERT INTO `lab` (`id`, `name`, `amount`) VALUES
(1, 'PCV', '2500'),
(2, 'FBS', '5000'),
(3, 'MALARIA PARASITE', '2500'),
(4, 'EMOGLOBIM', '2500'),
(5, 'BLOOD TEST', '5000');

-- --------------------------------------------------------

--
-- Table structure for table `lab_consumables`
--

CREATE TABLE `lab_consumables` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(20) DEFAULT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_consumables`
--

INSERT INTO `lab_consumables` (`id`, `name`, `quantity`, `category`) VALUES
(168, 'Plastic Petri DIsh', NULL, 'Plastics'),
(169, 'Conical flask - 500 ml', NULL, 'plastics'),
(170, '5ml Needle & Syringe', NULL, 'Plastics'),
(171, '5ml Needle & Syringe', NULL, 'Plastics');

-- --------------------------------------------------------

--
-- Table structure for table `lab_consumable_distribution`
--

CREATE TABLE `lab_consumable_distribution` (
  `consumable_id` int(10) DEFAULT NULL,
  `campus` varchar(50) DEFAULT NULL,
  `quantity` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_consumable_stock`
--

CREATE TABLE `lab_consumable_stock` (
  `id` int(11) NOT NULL,
  `consumable_id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) NOT NULL DEFAULT 'Unknown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_consumable_stock`
--

INSERT INTO `lab_consumable_stock` (`id`, `consumable_id`, `campus_id`, `quantity`, `location`) VALUES
(2, 168, 5, 100, 'Unknown'),
(3, 169, 5, 100, 'Unknown'),
(4, 170, 5, 100, 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `lab_locations`
--

CREATE TABLE `lab_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_location_stock`
--

CREATE TABLE `lab_location_stock` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `action` varchar(255) NOT NULL,
  `mac` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `mac`, `created_at`) VALUES
(1, '13', 'LOGIN', '::1', '2025-10-15 15:42:44'),
(2, '13', 'LOGOUT', '::1', '2025-10-15 15:44:26'),
(3, '15', 'LOGIN', '::1', '2025-10-15 16:07:45'),
(4, '15', 'LOGOUT', '::1', '2025-10-16 09:04:56'),
(5, '15', 'LOGIN', '::1', '2025-10-16 09:05:07'),
(6, '15', 'LOGIN', '::1', '2025-10-16 09:18:43'),
(7, '15', 'LOGIN', '::1', '2025-10-16 09:22:07'),
(8, '15', 'LOGOUT', '::1', '2025-10-16 11:03:57'),
(9, '13', 'LOGIN', '::1', '2025-10-16 11:04:03'),
(10, '13', 'LOGOUT', '::1', '2025-10-16 11:13:04'),
(11, '14', 'LOGIN', '::1', '2025-10-16 11:13:13'),
(12, '14', 'LOGOUT', '::1', '2025-10-16 11:13:34'),
(13, '24', 'LOGIN', '::1', '2025-10-16 11:13:42'),
(14, '15', 'LOGIN', '::1', '2025-10-16 11:18:52'),
(15, '15', 'LOGOUT', '::1', '2025-10-16 11:25:14'),
(16, '15', 'LOGIN', '::1', '2025-10-16 13:50:28'),
(17, '15', 'LOGOUT', '::1', '2025-10-16 13:50:31'),
(18, '13', 'LOGIN', '::1', '2025-10-16 13:51:18'),
(19, '13', 'LOGOUT', '::1', '2025-10-16 13:52:48'),
(20, '14', 'LOGIN', '::1', '2025-10-16 13:53:02'),
(21, '14', 'LOGOUT', '::1', '2025-10-16 13:55:03'),
(22, '24', 'LOGIN', '::1', '2025-10-16 13:55:15'),
(23, '24', 'LOGOUT', '::1', '2025-10-16 13:57:42'),
(24, '24', 'LOGIN', '::1', '2025-10-16 13:57:51'),
(25, '24', 'LOGOUT', '::1', '2025-10-16 13:57:55'),
(26, '13', 'LOGIN', '::1', '2025-10-16 13:58:01'),
(27, '13', 'LOGOUT', '::1', '2025-10-16 13:58:48'),
(28, '14', 'LOGIN', '::1', '2025-10-16 13:59:19'),
(29, '14', 'LOGOUT', '::1', '2025-10-16 13:59:39'),
(30, '24', 'LOGIN', '::1', '2025-10-16 13:59:46'),
(31, '24', 'LOGOUT', '::1', '2025-10-16 15:23:12'),
(32, '23', 'LOGIN', '::1', '2025-10-16 15:23:23'),
(33, '23', 'LOGOUT', '::1', '2025-10-16 15:23:43'),
(34, '24', 'LOGIN', '::1', '2025-10-16 15:23:58'),
(35, '24', 'LOGOUT', '::1', '2025-10-16 15:24:15'),
(36, '25', 'LOGIN', '::1', '2025-10-16 15:24:24'),
(37, '25', 'LOGOUT', '::1', '2025-10-16 15:29:15'),
(38, '23', 'LOGIN', '::1', '2025-10-16 15:29:33'),
(39, '23', 'LOGOUT', '::1', '2025-10-16 15:31:02'),
(40, '27', 'LOGIN', '::1', '2025-10-16 15:44:42'),
(41, '13', 'LOGIN', '::1', '2025-10-17 10:00:55'),
(42, '13', 'LOGOUT', '::1', '2025-10-18 21:11:33'),
(43, '15', 'LOGIN', '::1', '2025-10-18 21:11:43'),
(44, '15', 'LOGIN', '::1', '2025-10-19 11:45:18'),
(45, '15', 'LOGOUT', '::1', '2025-10-20 09:04:45'),
(46, '15', 'LOGIN', '::1', '2025-10-20 09:04:53'),
(47, '15', 'LOGOUT', '::1', '2025-10-20 09:06:03'),
(48, '27', 'LOGIN', '::1', '2025-10-20 09:06:17'),
(49, '27', 'LOGOUT', '::1', '2025-10-20 09:21:06'),
(50, '27', 'LOGIN', '::1', '2025-10-20 09:21:18'),
(51, '13', 'LOGIN', '::1', '2025-10-20 09:26:27'),
(52, '13', 'LOGOUT', '::1', '2025-10-20 09:26:51'),
(53, '14', 'LOGIN', '::1', '2025-10-20 09:26:59'),
(54, '14', 'LOGOUT', '::1', '2025-10-20 09:27:14'),
(55, '24', 'LOGIN', '::1', '2025-10-20 09:27:21'),
(56, '24', 'LOGOUT', '::1', '2025-10-20 10:04:31'),
(57, '27', 'LOGIN', '::1', '2025-10-20 10:04:42'),
(58, '27', 'LOGOUT', '::1', '2025-10-20 10:26:04'),
(59, '27', 'LOGIN', '::1', '2025-10-20 10:56:02'),
(60, '27', 'LOGOUT', '::1', '2025-10-20 13:56:48'),
(61, '14', 'LOGIN', '::1', '2025-10-20 13:56:57'),
(62, '14', 'LOGOUT', '::1', '2025-10-20 13:57:07'),
(63, '23', 'LOGIN', '::1', '2025-10-20 13:57:15'),
(64, '23', 'LOGOUT', '::1', '2025-10-20 14:22:36'),
(65, '25', 'LOGIN', '::1', '2025-10-20 14:22:46'),
(66, '25', 'LOGOUT', '::1', '2025-10-21 09:24:54'),
(67, '15', 'LOGIN', '::1', '2025-10-21 09:25:04'),
(68, '15', 'LOGOUT', '::1', '2025-10-21 14:11:50'),
(69, '15', 'LOGIN', '::1', '2025-10-21 14:11:58'),
(70, '15', 'LOGOUT', '::1', '2025-10-21 14:12:52'),
(71, '15', 'LOGIN', '::1', '2025-10-21 14:13:00'),
(72, '15', 'LOGOUT', '::1', '2025-10-21 14:14:22'),
(73, '15', 'LOGIN', '::1', '2025-10-21 14:14:29'),
(74, '15', 'LOGOUT', '::1', '2025-10-21 14:15:08'),
(75, '15', 'LOGIN', '::1', '2025-10-21 14:29:54'),
(76, '15', 'LOGOUT', '::1', '2025-10-21 14:57:25'),
(77, '15', 'LOGIN', '::1', '2025-10-21 14:57:33'),
(78, '15', 'LOGOUT', '::1', '2025-10-21 15:08:15'),
(79, '15', 'LOGIN', '::1', '2025-10-21 15:08:24'),
(80, '15', 'LOGOUT', '::1', '2025-10-21 15:31:01'),
(81, '13', 'LOGIN', '::1', '2025-10-21 15:31:09'),
(82, '13', 'LOGOUT', '::1', '2025-10-21 15:31:37'),
(83, '14', 'LOGIN', '::1', '2025-10-21 15:31:50'),
(84, '14', 'LOGOUT', '::1', '2025-10-21 15:32:05'),
(85, '24', 'LOGIN', '::1', '2025-10-21 15:32:13'),
(86, '24', 'LOGOUT', '::1', '2025-10-21 16:04:45'),
(87, '15', 'LOGIN', '::1', '2025-10-21 16:04:53'),
(88, '15', 'LOGOUT', '::1', '2025-10-21 16:14:10'),
(89, '24', 'LOGIN', '::1', '2025-10-21 16:14:18'),
(90, '24', 'LOGOUT', '::1', '2025-10-21 17:12:20'),
(91, '24', 'LOGIN', '::1', '2025-10-21 17:12:28'),
(92, '24', 'LOGOUT', '::1', '2025-10-22 09:11:00'),
(93, '13', 'LOGIN', '::1', '2025-10-22 09:12:37'),
(94, '13', 'LOGOUT', '::1', '2025-10-22 09:13:02'),
(95, '14', 'LOGIN', '::1', '2025-10-22 09:13:11'),
(96, '14', 'LOGOUT', '::1', '2025-10-22 09:13:26'),
(97, '24', 'LOGIN', '::1', '2025-10-22 09:13:35'),
(98, '24', 'LOGOUT', '::1', '2025-10-22 09:15:29'),
(99, '24', 'LOGIN', '::1', '2025-10-22 09:15:37'),
(100, '24', 'LOGOUT', '::1', '2025-10-22 09:32:09'),
(101, '24', 'LOGIN', '::1', '2025-10-22 09:32:15'),
(102, '24', 'LOGOUT', '::1', '2025-10-22 09:40:48'),
(103, '13', 'LOGIN', '::1', '2025-10-22 09:40:57'),
(104, '13', 'LOGOUT', '::1', '2025-10-22 09:41:19'),
(105, '14', 'LOGIN', '::1', '2025-10-22 09:41:34'),
(106, '14', 'LOGOUT', '::1', '2025-10-22 09:41:55'),
(107, '24', 'LOGIN', '::1', '2025-10-22 09:42:03'),
(108, '24', 'LOGOUT', '::1', '2025-10-22 10:32:26'),
(109, '24', 'LOGIN', '::1', '2025-10-22 10:32:39'),
(110, '24', 'LOGOUT', '::1', '2025-10-22 10:45:38'),
(111, '25', 'LOGIN', '::1', '2025-10-22 10:45:47'),
(112, '25', 'LOGOUT', '::1', '2025-10-22 10:49:33'),
(113, '23', 'LOGIN', '::1', '2025-10-22 10:49:43'),
(114, '23', 'LOGOUT', '::1', '2025-10-22 11:21:13'),
(115, '24', 'LOGIN', '::1', '2025-10-22 11:21:21'),
(116, '24', 'LOGOUT', '::1', '2025-10-22 11:22:52'),
(117, '25', 'LOGIN', '::1', '2025-10-22 11:22:59'),
(118, '25', 'LOGOUT', '::1', '2025-10-22 11:23:33'),
(119, '23', 'LOGIN', '::1', '2025-10-22 11:23:40'),
(120, '23', 'LOGOUT', '::1', '2025-10-22 11:25:02'),
(121, '15', 'LOGIN', '::1', '2025-10-22 11:25:34'),
(122, '15', 'LOGOUT', '::1', '2025-10-22 11:41:37'),
(123, '24', 'LOGIN', '::1', '2025-10-22 11:41:43'),
(124, '24', 'LOGOUT', '::1', '2025-10-22 11:46:02'),
(125, '15', 'LOGIN', '::1', '2025-10-22 11:46:11'),
(126, '15', 'LOGIN', '::1', '2025-10-22 11:46:44'),
(127, '15', 'LOGIN', '::1', '2025-10-22 11:47:18'),
(128, '15', 'LOGOUT', '::1', '2025-10-22 12:03:42'),
(129, '24', 'LOGIN', '::1', '2025-10-22 12:03:50'),
(130, '24', 'LOGOUT', '::1', '2025-10-22 12:09:04'),
(131, '15', 'LOGIN', '::1', '2025-10-22 12:09:15'),
(132, '15', 'LOGOUT', '::1', '2025-10-22 12:32:15'),
(133, '24', 'LOGIN', '::1', '2025-10-22 12:32:23'),
(134, '24', 'LOGOUT', '::1', '2025-10-22 12:32:35'),
(135, '24', 'LOGIN', '::1', '2025-10-22 12:32:41'),
(136, '24', 'LOGOUT', '::1', '2025-10-22 12:44:57'),
(137, '24', 'LOGIN', '::1', '2025-10-22 12:45:03'),
(138, '24', 'LOGOUT', '::1', '2025-10-22 12:50:49'),
(139, '21', 'LOGIN', '::1', '2025-10-22 12:51:01'),
(140, '21', 'LOGOUT', '::1', '2025-10-22 12:52:34'),
(141, '24', 'LOGIN', '::1', '2025-10-22 12:52:40'),
(142, '24', 'LOGOUT', '::1', '2025-10-22 12:53:04'),
(143, '21', 'LOGIN', '::1', '2025-10-22 12:53:16'),
(144, '21', 'LOGOUT', '::1', '2025-10-22 12:53:32'),
(145, '24', 'LOGIN', '::1', '2025-10-22 12:53:38'),
(146, '24', 'LOGOUT', '::1', '2025-10-22 12:54:02'),
(147, '24', 'LOGIN', '::1', '2025-10-22 12:54:14'),
(148, '24', 'LOGOUT', '::1', '2025-10-22 12:59:59'),
(149, '26', 'LOGIN', '::1', '2025-10-22 13:10:03'),
(150, '26', 'LOGOUT', '::1', '2025-10-22 13:10:49'),
(151, '24', 'LOGIN', '::1', '2025-10-22 13:10:59'),
(152, '24', 'LOGOUT', '::1', '2025-10-22 13:11:20'),
(153, '23', 'LOGIN', '::1', '2025-10-22 13:11:29'),
(154, '23', 'LOGOUT', '::1', '2025-10-22 13:38:43'),
(155, '24', 'LOGIN', '::1', '2025-10-22 13:38:51'),
(156, '24', 'LOGOUT', '::1', '2025-10-22 13:41:24'),
(157, '15', 'LOGIN', '::1', '2025-10-22 13:41:33'),
(158, '15', 'LOGOUT', '::1', '2025-10-22 14:01:13'),
(159, '24', 'LOGIN', '::1', '2025-10-22 14:01:22'),
(160, '24', 'LOGOUT', '::1', '2025-10-22 14:01:29'),
(161, '13', 'LOGIN', '::1', '2025-10-22 14:01:35'),
(162, '13', 'LOGOUT', '::1', '2025-10-22 14:21:58'),
(163, '14', 'LOGIN', '::1', '2025-10-22 14:22:05'),
(164, '14', 'LOGOUT', '::1', '2025-10-22 14:22:21'),
(165, '24', 'LOGIN', '::1', '2025-10-22 14:22:28'),
(166, '24', 'LOGOUT', '::1', '2025-10-22 14:22:53'),
(167, '13', 'LOGIN', '::1', '2025-10-23 08:51:29'),
(168, '13', 'LOGOUT', '::1', '2025-10-23 08:52:01'),
(169, '14', 'LOGIN', '::1', '2025-10-23 08:52:10'),
(170, '14', 'LOGOUT', '::1', '2025-10-23 08:52:31'),
(171, '24', 'LOGIN', '::1', '2025-10-23 08:52:38'),
(172, '24', 'LOGOUT', '::1', '2025-10-23 08:53:55'),
(173, '13', 'LOGIN', '::1', '2025-10-23 10:09:50'),
(174, '13', 'LOGOUT', '::1', '2025-10-23 10:13:10'),
(175, '14', 'LOGIN', '::1', '2025-10-23 10:13:25'),
(176, '14', 'LOGOUT', '::1', '2025-10-23 10:21:50'),
(177, '24', 'LOGIN', '::1', '2025-10-23 10:26:50'),
(178, '24', 'LOGOUT', '::1', '2025-10-23 10:35:22'),
(179, '13', 'LOGIN', '::1', '2025-10-23 10:35:30'),
(180, '13', 'LOGOUT', '::1', '2025-10-23 10:38:40'),
(181, '14', 'LOGIN', '::1', '2025-10-23 10:38:48'),
(182, '14', 'LOGOUT', '::1', '2025-10-23 10:39:00'),
(183, '13', 'LOGIN', '::1', '2025-10-23 10:39:10'),
(184, '13', 'LOGOUT', '::1', '2025-10-23 10:40:52'),
(185, '14', 'LOGIN', '::1', '2025-10-23 10:41:02'),
(186, '14', 'LOGOUT', '::1', '2025-10-23 10:43:54'),
(187, '24', 'LOGIN', '::1', '2025-10-23 10:44:01'),
(188, '24', 'LOGOUT', '::1', '2025-10-23 10:49:32'),
(189, '25', 'LOGIN', '::1', '2025-10-23 10:49:41'),
(190, '25', 'LOGOUT', '::1', '2025-10-23 10:50:12'),
(191, '23', 'LOGIN', '::1', '2025-10-23 10:50:23'),
(192, '23', 'LOGOUT', '::1', '2025-10-23 10:51:12'),
(193, '25', 'LOGIN', '::1', '2025-10-23 10:51:20'),
(194, '25', 'LOGOUT', '::1', '2025-10-23 10:52:13'),
(195, '15', 'LOGIN', '::1', '2025-10-23 10:52:30'),
(196, '15', 'LOGOUT', '::1', '2025-10-23 10:56:40'),
(197, '27', 'LOGIN', '::1', '2025-10-23 10:56:56'),
(198, '27', 'LOGOUT', '::1', '2025-10-23 10:59:39'),
(199, '15', 'LOGIN', '::1', '2025-10-23 10:59:47'),
(200, '15', 'LOGOUT', '::1', '2025-10-23 11:29:15'),
(201, '24', 'LOGIN', '::1', '2025-10-23 11:29:23'),
(202, '24', 'LOGOUT', '::1', '2025-10-23 14:29:05'),
(203, '13', 'LOGIN', '::1', '2025-10-23 14:29:13'),
(204, '13', 'LOGOUT', '::1', '2025-10-23 14:29:16'),
(205, '14', 'LOGIN', '::1', '2025-10-23 14:29:23'),
(206, '14', 'LOGOUT', '::1', '2025-10-23 14:31:18'),
(207, '15', 'LOGIN', '::1', '2025-10-24 09:17:15'),
(208, '15', 'LOGOUT', '::1', '2025-10-26 10:01:32'),
(209, '13', 'LOGIN', '::1', '2025-10-26 10:01:46'),
(210, '13', 'LOGOUT', '::1', '2025-10-26 10:10:43'),
(211, '13', 'LOGIN', '::1', '2025-10-26 10:10:50'),
(212, '13', 'LOGOUT', '::1', '2025-10-26 10:11:33'),
(213, '13', 'LOGIN', '::1', '2025-10-26 10:11:41'),
(214, '13', 'LOGOUT', '::1', '2025-10-26 11:15:27'),
(215, '13', 'LOGIN', '::1', '2025-10-26 11:15:36'),
(216, '13', 'LOGOUT', '::1', '2025-10-27 08:49:27'),
(217, '13', 'LOGIN', '::1', '2025-10-27 08:49:36'),
(218, '13', 'LOGOUT', '::1', '2025-10-27 09:24:01'),
(219, '13', 'LOGIN', '::1', '2025-10-27 09:24:08'),
(220, '13', 'LOGOUT', '::1', '2025-10-27 09:24:36'),
(221, '14', 'LOGIN', '::1', '2025-10-27 09:24:46'),
(222, '14', 'LOGOUT', '::1', '2025-10-27 09:25:05'),
(223, '24', 'LOGIN', '::1', '2025-10-27 09:25:21'),
(224, '24', 'LOGOUT', '::1', '2025-11-04 11:34:43'),
(225, '15', 'LOGIN', '::1', '2025-11-04 11:34:55'),
(226, '15', 'LOGIN', '::1', '2025-11-20 10:17:28'),
(227, '21', 'LOGIN', '::1', '2025-11-20 11:50:31'),
(228, '21', 'LOGOUT', '::1', '2025-11-20 11:50:36'),
(229, '15', 'LOGIN', '::1', '2025-11-20 11:50:43'),
(230, '15', 'LOGOUT', '::1', '2025-11-20 12:02:21'),
(231, '15', 'LOGIN', '::1', '2025-11-20 12:11:20'),
(232, '15', 'LOGOUT', '::1', '2025-11-20 12:27:40'),
(233, '15', 'LOGIN', '::1', '2025-11-20 12:27:50'),
(234, '15', 'LOGOUT', '::1', '2025-11-20 12:30:00'),
(235, '15', 'LOGIN', '::1', '2025-11-20 12:30:10'),
(236, '15', 'LOGOUT', '::1', '2025-11-20 13:12:23'),
(237, '15', 'LOGIN', '::1', '2025-11-20 13:12:30'),
(238, '15', 'LOGOUT', '::1', '2025-11-20 13:31:23'),
(239, '15', 'LOGIN', '::1', '2025-11-20 13:31:29'),
(240, '15', 'LOGOUT', '::1', '2025-11-20 13:55:55'),
(241, '15', 'LOGIN', '::1', '2025-11-20 13:56:03'),
(242, '15', 'LOGOUT', '::1', '2025-11-20 14:07:31'),
(243, '15', 'LOGIN', '::1', '2025-11-20 14:07:41'),
(244, '15', 'LOGOUT', '::1', '2025-11-20 15:02:56'),
(245, '15', 'LOGIN', '::1', '2025-11-20 15:03:03'),
(246, '15', 'LOGIN', '::1', '2025-11-20 15:21:20'),
(247, '15', 'LOGOUT', '::1', '2025-11-21 09:45:56'),
(248, '14', 'LOGIN', '::1', '2025-11-21 09:46:06'),
(249, '14', 'LOGOUT', '::1', '2025-11-21 09:46:10'),
(250, '15', 'LOGIN', '::1', '2025-11-21 09:46:17'),
(251, '15', 'LOGOUT', '::1', '2025-11-25 09:27:41'),
(252, '15', 'LOGIN', '::1', '2025-11-25 09:27:49'),
(253, '15', 'LOGIN', '::1', '2025-11-27 11:21:48'),
(254, '15', 'LOGIN', '::1', '2025-11-27 11:30:07'),
(255, '15', 'LOGOUT', '::1', '2025-11-27 11:45:43'),
(256, '25', 'LOGIN', '::1', '2025-11-27 11:45:52'),
(257, '25', 'LOGOUT', '::1', '2025-11-27 11:46:48'),
(258, '24', 'LOGIN', '::1', '2025-11-27 11:47:01'),
(259, '24', 'LOGOUT', '::1', '2025-11-27 11:47:19'),
(260, '13', 'LOGIN', '::1', '2025-11-27 11:47:27'),
(261, '13', 'LOGOUT', '::1', '2025-11-27 11:49:47'),
(262, '14', 'LOGIN', '::1', '2025-11-27 11:49:55'),
(263, '14', 'LOGOUT', '::1', '2025-11-27 11:50:10'),
(264, '24', 'LOGIN', '::1', '2025-11-27 11:50:18'),
(265, '24', 'LOGOUT', '::1', '2025-11-27 12:18:31'),
(266, '21', 'LOGIN', '::1', '2025-11-27 12:18:45'),
(267, '21', 'LOGOUT', '::1', '2025-11-27 12:18:53'),
(268, '15', 'LOGIN', '::1', '2025-11-27 12:18:59'),
(269, '15', 'LOGIN', '::1', '2025-11-28 10:32:18'),
(270, '15', 'LOGIN', '::1', '2025-11-28 10:32:29'),
(271, '15', 'LOGIN', '::1', '2025-11-28 10:58:02'),
(272, '15', 'LOGOUT', '::1', '2025-11-29 14:34:47'),
(273, '13', 'LOGIN', '::1', '2025-11-29 14:34:57'),
(274, '13', 'LOGOUT', '::1', '2025-11-29 14:37:44'),
(275, '14', 'LOGIN', '::1', '2025-11-29 14:37:54'),
(276, '14', 'LOGOUT', '::1', '2025-11-29 14:38:30'),
(277, '24', 'LOGIN', '::1', '2025-11-29 14:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `nurse_consumables`
--

CREATE TABLE `nurse_consumables` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nurse_consumables`
--

INSERT INTO `nurse_consumables` (`id`, `name`, `category`) VALUES
(1, 'Hand sanitizer', 'platic');

-- --------------------------------------------------------

--
-- Table structure for table `nurse_consumable_stock`
--

CREATE TABLE `nurse_consumable_stock` (
  `id` int(11) NOT NULL,
  `consumable_id` int(11) DEFAULT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nurse_consumable_stock`
--

INSERT INTO `nurse_consumable_stock` (`id`, `consumable_id`, `campus_id`, `quantity`) VALUES
(1, 1, 5, 380),
(2, 1, 3, 20);

-- --------------------------------------------------------

--
-- Table structure for table `outpatient_visist_record`
--

CREATE TABLE `outpatient_visist_record` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `patid` int(20) NOT NULL,
  `name` varchar(300) NOT NULL,
  `diagnosis` varchar(300) NOT NULL,
  `proceedure` varchar(300) NOT NULL,
  `plan` varchar(500) NOT NULL,
  `doc_incharge` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outpatient_visist_record`
--

INSERT INTO `outpatient_visist_record` (`id`, `date`, `patid`, `name`, `diagnosis`, `proceedure`, `plan`, `doc_incharge`) VALUES
(1, '2025', 0, 'MR ABIODUN SHERIF SEUN', '', '', '', ''),
(2, '2025', 0, 'MR ABIODUN SHERIF SEUN', '', '', '', ''),
(3, '2025', 0, 'MR ABIODUN SHERIF SEUN', 'COUGH,COUGH', '                                                              ', '                                                              ', ''),
(4, '2025', 0, 'MR ABIODUN SHERIF SEUN', 'COUGH,COUGH', '                                                              ', '                                                              ', ''),
(5, '2025', 0, 'MR ABIODUN SHERIF SEUN', 'COUGH,COUGH', '                                                              ', '                                                              ', ''),
(6, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(7, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(8, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(9, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(10, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(11, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(12, '2025', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(13, '2025-10-16', 0, 'OLATUNBOSUN OYINLOLA', '                                                              ', '                                                              ', '                                                              ', ''),
(14, '2025-10-22', 0, ';', '                                                              ', '                                                              ', '                                                              ', ''),
(15, '2025-10-22', 0, ';', '                                                              ', '                                                              ', '                                                              ', ''),
(16, '2025-10-22', 0, ';', '                                                              ', '                                                              ', '                                                              ', ''),
(17, '2025-10-22', 0, 'Adeyemi  Nurudeen Adewale', '                                                              ', '                                                              ', '                                                              ', ''),
(18, '2025-10-22', 0, 'OLATUNBOSUN OYINLOLA MICHAEL', '                                                              ', '                                                              ', '                                                              ', ''),
(19, '2025-10-23', 0, 'Adeyemi  Nurudeen Adewale', '                                                              ', '                                                              ', '                                                              ', '');

-- --------------------------------------------------------

--
-- Table structure for table `patient_bill`
--

CREATE TABLE `patient_bill` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `patid` varchar(20) NOT NULL,
  `name` varchar(300) NOT NULL,
  `const` varchar(300) NOT NULL,
  `proceedure` varchar(300) NOT NULL,
  `total` varchar(500) NOT NULL,
  `doc_incharge` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_bill`
--

INSERT INTO `patient_bill` (`id`, `date`, `patid`, `name`, `const`, `proceedure`, `total`, `doc_incharge`) VALUES
(1, '2025-09-20', 'F533325226', 'MR ABIODUN SHERIF SEUN', '', '', '0', ''),
(2, '2025-09-20', 'F533325226', 'MR ABIODUN SHERIF SEUN', '', '', '0', ''),
(3, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', '200', '200000', '200200', ''),
(4, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', '200', '200000', '200200', ''),
(5, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', '200', '200000', '200200', ''),
(6, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(7, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(8, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(9, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(10, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(11, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(12, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '200', '0', '200', ''),
(13, '2025-10-16', 'IND44577', 'OLATUNBOSUN OYINLOLA', '0', '0', '0', ''),
(14, '2025-10-22', '', ';', '0', '0', '0', ''),
(15, '2025-10-22', '', ';', '0', '0', '0', ''),
(16, '2025-10-22', '', ';', '0', '0', '0', ''),
(17, '2025-10-22', 'S4310', 'Adeyemi  Nurudeen Adewale', '0', '0', '0', ''),
(18, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '0', '0', '0', ''),
(19, '2025-10-23', 'S4310', 'Adeyemi  Nurudeen Adewale', '0', '0', '0', '');

-- --------------------------------------------------------

--
-- Table structure for table `patient_drug_history`
--

CREATE TABLE `patient_drug_history` (
  `id` int(10) NOT NULL,
  `date` varchar(50) NOT NULL,
  `patid` varchar(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `drug` varchar(100) NOT NULL,
  `qnt` varchar(20) NOT NULL,
  `const` varchar(20) NOT NULL,
  `duration` varchar(20) NOT NULL,
  `total` varchar(20) NOT NULL,
  `totdrug` varchar(50) NOT NULL,
  `amnt` varchar(20) NOT NULL,
  `cate` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_drug_history`
--

INSERT INTO `patient_drug_history` (`id`, `date`, `patid`, `name`, `drug`, `qnt`, `const`, `duration`, `total`, `totdrug`, `amnt`, `cate`) VALUES
(1, '2025-09-20', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PARASTAMOL 500G', '1', '3hly', '1/7', '8', '8Tab', '320', 'Tab'),
(2, '2025-09-20', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PARASTAMOL 500G', '1', '3hly', '1/7', '8', '8Tab', '320', 'Tab'),
(3, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PARASTAMOL 500G', '1', '3hly', '6/7', '48', '48Tab', '1920', 'Tab'),
(4, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PARASTAMOL 500G', '1', '3hly', '6/7', '48', '48Tab', '1920', 'Tab'),
(5, '2025-09-21', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PARASTAMOL 500G', '1', '3hly', '6/7', '48', '48Tab', '1920', 'Tab'),
(6, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'PARASTAMOL 500G', '5', '3hly', '5/7', '200', '200Tab', '8000', 'Tab'),
(7, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'SHANTOX', '1', '3', '5/7', '0', '0Tab', '0', 'Tab'),
(8, '2025-10-16', 'IND44577', 'OLATUNBOSUN OYINLOLA', 'CHLOROQUINN', '2', '12hly', '1/7', '4', '4Tab', '0', 'Tab'),
(9, '2025-10-22', '', ';', 'PARACETAMOL 500G', '2', '12hly', '3/7', '12', '12Tab', '480', 'Tab'),
(10, '2025-10-22', '', ';', 'CHLOROQUINN', '2', '12hly', '3/7', '12', '12Tab', '0', 'Tab'),
(11, '2025-10-22', '', ';', 'SHANTOX', '2', '12hly', '3/7', '12', '12Tab', '1200', 'Tab'),
(12, '2025-10-22', 'S4310', 'Adeyemi  Nurudeen Adewale', 'PARACETAMOL 500G', '2', '12hly', '3/7', '12', '12Tab', '480', 'Tab'),
(13, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'PARACETAMOL 500G', '2', '12hly', '1/7', '4', '4Tab', '160', 'Tab'),
(14, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'AMOXILIN 500G', '2', '12hly', '1/7', '4', '4Tab', '0', 'Tab'),
(15, '2025-10-23', 'S4310', 'Adeyemi  Nurudeen Adewale', 'PARACETAMOL 500G', '2', '8hly', '3/7', '18', '18Tab', '720', 'Tab');

-- --------------------------------------------------------

--
-- Table structure for table `patient_lab`
--

CREATE TABLE `patient_lab` (
  `id` int(10) NOT NULL,
  `date` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(300) NOT NULL,
  `test` varchar(300) NOT NULL,
  `result` varchar(500) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_lab`
--

INSERT INTO `patient_lab` (`id`, `date`, `code`, `name`, `test`, `result`, `category`) VALUES
(1, '2025-10-10', 'IND45592', 'TEMITAYO BLESSED OTITE', 'PCV', 'POSSITIVE RESULT WITH ZERO NEGATIVE POINTS', 'INDIVIDUAL CARD'),
(2, '2025-10-10', 'IND45592', 'TEMITAYO BLESSED OTITE', 'PCV', 'POSSITIVE RESULT WITH ZERO NEGATIVE POINTS', 'INDIVIDUAL CARD'),
(7, '2025-10-13', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'PCV', 'PCV RESULT FOR PATIENT OLATUNBOSUN IS 88% POSSITIVE OKAY                           ', ''),
(8, '2025-10-15', 'S4310', 'Adeyemi  Nurudeen Adewale', 'MALARIA PARASITE', '                                                              -ve', ''),
(9, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'MALARIA PARASITE', '                                                              Test -ve', ''),
(10, '2025-10-22', 'S4310', 'Adeyemi  Nurudeen Adewale', 'MALARIA PARASITE,MALARIA PARASITE', '                                                              Test -ve', '');

-- --------------------------------------------------------

--
-- Table structure for table `patient_scan`
--

CREATE TABLE `patient_scan` (
  `id` int(10) NOT NULL,
  `date` varchar(100) NOT NULL,
  `code` varchar(200) NOT NULL,
  `name` varchar(300) NOT NULL,
  `test` varchar(100) NOT NULL,
  `result` varchar(500) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_scan`
--

INSERT INTO `patient_scan` (`id`, `date`, `code`, `name`, `test`, `result`, `category`) VALUES
(1, '2025-10-01', 'IND45592', 'TEMITAYO BLESSED OTITE', 'PELVIC SCAN', '                  RESULT POSTED FOR PELVIC SCAN                                     ', 'INDIVIDUAL CARD'),
(2, '2025-10-02', 'IND45523', 'ADEBOWALE SAMUEL LAWAL', 'PELVIC SCAN', 'RESULT IS NEGATIVE MEANING THAT THE PELVIC GIRDLE IS PERFECTLY OKAY.                ', 'INDIVIDUAL CARD'),
(3, '2025-10-02', 'F533325226', 'MR ABIODUN SHERIF SEUN', 'PRENANCY SCAN', '                                                              PREGNANCY TEST DONE, POSSITIVE RESULT', 'FAMILY CARD'),
(4, '2025-10-04', 'IND45523', 'ADEBOWALE SAMUEL LAWAL', 'PELVIC SCAN,PCV', 'SHORT OF CALORIES IN THE SYSTEM RESULT INTO THIS                                                              ', ''),
(5, '2025-10-13', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'LUMBA SCAN,HEAD SCAN', 'lumba resul has come ou succesfull                                               ', ''),
(10, '2025-10-15', 'ST43173', 'Adeyemi Nurudeen', 'PRENANCY SCAN', '                                                              -ve', ''),
(11, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'HEAD SCAN', '                                                              scan  -ve', ''),
(12, '2025-10-22', 'S4310', 'Adeyemi  Nurudeen Adewale', 'HEAD SCAN,HEAD SCAN', '                                              scan -ve                ', '');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `teller` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `bank` varchar(100) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `officer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `date`, `teller`, `name`, `bank`, `mode`, `amount`, `officer`) VALUES
(3, '2025-07-11', '', 'OLABANJI OLUWASEYI', 'PETTY CASH', 'Cash', '2500', 'OLABANJI OLUWASEUN'),
(4, '2025-07-11', '', 'OLABANJI OLUWASEYI', 'STANBIC IBTC', 'POS', '5000', 'OLABANJI OLUWASEUN'),
(5, '2025-07-11', 'INV4373', 'OLABANJI OLUWASEYI', 'PETTY CASH', 'Cash', '2000', 'OLABANJI OLUWASEUN'),
(6, '2025-07-11', 'INV4373', 'OLABANJI OLUWASEYI', 'OPAY ACCOUNT', 'Transfer', '6000', 'OLABANJI OLUWASEUN'),
(7, '2025-07-24', 'INV2147', '', 'PETTY CASH', 'Cash', '5000', 'OLABANJI OLUWASEUN'),
(8, '2025/7/28', 'Ph7765', 'OLADAYO IDUNNU', 'PETTY CASH', 'Cash', '2,000.00', 'OLABANJI OLUWASEUN'),
(9, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '', '', '', 'ADEYEMI NURUDEEN ADEWALE'),
(10, '2025-09-26', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '', '', '', 'ADEYEMI NURUDEEN ADEWALE'),
(11, '2025-10-22', 'S4310', 'Adeyemi  Nurudeen Adewale', 'STANBIC IBTC', 'Transfer', '480.00', 'ADEYEMI NURUDEEN ADEWALE'),
(12, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'STANBIC IBTC', 'Transfer', '160.00', 'ADEYEMI NURUDEEN ADEWALE'),
(13, '2025-10-22', 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', 'STANBIC IBTC', 'Transfer', '160.00', 'ADEYEMI NURUDEEN ADEWALE'),
(14, '2025-10-23', 'S4310', 'Adeyemi  Nurudeen Adewale', 'STANBIC IBTC', 'Transfer', '720.00', 'ADEYEMI NURUDEEN ADEWALE');

-- --------------------------------------------------------

--
-- Table structure for table `pcart`
--

CREATE TABLE `pcart` (
  `id` int(10) NOT NULL,
  `date` varchar(100) NOT NULL,
  `drug` varchar(200) NOT NULL,
  `qnt` varchar(50) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy`
--

CREATE TABLE `pharmacy` (
  `id` int(10) NOT NULL,
  `name` varchar(200) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `category` varchar(100) NOT NULL,
  `pharmacy_location_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy`
--

INSERT INTO `pharmacy` (`id`, `name`, `quantity`, `amount`, `category`, `pharmacy_location_id`) VALUES
(2, 'PARACETAMOL 500G', '110', '40', 'Tab', NULL),
(3, 'SHANTOX', '56', '100', 'Tab', NULL),
(6, 'CHLOROQUINE', '200', '0', 'TAB', NULL),
(7, 'AMOXILIN 500G', '400', '0', 'Tab', NULL),
(8, 'PARACETAMOL 500G', '10', '0', 'Tab', 4);

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_invoice`
--

CREATE TABLE `pharmacy_invoice` (
  `id` int(10) NOT NULL,
  `date` varchar(200) NOT NULL,
  `time` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `pharmacyid` varchar(100) NOT NULL,
  `drug` varchar(200) NOT NULL,
  `qnt` varchar(100) NOT NULL,
  `amount` varchar(200) NOT NULL,
  `const` varchar(200) NOT NULL,
  `officer` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy_invoice`
--

INSERT INTO `pharmacy_invoice` (`id`, `date`, `time`, `name`, `pharmacyid`, `drug`, `qnt`, `amount`, `const`, `officer`) VALUES
(7, '2025-10-23', '11:51 am', 'Adeyemi  Nurudeen Adewale', 'S4310', 'PARACETAMOL 500G', '2', '720', '8hly', 'ADEYEMI NURUDEEN ADEWALE');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_location`
--

CREATE TABLE `pharmacy_location` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy_location`
--

INSERT INTO `pharmacy_location` (`id`, `name`) VALUES
(1, 'Main Pharmacy'),
(2, 'Ibogun Campus Pharmacy'),
(3, 'Mini Campus Pharmacy'),
(4, 'Ayetoro Campus Pharmacy');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_location_stock`
--

CREATE TABLE `pharmacy_location_stock` (
  `id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `moved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy_location_stock`
--

INSERT INTO `pharmacy_location_stock` (`id`, `drug_id`, `location_id`, `quantity`, `moved_at`) VALUES
(1, 1, 4, 10, '2025-11-27 11:29:34'),
(2, 1, 4, 10, '2025-11-27 11:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_order`
--

CREATE TABLE `pharmacy_order` (
  `id` int(10) NOT NULL,
  `trackid` varchar(100) NOT NULL,
  `customer` varchar(200) NOT NULL,
  `drug` varchar(200) NOT NULL,
  `Qnt` varchar(50) NOT NULL,
  `const` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL,
  `status` varchar(50) NOT NULL,
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy_order`
--

INSERT INTO `pharmacy_order` (`id`, `trackid`, `customer`, `drug`, `Qnt`, `const`, `amount`, `status`, `date`) VALUES
(23, 'S4310', 'Adeyemi  Nurudeen Adewale', 'PARACETAMOL 500G', '2', '8hly', '720', 'Paid', '2025-10-23');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_stock`
--

CREATE TABLE `pharmacy_stock` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `opening` varchar(50) NOT NULL,
  `addstock` varchar(10) NOT NULL,
  `closing` varchar(10) NOT NULL,
  `date` varchar(20) NOT NULL,
  `pharmacy_location_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy_stock`
--

INSERT INTO `pharmacy_stock` (`id`, `name`, `opening`, `addstock`, `closing`, `date`, `pharmacy_location_id`) VALUES
(1, 'CHOLOQUEEN', '21', '4', '25', '2025-06-24', 1),
(2, 'PARACETAMOL 500G', '50', '0', '50', '2025-06-24', 1),
(4, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-03', 1),
(5, 'SHANTOX', '6', '0', '6', '2025-07-03', 1),
(7, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-05', 1),
(8, 'SHANTOX', '6', '0', '6', '2025-07-05', 1),
(10, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-08', 1),
(11, 'SHANTOX', '6', '0', '6', '2025-07-08', 1),
(13, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-09', 1),
(14, 'SHANTOX', '6', '0', '6', '2025-07-09', 1),
(16, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-10', 1),
(19, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-11', 1),
(20, 'SHANTOX', '6', '0', '6', '2025-07-11', 1),
(22, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-13', 1),
(23, 'SHANTOX', '6', '0', '6', '2025-07-13', 1),
(25, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-24', 1),
(26, 'SHANTOX', '6', '0', '6', '2025-07-24', 1),
(28, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-29', 1),
(29, 'SHANTOX', '6', '0', '6', '2025-07-29', 1),
(31, 'PARACETAMOL 500G', '50', '0', '50', '2025-07-31', 1),
(32, 'SHANTOX', '6', '0', '6', '2025-07-31', 1),
(34, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-08', 1),
(35, 'SHANTOX', '6', '0', '6', '2025-08-08', 1),
(37, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-13', 1),
(38, 'SHANTOX', '6', '0', '6', '2025-08-13', 1),
(40, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-14', 1),
(41, 'SHANTOX', '6', '0', '6', '2025-08-14', 1),
(43, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-16', 1),
(44, 'SHANTOX', '6', '0', '6', '2025-08-16', 1),
(46, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-17', 1),
(47, 'SHANTOX', '6', '0', '6', '2025-08-17', 1),
(49, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-19', 1),
(50, 'SHANTOX', '6', '0', '6', '2025-08-19', 1),
(52, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-20', 1),
(53, 'SHANTOX', '6', '0', '6', '2025-08-20', 1),
(55, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-27', 1),
(56, 'SHANTOX', '6', '0', '6', '2025-08-27', 1),
(58, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-28', 1),
(59, 'SHANTOX', '6', '0', '6', '2025-08-28', 1),
(61, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-29', 1),
(62, 'SHANTOX', '6', '0', '6', '2025-08-29', 1),
(64, 'PARACETAMOL 500G', '50', '0', '50', '2025-08-30', 1),
(65, 'SHANTOX', '6', '0', '6', '2025-08-30', 1),
(67, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-11', 1),
(68, 'SHANTOX', '6', '0', '6', '2025-09-11', 1),
(70, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-16', 1),
(71, 'SHANTOX', '6', '0', '6', '2025-09-16', 1),
(73, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-20', 1),
(74, 'SHANTOX', '6', '0', '6', '2025-09-20', 1),
(76, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-21', 1),
(77, 'SHANTOX', '6', '0', '6', '2025-09-21', 1),
(79, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-25', 1),
(80, 'SHANTOX', '6', '0', '6', '2025-09-25', 1),
(82, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-26', 1),
(83, 'SHANTOX', '6', '0', '6', '2025-09-26', 1),
(85, 'PARACETAMOL 500G', '50', '0', '50', '2025-09-29', 1),
(86, 'SHANTOX', '6', '0', '6', '2025-09-29', 1),
(88, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-01', 1),
(89, 'SHANTOX', '6', '0', '6', '2025-10-01', 1),
(91, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-02', 1),
(92, 'SHANTOX', '6', '0', '6', '2025-10-02', 1),
(94, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-04', 1),
(95, 'SHANTOX', '6', '0', '6', '2025-10-04', 1),
(97, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-10', 1),
(98, 'SHANTOX', '6', '0', '6', '2025-10-10', 1),
(100, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-13', 1),
(101, 'SHANTOX', '6', '0', '6', '2025-10-13', 1),
(103, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-14', 1),
(104, 'SHANTOX', '6', '0', '6', '2025-10-14', 1),
(106, 'PARACETAMOL 500G', '50', '0', '50', '2025-10-15', 1),
(107, 'SHANTOX', '6', '0', '6', '2025-10-15', 1),
(109, 'PARACETAMOL 500G', '50', '50', '50', '2025-10-16', 1),
(110, 'SHANTOX', '6', '0', '6', '2025-10-16', 1),
(112, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-17', 1),
(113, 'SHANTOX', '6', '0', '6', '2025-10-17', 1),
(116, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-18', 1),
(117, 'SHANTOX', '6', '0', '6', '2025-10-18', 1),
(120, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-19', 1),
(121, 'SHANTOX', '6', '0', '6', '2025-10-19', 1),
(124, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-20', 1),
(125, 'SHANTOX', '6', '0', '6', '2025-10-20', 1),
(128, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-21', 1),
(129, 'SHANTOX', '6', '0', '6', '2025-10-21', 1),
(132, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-22', 1),
(133, 'SHANTOX', '6', '50', '6', '2025-10-22', 1),
(136, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-23', 1),
(137, 'SHANTOX', '56', '0', '56', '2025-10-23', 1),
(138, 'CHLOROQUINE', '200', '0', '200', '2025-10-23', 1),
(139, 'AMOXILIN 500G', '400', '0', '400', '2025-10-23', 1),
(140, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-24', 1),
(141, 'SHANTOX', '56', '0', '56', '2025-10-24', 1),
(142, 'CHLOROQUINE', '200', '0', '200', '2025-10-24', 1),
(143, 'AMOXILIN 500G', '400', '0', '400', '2025-10-24', 1),
(144, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-26', 1),
(145, 'SHANTOX', '56', '0', '56', '2025-10-26', 1),
(146, 'CHLOROQUINE', '200', '0', '200', '2025-10-26', 1),
(147, 'AMOXILIN 500G', '400', '0', '400', '2025-10-26', 1),
(148, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-27', 1),
(149, 'SHANTOX', '56', '0', '56', '2025-10-27', 1),
(150, 'CHLOROQUINE', '200', '0', '200', '2025-10-27', 1),
(151, 'AMOXILIN 500G', '400', '0', '400', '2025-10-27', 1),
(152, 'PARACETAMOL 500G', '100', '0', '100', '2025-11-04', 1),
(153, 'SHANTOX', '56', '0', '56', '2025-11-04', 1),
(154, 'CHLOROQUINE', '200', '0', '200', '2025-11-04', 1),
(155, 'AMOXILIN 500G', '400', '0', '400', '2025-11-04', 1),
(156, 'PARACETAMOL 500G', '100', '0', '100', '2025-11-20', 1),
(157, 'SHANTOX', '56', '0', '56', '2025-11-20', 1),
(158, 'CHLOROQUINE', '200', '0', '200', '2025-11-20', 1),
(159, 'AMOXILIN 500G', '400', '0', '400', '2025-11-20', 1),
(160, 'PARACETAMOL 500G', '100', '0', '100', '2025-11-21', 1),
(161, 'SHANTOX', '56', '0', '56', '2025-11-21', 1),
(162, 'CHLOROQUINE', '200', '0', '200', '2025-11-21', 1),
(163, 'AMOXILIN 500G', '400', '0', '400', '2025-11-21', 1),
(164, 'PARACETAMOL 500G', '100', '10', '100', '2025-11-25', 1),
(165, 'SHANTOX', '56', '0', '56', '2025-11-25', 1),
(166, 'CHLOROQUINE', '200', '0', '200', '2025-11-25', 1),
(167, 'AMOXILIN 500G', '400', '0', '400', '2025-11-25', 1),
(168, 'PARACETAMOL 500G', '110', '0', '110', '2025-11-27', 1),
(169, 'SHANTOX', '56', '0', '56', '2025-11-27', 1),
(170, 'CHLOROQUINE', '200', '0', '200', '2025-11-27', 1),
(171, 'AMOXILIN 500G', '400', '0', '400', '2025-11-27', 1),
(172, 'PARACETAMOL 500G', '110', '0', '110', '2025-11-28', 1),
(173, 'SHANTOX', '56', '0', '56', '2025-11-28', 1),
(174, 'CHLOROQUINE', '200', '0', '200', '2025-11-28', 1),
(175, 'AMOXILIN 500G', '400', '0', '400', '2025-11-28', 1),
(176, 'PARACETAMOL 500G', '10', '0', '10', '2025-11-28', 1),
(177, 'PARACETAMOL 500G', '110', '0', '110', '2025-11-29', 1),
(178, 'SHANTOX', '56', '0', '56', '2025-11-29', 1),
(179, 'CHLOROQUINE', '200', '0', '200', '2025-11-29', 1),
(180, 'AMOXILIN 500G', '400', '0', '400', '2025-11-29', 1),
(181, 'PARACETAMOL 500G', '10', '0', '10', '2025-11-29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `procedures`
--

CREATE TABLE `procedures` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procedures`
--

INSERT INTO `procedures` (`id`, `name`, `amount`) VALUES
(1, 'CEASERIAN', '200000'),
(2, 'GULL OPERATION', '50000');

-- --------------------------------------------------------

--
-- Table structure for table `refer`
--

CREATE TABLE `refer` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `test` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refer`
--

INSERT INTO `refer` (`id`, `date`, `name`, `test`) VALUES
(1, '2025-09-11', 'OLATUNBOSUN OYINLOLA', 'LUMBA SCAN,PCV'),
(2, '2025-10-04', 'ADEBOWALE SAMUEL LAW', 'LUMBA SCAN,FBS'),
(3, '2025-10-04', 'ADEBOWALE SAMUEL LAW', 'PELVIC SCAN,PCV'),
(4, '2025-10-04', 'ADEBOWALE SAMUEL LAW', 'PELVIC SCAN,PCV'),
(5, '2025-10-13', 'OLATUNBOSUN OYINLOLA', 'LUMBA SCAN,HEAD SCAN'),
(6, '2025-10-13', 'OLATUNBOSUN OYINLOLA', '                                                              '),
(7, '2025-10-13', 'OLATUNBOSUN OYINLOLA', '                                                              '),
(8, '2025-10-13', 'OLATUNBOSUN OYINLOLA', '                                                              '),
(9, '2025-10-13', 'OLATUNBOSUN OYINLOLA', '                                                              '),
(10, '2025-10-15', 'Adeyemi Nurudeen', 'PRENANCY SCAN'),
(11, '2025-10-22', 'OLATUNBOSUN OYINLOLA', 'HEAD SCAN'),
(12, '2025-10-22', 'Adeyemi  Nurudeen Ad', 'HEAD SCAN,HEAD SCAN');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`id`, `name`, `amount`) VALUES
(1, 'GENERAL ROOM', '5000'),
(2, 'STANDARD1 ROOM', '10000'),
(3, 'STANDARD2 ROOM', '10000'),
(4, 'PRIVATE ROOM', '20000');

-- --------------------------------------------------------

--
-- Table structure for table `scan`
--

CREATE TABLE `scan` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scan`
--

INSERT INTO `scan` (`id`, `name`, `amount`) VALUES
(1, 'LUMBA SCAN', '2500'),
(2, 'PELVIC SCAN', '3000'),
(3, 'PRENANCY SCAN', '6000'),
(4, 'HEAD SCAN', '5500');

-- --------------------------------------------------------

--
-- Table structure for table `sendsignal`
--

CREATE TABLE `sendsignal` (
  `id` int(10) NOT NULL,
  `pat_code` varchar(20) NOT NULL,
  `Fullname` varchar(100) NOT NULL,
  `Date` date NOT NULL,
  `Time` varchar(10) NOT NULL,
  `Category` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `picture` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sendsignal`
--

INSERT INTO `sendsignal` (`id`, `pat_code`, `Fullname`, `Date`, `Time`, `Category`, `dob`, `picture`, `status`) VALUES
(7, 'H6123975', 'OGODU WALE OMOGA', '2025-05-01', ' 05:03:40p', 'HMO CARD', '2024-01-30', 'ABIOLA MOSHOOD_01.jpg', ''),
(8, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-05-01', ' 05:05:38p', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', ''),
(9, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-05-02', ' 12:21:28p', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', ''),
(10, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-06-07', ' 10:55:38a', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', 'Not Yet'),
(11, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', '2025-06-07', ' 11:17:43a', 'INDIVIDUAL CARD', '1987-06-09', 'ADEBAYO ALIU Olamilekan.jpg', 'Checked'),
(12, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-08-30', '12:17:30pm', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', 'Checked'),
(13, 'IND437', 'OMOSEYIN OLUKAYODE KUNLE', '2025-08-30', '12:18:00pm', 'INDIVIDUAL CARD', '1987-10-13', '', 'Checked'),
(14, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', '2025-08-30', '12:18:10pm', 'INDIVIDUAL CARD', '1987-06-09', 'ADEBAYO ALIU Olamilekan.jpg', 'Checked'),
(15, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-08-30', '03:40:05pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(16, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-09-11', '06:16:22pm', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', 'Checked'),
(17, 'F533325226', 'MR ABIODUN SHERIF SEUN', '2025-09-16', '09:35:31am', 'FAMILY CARD', '1982-11-25', 'team-1.jpg', 'Checked'),
(18, 'F533325226', 'MR ABIODUN SHERIF SEUN', '2025-09-20', '06:12:59pm', 'FAMILY CARD', '1982-11-25', 'team-1.jpg', 'Checked'),
(19, 'F533325226', 'MR ABIODUN SHERIF SEUN', '2025-09-21', '09:59:50pm', 'FAMILY CARD', '1982-11-25', 'team-1.jpg', 'Checked'),
(20, 'F533325226', 'MR ABIODUN SHERIF SEUN', '2025-09-21', '10:00:00pm', 'FAMILY CARD', '1982-11-25', 'team-1.jpg', 'Checked'),
(21, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-09-26', '09:42:21am', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(22, 'IND45592', 'TEMITAYO BLESSED OTITE', '2025-10-01', '07:40:55pm', 'INDIVIDUAL CARD', '1955-02-15', 'testimonial-4.jpg', 'Not Yet'),
(23, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', '2025-10-04', '10:22:25am', 'INDIVIDUAL CARD', '1987-06-09', 'ADEBAYO ALIU Olamilekan.jpg', 'Checked'),
(24, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-13', '05:04:33pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(25, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-14', '06:36:49am', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(26, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-14', '07:11:14am', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(27, 'IND45523', 'ADEBOWALE SAMUEL LAWAL', '2025-10-14', '07:20:45am', 'INDIVIDUAL CARD', '1987-06-09', 'ADEBAYO ALIU Olamilekan.jpg', 'Checked'),
(28, 'ST43173', 'Adeyemi Nurudeen Adewale', '2025-10-14', '10:12:56am', 'STUDENT CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(29, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-15', '01:35:50pm', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(30, 'ST43173', 'Adeyemi Nurudeen ', '2025-10-15', '02:47:55pm', 'STUDENT CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(31, 'ST43173', 'Adeyemi Nurudeen ', '2025-10-15', '02:55:59pm', 'STUDENT CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(32, 'IND44577', 'OLATUNBOSUN OYINLOLA ', '2025-10-16', '12:04:32pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(33, 'IND44577', 'OLATUNBOSUN OYINLOLA ', '2025-10-16', '12:05:45pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(34, 'IND44577', 'OLATUNBOSUN OYINLOLA ', '2025-10-16', '12:06:11pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(35, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-16', '12:12:52pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(36, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-16', '02:58:42pm', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(37, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-20', '10:26:48am', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(38, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-21', '04:31:34pm', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(39, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-22', '10:12:59am', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(40, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-22', '10:41:16am', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(41, 'ST4376', 'Adeyemi Nurudeen Adewale', '2025-10-22', '03:21:55pm', 'STUDENT CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(42, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-23', '09:51:57am', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(43, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-10-23', '11:12:54am', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(44, 'ST4376', 'Adeyemi Nurudeen Adewale', '2025-10-23', '11:40:49am', 'STUDENT CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(45, 'IND44577', 'OLATUNBOSUN OYINLOLA MICHAEL', '2025-10-27', '09:24:31am', 'INDIVIDUAL CARD', '2022-01-10', 'we.png', 'Checked'),
(46, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-11-27', '11:49:44am', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked'),
(47, 'S4310', 'Adeyemi  Nurudeen Adewale', '2025-11-29', '02:37:33pm', 'STAFF CARD', '1982-07-31', 'de icon.jpg', 'Checked');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(20) DEFAULT NULL,
  `scode` varchar(20) DEFAULT NULL,
  `title` varchar(30) DEFAULT NULL,
  `surname` varchar(30) DEFAULT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `middlename` varchar(30) DEFAULT NULL,
  `staff_no` varchar(20) DEFAULT NULL,
  `dept` varchar(20) DEFAULT NULL,
  `faculty` varchar(20) DEFAULT NULL,
  `reg_date` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `address` varchar(80) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nok` varchar(80) DEFAULT NULL,
  `nok_contact` varchar(20) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `picture` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `scode`, `title`, `surname`, `firstname`, `middlename`, `staff_no`, `dept`, `faculty`, `reg_date`, `dob`, `age`, `address`, `phone`, `nok`, `nok_contact`, `marital_status`, `picture`) VALUES
(NULL, 'S4310', 'Mr', 'Adeyemi ', 'Nurudeen', 'Adewale', 'OOU/NANS/P.1490/22', 'ICT', 'ICT', '2025-10-14', '1982-07-31', '43', '118, Folagbade Ijebu Ode', '08056600048', 'Adeyemi Rihanat Temitope', '08056600048', 'Married', 'de icon.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `store`
--

CREATE TABLE `store` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` varchar(100) NOT NULL,
  `contact` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store`
--

INSERT INTO `store` (`id`, `name`, `address`, `contact`) VALUES
(1, 'OLABISI ONABANJO UNIVERSITY ', 'PMB 2002 AGO IWOYE', '');

-- --------------------------------------------------------

--
-- Table structure for table `store_stock`
--

CREATE TABLE `store_stock` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `opening` varchar(50) NOT NULL,
  `addstock` varchar(10) NOT NULL,
  `closing` varchar(10) NOT NULL,
  `date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_stock`
--

INSERT INTO `store_stock` (`id`, `name`, `opening`, `addstock`, `closing`, `date`) VALUES
(1, 'PARACETAMOL 500G', '150', '0', '150', '2025-06-24'),
(3, 'SHANTOX', '10', '0', '7', '2025-06-24'),
(4, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-03'),
(6, 'SHANTOX', '7', '0', '7', '2025-07-03'),
(7, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-05'),
(9, 'SHANTOX', '7', '0', '7', '2025-07-05'),
(10, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-08'),
(12, 'SHANTOX', '7', '0', '7', '2025-07-08'),
(13, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-09'),
(15, 'SHANTOX', '7', '0', '7', '2025-07-09'),
(16, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-10'),
(18, 'SHANTOX', '7', '0', '7', '2025-07-10'),
(19, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-11'),
(21, 'SHANTOX', '7', '0', '7', '2025-07-11'),
(22, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-13'),
(24, 'SHANTOX', '7', '0', '7', '2025-07-13'),
(25, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-24'),
(26, 'CHOLOQUEEN', '44', '0', '44', '2025-07-24'),
(27, 'SHANTOX', '7', '0', '7', '2025-07-24'),
(28, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-29'),
(29, 'CHOLOQUEEN', '44', '0', '44', '2025-07-29'),
(30, 'SHANTOX', '7', '0', '7', '2025-07-29'),
(31, 'PARACETAMOL 500G', '150', '0', '150', '2025-07-31'),
(32, 'CHOLOQUEEN', '44', '0', '44', '2025-07-31'),
(33, 'SHANTOX', '7', '0', '7', '2025-07-31'),
(34, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-08'),
(36, 'SHANTOX', '7', '0', '7', '2025-08-08'),
(37, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-13'),
(39, 'SHANTOX', '7', '0', '7', '2025-08-13'),
(40, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-14'),
(42, 'SHANTOX', '7', '0', '7', '2025-08-14'),
(43, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-16'),
(45, 'SHANTOX', '7', '0', '7', '2025-08-16'),
(46, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-17'),
(48, 'SHANTOX', '7', '0', '7', '2025-08-17'),
(49, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-19'),
(51, 'SHANTOX', '7', '0', '7', '2025-08-19'),
(52, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-20'),
(54, 'SHANTOX', '7', '0', '7', '2025-08-20'),
(55, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-27'),
(57, 'SHANTOX', '7', '0', '7', '2025-08-27'),
(58, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-28'),
(59, 'CHOLOQUEEN', '44', '0', '44', '2025-08-28'),
(60, 'SHANTOX', '7', '0', '7', '2025-08-28'),
(61, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-29'),
(62, 'CHOLOQUEEN', '44', '0', '44', '2025-08-29'),
(63, 'SHANTOX', '7', '0', '7', '2025-08-29'),
(64, 'PARACETAMOL 500G', '150', '0', '150', '2025-08-30'),
(65, 'CHOLOQUEEN', '44', '0', '44', '2025-08-30'),
(66, 'SHANTOX', '7', '0', '7', '2025-08-30'),
(67, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-11'),
(69, 'SHANTOX', '7', '0', '7', '2025-09-11'),
(70, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-16'),
(72, 'SHANTOX', '7', '0', '7', '2025-09-16'),
(73, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-20'),
(75, 'SHANTOX', '7', '0', '7', '2025-09-20'),
(76, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-21'),
(78, 'SHANTOX', '7', '0', '7', '2025-09-21'),
(79, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-25'),
(81, 'SHANTOX', '7', '0', '7', '2025-09-25'),
(82, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-26'),
(84, 'SHANTOX', '7', '0', '7', '2025-09-26'),
(85, 'PARACETAMOL 500G', '150', '0', '150', '2025-09-29'),
(87, 'SHANTOX', '7', '0', '7', '2025-09-29'),
(88, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-01'),
(90, 'SHANTOX', '7', '0', '7', '2025-10-01'),
(91, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-02'),
(92, 'CHOLOQUEEN', '44', '0', '44', '2025-10-02'),
(93, 'SHANTOX', '7', '0', '7', '2025-10-02'),
(94, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-04'),
(95, 'CHOLOQUEEN', '44', '0', '44', '2025-10-04'),
(96, 'SHANTOX', '7', '0', '7', '2025-10-04'),
(97, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-10'),
(98, 'CHOLOQUEEN', '44', '0', '44', '2025-10-10'),
(99, 'SHANTOX', '7', '0', '7', '2025-10-10'),
(100, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-13'),
(102, 'SHANTOX', '7', '0', '7', '2025-10-13'),
(103, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-14'),
(105, 'SHANTOX', '7', '0', '7', '2025-10-14'),
(106, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-15'),
(108, 'SHANTOX', '7', '0', '7', '2025-10-15'),
(109, 'PARACETAMOL 500G', '150', '0', '150', '2025-10-16'),
(111, 'SHANTOX', '7', '0', '7', '2025-10-16'),
(112, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-17'),
(114, 'SHANTOX', '7', '0', '7', '2025-10-17'),
(116, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-18'),
(118, 'SHANTOX', '7', '0', '7', '2025-10-18'),
(120, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-19'),
(122, 'SHANTOX', '7', '0', '7', '2025-10-19'),
(124, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-20'),
(125, 'CHOLOQUEEN', '44', '0', '44', '2025-10-20'),
(126, 'SHANTOX', '7', '0', '7', '2025-10-20'),
(127, 'CHLOROQUINN', '300', '0', '300', '2025-10-20'),
(128, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-21'),
(129, 'CHOLOQUEEN', '44', '0', '44', '2025-10-21'),
(130, 'SHANTOX', '7', '0', '7', '2025-10-21'),
(131, 'CHLOROQUINN', '300', '0', '300', '2025-10-21'),
(132, 'PARACETAMOL 500G', '100', '0', '100', '2025-10-22'),
(133, 'CHOLOQUEEN', '44', '0', '44', '2025-10-22'),
(134, 'SHANTOX', '100', '0', '100', '2025-10-22'),
(137, 'PARACETAMOL 500G', '300', '0', '300', '2025-10-23'),
(138, 'CHLOROQUINE', '400', '0', '400', '2025-10-23'),
(139, 'SHANTOX', '50', '0', '50', '2025-10-23'),
(140, 'AMOXILIN 500G', '200', '0', '200', '2025-10-23'),
(141, 'PARACETAMOL 500G', '300', '0', '300', '2025-10-24'),
(142, 'CHLOROQUINE', '400', '0', '400', '2025-10-24'),
(143, 'SHANTOX', '50', '0', '50', '2025-10-24'),
(144, 'AMOXILIN 500G', '200', '0', '200', '2025-10-24'),
(145, 'PARACETAMOL 500G', '300', '0', '300', '2025-10-26'),
(146, 'CHLOROQUINE', '400', '0', '400', '2025-10-26'),
(147, 'SHANTOX', '50', '0', '50', '2025-10-26'),
(148, 'AMOXILIN 500G', '200', '0', '200', '2025-10-26'),
(149, 'PARACETAMOL 500G', '300', '0', '300', '2025-10-27'),
(150, 'CHLOROQUINE', '400', '0', '400', '2025-10-27'),
(151, 'SHANTOX', '50', '0', '50', '2025-10-27'),
(152, 'AMOXILIN 500G', '200', '0', '200', '2025-10-27'),
(153, 'PARACETAMOL 500G', '300', '0', '300', '2025-11-04'),
(154, 'CHLOROQUINE', '400', '0', '400', '2025-11-04'),
(155, 'SHANTOX', '50', '0', '50', '2025-11-04'),
(156, 'AMOXILIN 500G', '200', '0', '200', '2025-11-04'),
(157, 'PARACETAMOL 500G', '300', '0', '300', '2025-11-20'),
(158, 'CHLOROQUINE', '400', '0', '400', '2025-11-20'),
(159, 'SHANTOX', '50', '0', '50', '2025-11-20'),
(160, 'AMOXILIN 500G', '200', '0', '200', '2025-11-20'),
(161, 'PARACETAMOL 500G', '300', '0', '300', '2025-11-21'),
(162, 'CHLOROQUINE', '400', '0', '400', '2025-11-21'),
(163, 'SHANTOX', '50', '0', '50', '2025-11-21'),
(164, 'AMOXILIN 500G', '200', '0', '200', '2025-11-21'),
(165, 'PARACETAMOL 500G', '300', '0', '300', '2025-11-25'),
(166, 'CHLOROQUINE', '400', '0', '400', '2025-11-25'),
(167, 'SHANTOX', '50', '0', '50', '2025-11-25'),
(168, 'AMOXILIN 500G', '200', '0', '200', '2025-11-25'),
(169, 'PARACETAMOL 500G', '290', '0', '290', '2025-11-27'),
(170, 'CHLOROQUINE', '400', '0', '400', '2025-11-27'),
(171, 'SHANTOX', '50', '0', '50', '2025-11-27'),
(172, 'AMOXILIN 500G', '200', '0', '200', '2025-11-27'),
(173, 'PARACETAMOL 500G', '260', '0', '260', '2025-11-28'),
(174, 'CHLOROQUINE', '400', '0', '400', '2025-11-28'),
(175, 'SHANTOX', '50', '0', '50', '2025-11-28'),
(176, 'AMOXILIN 500G', '200', '0', '200', '2025-11-28'),
(177, 'PARACETAMOL 500G', '260', '0', '260', '2025-11-29'),
(178, 'CHLOROQUINE', '400', '0', '400', '2025-11-29'),
(179, 'SHANTOX', '50', '0', '50', '2025-11-29'),
(180, 'AMOXILIN 500G', '200', '0', '200', '2025-11-29');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(20) NOT NULL,
  `stcode` varchar(20) DEFAULT NULL,
  `title` varchar(30) DEFAULT NULL,
  `surname` varchar(30) DEFAULT NULL,
  `firstname` varchar(30) DEFAULT NULL,
  `middlename` varchar(30) DEFAULT NULL,
  `matric_no` varchar(20) DEFAULT NULL,
  `dept` varchar(20) DEFAULT NULL,
  `faculty` varchar(20) DEFAULT NULL,
  `reg_date` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `age` varchar(20) DEFAULT NULL,
  `address` varchar(80) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nok` varchar(80) DEFAULT NULL,
  `nok_contact` varchar(20) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `picture` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `stcode`, `title`, `surname`, `firstname`, `middlename`, `matric_no`, `dept`, `faculty`, `reg_date`, `dob`, `age`, `address`, `phone`, `nok`, `nok_contact`, `marital_status`, `picture`) VALUES
(2, 'ST4376', 'Mr', 'Adeyemi', 'Nurudeen', 'Adewale', 'PSC/19/20/0045', 'Computer Science', 'Science', '2025-10-22', '1982-07-31', '43', '118, Folagbade Ijebu Ode', '08056600048', 'Adeyemi Rihanat Temitope', '08056600048', 'Married', 'de icon.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_type`
--
ALTER TABLE `account_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `campus_locations`
--
ALTER TABLE `campus_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_pay`
--
ALTER TABLE `cart_pay`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashbook`
--
ALTER TABLE `cashbook`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chart`
--
ALTER TABLE `chart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `company_individual`
--
ALTER TABLE `company_individual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `diagnosis`
--
ALTER TABLE `diagnosis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `doc_diagnosis`
--
ALTER TABLE `doc_diagnosis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doc_lab`
--
ALTER TABLE `doc_lab`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doc_procedure`
--
ALTER TABLE `doc_procedure`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doc_scanlab`
--
ALTER TABLE `doc_scanlab`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug`
--
ALTER TABLE `drug`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `pharmacy_location_id` (`pharmacy_location_id`);

--
-- Indexes for table `drugchart`
--
ALTER TABLE `drugchart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug_ayetoro`
--
ALTER TABLE `drug_ayetoro`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug_ibogun`
--
ALTER TABLE `drug_ibogun`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug_minicampus`
--
ALTER TABLE `drug_minicampus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug_prescription`
--
ALTER TABLE `drug_prescription`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `family`
--
ALTER TABLE `family`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `family_individual`
--
ALTER TABLE `family_individual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `his_accounts`
--
ALTER TABLE `his_accounts`
  ADD PRIMARY KEY (`acc_id`);

--
-- Indexes for table `his_admin`
--
ALTER TABLE `his_admin`
  ADD PRIMARY KEY (`ad_id`);

--
-- Indexes for table `his_assets`
--
ALTER TABLE `his_assets`
  ADD PRIMARY KEY (`asst_id`);

--
-- Indexes for table `his_docs`
--
ALTER TABLE `his_docs`
  ADD PRIMARY KEY (`doc_id`);

--
-- Indexes for table `his_equipments`
--
ALTER TABLE `his_equipments`
  ADD PRIMARY KEY (`eqp_id`);

--
-- Indexes for table `his_laboratory`
--
ALTER TABLE `his_laboratory`
  ADD PRIMARY KEY (`lab_id`);

--
-- Indexes for table `his_medical_records`
--
ALTER TABLE `his_medical_records`
  ADD PRIMARY KEY (`mdr_id`);

--
-- Indexes for table `his_patients`
--
ALTER TABLE `his_patients`
  ADD PRIMARY KEY (`pat_id`);

--
-- Indexes for table `his_patient_transfers`
--
ALTER TABLE `his_patient_transfers`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `his_payrolls`
--
ALTER TABLE `his_payrolls`
  ADD PRIMARY KEY (`pay_id`);

--
-- Indexes for table `his_pharmaceuticals`
--
ALTER TABLE `his_pharmaceuticals`
  ADD PRIMARY KEY (`phar_id`);

--
-- Indexes for table `his_pharmaceuticals_categories`
--
ALTER TABLE `his_pharmaceuticals_categories`
  ADD PRIMARY KEY (`pharm_cat_id`);

--
-- Indexes for table `his_prescriptions`
--
ALTER TABLE `his_prescriptions`
  ADD PRIMARY KEY (`pres_id`);

--
-- Indexes for table `his_pwdresets`
--
ALTER TABLE `his_pwdresets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `his_surgery`
--
ALTER TABLE `his_surgery`
  ADD PRIMARY KEY (`s_id`);

--
-- Indexes for table `his_vendor`
--
ALTER TABLE `his_vendor`
  ADD PRIMARY KEY (`v_id`);

--
-- Indexes for table `his_vitals`
--
ALTER TABLE `his_vitals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hmo`
--
ALTER TABLE `hmo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `hmocompany`
--
ALTER TABLE `hmocompany`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `hmocompany_individual`
--
ALTER TABLE `hmocompany_individual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `individual`
--
ALTER TABLE `individual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `injectionchart`
--
ALTER TABLE `injectionchart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal`
--
ALTER TABLE `journal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab`
--
ALTER TABLE `lab`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lab_consumables`
--
ALTER TABLE `lab_consumables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_consumable_stock`
--
ALTER TABLE `lab_consumable_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `consumable_campus_unique` (`consumable_id`,`campus_id`),
  ADD KEY `campus_id` (`campus_id`);

--
-- Indexes for table `lab_locations`
--
ALTER TABLE `lab_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_location_stock`
--
ALTER TABLE `lab_location_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nurse_consumables`
--
ALTER TABLE `nurse_consumables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nurse_consumable_stock`
--
ALTER TABLE `nurse_consumable_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consumable_id` (`consumable_id`),
  ADD KEY `campus_id` (`campus_id`);

--
-- Indexes for table `outpatient_visist_record`
--
ALTER TABLE `outpatient_visist_record`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_bill`
--
ALTER TABLE `patient_bill`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_drug_history`
--
ALTER TABLE `patient_drug_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_lab`
--
ALTER TABLE `patient_lab`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_scan`
--
ALTER TABLE `patient_scan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pcart`
--
ALTER TABLE `pcart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy`
--
ALTER TABLE `pharmacy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pharmacy_location_id` (`pharmacy_location_id`);

--
-- Indexes for table `pharmacy_invoice`
--
ALTER TABLE `pharmacy_invoice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy_location`
--
ALTER TABLE `pharmacy_location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy_location_stock`
--
ALTER TABLE `pharmacy_location_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `drug_id` (`drug_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `pharmacy_order`
--
ALTER TABLE `pharmacy_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy_stock`
--
ALTER TABLE `pharmacy_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pharmacy_location_id` (`pharmacy_location_id`);

--
-- Indexes for table `procedures`
--
ALTER TABLE `procedures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `refer`
--
ALTER TABLE `refer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `scan`
--
ALTER TABLE `scan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sendsignal`
--
ALTER TABLE `sendsignal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store`
--
ALTER TABLE `store`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_stock`
--
ALTER TABLE `store_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_type`
--
ALTER TABLE `account_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `campus_locations`
--
ALTER TABLE `campus_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `card`
--
ALTER TABLE `card`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `cart_pay`
--
ALTER TABLE `cart_pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cashbook`
--
ALTER TABLE `cashbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=433;

--
-- AUTO_INCREMENT for table `chart`
--
ALTER TABLE `chart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `company_individual`
--
ALTER TABLE `company_individual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `diagnosis`
--
ALTER TABLE `diagnosis`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `doc_diagnosis`
--
ALTER TABLE `doc_diagnosis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `doc_lab`
--
ALTER TABLE `doc_lab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doc_procedure`
--
ALTER TABLE `doc_procedure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `doc_scanlab`
--
ALTER TABLE `doc_scanlab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `drug`
--
ALTER TABLE `drug`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `drugchart`
--
ALTER TABLE `drugchart`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `drug_ayetoro`
--
ALTER TABLE `drug_ayetoro`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drug_ibogun`
--
ALTER TABLE `drug_ibogun`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drug_minicampus`
--
ALTER TABLE `drug_minicampus`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drug_prescription`
--
ALTER TABLE `drug_prescription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `family`
--
ALTER TABLE `family`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `family_individual`
--
ALTER TABLE `family_individual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `his_accounts`
--
ALTER TABLE `his_accounts`
  MODIFY `acc_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `his_admin`
--
ALTER TABLE `his_admin`
  MODIFY `ad_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `his_assets`
--
ALTER TABLE `his_assets`
  MODIFY `asst_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `his_docs`
--
ALTER TABLE `his_docs`
  MODIFY `doc_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `his_equipments`
--
ALTER TABLE `his_equipments`
  MODIFY `eqp_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `his_laboratory`
--
ALTER TABLE `his_laboratory`
  MODIFY `lab_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `his_medical_records`
--
ALTER TABLE `his_medical_records`
  MODIFY `mdr_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `his_patients`
--
ALTER TABLE `his_patients`
  MODIFY `pat_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `his_patient_transfers`
--
ALTER TABLE `his_patient_transfers`
  MODIFY `t_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `his_payrolls`
--
ALTER TABLE `his_payrolls`
  MODIFY `pay_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `his_pharmaceuticals`
--
ALTER TABLE `his_pharmaceuticals`
  MODIFY `phar_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `his_pharmaceuticals_categories`
--
ALTER TABLE `his_pharmaceuticals_categories`
  MODIFY `pharm_cat_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `his_prescriptions`
--
ALTER TABLE `his_prescriptions`
  MODIFY `pres_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `his_pwdresets`
--
ALTER TABLE `his_pwdresets`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `his_surgery`
--
ALTER TABLE `his_surgery`
  MODIFY `s_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `his_vendor`
--
ALTER TABLE `his_vendor`
  MODIFY `v_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `his_vitals`
--
ALTER TABLE `his_vitals`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `hmo`
--
ALTER TABLE `hmo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `hmocompany`
--
ALTER TABLE `hmocompany`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hmocompany_individual`
--
ALTER TABLE `hmocompany_individual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `individual`
--
ALTER TABLE `individual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `injectionchart`
--
ALTER TABLE `injectionchart`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `journal`
--
ALTER TABLE `journal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `lab`
--
ALTER TABLE `lab`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lab_consumables`
--
ALTER TABLE `lab_consumables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `lab_consumable_stock`
--
ALTER TABLE `lab_consumable_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lab_locations`
--
ALTER TABLE `lab_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_location_stock`
--
ALTER TABLE `lab_location_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `nurse_consumables`
--
ALTER TABLE `nurse_consumables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nurse_consumable_stock`
--
ALTER TABLE `nurse_consumable_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `outpatient_visist_record`
--
ALTER TABLE `outpatient_visist_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `patient_bill`
--
ALTER TABLE `patient_bill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `patient_drug_history`
--
ALTER TABLE `patient_drug_history`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `patient_lab`
--
ALTER TABLE `patient_lab`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `patient_scan`
--
ALTER TABLE `patient_scan`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pcart`
--
ALTER TABLE `pcart`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pharmacy`
--
ALTER TABLE `pharmacy`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pharmacy_invoice`
--
ALTER TABLE `pharmacy_invoice`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pharmacy_location`
--
ALTER TABLE `pharmacy_location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pharmacy_location_stock`
--
ALTER TABLE `pharmacy_location_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pharmacy_order`
--
ALTER TABLE `pharmacy_order`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pharmacy_stock`
--
ALTER TABLE `pharmacy_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `refer`
--
ALTER TABLE `refer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scan`
--
ALTER TABLE `scan`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sendsignal`
--
ALTER TABLE `sendsignal`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `store`
--
ALTER TABLE `store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_stock`
--
ALTER TABLE `store_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drug`
--
ALTER TABLE `drug`
  ADD CONSTRAINT `drug_ibfk_1` FOREIGN KEY (`pharmacy_location_id`) REFERENCES `pharmacy_location` (`id`);

--
-- Constraints for table `lab_consumable_stock`
--
ALTER TABLE `lab_consumable_stock`
  ADD CONSTRAINT `lab_consumable_stock_ibfk_1` FOREIGN KEY (`consumable_id`) REFERENCES `lab_consumables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_consumable_stock_ibfk_2` FOREIGN KEY (`campus_id`) REFERENCES `campus_locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_location_stock`
--
ALTER TABLE `lab_location_stock`
  ADD CONSTRAINT `lab_location_stock_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `lab` (`id`),
  ADD CONSTRAINT `lab_location_stock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `lab_locations` (`id`);

--
-- Constraints for table `nurse_consumable_stock`
--
ALTER TABLE `nurse_consumable_stock`
  ADD CONSTRAINT `nurse_consumable_stock_ibfk_1` FOREIGN KEY (`consumable_id`) REFERENCES `nurse_consumables` (`id`),
  ADD CONSTRAINT `nurse_consumable_stock_ibfk_2` FOREIGN KEY (`campus_id`) REFERENCES `campus_locations` (`id`);

--
-- Constraints for table `pharmacy`
--
ALTER TABLE `pharmacy`
  ADD CONSTRAINT `pharmacy_ibfk_1` FOREIGN KEY (`pharmacy_location_id`) REFERENCES `pharmacy_location` (`id`);

--
-- Constraints for table `pharmacy_location_stock`
--
ALTER TABLE `pharmacy_location_stock`
  ADD CONSTRAINT `pharmacy_location_stock_ibfk_1` FOREIGN KEY (`drug_id`) REFERENCES `drug` (`id`),
  ADD CONSTRAINT `pharmacy_location_stock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `pharmacy_location` (`id`);

--
-- Constraints for table `pharmacy_stock`
--
ALTER TABLE `pharmacy_stock`
  ADD CONSTRAINT `pharmacy_stock_ibfk_1` FOREIGN KEY (`pharmacy_location_id`) REFERENCES `pharmacy_location` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
