-- Add payments table for repayment tracking
-- Run this SQL to enable payment/repayment features

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_number` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `account_number` int(10) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_number`)
);
