USE onlineDB;

-- 1) Users: add email, password hashing length, quiz_taken flag, total_spent
ALTER TABLE `user`
  ADD COLUMN `email` varchar(100) DEFAULT NULL AFTER `username`,
  MODIFY `Password` varchar(255) NOT NULL,
  ADD COLUMN `quiz_taken` TINYINT(1) DEFAULT 0 AFTER `User_Type`,
  ADD COLUMN `total_spent` INT(10) DEFAULT 0 AFTER `quiz_taken`,
  ADD COLUMN `role` VARCHAR(10) DEFAULT 'buyer' AFTER `total_spent`;

-- 2) Make primary keys auto_increment where appropriate
ALTER TABLE `user` MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `product` MODIFY `ProductID` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cart` MODIFY `CartID` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order` MODIFY `OrderID` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `discountoffer` MODIFY `OfferID` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `quiz` MODIFY `QuizID` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `payment_method` MODIFY `TRANSACTIONID` int(10) NOT NULL AUTO_INCREMENT;

-- 3) Add image column to product
ALTER TABLE `product` ADD COLUMN `ImagePath` VARCHAR(255) DEFAULT NULL AFTER `Review`;

-- 4) Add order total, payment method reference
ALTER TABLE `order` ADD COLUMN `UserID` int(11) NOT NULL AFTER `OrderID`, ADD COLUMN `TotalAmount` INT(10) DEFAULT 0 AFTER `Order_date`, ADD COLUMN `PaymentMethod` VARCHAR(20) NULL AFTER `TotalAmount`;
