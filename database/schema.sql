-- ===================================================
-- Kuro Exclusive Mini E-Commerce - Database Schema
-- Brand: Kuro (Japanese Executive Luxury Perfume)
-- ===================================================

CREATE DATABASE IF NOT EXISTS `kuro_ecommerce` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kuro_ecommerce`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('client', 'kuro_admin') DEFAULT 'client',
    `membership_status` ENUM('unverified', 'pending', 'approved', 'rejected') DEFAULT 'unverified',
    `title_company` VARCHAR(200) NULL,
    `invite_code_used` VARCHAR(50) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Invite Codes Table
CREATE TABLE IF NOT EXISTS `invite_codes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `max_uses` INT DEFAULT 1,
    `times_used` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Whitelist Requests Table
CREATE TABLE IF NOT EXISTS `whitelist_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `organization_title` VARCHAR(200) NOT NULL,
    `statement_of_intent` TEXT NOT NULL,
    `olfactory_preference` VARCHAR(255) NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_note` TEXT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Products Table (Bespoke Showcase)
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `subtitle` VARCHAR(255) NOT NULL,
    `japanese_name` VARCHAR(100) NOT NULL,
    `edition_type` ENUM('1-of-1', 'Limited Reserve') DEFAULT '1-of-1',
    `edition_serial` VARCHAR(50) NOT NULL,
    `price` DECIMAL(15, 2) NOT NULL,
    `volume_ml` INT DEFAULT 100,
    `concentration` VARCHAR(50) DEFAULT 'Extrait de Parfum (35%)',
    `description` TEXT NOT NULL,
    `philosophy` TEXT NOT NULL,
    `flacon_craftsmanship` TEXT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `status` ENUM('available', 'reserved', 'acquired') DEFAULT 'available',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Olfactory Notes Table
CREATE TABLE IF NOT EXISTS `olfactory_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `note_type` ENUM('top', 'heart', 'base') NOT NULL,
    `note_name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Concierge Messages Table (Direct Private Consultation)
CREATE TABLE IF NOT EXISTS `concierge_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `thread_id` VARCHAR(100) NOT NULL,
    `sender_id` INT NOT NULL,
    `sender_name` VARCHAR(150) NOT NULL,
    `sender_role` ENUM('client', 'kuro_admin') NOT NULL,
    `product_id` INT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. High-Value Orders & Acquisitions Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `total_amount` DECIMAL(15, 2) NOT NULL,
    `payment_method` VARCHAR(100) NOT NULL,
    `payment_status` ENUM('pending_verification', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending_verification',
    `delivery_address` TEXT NOT NULL,
    `custom_engraving` VARCHAR(100) NULL,
    `wire_reference` VARCHAR(100) NULL,
    `coa_serial` VARCHAR(100) NOT NULL UNIQUE,
    `coa_hash` VARCHAR(64) NOT NULL,
    `acquired_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- SEED INITIAL DATA
-- ===================================================

-- Clear existing data if re-running
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `olfactory_notes`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `invite_codes`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users:
-- Kuro Admin (Creator): kuro@atelier.com / password: password123
-- Sample Approved Collector: kenji@sato-corp.jp / password: password123
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `membership_status`, `title_company`, `created_at`) VALUES
(1, 'Kuro (黒) Master Perfumer', 'kuro@atelier.com', '$2y$10$wT8KzU9n2yY3r1pZ3sB6Le7vEfg9bXQvP3vM9kZ2bM8X1c9kZ3sB6', 'kuro_admin', 'approved', 'Founder & Master Perfumer, Kuro Atelier Tokyo', NOW()),
(2, 'Daisuke Tanaka', 'tanaka@executives.co.jp', '$2y$10$wT8KzU9n2yY3r1pZ3sB6Le7vEfg9bXQvP3vM9kZ2bM8X1c9kZ3sB6', 'client', 'approved', 'CEO, Tanaka Private Equity Tokyo', NOW()),
(3, 'Elena Rostova', 'elena.rostova@geneva-arts.ch', '$2y$10$wT8KzU9n2yY3r1pZ3sB6Le7vEfg9bXQvP3vM9kZ2bM8X1c9kZ3sB6', 'client', 'approved', 'Senior Director, Geneva Haute Horlogerie', NOW());

-- Invite Codes
INSERT INTO `invite_codes` (`code`, `description`, `max_uses`, `times_used`, `is_active`) VALUES
('KURO-VIP-2026', 'Private VIP invitation for Sovereign collectors', 10, 2, 1),
('SHIBUI-CHAMBER', 'Exclusive access for Tokyo Art & Perfume Patron', 5, 1, 1),
('WABI-SABI-01', 'Private Founder Allocation code', 3, 0, 1);

-- Products (3 Bespoke 1-of-1 Flacons from PRD)
INSERT INTO `products` (`id`, `slug`, `name`, `subtitle`, `japanese_name`, `edition_type`, `edition_serial`, `price`, `volume_ml`, `concentration`, `description`, `philosophy`, `flacon_craftsmanship`, `image_url`, `status`) VALUES
(1, 'sumi-no-hikari', 'KURO Sumi No Hikari', 'Sovereign Kyara Oud & Smoked Hinoki Flacon', '墨の光 (Black Light)', '1-of-1', '#KURO-001/01 (1 of 1 Global Edition)', 85000000.00, 100, 'Extrait de Parfum (38% Oil Concentration)', 
'Sumi No Hikari merepresentasikan paradoks cahaya di dalam kegelapan pekat. Dibuat selama 7 tahun fermentasi kayu Kyara Oud liar berusia 80 tahun dari hutan purba Yakushima, dipadukan dengan getah Hinoki yang dipanen saat bulan purnama musim dingin.', 
'Mengusung filosofi Shibui (elegan yang tenang dan mendalam). Tidak ada aroma yang berteriak; setiap molekul wangi berbisik tentang ketenangan kuil Zen abad ke-16, membangkitkan aura kharisma maskulin seorang pemimpin yang tenang namun tak tergoyahkan.', 
'Flacon batu obsidian monolitik hitam pekat dipotong manual oleh pemahat batu warisan Kyoto. Dihiasi lelehan serbuk emas murni 24 karat yang dilebur dengan teknik pernis Urushi tradisional Jepang. Tutup botol dari kuningan padat bersegel cap segel Kuro.', 
'assets/images/kuro_sumi.jpg', 'available'),

(2, 'kintsugi-sovereign', 'KURO Kintsugi', 'The Golden Seam of Imperfection & Resilience', '金継ぎ (Golden Seam)', '1-of-1', '#KURO-002/01 (1 of 1 Global Edition)', 120000000.00, 100, 'Extrait de Parfum (40% Oil Concentration)', 
'Kintsugi adalah penghormatan tertinggi kepada keindahan bekas luka yang diisi emas. Membawa komposisi langka dari Calabrian Bergamot terpilih, Orris Butter berusia 5 tahun seharga 100.000 euro per kilogram, Red Saffron Kashmir, dan Mysore Sandalwood yang telah dilarang ekspornya.', 
'Didasarkan pada filosofi Wabi-sabi: kesempurnaan sejati justru lahir dari penerimaan ketidaksempurnaan. Sebuah parfum yang hanya diperuntukkan bagi jiwa tangguh yang telah melewati badai besar kehidupan dan mengubahnya menjadi kemuliaan tak ternilai.', 
'Setiap botol keramik hitam dibakar pada suhu 1.300°C di tanur Bizen kuno, kemudian diretakkan secara terkontrol dan disatukan kembali secara manual menggunakan emas murni 24K cair oleh seniman Kintsugi generasi ke-5 di Kanazawa.', 
'assets/images/kuro_kintsugi.jpg', 'available'),

(3, 'yugen-obsidian', 'KURO Yūgen', 'Subtle Grace, Eternal Smoke & Obsidian Crystal', '幽玄 (Profound Grace)', '1-of-1', '#KURO-003/01 (1 of 1 Global Edition)', 68000000.00, 100, 'Extrait de Parfum (35% Oil Concentration)', 
'Yūgen menangkap misteri yang tidak terucap: aroma kabut pagi di hutan cedar pegunungan Nikko, bunga Damask Rose Otto yang dipetik sebelum fajar, tembakau pipa daun kering, dan getah kemenyan Siam yang menghangatkan jiwa.', 
'Yūgen adalah konsep estetika Jepang yang berarti keanggunan yang samar, rasa kagum terhadap keindahan alam semesta yang terlalu megah untuk diungkapkan dengan kata-kata. Parfum ini menciptakan aura privasi yang magnetis dan misterius.', 
'Flacon kristal bersegi geometris dengan potongan berlian gelap (Smoky Crystal Glass) yang membiaskan cahaya keemasan dari cairan parfum kental di dalamnya. Dilengkapi cap knurled emas tembaga bermassa 320 gram yang memberikan sensasi genggaman eksekutif.', 
'assets/images/kuro_yugen.jpg', 'available');

-- Olfactory Notes
INSERT INTO `olfactory_notes` (`product_id`, `note_type`, `note_name`, `description`) VALUES
-- Product 1: Sumi No Hikari
(1, 'top', 'Yakushima Winter Hinoki', 'Aroma kayu cemara suci yang dingin dan segar bagai udara kuil bersalju'),
(1, 'top', 'Smoked Black Pepper of Malabar', 'Sentuhan pedas kering yang tajam dan berkarakter eksekutif'),
(1, 'heart', '80-Year Aged Kyara Oud', 'Kayu gaharu termahal di dunia dengan nuansa resin balsam dan madu gelap'),
(1, 'heart', 'Kyoto Incense Smoke', 'Asap dupa kuno penenang batin dan meditasi penguasa'),
(1, 'base', 'Black Ambergris', 'Fermentasi laut murni yang memberikan daya sebar megah tak terbatas'),
(1, 'base', 'Smoky Castoreum & Leather', 'Sentuhan kulit hitam pekat berkelas bespoke haute couture'),

-- Product 2: Kintsugi
(2, 'top', 'Sun-drenched Calabrian Bergamot', 'Percikan sitrus bangsawan yang berkilau di atas kegelapan'),
(2, 'top', 'Royal Kashmir Saffron', 'Benang saffron emas murni pembawa kemewahan oriental pertama'),
(2, 'heart', 'Florentine Orris Butter (5-Year Aged)', 'Bedak iris paling mahal di industri parfum dengan tekstur sutra'),
(2, 'heart', 'Black Truffle Accord', 'Kedalaman misterius tanah basah hutan rahasia'),
(2, 'base', 'Vintage Mysore Sandalwood', 'Cendana langka beraroma krim susu kayu yang hangat dan menenangkan'),
(2, 'base', '24K Liquid Gold Amber', 'Damar emas murni dengan ketahanan lebih dari 48 jam pada kulit'),

-- Product 3: Yugen
(3, 'top', 'Nikko Mountain Mist & Cedrat', 'Sensasi kabut pagi pegunungan tinggi Jepang'),
(3, 'top', 'Pink Peppercorn', 'Aksen segar modern yang membangkitkan indra penciuman'),
(3, 'heart', 'Midnight Damask Rose Otto', 'Mawar terpekat yang diekstraksi uap air dingin di tengah malam'),
(3, 'heart', 'Smoked Cuban Tobacco Leaf', 'Kehangatan tembakau eksekutif di lounge privat'),
(3, 'base', 'Benzoin of Siam', 'Resin manis hangat pembungkus aroma yang intim'),
(3, 'base', 'Obsidian Cedarwood', 'Pondasi kokoh kayu cedar hitam yang abadi');

-- Whitelist Request Initial Sample
INSERT INTO `whitelist_requests` (`id`, `user_id`, `full_name`, `email`, `phone`, `organization_title`, `statement_of_intent`, `olfactory_preference`, `status`, `reviewed_at`) VALUES
(1, 2, 'Daisuke Tanaka', 'tanaka@executives.co.jp', '+81 90-1234-5678', 'CEO, Tanaka Private Equity', 'Saya mengoleksi haute perfumerie 1-of-1 sejak tahun 2012. Kuro Sumi No Hikari adalah puncak karya yang telah lama saya nanti untuk koleksi pribadi saya.', 'Woody, Dark Kyara Oud, Incense', 'approved', NOW()),
(2, 3, 'Elena Rostova', 'elena.rostova@geneva-arts.ch', '+41 22 819 0000', 'Senior Director, Geneva Haute Horlogerie', 'Mencari wewangian edisi tunggal untuk menandai pembukaan galeri seni eksklusif kami di Zurich.', 'Orris, Floral Leather, Warm Amber', 'approved', NOW());

-- Concierge Initial Messages
INSERT INTO `concierge_messages` (`thread_id`, `sender_id`, `sender_name`, `sender_role`, `product_id`, `message`, `created_at`) VALUES
('thread_tanaka_sumi', 2, 'Daisuke Tanaka', 'client', 1, 'Konbanwa Kuro-sensei. Apakah botol Sumi No Hikari #KURO-001/01 memungkinkan untuk diukir inisial keluarga Tanaka dengan huruf kanji tradisional pada plat emasnya?', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('thread_tanaka_sumi', 1, 'Kuro (黒) Master Perfumer', 'kuro_admin', 1, 'Konbanwa Tanaka-san. Merupakan sebuah kehormatan. Plat emas 24K pada flacon Sumi No Hikari memang kami sediakan ruang khusus untuk personalisasi grafir tangan oleh pemahat kekaisaran Kyoto. Kami dapat melaksanakannya bersamaan dengan persiapan Paulownia wooden box.', DATE_SUB(NOW(), INTERVAL 1 HOUR));
