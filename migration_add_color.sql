-- Run this once on your database to add color tracking
ALTER TABLE `order_items`
  ADD COLUMN IF NOT EXISTS `selected_color` VARCHAR(80) DEFAULT NULL 
  COMMENT 'Color hex/label chosen at purchase (e.g. "#FF0000 Red")';
