-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 12. Feb 2025 um 12:35
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `highscores` (
  `id` int(11) NOT NULL,
  `highscore` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `highscores` (`id`, `highscore`) VALUES
(1, 11);



CREATE TABLE `künstler` (
  `id` int(11) NOT NULL,
  `künstler_name` varchar(50) NOT NULL,
  `künstler_streams` int(11) NOT NULL,
  `song` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `künstler` (`id`, `künstler_name`, `künstler_streams`, `song`) VALUES
(1, 'The neighbourhood', 1000000, 'Sweather weather'),
(2, 'Billie Eilish', 2470000, 'lovley'),
(4, 'Sabrina Carpenter', 300000, 'Espresso'),
(5, 'Vance Joy', 2000000, 'Riptide'),
(6, 'Drake', 1200000, 'Gods Plan'),
(7, 'Kendrick Lamar', 11009562, 'Not Like US'),
(8, 'Lady Gaga', 9564310, 'Die With a Smile'),
(9, 'Kendrick Lamar', 9057832, 'luther'),
(10, 'Billie Eilish', 7556983, 'BIRDS OF A FEATHER'),
(11, 'ROSE', 7253795, 'APT'),
(12, 'Bad Bunny', 6865751, 'DtMF'),
(13, 'Lady Gaga', 687740, 'Abracadabra'),
(14, 'Kendrick Lamar', 6550133, 'tv off'),
(15, 'Kendrick Lamar', 6000000, 'All The Stars'),
(16, 'Gracie Abrams', 5107077, 'Thats So True'),
(17, 'Bad Bunny', 5095579, 'BAILE INoLIDABLE'),
(18, 'Billie EiIish', 4988433, 'WILDFLOWER'),
(19, 'Kendrick Lamar', 4732924, 'squabble up'),
(20, 'The Weekend', 4530552, 'Timeless'),
(21, 'Lola Young', 4480000, 'Messy'),
(22, 'Chappell Roan', 4224704, 'Good Luck, Babe!'),
(23, 'Jimin', 4171136, 'Who'),
(24, 'Bad Bunny', 4158385, 'NUEVAYoL'),
(25, 'Lisa', 4098441, 'Born Again'),
(26, 'Neton Vega', 3983281, 'Loco'),
(27, 'Gigi Perez', 3882523, 'Sailor Song'),
(28, 'Doechii', 3880077, 'DENIAL IS A RIVER'),
(29, 'Bad Bunny', 3774256, 'VOY ALLeVARTE PA PR'),
(30, 'Chappell Roan', 3683278, 'Pink Pony Club'),
(31, 'Benson Boone', 3677557, 'Beautiful  Things'),
(32, 'The Weekend', 3486022, 'Cry For Me'),
(33, 'SZA', 3220524, '30 For 30'),
(34, 'Kendrick Lamar', 3204139, 'HUMBLE'),
(35, 'Bad Bunny', 3135058, 'VeLDA'),
(36, 'Bad Bunny', 3035058, 'EoO'),
(37, 'Gabito Ballesteros', 3000716, '7 Dias'),
(38, 'Tyler', 2880723, 'Like Him'),
(39, 'The Weekend', 3, 'One Of The Girls'),
(40, 'Sabrina Carpenter', 2808907, 'Taste'),
(41, 'Rauw Alejandro', 2775727, 'Que Pasaria'),
(42, 'Kendrick Lamar ', 2763234, 'Money Trees'),
(43, 'The Weekend', 2707523, 'Sao Paulo'),
(44, 'Lord Huron', 2690234, 'The Night We Met'),
(45, 'Tyler', 2678412, 'See You Again'),
(46, 'Aleman', 2675891, 'Te Queria Ver'),
(47, 'KAROL G', 2659222, 'Si Antes Te Hubiera Conocido'),
(48, 'Tate MCRae', 2634781, 'Sports Car'),
(49, 'Teddy Swims', 2734127, 'Lose Control '),
(50, 'yung kai', 2633012, 'blue'),
(51, 'Alleh', 2562122, 'capaz'),
(52, 'Rauw Alejandro', 2557212, 'Khe?'),
(53, 'Sabrina Carpenter', 2519252, 'Please Please Please'),
(54, 'SZA', 2500000, 'BMF'),
(55, 'Tito Double P', 2417222, 'Rosones'),
(56, 'Linkin Park', 2300333, 'The Emptiness Machine');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `leaderboard`
--

CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `leaderboard`
--

INSERT INTO `leaderboard` (`id`, `name`, `score`, `timestamp`) VALUES
(1, 'Chris', 1221, '2025-02-12 09:35:37'),
(2, 'lola', 24, '2025-02-12 09:35:58'),
(3, 'MAtze', 12, '2025-02-12 09:48:04'),
(4, 'lola', 1, '2025-02-12 09:49:22'),
(5, 'Chris', 2, '2025-02-12 09:53:33'),
(6, 'Chris', 3, '2025-02-12 09:55:43'),
(7, 'Chris', 0, '2025-02-12 10:00:03'),
(8, 'Chris', 1, '2025-02-12 10:04:11'),
(9, 'Chris', 0, '2025-02-12 10:06:35'),
(10, 'Chris', 1, '2025-02-12 10:25:50'),
(11, 'Chris', 1, '2025-02-12 10:26:28'),
(12, 'Chris', 2, '2025-02-12 10:27:59'),
(13, 'Chris', 1, '2025-02-12 10:45:51'),
(14, '', 0, '2025-02-12 10:48:13'),
(15, '', 0, '2025-02-12 10:48:26'),
(16, '', 0, '2025-02-12 10:48:33'),
(17, '', 0, '2025-02-12 10:48:47'),
(18, 'Chris', 1, '2025-02-12 10:49:41'),
(19, 'Chris', 1, '2025-02-12 10:49:44'),
(20, '', 0, '2025-02-12 10:51:42'),
(21, 'Chris', 1, '2025-02-12 10:52:00'),
(22, 'Chris', 1, '2025-02-12 11:06:49'),
(23, 'Chris', 1, '2025-02-12 11:08:48'),
(24, 'Chris', 1, '2025-02-12 11:09:24'),
(25, 'Chris', 1, '2025-02-12 11:09:36'),
(26, 'Chris', 3535, '2025-02-12 11:09:45'),
(27, 'lol', 1123, '2025-02-12 11:10:14'),
(28, 'dadaw', 1123, '2025-02-12 11:10:23'),
(29, 'daddadadaaw', 1123, '2025-02-12 11:10:32');


ALTER TABLE `highscores`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `künstler`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `highscores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `künstler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;


ALTER TABLE `leaderboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
