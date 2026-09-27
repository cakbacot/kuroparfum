-- Kuro Atelier - Admin Order Fulfillment & Status Patch
USE `kuro_ecommerce`;

ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `fulfillment_status` ENUM('menunggu', 'dikemas', 'dikirim', 'selesai', 'dibatalkan') DEFAULT 'menunggu',
    ADD COLUMN IF NOT EXISTS `courier_name` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `tracking_number` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `fulfillment_notes` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
