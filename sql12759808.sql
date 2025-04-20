-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 05:04 PM
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
-- Database: `kaperustiko-pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_no` int(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `order_name` varchar(255) NOT NULL,
  `order_name2` varchar(255) NOT NULL,
  `order_quantity` int(255) NOT NULL,
  `order_size` varchar(255) NOT NULL,
  `basePrice` int(255) NOT NULL,
  `order_price` int(255) NOT NULL,
  `order_addons` varchar(255) NOT NULL,
  `order_addons_price` int(255) NOT NULL,
  `order_image` varchar(255) NOT NULL,
  `order_addons2` varchar(255) NOT NULL,
  `order_addons_price2` int(255) NOT NULL,
  `order_addons3` varchar(255) NOT NULL,
  `order_addons_price3` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_no`, `code`, `order_name`, `order_name2`, `order_quantity`, `order_size`, `basePrice`, `order_price`, `order_addons`, `order_addons_price`, `order_image`, `order_addons2`, `order_addons_price2`, `order_addons3`, `order_addons_price3`) VALUES
(287, 'P001', 'Carbonara', 'Pasta', 1, 'Regular', 150, 150, 'None', 0, 'carbonara.jpg', 'None', 0, 'None', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pos-menu`
--

CREATE TABLE `pos-menu` (
  `menu_no` int(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `title1` varchar(255) NOT NULL,
  `title2` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `label2` varchar(255) NOT NULL,
  `price1` int(255) NOT NULL,
  `price2` int(255) NOT NULL,
  `price3` int(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `qty` int(255) NOT NULL,
  `stock_date` varchar(255) NOT NULL,
  `stock_time` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos-menu`
--

INSERT INTO `pos-menu` (`menu_no`, `code`, `title1`, `title2`, `label`, `label2`, `price1`, `price2`, `price3`, `image`, `qty`, `stock_date`, `stock_time`) VALUES
(1, 'A001', 'Peperoni', 'Pizza', 'Food', 'Pizza', 150, 300, 900, 'pizza.jpg', 3, '11/17/2024', '07:00:38 PM'),
(2, 'A002', 'Cheese', 'Burger', 'Food', 'Burger', 100, 180, 250, 'burger.jpg', 1, '11/18/2024', '05:36:05 PM'),
(3, 'B001', 'Americano', 'Coffee', 'Beverages', 'Coffee', 80, 120, 150, 'americano.jpg', 1, '11/17/2024\r\n', '07:13:00 PM'),
(4, 'B002', 'Cappuccino', 'Coffee', 'Beverages', 'Coffee', 80, 120, 150, 'cappuccino.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(5, 'B003', 'Spanish', 'Latte', 'Beverages', 'Coffee', 80, 120, 150, 'latte.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(6, 'B004', 'Hazelnut', 'Latte', 'Beverages', 'Coffee', 80, 120, 150, 'hazelnut-latte.jpg', 0, '2024-11-18', '17:36:27'),
(7, 'U001', 'Sisig', 'Beef', 'Food', 'Ulam', 250, 450, 900, 'sisig.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(8, 'P001', 'Carbonara', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'carbonara.jpg', 6, '2024-11-19', '00:37:01'),
(9, 'P002', 'Spaghetti', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'spagheti.jpg', 0, '11/18/2024', '05:35:28 PM'),
(10, 'P003', 'Miki', 'Pasta', 'Food', 'Pasta', 150, 300, 500, 'pansit-miki.jpg', 14, '2024-11-18', '05:33:41 PM'),
(11, 'D001', 'Buko Pandan', 'Salad', 'Food', 'Dessert', 50, 90, 150, 'buko-pandan.jpg', 0, '11/17/2024\r\n', '07:13:00 PM'),
(32, 'U002', 'Chicken', 'Adobo', 'Food', 'Ulam', 150, 300, 500, 'd741f28eef20eaef73b8d93df2ce555c.jpg', 10, '2024-11-18', '16:41:11'),
(35, 'D002', 'Cassava ', 'Cake', 'Food', 'Dessert', 80, 125, 250, 'cassava-cake.jpg', 10, '2024-11-25', '15:54:10'),
(36, 'U003', 'Pork', 'Sinigang', 'Food', 'Ulam', 150, 250, 500, 'pork-sinigang.jpg', 10, '2024-11-25', '16:12:42'),
(37, 'U004', 'Chicken', 'Inasal', 'Food', 'Ulam', 120, 250, 550, 'chicken-inasal.jpg', 5, '2024-11-25', '16:14:27'),
(38, 'U004', 'Laing', 'Authentic', 'Food', 'Ulam', 80, 150, 250, 'laing.jpg', 5, '2024-11-25', '16:17:38'),
(39, 'D003', 'Halo-Halo', 'Special', 'Food', 'Dessert', 85, 130, 200, 'halo-halo.jpg', 5, '2024-11-25', '16:19:15'),
(40, 'T001', 'Strawberry', 'Fruit Tea', 'Beverages', 'Tea', 85, 120, 150, 'strawberry-fruit-tea.jpg', 9, '2024-11-25', '16:22:21'),
(41, 'T002', 'Blueberry', 'Fruit Tea', 'Beverages', 'Tea', 85, 120, 150, 'blueberry-fruit-tea.jpg', 10, '2024-11-25', '16:24:18'),
(42, 'T003', 'Green Apple', 'Fruit Tea', 'Beverages', 'Tea', 80, 120, 150, 'greenapple-fruit-tea.jpg', 6, '2024-11-25', '16:26:01'),
(43, 'S001', 'Coke', 'Soda', 'Beverages', 'Soda', 30, 75, 120, 'coke.jpg', 14, '2024-11-25', '16:28:31'),
(44, 'R001', 'Plain', 'Rice', 'Food', 'Rice', 20, 150, 300, 'plain-rice.jpg', 14, '2024-11-25', '16:41:04'),
(45, 'R002', 'Java', 'Rice', 'Food', 'Rice', 20, 120, 300, 'java-rice.jpg', 13, '2024-11-25', '16:41:58'),
(46, 'R003', 'Fried', 'Rice', 'Food', 'Rice', 30, 150, 400, 'fried-rice.jpg', 14, '2024-11-25', '16:42:58'),
(47, 'T003', 'Mango', 'Fruit Tea', 'Beverages', 'Tea', 80, 120, 150, 'mango-fruit-tea.jpg', 5, '2024-11-25', '23:45:33');

-- --------------------------------------------------------

--
-- Table structure for table `remit_returns`
--

CREATE TABLE `remit_returns` (
  `return_id` int(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `total_sales` varchar(255) NOT NULL,
  `return_date` varchar(255) NOT NULL,
  `return_time` varchar(255) NOT NULL,
  `return_validation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remit_returns`
--

INSERT INTO `remit_returns` (`return_id`, `cashier_name`, `total_sales`, `return_date`, `return_time`, `return_validation`) VALUES
(1, 'Mike', '945.00', '2024-11-17', '5:17:08 PM', 'Validated'),
(2, 'Mike', '945.00', '2024-11-17', '5:17:08 PM', 'Validated'),
(3, 'Mike', '150.00', '2024-11-18', '5:43:13 PM', 'Pending'),
(4, 'Mike', '150.00', '2024-11-18', '5:45:02 PM', 'Validated'),
(5, 'sample', '125.00', '2024-11-19', '3:53:17 PM', 'Validated');

-- --------------------------------------------------------

--
-- Table structure for table `remit_sales`
--

CREATE TABLE `remit_sales` (
  `remit_id` int(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `total_sales` varchar(255) NOT NULL,
  `remit_date` varchar(255) NOT NULL,
  `remit_time` varchar(255) NOT NULL,
  `remit_shortage` int(255) NOT NULL,
  `remit_validation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remit_sales`
--

INSERT INTO `remit_sales` (`remit_id`, `cashier_name`, `total_sales`, `remit_date`, `remit_time`, `remit_shortage`, `remit_validation`) VALUES
(11, 'Mike', '645.00', '2024-11-17', '7:08:39 PM', 255, 'Validated'),
(12, 'Mike', '165.00', '2024-11-16', '7:18:48 PM', 23, 'Pending'),
(13, 'Mike', '300.00', '2024-11-18', '5:50:13 PM', 0, 'Validated');

-- --------------------------------------------------------

--
-- Table structure for table `return-orders`
--

CREATE TABLE `return-orders` (
  `return_id` int(255) NOT NULL,
  `receipt_number` int(255) NOT NULL,
  `return_date` varchar(255) NOT NULL,
  `return_time` varchar(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `items_ordered` varchar(255) NOT NULL,
  `total_amount` int(255) NOT NULL,
  `amount_paid` int(255) NOT NULL,
  `amount_change` int(255) NOT NULL,
  `order_take` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return-orders`
--

INSERT INTO `return-orders` (`return_id`, `receipt_number`, `return_date`, `return_time`, `cashier_name`, `items_ordered`, `total_amount`, `amount_paid`, `amount_change`, `order_take`) VALUES
(4, 101, '11/17/2024', '4:59:10 PM', 'Mike', '[{\"order_name\":\"Miki\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"150\"}]', 150, 500, 350, 'Take Out'),
(5, 100, '11/17/2024', '4:58:51 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"100\"}]', 100, 500, 400, 'Take Out'),
(6, 102, '11/17/2024', '4:59:31 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"1\"}]', 165, 500, 335, 'Take Out'),
(7, 103, '11/17/2024', '5:13:41 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\",\"order_price\":\"115\"}]', 115, 500, 385, 'Take Out'),
(8, 105, '11/17/2024', '5:25:39 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"10\"}]', 115, 500, 385, 'Take Out'),
(9, 105, '11/17/2024', '9:51:40 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(10, 105, '11/17/2024', '9:52:46 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(11, 105, '11/17/2024', '10:01:22 PM', 'Mike', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 100, 500, 400, 'Take Out'),
(12, 105, '11/18/2024', '3:37:27 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 500, 350, 'Take Out'),
(13, 119, '11/18/2024', '6:30:27 PM', 'Mike', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Peperoni\",\"order_name2\":\"Pizza\",\"order_quantity\":\"x1\",\"order_size\":', 380, 500, 120, 'Dine In'),
(14, 1, '11/18/2024', '6:29:12 PM', 'Mike', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"10\"}]', 195, 500, 305, 'Dine In'),
(15, 1, '11/18/2024', '6:53:18 PM', 'Mike', '[{\"order_name\":\"Americano\",\"order_name2\":\"Coffee\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\"', 380, 500, 120, 'Take Out'),
(16, 122, '11/18/2024', '6:57:02 PM', '', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 500, 350, 'Take Out'),
(17, 121, '11/18/2024', '6:54:25 PM', '', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 200, 50, 'Take Out'),
(18, 127, '11/19/2024', '3:48:51 PM', 'sample', '[{\"order_name\":\"Spanish\",\"order_name2\":\"Latte\",\"order_quantity\":\"x1\",\"order_size\":\"Large\",\"order_addons\":\"Sugar\",\"order_addons_price\":\"5\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 125, 200, 75, 'Take Out');

-- --------------------------------------------------------

--
-- Table structure for table `total_sales`
--

CREATE TABLE `total_sales` (
  `total_order` int(255) NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `items_ordered` varchar(999) NOT NULL,
  `total_amount` int(255) NOT NULL,
  `amount_paid` int(255) NOT NULL,
  `amount_change` int(255) NOT NULL,
  `order_take` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `total_sales`
--

INSERT INTO `total_sales` (`total_order`, `receipt_number`, `date`, `time`, `cashier_name`, `items_ordered`, `total_amount`, `amount_paid`, `amount_change`, `order_take`) VALUES
(123, '123', '11/18/2024', '6:59:37 PM', 'Mike', '[{\"order_name\":\"Peperoni\",\"order_name2\":\"Pizza\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 500, 350, 'Take Out'),
(124, '124', '11/18/2024', '7:00:54 PM', 'sample', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 200, 50, 'Take Out'),
(125, '125', '11/18/2024', '7:02:22 PM', 'sample', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 200, 50, 'Take Out'),
(126, '126', '11/18/2024', '7:02:40 PM', 'sample', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 300, 200, -100, 'Take Out'),
(128, '127', '11/19/2024', '3:55:49 PM', 'sample', '[{\"order_name\":\"Cheese\",\"order_name2\":\"Burger\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons\":\"Extra Cheese\",\"order_addons_price\":\"15\",\"order_addons2\":\"Bacon\",\"order_addons_price2\":\"20\",\"order_addons3\":\"Olives\",\"order_addons_price3\":\"10\"}]', 145, 500, 355, 'Take Out'),
(129, '129', '11/25/2024', '11:49:15 PM', 'sample', '[{\"order_name\":\"Strawberry\",\"order_name2\":\"Fruit Tea\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Spaghetti\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 235, 200, -35, 'Take Out'),
(130, '130', '11/25/2024', '11:51:29 PM', 'sample', '[{\"order_name\":\"Hazelnut\",\"order_name2\":\"Latte\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"},{\"order_name\":\"Hazelnut\",\"order_name2\":\"Latte\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 160, 20, -140, 'Take Out'),
(131, '131', '11/25/2024', '11:57:10 PM', 'sample', '[{\"order_name\":\"Carbonara\",\"order_name2\":\"Pasta\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 150, 2, -148, 'Dine In'),
(132, '132', '11/25/2024', '11:57:29 PM', 'sample', '[{\"order_name\":\"Hazelnut\",\"order_name2\":\"Latte\",\"order_quantity\":\"x1\",\"order_size\":\"Regular\",\"order_addons_price\":\"0\",\"order_addons_price2\":\"0\",\"order_addons_price3\":\"0\"}]', 80, 2, -78, 'Take Out');

-- --------------------------------------------------------

--
-- Table structure for table `user-staff`
--

CREATE TABLE `user-staff` (
  `staff_no` int(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contactNumber` int(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `staff_token` int(255) NOT NULL,
  `avatar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user-staff`
--

INSERT INTO `user-staff` (`staff_no`, `lastName`, `middleName`, `firstName`, `password`, `contactNumber`, `email`, `staff_token`, `avatar`) VALUES
(18, 'Dayandante', 'Dalaguit', 'Mike', '$2y$10$8waarktMtnJGANyeIZeMSeS6pypgTZOicWDRmhY6Tjp.i70wrxG5a', 2147483647, 'mikedayandante@gmail.com', 18, 'default.jpg'),
(20, 'sample', 'sample', 'sample', '$2y$10$9AFAZTDhYB8pzMZ4uWK.Ceb6nOVfLXWLerh4c0aLSZmO34KriPGVW', 2147483647, 'sample@gmail.com', 20, 'default.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_no`);

--
-- Indexes for table `pos-menu`
--
ALTER TABLE `pos-menu`
  ADD PRIMARY KEY (`menu_no`);

--
-- Indexes for table `remit_returns`
--
ALTER TABLE `remit_returns`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `remit_sales`
--
ALTER TABLE `remit_sales`
  ADD PRIMARY KEY (`remit_id`);

--
-- Indexes for table `return-orders`
--
ALTER TABLE `return-orders`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `total_sales`
--
ALTER TABLE `total_sales`
  ADD PRIMARY KEY (`total_order`);

--
-- Indexes for table `user-staff`
--
ALTER TABLE `user-staff`
  ADD PRIMARY KEY (`staff_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT for table `pos-menu`
--
ALTER TABLE `pos-menu`
  MODIFY `menu_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `remit_returns`
--
ALTER TABLE `remit_returns`
  MODIFY `return_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `remit_sales`
--
ALTER TABLE `remit_sales`
  MODIFY `remit_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `return-orders`
--
ALTER TABLE `return-orders`
  MODIFY `return_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `total_sales`
--
ALTER TABLE `total_sales`
  MODIFY `total_order` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `user-staff`
--
ALTER TABLE `user-staff`
  MODIFY `staff_no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
