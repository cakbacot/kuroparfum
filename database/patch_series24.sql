USE `kuro_ecommerce`;

-- 1. Modify products table for Series 24
ALTER TABLE `products` 
    MODIFY COLUMN `edition_type` ENUM('1-of-1', 'Series-24', 'Limited Reserve') DEFAULT 'Series-24',
    ADD COLUMN IF NOT EXISTS `stock` INT DEFAULT 24 AFTER `price`;

-- Set 1-of-1 products to stock 1
UPDATE `products` SET `stock` = 1 WHERE `edition_type` = '1-of-1';

-- 2. Add Cart Items Table
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Modify Orders Table to support Checkout Review stage & Official Invoice
ALTER TABLE `orders`
    MODIFY COLUMN `payment_method` VARCHAR(100) NULL,
    MODIFY COLUMN `payment_status` ENUM('checkout_review', 'pending_verification', 'confirmed', 'completed', 'cancelled') DEFAULT 'checkout_review',
    MODIFY COLUMN `coa_serial` VARCHAR(100) NULL,
    MODIFY COLUMN `coa_hash` VARCHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS `invoice_number` VARCHAR(60) NULL AFTER `order_number`,
    ADD COLUMN IF NOT EXISTS `invoice_hash` VARCHAR(64) NULL AFTER `invoice_number`;

-- 4. Add Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price_per_item` DECIMAL(15, 2) NOT NULL,
    `subtotal` DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Insert Series 24 Products (Limited 24 Units per Product)
INSERT INTO `products` (`id`, `slug`, `name`, `subtitle`, `japanese_name`, `edition_type`, `edition_serial`, `price`, `stock`, `volume_ml`, `concentration`, `description`, `philosophy`, `flacon_craftsmanship`, `image_url`, `status`) 
VALUES
(4, 'series-24-noir', 'KURO Noir (Series 24)', 'Limited Atelier Allocation of 24 Flacons Worldwide', '黒・第二十四輯 (Series 24)', 'Series-24', 'Series 24 Edition (Limit 24 Botol)', 4850000.00, 24, 50, 'Eau de Parfum Intense (26% Concentration)',
'Kuro Series 24 dirancang untuk memperluas akses seni wewangian Kuro kepada kolektor global. Dibuat dalam alokasi terukur tepat 24 botol di seluruh dunia per batch produksi, menggabungkan Black Amber, Smoked Incense, dan Bergamot Calabria.',
'Konsep Series 24 menjembatani kemewahan murni dengan ketersediaan eksklusif. Tetap memegang teguh standar olfaktori Ginza, setiap botol diberi nomor urut seri 01/24 hingga 24/24.',
'Botol persegi hitam doff monolitik dengan tutup kuningan satin berukir monogram Kuro dan penomoran laser batch 01/24.',
'assets/images/kuro_series24_noir.jpg', 'available'),

(5, 'series-24-amber-hinoki', 'KURO Hinoki Amber (Series 24)', 'Sacred Forest Resin & Golden Honey Amber', '檜琥珀・第二十四輯 (Series 24)', 'Series-24', 'Series 24 Edition (Limit 24 Botol)', 5600000.00, 24, 50, 'Eau de Parfum Intense (28% Concentration)',
'Edisi Series 24 kedua yang menghadirkan kehangatan getah cemara Hinoki pegunungan Kiso dan damar emas amber bercampur madu liar. Tersedia tepat 24 botol untuk kolektor yang telah memiliki akun terdaftar.',
'Harmoni antara aroma kayu suci kuil Shinto dan kehangatan amber yang bersinar di tengah kegelapan, memberikan aura eksekutif yang ramah namun berkarisma tinggi.',
'Flacon kristal gelap transparan dengan kilauan cairan emas di dalamnya, dilengkapi tutup hitam bertekstur arang Binchotan.',
'assets/images/kuro_series24_amber.jpg', 'available')
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `stock` = VALUES(`stock`),
    `price` = VALUES(`price`),
    `edition_type` = VALUES(`edition_type`),
    `image_url` = VALUES(`image_url`);

-- Notes for Product 4 (Series 24 Noir)
INSERT INTO `olfactory_notes` (`product_id`, `note_type`, `note_name`, `description`) VALUES
(4, 'top', 'Italian Bergamot & Pink Pepper', 'Segar tajam dan membangkitkan pesona karismatik'),
(4, 'heart', 'Black Incense & Nutmeg', 'Asap dupa hitam hangat khas kuil malam Ginza'),
(4, 'base', 'Smoked Vetiver & Amber Noir', 'Pondasi kokoh kayu aromatik berkarakter eksekutif');

-- Notes for Product 5 (Series 24 Hinoki Amber)
INSERT INTO `olfactory_notes` (`product_id`, `note_type`, `note_name`, `description`) VALUES
(5, 'top', 'Kiso Hinoki Needle Mist', 'Kesejukan embun pagi hutan cemara Kiso Jepang'),
(5, 'heart', 'Golden Honey Accord & Cedar', 'Sentuhan manis damar madu alami berpadu kayu cedar'),
(5, 'base', 'Smoky Labdanum & White Amber', 'Jejak hangat sensual dengan ketahanan 18+ jam');
