SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `bfrom` int(11) NOT NULL,
  `bto` int(11) NOT NULL,
  `bdate` varchar(8) NOT NULL,
  `btime` varchar(8) NOT NULL,
  `amount` varchar(6) NOT NULL,
  `description` varchar(50) NOT NULL,
  `paid` varchar(1) NOT NULL DEFAULT 'N',
  `paidDate` varchar(8) DEFAULT NULL,
  `deleted` varchar(1) NOT NULL DEFAULT 'N',
  `deletedDate` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groceries` (
  `id` int(4) NOT NULL,
  `item` varchar(15) NOT NULL,
  `users` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groceryHistory` (
  `id` int(4) NOT NULL,
  `name` int(4) NOT NULL,
  `item` int(4) NOT NULL,
  `amount` varchar(6) DEFAULT NULL,
  `date` varchar(8) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `paypal` varchar(40) DEFAULT NULL,
  `resetKey` varchar(10) DEFAULT NULL,
  `resetRequired` varchar(1) NOT NULL DEFAULT 'Y',
  `apiKey` varchar(32) DEFAULT NULL,
  `role` varchar(5) NOT NULL DEFAULT 'USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groceries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groceryHistory`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
ALTER TABLE `groceries`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `groceryHistory`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `paypal`, `resetKey`, `resetRequired`, `apiKey`, `role`) VALUES
(10, 'admin', 'Administrator', '', '$6$efe91420ba$YFxJpk7FMPL4G4sJg5jfgceRzsqBOuqB6g9j2fRKl1f0puuIlE2Gew0nqWB82UsKRNIhPRFXrG7KPDmQCILbv1', '', NULL, 'N', NULL, 'ADMIN');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
