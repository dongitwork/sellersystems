-- Check if tables exist and add sample data

-- Check tables
SHOW TABLES LIKE 'srs_%';

-- Sample products
INSERT INTO `srs_products` (`product_name`, `brand`, `category`, `style`, `description`, `is_active`) VALUES
('Gildan Heavy Cotton T-Shirt', 'Gildan', 'T-Shirts', 'G5000', 'Classic heavy cotton t-shirt with seamless double-needle collar', 1),
('Bella Canvas Unisex Jersey', 'Bella+Canvas', 'T-Shirts', 'BC3001', 'Soft airlume combed and ring-spun cotton', 1),
('Hanes ComfortSoft Hoodie', 'Hanes', 'Hoodies', 'P170', 'EcoSmart fleece hoodie with front pocket', 1),
('Port & Company Tote Bag', 'Port & Company', 'Bags', 'B100', 'Essential tote bag 100% cotton canvas', 1),
('Champion Performance T-Shirt', 'Champion', 'T-Shirts', 'CW22', 'Moisture-wicking performance tee', 1);

-- Sample colors for first product
INSERT INTO `srs_product_colors` (`product_id`, `color_name`, `color_code`, `is_active`) VALUES
(1, 'White', '#FFFFFF', 1),
(1, 'Black', '#000000', 1),
(1, 'Navy', '#000080', 1),
(1, 'Red', '#FF0000', 1);

-- Sample base prices for first product
INSERT INTO `srs_product_prices` (`product_id`, `size`, `price`, `extra_price`, `rush_fee`, `shipping_fee`, `is_active`) VALUES
(1, 'S', 5.99, 0.00, 1.49, 2.50, 1),
(1, 'M', 5.99, 0.00, 1.49, 2.50, 1),
(1, 'L', 5.99, 0.00, 1.49, 2.50, 1),
(1, 'XL', 6.99, 0.50, 1.49, 2.50, 1),
(1, '2XL', 7.99, 1.00, 1.49, 2.50, 1);

SELECT 'Sample data inserted successfully' as status;
