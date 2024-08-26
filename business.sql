-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 15 Δεκ 2023 στις 16:08:10
-- Έκδοση διακομιστή: 10.4.28-MariaDB
-- Έκδοση PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `business`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `admin`
--

CREATE TABLE `admin` (
  `adm_id` int(5) NOT NULL,
  `adm_lat` double NOT NULL,
  `adm_lng` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `admin`
--

INSERT INTO `admin` (`adm_id`, `adm_lat`, `adm_lng`) VALUES
(2, 14.252505883489249, 7.734375000000001),
(4, 14.252505883489249, 7.734375000000001);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `citizen`
--

CREATE TABLE `citizen` (
  `cit_id` int(5) NOT NULL,
  `cit_lat` double NOT NULL,
  `cit_lng` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `citizen`
--

INSERT INTO `citizen` (`cit_id`, `cit_lat`, `cit_lng`) VALUES
(16, 0, 0);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `rescuer`
--

CREATE TABLE `rescuer` (
  `res_id` int(5) NOT NULL,
  `res_lat` double NOT NULL,
  `res_lng` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `rescuer`
--

INSERT INTO `rescuer` (`res_id`, `res_lat`, `res_lng`) VALUES
(3, 37.02954851545319, 22.148437500000004),
(5, 38.15228912751926, 24.104003906250004);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(5) NOT NULL,
  `username` varchar(10) NOT NULL,
  `password` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `phone` bigint(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `surname`, `phone`) VALUES
(2, 'nick', '12345', 'Nikos', 'Stamatiou', 6954392007),
(3, 'mike', 'mike', 'Michalis', 'Zannis', 6980439896),
(4, 'theuser', 'oof', 'Giannis', 'Andreou', 6954392509),
(5, 'res', '11111', 'Sotiris', 'Ioannou', 6946534023),
(16, 'snikthehus', 'wins', 'Dimitris', 'Giannoulis', 2610323606),
(17, 'chris', 'sporar', 'christos', 'psathogiannakis', 2610326982);

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adm_id`);

--
-- Ευρετήρια για πίνακα `citizen`
--
ALTER TABLE `citizen`
  ADD PRIMARY KEY (`cit_id`);

--
-- Ευρετήρια για πίνακα `rescuer`
--
ALTER TABLE `rescuer`
  ADD PRIMARY KEY (`res_id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `citizen`
--
ALTER TABLE `citizen`
  MODIFY `cit_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
