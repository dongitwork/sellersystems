-- =====================================================
-- OPTIMIZED PRODUCT DATABASE SCHEMA WITH LOYALTY PRICING
-- =====================================================

-- 1. Bảng cấp độ loyalty (thêm mới)
CREATE TABLE IF NOT EXISTS `srs_loyalty_tiers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tier_name` VARCHAR(50) NOT NULL,
  `tier_slug` VARCHAR(50) NOT NULL,
  `min_orders` INT(11) DEFAULT 0,
  `min_amount` DECIMAL(10,2) DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tier_slug` (`tier_slug`),
  KEY `idx_tier_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng sản phẩm (tối ưu với indexes)
CREATE TABLE IF NOT EXISTS `srs_products` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(255) NOT NULL,
  `category` VARCHAR(255) NOT NULL,
  `style` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image_url` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `meta_data` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_style` (`style`),
  KEY `idx_product_name` (`product_name`),
  KEY `idx_brand` (`brand`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng màu sản phẩm (tối ưu)
CREATE TABLE IF NOT EXISTS `srs_product_colors` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT(20) NOT NULL,
  `color_name` VARCHAR(20) NOT NULL,
  `color_code` VARCHAR(20) NOT NULL,
  `color_image` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `meta_data` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_color` (`product_id`,`color_name`),
  KEY `idx_color_active` (`is_active`),
  CONSTRAINT `fk_color_product`
    FOREIGN KEY (`product_id`) REFERENCES `srs_products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng giá base (base price cho tất cả users)
CREATE TABLE IF NOT EXISTS `srs_product_prices` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT(20) NOT NULL,
  `size` VARCHAR(10) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extra_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `rush_fee` DECIMAL(10,2) NOT NULL DEFAULT 1.49,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extra_shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `priority_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extra_priority_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `label_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.30,
  `special_fee` DECIMAL(10,2) NOT NULL DEFAULT 2.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `meta_data` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_size` (`product_id`,`size`),
  KEY `idx_price_active` (`is_active`),
  CONSTRAINT `fk_price_product`
    FOREIGN KEY (`product_id`) REFERENCES `srs_products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng giá theo loyalty tier (thêm mới)
CREATE TABLE IF NOT EXISTS `srs_loyalty_prices` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT(20) NOT NULL,
  `tier_id` INT(11) NOT NULL,
  `size` VARCHAR(10) NOT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `extra_price` DECIMAL(10,2) DEFAULT NULL,
  `rush_fee` DECIMAL(10,2) DEFAULT NULL,
  `shipping_fee` DECIMAL(10,2) DEFAULT NULL,
  `extra_shipping_fee` DECIMAL(10,2) DEFAULT NULL,
  `priority_fee` DECIMAL(10,2) DEFAULT NULL,
  `extra_priority_fee` DECIMAL(10,2) DEFAULT NULL,
  `label_fee` DECIMAL(10,2) DEFAULT NULL,
  `special_fee` DECIMAL(10,2) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `meta_data` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loyalty_price` (`product_id`,`tier_id`,`size`),
  KEY `idx_loyalty_active` (`is_active`),
  CONSTRAINT `fk_loyalty_product`
    FOREIGN KEY (`product_id`) REFERENCES `srs_products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_loyalty_tier`
    FOREIGN KEY (`tier_id`) REFERENCES `srs_loyalty_tiers` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Thêm cột loyalty_tier_id vào bảng users (nếu chưa có)
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `loyalty_tier_id` INT(11) DEFAULT NULL AFTER `api_token`,
ADD CONSTRAINT `fk_user_loyalty_tier`
  FOREIGN KEY (`loyalty_tier_id`) REFERENCES `srs_loyalty_tiers` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- SAMPLE DATA FOR LOYALTY TIERS
-- =====================================================

INSERT INTO `srs_loyalty_tiers` (`tier_name`, `tier_slug`, `min_orders`, `min_amount`, `discount_percent`, `description`, `sort_order`) VALUES
('Standard', 'standard', 0, 0.00, 0.00, 'Giá chuẩn cho khách hàng mới', 1),
('Silver', 'silver', 10, 1000.00, 5.00, 'Giảm 5% cho khách hàng thân thiết', 2),
('Gold', 'gold', 50, 5000.00, 10.00, 'Giảm 10% cho khách hàng VIP', 3),
('Platinum', 'platinum', 100, 10000.00, 15.00, 'Giảm 15% cho khách hàng VVIP', 4),
('Diamond', 'diamond', 200, 20000.00, 20.00, 'Giảm 20% cho đối tác chiến lược', 5);

-- =====================================================
-- VIEWS FOR EASY QUERYING
-- =====================================================

-- View: Lấy giá sản phẩm theo user (có loyalty tier)
CREATE OR REPLACE VIEW `vw_user_product_prices` AS
SELECT
  p.id AS product_id,
  p.product_name,
  p.brand,
  p.category,
  p.style,
  u.id AS user_id,
  u.username,
  u.loyalty_tier_id,
  lt.tier_name,
  lt.discount_percent,
  pp.size,
  COALESCE(lp.price, pp.price) AS price,
  COALESCE(lp.extra_price, pp.extra_price) AS extra_price,
  COALESCE(lp.rush_fee, pp.rush_fee) AS rush_fee,
  COALESCE(lp.shipping_fee, pp.shipping_fee) AS shipping_fee,
  COALESCE(lp.extra_shipping_fee, pp.extra_shipping_fee) AS extra_shipping_fee,
  COALESCE(lp.priority_fee, pp.priority_fee) AS priority_fee,
  COALESCE(lp.extra_priority_fee, pp.extra_priority_fee) AS extra_priority_fee,
  COALESCE(lp.label_fee, pp.label_fee) AS label_fee,
  COALESCE(lp.special_fee, pp.special_fee) AS special_fee
FROM srs_products p
CROSS JOIN users u
INNER JOIN srs_product_prices pp ON p.id = pp.product_id
LEFT JOIN srs_loyalty_tiers lt ON u.loyalty_tier_id = lt.id
LEFT JOIN srs_loyalty_prices lp ON p.id = lp.product_id
  AND lp.tier_id = u.loyalty_tier_id
  AND lp.size = pp.size
  AND lp.is_active = 1
WHERE p.is_active = 1
  AND pp.is_active = 1
  AND u.status = 'active';

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

DELIMITER $$

-- Procedure: Lấy giá sản phẩm cho user cụ thể
CREATE PROCEDURE `sp_get_product_price_for_user`(
  IN p_product_id BIGINT,
  IN p_user_id BIGINT,
  IN p_size VARCHAR(10)
)
BEGIN
  SELECT
    COALESCE(lp.price, pp.price) AS price,
    COALESCE(lp.extra_price, pp.extra_price) AS extra_price,
    COALESCE(lp.rush_fee, pp.rush_fee) AS rush_fee,
    COALESCE(lp.shipping_fee, pp.shipping_fee) AS shipping_fee,
    COALESCE(lp.extra_shipping_fee, pp.extra_shipping_fee) AS extra_shipping_fee,
    COALESCE(lp.priority_fee, pp.priority_fee) AS priority_fee,
    COALESCE(lp.extra_priority_fee, pp.extra_priority_fee) AS extra_priority_fee,
    COALESCE(lp.label_fee, pp.label_fee) AS label_fee,
    COALESCE(lp.special_fee, pp.special_fee) AS special_fee,
    lt.tier_name,
    lt.discount_percent
  FROM srs_product_prices pp
  LEFT JOIN users u ON u.id = p_user_id
  LEFT JOIN srs_loyalty_tiers lt ON u.loyalty_tier_id = lt.id
  LEFT JOIN srs_loyalty_prices lp ON pp.product_id = lp.product_id
    AND lp.tier_id = u.loyalty_tier_id
    AND lp.size = pp.size
    AND lp.is_active = 1
  WHERE pp.product_id = p_product_id
    AND pp.size = p_size
    AND pp.is_active = 1;
END$$

DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================

-- Composite indexes for common queries
CREATE INDEX idx_product_active_brand ON srs_products(is_active, brand);
CREATE INDEX idx_product_active_category ON srs_products(is_active, category);
CREATE INDEX idx_loyalty_tier_product ON srs_loyalty_prices(tier_id, product_id);
CREATE INDEX idx_user_loyalty ON users(loyalty_tier_id);

-- =====================================================
-- END OF SCHEMA
-- =====================================================
