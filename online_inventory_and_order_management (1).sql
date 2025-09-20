-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 09:05 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online inventory and order management`
--

-- --------------------------------------------------------

--
-- Table structure for table `buys`
--
Create Database onlineDB;
use onlineDB;

CREATE TABLE `buys` (
  `BUserID` int(11) NOT NULL,
  `CartID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `CartID` int(10) NOT NULL,
  `Quantity` int(10) NOT NULL,
  `Discount_Applied` int(10) NOT NULL,
  `Total` int(10) NOT NULL,
  `BUserID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `catagory`
--

CREATE TABLE `catagory` (
  `Catagory` varchar(30) NOT NULL,
  `ProductID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `discountoffer`
--

CREATE TABLE `discountoffer` (
  `OfferID` int(10) NOT NULL,
  `DiscountPercent` varchar(20) NOT NULL,
  `CoinsRequired` int(10) NOT NULL,
  `DUserID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `gateway_type`
--

CREATE TABLE `gateway_type` (
  `Gateway_Type` varchar(20) NOT NULL,
  `TransactionID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `OrderID` int(10) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Shipping_Date` date NOT NULL,
  `Shipping_Address` varchar(30) NOT NULL,
  `Order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `TRANSACTIONID` int(10) NOT NULL,
  `PORDERID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `UserID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(10) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Brand` varchar(20) NOT NULL,
  `StockQuantity` int(10) NOT NULL,
  `Price` int(10) NOT NULL,
  `Review` varchar(20) NOT NULL,
  `SUserID` int(10) NOT NULL,
  `DofferID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `QuizID` int(10) NOT NULL,
  `Title` varchar(30) NOT NULL,
  `Question` varchar(30) NOT NULL,
  `Answer` varchar(30) NOT NULL,
  `Reward` int(10) NOT NULL,
  `QUserID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Contact` int(11) NOT NULL,
  `Street` varchar(20) NOT NULL,
  `City` varchar(20) NOT NULL,
  `Building_No` int(11) NOT NULL,
  `User_Type` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buys`
--
ALTER TABLE `buys`
  ADD PRIMARY KEY (`BUserID`,`CartID`,`ProductID`),
  ADD KEY `CartID` (`CartID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CartID`),
  ADD KEY `BUserID` (`BUserID`);

--
-- Indexes for table `catagory`
--
ALTER TABLE `catagory`
  ADD PRIMARY KEY (`Catagory`,`ProductID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `discountoffer`
--
ALTER TABLE `discountoffer`
  ADD PRIMARY KEY (`OfferID`),
  ADD KEY `DUserID` (`DUserID`);

--
-- Indexes for table `gateway_type`
--
ALTER TABLE `gateway_type`
  ADD PRIMARY KEY (`Gateway_Type`,`TransactionID`),
  ADD KEY `TransactionID` (`TransactionID`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`TRANSACTIONID`),
  ADD KEY `PORDERID` (`PORDERID`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`UserID`,`ProductID`,`OrderID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `product_ibfk_1` (`DofferID`),
  ADD KEY `SUserID` (`SUserID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`QuizID`),
  ADD KEY `QUserID` (`QUserID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buys`
--
ALTER TABLE `buys`
  ADD CONSTRAINT `buys_ibfk_1` FOREIGN KEY (`BUserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `buys_ibfk_2` FOREIGN KEY (`CartID`) REFERENCES `cart` (`CartID`),
  ADD CONSTRAINT `buys_ibfk_3` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`BUserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `catagory`
--
ALTER TABLE `catagory`
  ADD CONSTRAINT `catagory_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`);

--
-- Constraints for table `discountoffer`
--
ALTER TABLE `discountoffer`
  ADD CONSTRAINT `discountoffer_ibfk_1` FOREIGN KEY (`DUserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `gateway_type`
--
ALTER TABLE `gateway_type`
  ADD CONSTRAINT `gateway_type_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `payment_method` (`TRANSACTIONID`);

--
-- Constraints for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD CONSTRAINT `payment_method_ibfk_1` FOREIGN KEY (`PORDERID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `places`
--
ALTER TABLE `places`
  ADD CONSTRAINT `places_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `places_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`),
  ADD CONSTRAINT `places_ibfk_3` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`DofferID`) REFERENCES `discountoffer` (`OfferID`),
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`SUserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`QUserID`) REFERENCES `user` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
