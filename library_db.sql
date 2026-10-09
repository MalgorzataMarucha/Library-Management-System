-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Wersja serwera:               8.4.3 - MySQL Community Server - GPL
-- Serwer OS:                    Win64
-- HeidiSQL Wersja:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb3 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Zrzut struktury bazy danych library_db
CREATE DATABASE IF NOT EXISTS `library_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `library_db`;

-- Zrzut struktury tabela library_db.book
CREATE TABLE IF NOT EXISTS `book` (
  `id_b` int NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `author` varchar(50) NOT NULL,
  `genre` varchar(15) NOT NULL,
  `release_date` date NOT NULL,
  PRIMARY KEY (`id_b`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.book: ~6 rows (około)
INSERT INTO `book` (`id_b`, `title`, `author`, `genre`, `release_date`) VALUES
	(1, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', '1937-09-21'),
	(2, 'Clean Code', 'Robert C. Martin', 'IT', '2008-08-01'),
	(3, 'Dune', 'Frank Herbert', 'Sci-Fi', '1965-06-01'),
	(4, '1984', 'George Orwell', 'Dystopian', '1949-06-08'),
	(5, 'The Witcher', 'Andrzej Sapkowski', 'Fantasy', '1993-02-15'),
	(6, 'Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 'Fantasy', '1997-06-26');

-- Zrzut struktury tabela library_db.copy
CREATE TABLE IF NOT EXISTS `copy` (
  `id_c` int NOT NULL AUTO_INCREMENT,
  `damage_status` varchar(30) NOT NULL,
  `cover_type` varchar(10) NOT NULL,
  `book_id` int NOT NULL,
  `library_id` int NOT NULL,
  `status_id` int DEFAULT NULL,
  PRIMARY KEY (`id_c`),
  KEY `library_id` (`library_id`),
  KEY `status_id` (`status_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `copy_ibfk_1` FOREIGN KEY (`library_id`) REFERENCES `library` (`id_l`),
  CONSTRAINT `copy_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_s`),
  CONSTRAINT `copy_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `book` (`id_b`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.copy: ~9 rows (około)
INSERT INTO `copy` (`id_c`, `damage_status`, `cover_type`, `book_id`, `library_id`, `status_id`) VALUES
	(1, 'intact', 'hardcover', 1, 1, 1),
	(2, 'slightly damaged', 'paperback', 1, 1, 2),
	(3, 'intact', 'hardcover', 2, 2, 3),
	(4, 'worn out', 'paperback', 3, 3, 4),
	(5, 'intact', 'hardcover', 4, 4, 5),
	(6, 'intact', 'paperback', 5, 5, NULL),
	(7, 'intact', 'hardcover', 3, 6, NULL),
	(8, 'intact', 'hardcover', 3, 6, NULL),
	(9, 'intact', 'paperback', 6, 6, NULL);

-- Zrzut struktury tabela library_db.fine
CREATE TABLE IF NOT EXISTS `fine` (
  `id_f` int NOT NULL AUTO_INCREMENT,
  `amount` float NOT NULL,
  `is_paid` tinyint(1) NOT NULL,
  `reason` varchar(30) NOT NULL,
  `reader_id` int NOT NULL,
  PRIMARY KEY (`id_f`),
  KEY `reader_id` (`reader_id`),
  CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`reader_id`) REFERENCES `reader` (`id_r`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.fine: ~5 rows (około)
INSERT INTO `fine` (`id_f`, `amount`, `is_paid`, `reason`, `reader_id`) VALUES
	(1, 15.5, 1, 'late return', 1),
	(2, 50, 1, 'damaged cover', 2),
	(3, 10, 1, 'lost barcode label', 3),
	(4, 120, 0, 'lost book', 4),
	(5, 25.5, 0, 'water damage', 5);

-- Zrzut struktury tabela library_db.library
CREATE TABLE IF NOT EXISTS `library` (
  `id_l` int NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `opening_time` time NOT NULL,
  `closing_time` time NOT NULL,
  `total_copies` int NOT NULL,
  PRIMARY KEY (`id_l`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.library: ~6 rows (około)
INSERT INTO `library` (`id_l`, `name`, `address`, `opening_time`, `closing_time`, `total_copies`) VALUES
	(1, 'City Library', 'Main Street 12', '08:00:00', '20:00:00', 2),
	(2, 'Main Library', 'Central Avenue 45', '09:30:00', '21:00:00', 1),
	(3, 'Public Library', 'Park Road 8', '09:00:00', '20:30:00', 1),
	(4, 'School Library', 'School Street 3', '09:00:00', '21:00:00', 1),
	(5, 'Town Library', 'Market Square 15', '09:00:00', '20:00:00', 1),
	(6, 'Central Library', '42 University Street', '07:00:00', '16:00:00', 3);

-- Zrzut struktury tabela library_db.reader
CREATE TABLE IF NOT EXISTS `reader` (
  `id_r` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(10) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `library_id` int NOT NULL,
  PRIMARY KEY (`id_r`),
  KEY `library_id` (`library_id`),
  CONSTRAINT `reader_ibfk_1` FOREIGN KEY (`library_id`) REFERENCES `library` (`id_l`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.reader: ~5 rows (około)
INSERT INTO `reader` (`id_r`, `first_name`, `last_name`, `library_id`) VALUES
	(1, 'John', 'Smith', 1),
	(2, 'Emma', 'Watson', 2),
	(3, 'Michael', 'Brown', 3),
	(4, 'Sarah', 'Davis', 4),
	(5, 'David', 'Wilson', 5);

-- Zrzut struktury tabela library_db.status
CREATE TABLE IF NOT EXISTS `status` (
  `id_s` int NOT NULL AUTO_INCREMENT,
  `is_borrowed` tinyint(1) NOT NULL,
  `is_reserved` tinyint(1) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `copy_id` int NOT NULL,
  `reader_id` int DEFAULT NULL,
  PRIMARY KEY (`id_s`),
  KEY `copy_id` (`copy_id`),
  KEY `reader_id` (`reader_id`),
  CONSTRAINT `status_ibfk_1` FOREIGN KEY (`copy_id`) REFERENCES `copy` (`id_c`),
  CONSTRAINT `status_ibfk_2` FOREIGN KEY (`reader_id`) REFERENCES `reader` (`id_r`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- Zrzucanie danych dla tabeli library_db.status: ~5 rows (około)
INSERT INTO `status` (`id_s`, `is_borrowed`, `is_reserved`, `start_date`, `end_date`, `copy_id`, `reader_id`) VALUES
	(1, 1, 0, '2024-05-01', '2024-05-15', 1, 1),
	(2, 1, 0, '2024-05-10', '2024-05-24', 2, 2),
	(3, 1, 0, '2024-05-12', '2024-05-26', 3, 3),
	(4, 0, 1, '2024-05-20', '2024-06-03', 4, 4),
	(5, 0, 1, '2024-05-22', '2024-05-29', 5, 5);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
