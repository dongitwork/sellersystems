# Products Module Setup Guide

## Bước 1: Import Database Schema

Chạy lệnh sau để tạo tables:

```bash
mysql -u root -p your_database_name < database/products_schema.sql
```

**Hoặc** nếu dùng phpMyAdmin:
1. Mở phpMyAdmin
2. Chọn database của bạn
3. Chọn tab "SQL"
4. Copy toàn bộ nội dung file `database/products_schema.sql`
5. Paste và Execute

## Bước 2: Import Sample Data (Optional)

Để test module với dữ liệu mẫu:

```bash
mysql -u root -p your_database_name < database/sample_products.sql
```

Sample data bao gồm:
- 5 sản phẩm mẫu (Gildan, Bella+Canvas, Hanes, etc.)
- 4 màu cho sản phẩm đầu tiên
- 5 sizes với giá cho sản phẩm đầu tiên

## Bước 3: Kiểm tra Database

Sau khi import, kiểm tra các tables đã tạo:

```sql
SHOW TABLES LIKE 'srs_%';
```

Bạn sẽ thấy:
- `srs_loyalty_tiers` (5 loyalty tiers)
- `srs_products`
- `srs_product_colors`
- `srs_product_prices`
- `srs_loyalty_prices`

## Bước 4: Truy cập Module

Mở trình duyệt và truy cập:
```
http://your-domain/products
```

Bạn sẽ thấy:
- Danh sách sản phẩm dạng card grid
- Search và filter
- Buttons để thêm/sửa/xóa sản phẩm

## Troubleshooting

### Vấn đề: Trang trắng hoặc không hiển thị gì

**Kiểm tra:**

1. **Database connection:**
   ```php
   // Check trong app/Config/Database.php
   $_ENV['DB_DATABASE'] ?? 'user_management'
   ```

2. **Tables đã tạo chưa:**
   ```sql
   SELECT COUNT(*) FROM srs_products;
   ```

3. **PHP errors:**
   - Bật error display trong PHP
   - Kiểm tra error logs

### Vấn đề: "Product not found" hoặc empty table

**Giải pháp:**
- Import sample data: `mysql -u root -p your_db < database/sample_products.sql`
- Hoặc thêm product thủ công qua UI: Click "Add New Product"

### Vấn đề: 500 Error

**Kiểm tra:**
1. All models có được autoload đúng không
2. Database credentials đúng chưa
3. Check error logs: `tail -f /var/log/apache2/error.log`

## Database Schema Overview

```
srs_loyalty_tiers (5 tiers pre-populated)
    ├── Standard (0% discount)
    ├── Silver (5% discount)
    ├── Gold (10% discount)
    ├── Platinum (15% discount)
    └── Diamond (20% discount)

srs_products
    ├── id, product_name, brand, category, style
    ├── description, image_url, is_active
    └── Relationships:
        ├── hasMany: srs_product_colors
        ├── hasMany: srs_product_prices
        └── hasMany: srs_loyalty_prices

srs_product_colors
    └── product_id, color_name, color_code, color_image

srs_product_prices (base prices)
    └── product_id, size, price, extra_price, rush_fee,
        shipping_fee, priority_fee, label_fee, special_fee

srs_loyalty_prices (tier-specific prices)
    └── product_id, tier_id, size, [all price fields]
        Note: NULL values fallback to base price
```

## Quick Start Commands

```bash
# 1. Import schema
mysql -u root -p user_management < database/products_schema.sql

# 2. Import sample data
mysql -u root -p user_management < database/sample_products.sql

# 3. Verify tables
mysql -u root -p user_management -e "SHOW TABLES LIKE 'srs_%';"

# 4. Check sample products
mysql -u root -p user_management -e "SELECT id, product_name, brand, style FROM srs_products;"

# 5. Open browser
# Visit: http://localhost/products
```

## Features Available

✅ Product CRUD with popup modals
✅ Search and filter (brand, category, name)
✅ Pagination
✅ Product colors management
✅ Multi-tier pricing system
✅ Quick edit for prices (click cell to edit inline)
✅ Responsive design with Bootstrap 5

## Next Steps

1. ✅ Setup database (you are here)
2. Test product CRUD operations
3. Add product colors
4. Configure prices for different sizes
5. Set loyalty tier prices (optional)
6. Assign loyalty tiers to users

## Support

If you encounter issues:
1. Check this troubleshooting guide
2. Review `modules/Products/README.md` for detailed docs
3. Check PHP error logs
4. Verify database connection settings
