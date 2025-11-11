# Products Module - Documentation

## Overview
Module quản lý sản phẩm với hệ thống giá đa cấp độ (loyalty pricing system). Module này hỗ trợ:
- CRUD operations cho sản phẩm
- Quản lý màu sắc sản phẩm
- Quản lý giá theo size và loyalty tier
- Quick edit giá trực tiếp trên table
- Tìm kiếm và lọc sản phẩm

## Database Schema

### Tables
1. **srs_loyalty_tiers** - Cấp độ khách hàng (Standard, Silver, Gold, Platinum, Diamond)
2. **srs_products** - Thông tin sản phẩm
3. **srs_product_colors** - Màu sắc sản phẩm
4. **srs_product_prices** - Giá base cho tất cả users
5. **srs_loyalty_prices** - Giá đặc biệt theo loyalty tier

### Import Database
```bash
# Import schema vào database
mysql -u root -p your_database < database/products_schema.sql
```

### Thêm loyalty_tier_id vào bảng users
Schema đã bao gồm migration để thêm cột `loyalty_tier_id` vào bảng `users`.

## Features

### 1. Product Management (`/products`)
- Danh sách sản phẩm dạng card grid
- Tìm kiếm theo tên, brand, category, style
- Filter theo brand và category
- Thêm/sửa/xóa sản phẩm qua popup modal
- Pagination

**Actions:**
- **View** (👁️) - Xem chi tiết sản phẩm
- **Manage Prices** (💲) - Quản lý giá
- **Edit** (✏️) - Sửa thông tin
- **Delete** (🗑️) - Xóa sản phẩm (soft delete)

### 2. Product Details (`/products/{id}`)
- Hiển thị đầy đủ thông tin sản phẩm
- Quản lý màu sắc (add/edit/delete colors)
- Bảng tóm tắt giá theo size
- Color picker cho chọn màu

### 3. Price Management (`/products/{id}/prices`)
- **Base Prices Table** - Giá chuẩn cho tất cả users
- **Loyalty Tier Tables** - Giá đặc biệt theo từng tier
- **Quick Edit** - Click vào cell để edit inline
- Press **Enter** để save, **Escape** để cancel
- NULL values trong loyalty prices sẽ fallback về base price

**Price Fields:**
- Price - Giá cơ bản
- Extra Price - Giá phụ
- Rush Fee - Phí gấp
- Shipping Fee - Phí vận chuyển
- Extra Shipping Fee - Phí vận chuyển thêm
- Priority Fee - Phí ưu tiên
- Extra Priority Fee - Phí ưu tiên thêm
- Label Fee - Phí nhãn
- Special Fee - Phí đặc biệt

## API Endpoints

### Products
```
GET    /products                    - List products
GET    /products/{id}               - Show product details
POST   /products                    - Create product
GET    /products/{id}/edit          - Get product for editing (AJAX)
POST   /products/{id}/update        - Update product
POST   /products/{id}/delete        - Delete product
```

### Prices
```
GET    /products/{id}/prices                 - Price management page
POST   /products/{id}/prices                 - Create base price
POST   /products/prices/{price_id}/update    - Update base price
POST   /products/prices/{price_id}/delete    - Delete base price
POST   /products/prices/quick-edit           - Quick edit price field (AJAX)
POST   /products/{id}/loyalty-prices         - Create/update loyalty price
```

### Colors
```
GET    /products/{id}/colors                 - Get colors (AJAX)
POST   /products/{id}/colors                 - Create color
POST   /products/colors/{color_id}/update    - Update color
POST   /products/colors/{color_id}/delete    - Delete color
```

## Models

### Product Model
```php
$product->paginate($page, $perPage, $search, $filters)
$product->getWithDetails($id)
$product->getBrands()
$product->getCategories()
$product->styleExists($style, $excludeId)
```

### ProductPrice Model
```php
$price->getByProduct($productId)
$price->getByProductAndSize($productId, $size)
$price->quickUpdate($id, $field, $value)
$price->bulkCreate($productId, $prices)
```

### LoyaltyTier Model
```php
$tier->getActive()
$tier->getBySlug($slug)
```

### LoyaltyPrice Model
```php
$loyaltyPrice->getByProductAndTier($productId, $tierId)
$loyaltyPrice->getByProductGrouped($productId)
$loyaltyPrice->quickUpdate($id, $field, $value)
```

## Usage Examples

### 1. Tạo sản phẩm mới
1. Vào `/products`
2. Click "Add New Product"
3. Điền thông tin: Product Name, Style, Brand, Category
4. Click "Save Product"

### 2. Thêm giá cho sản phẩm
1. Vào `/products/{id}/prices`
2. Click "Add Size & Base Price"
3. Nhập size (S, M, L, XL) và các mức giá
4. Click "Save Price"

### 3. Tạo giá đặc biệt cho loyalty tier
1. Vào `/products/{id}/prices`
2. Scroll xuống phần loyalty tier (Silver, Gold, etc.)
3. Click vào cell muốn edit
4. Nhập giá mới hoặc để trống (NULL) để dùng base price
5. Press Enter để save

### 4. Quick Edit giá
1. Vào `/products/{id}/prices`
2. Click vào bất kỳ cell nào trong bảng giá
3. Input sẽ hiện ra, nhập giá mới
4. Press **Enter** để save hoặc **Escape** để cancel
5. Giá được update real-time qua AJAX

### 5. Quản lý màu sắc
1. Vào `/products/{id}`
2. Click "Add Color"
3. Nhập tên màu, chọn màu từ color picker
4. Click "Save Color"

## Frontend Technologies

- **Bootstrap 5.3.0** - UI Framework
- **jQuery 3.7.0** - AJAX & DOM manipulation
- **Bootstrap Icons 1.11.0** - Icons

## Quick Edit Feature

Quick edit cho phép chỉnh sửa giá trực tiếp trên table mà không cần mở modal:

**How it works:**
1. Click vào cell cần edit
2. Cell chuyển sang edit mode với input field
3. Nhập giá mới
4. Press Enter hoặc click outside để save
5. Press Escape để cancel
6. AJAX request gửi đến `/products/prices/quick-edit`
7. Database update và return giá mới
8. Cell update với giá mới

**Supported on:**
- Base prices table
- All loyalty tier tables
- All price fields (price, extra_price, rush_fee, etc.)

## Loyalty Pricing System

### Cách hoạt động:
1. **Base Price** - Giá chuẩn cho tất cả users (tier Standard)
2. **Loyalty Price** - Giá đặc biệt cho từng tier
3. Nếu loyalty price = NULL, system sẽ dùng base price
4. Nếu loyalty price được set, system sẽ dùng giá đó

### Price Calculation:
```
Effective Price = Loyalty Price ?? Base Price
```

### Loyalty Tiers (Default):
- **Standard** (0% discount) - Khách hàng mới
- **Silver** (5% discount) - 10+ orders, $1000+ spent
- **Gold** (10% discount) - 50+ orders, $5000+ spent
- **Platinum** (15% discount) - 100+ orders, $10000+ spent
- **Diamond** (20% discount) - 200+ orders, $20000+ spent

## Stored Procedures

### Get price for specific user
```sql
CALL sp_get_product_price_for_user(product_id, user_id, size);
```

Returns effective price based on user's loyalty tier.

## Views (Database)

### vw_user_product_prices
View để lấy giá sản phẩm theo user với loyalty tier:
```sql
SELECT * FROM vw_user_product_prices
WHERE user_id = 1 AND product_id = 10;
```

## Security

- ✅ CSRF protection trên tất cả POST requests
- ✅ Authentication required cho tất cả routes
- ✅ Input validation và sanitization
- ✅ SQL injection prevention (Medoo ORM)
- ✅ XSS prevention (htmlspecialchars)

## Performance Optimization

- Indexes trên các cột thường query (brand, category, is_active)
- Composite indexes cho queries phức tạp
- Pagination để giảm load
- AJAX cho quick edit (không reload page)
- Soft delete thay vì hard delete

## Troubleshooting

### Lỗi "Module not found"
```bash
# Kiểm tra module đã được register
cat app/Config/Modules.php
# Phải có 'Products' trong array
```

### Lỗi "Table not found"
```bash
# Import database schema
mysql -u root -p database_name < database/products_schema.sql
```

### Quick edit không hoạt động
1. Kiểm tra jQuery đã load (Console)
2. Kiểm tra route `/products/prices/quick-edit`
3. Kiểm tra AJAX response trong Network tab

### Giá không hiển thị đúng
1. Kiểm tra base price đã tạo chưa
2. Kiểm tra loyalty_tier_id của user
3. Kiểm tra loyalty price có NULL không

## Future Enhancements

- [ ] Bulk import products from CSV
- [ ] Image upload functionality
- [ ] Product variants (size/color combinations)
- [ ] Discount schedules
- [ ] Price history tracking
- [ ] Copy base prices to loyalty tier with auto-discount
- [ ] Export prices to Excel
- [ ] Product categories management
- [ ] Inventory tracking

## Support

For issues or questions, please create an issue in the repository.

---

**Version:** 1.0.0
**Last Updated:** 2025-11-11
**Author:** Claude
**License:** MIT
