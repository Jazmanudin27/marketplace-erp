# API Documentation Template

## Marketplace ERP - REST API V1

Base URL: `http://localhost/api/v1`

### Authentication

Semua endpoint (kecuali `/auth/login` dan `/auth/register`) memerlukan authentication token Sanctum.

**Header Authentication**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## Authentication Endpoints

### Register User
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "company_name": "My Company"
}
```

**Response** (201):
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "token_string_here"
}
```

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response** (200):
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "token_string_here"
}
```

---

## Users Endpoints

### List Users
```http
GET /api/v1/users
Authorization: Bearer {token}
```

**Query Parameters**:
- `page` (int) - Page number for pagination
- `per_page` (int) - Items per page (default: 15)
- `search` (string) - Search by name or email
- `sort_by` (string) - Sort field (default: created_at)
- `sort_order` (string) - asc or desc

**Response** (200):
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "company_id": 1,
      "created_at": "2026-05-25T10:00:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/users?page=1",
    "last": "http://localhost/api/v1/users?page=5",
    "prev": null,
    "next": "http://localhost/api/v1/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

### Create User
```http
POST /api/v1/users
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "company_id": 1
}
```

**Response** (201):
```json
{
  "id": 2,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "company_id": 1,
  "created_at": "2026-05-25T10:30:00Z"
}
```

### Get User
```http
GET /api/v1/users/{id}
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "company_id": 1,
  "created_at": "2026-05-25T10:00:00Z",
  "updated_at": "2026-05-25T10:00:00Z"
}
```

### Update User
```http
PUT /api/v1/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Smith",
  "email": "john.smith@example.com"
}
```

**Response** (200):
```json
{
  "id": 1,
  "name": "John Smith",
  "email": "john.smith@example.com",
  "company_id": 1,
  "updated_at": "2026-05-25T11:00:00Z"
}
```

### Delete User
```http
DELETE /api/v1/users/{id}
Authorization: Bearer {token}
```

**Response** (204): No Content

---

## Error Responses

### 400 Bad Request
```json
{
  "error": "Validation Error",
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Authentication failed"
}
```

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You do not have permission to perform this action"
}
```

### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "The requested resource was not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Server Error",
  "message": "An internal server error occurred"
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 204 | No Content - Request successful (usually DELETE) |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error |

---

## Pagination

Semua list endpoints menggunakan pagination. Format response:

```json
{
  "data": [...],
  "links": {...},
  "meta": {...}
}
```

Gunakan query parameter `page` untuk navigasi:
```
GET /api/v1/users?page=2&per_page=10
```

---

## Rate Limiting

API memiliki rate limiting. Limits:
- 60 requests per minute untuk authenticated requests
- 10 requests per minute untuk unauthenticated requests

Check headers untuk melihat limit info:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1672531200
```

---

## Webhooks

### Marketplace Order Webhook
```http
POST /api/v1/webhooks/marketplace/{account}
Content-Type: application/json
X-Webhook-Signature: signature_here

{
  "event": "order.created",
  "data": {
    "order_id": "MP12345",
    "marketplace": "shopee",
    "items": [...],
    "total": 100000
  }
}
```

---

**Last Updated**: 2026-05-25
**API Version**: 1.0.0
