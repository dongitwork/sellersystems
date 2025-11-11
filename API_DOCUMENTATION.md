# API Documentation

## Authentication

All protected API endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_api_token}
```

## Public Endpoints (No Authentication Required)

### 1. API Login
**POST** `/api/login`

Returns an API token on successful authentication.

**Request Body:**
```json
{
  "username": "admin",  // or "email": "admin@example.com"
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "80_character_api_token_here",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin User",
      "username": "admin",
      "email": "admin@example.com",
      "status": "active",
      "roles": ["admin"]
    }
  }
}
```

**Error Responses:**
- `400` - Missing credentials
- `401` - Invalid credentials
- `403` - Account banned or inactive

---

### 2. API Register
**POST** `/api/register`

Creates a new user account and returns an API token.

**Request Body:**
```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "80_character_api_token_here",
    "token_type": "Bearer",
    "user": {
      "id": 2,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "status": "active",
      "roles": ["customer"]
    }
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "username": "Username already exists",
    "email": "Invalid email format"
  }
}
```

---

## Protected Endpoints (Authentication Required)

### 3. API Logout
**POST** `/api/logout`

Revokes the current API token.

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## User Management Endpoints (Admin Only)

### 4. List Users
**GET** `/api/users`

Get paginated list of all users.

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Query Parameters:**
- `page` (int, default: 1) - Page number
- `per_page` (int, default: 20) - Items per page
- `q` (string) - Search query (name, username, email)
- `status` (string) - Filter by status: active, inactive, banned
- `role` (string) - Filter by role: admin, seller, customer

**Example:**
```
GET /api/users?page=1&per_page=10&status=active&q=john
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "name": "Admin User",
        "username": "admin",
        "email": "admin@example.com",
        "status": "active",
        "created_at": "2024-01-01 00:00:00",
        "updated_at": "2024-01-01 00:00:00",
        "roles": ["admin"]
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10,
      "from": 1,
      "to": 10
    }
  }
}
```

---

### 5. Get Single User
**GET** `/api/users/{id}`

Get details of a specific user.

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "username": "admin",
      "email": "admin@example.com",
      "api_token": "80_character_token",
      "status": "active",
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-01 00:00:00",
      "roles": ["admin"]
    }
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "error": "User not found"
}
```

---

### 6. Create User
**POST** `/api/users`

Create a new user (admin only).

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "name": "Jane Doe",
  "username": "janedoe",
  "email": "jane@example.com",
  "password": "password123",
  "status": "active",
  "roles": ["seller", "customer"]
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "user": {
      "id": 3,
      "name": "Jane Doe",
      "username": "janedoe",
      "email": "jane@example.com",
      "api_token": "80_character_token",
      "status": "active",
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-01 00:00:00",
      "roles": ["seller", "customer"]
    }
  }
}
```

---

### 7. Update User
**PUT** `/api/users/{id}`

Update an existing user (admin only).

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "name": "Jane Smith",
  "username": "janesmith",
  "email": "jane.smith@example.com",
  "password": "newpassword123",  // Optional
  "status": "inactive",
  "roles": ["admin"]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "user": {
      "id": 3,
      "name": "Jane Smith",
      "username": "janesmith",
      "email": "jane.smith@example.com",
      "status": "inactive",
      "roles": ["admin"]
    }
  }
}
```

---

### 8. Delete User
**DELETE** `/api/users/{id}`

Delete a user (admin only). Cannot delete yourself.

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

**Error Responses:**
- `400` - Cannot delete your own account
- `404` - User not found
- `403` - Unauthorized (not admin)

---

## Error Responses

All API endpoints return consistent error responses:

**Unauthorized (401):**
```json
{
  "success": false,
  "error": "No authorization header provided"
}
```

**Forbidden (403):**
```json
{
  "success": false,
  "error": "Unauthorized. Admin access required."
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "field_name": "Error message"
  }
}
```

**Server Error (500):**
```json
{
  "success": false,
  "error": "Internal server error message"
}
```

---

## Testing with cURL

### 1. Register a new user:
```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "username": "testuser",
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 2. Login:
```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }'
```

### 3. List users (with token):
```bash
curl -X GET http://localhost/api/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. Create user (admin):
```bash
curl -X POST http://localhost/api/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New User",
    "username": "newuser",
    "email": "new@example.com",
    "password": "password123",
    "roles": ["customer"]
  }'
```

### 5. Update user:
```bash
curl -X PUT http://localhost/api/users/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "username": "updateduser",
    "email": "updated@example.com",
    "status": "active"
  }'
```

### 6. Delete user:
```bash
curl -X DELETE http://localhost/api/users/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 7. Logout:
```bash
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Available Roles

- `admin` - Full system access, can manage users
- `seller` - Can manage products and orders
- `customer` - Regular user access

## User Status

- `active` - User can login and use the system
- `inactive` - User cannot login
- `banned` - User is permanently banned

---

## Notes

1. All API responses use `application/json` content type
2. Dates are in `Y-m-d H:i:s` format
3. API tokens are 80 characters long
4. Password minimum length is 6 characters
5. Admin role is required for all user management operations
6. Users cannot delete their own accounts via the API
