# Mini POS System (Laravel + Vue.js - Single Project)

## 1. Overview
A Mini Point of Sale (POS) system built as a **single project using Laravel + Vue.js together**.

- Laravel handles backend (API, database, authentication)
- Vue.js is integrated inside Laravel (SPA using Vite)

This means **one codebase**, not separate frontend/backend projects.

---

## 2. Core Features

### Admin Panel
- Manage users (admin, cashier)
- Role & permission control
- Dashboard overview (sales, orders, revenue)

### User Order (POS)
- Create new orders
- Add/remove items to cart
- Apply discounts and taxes
- Select payment methods (cash, card)

### Stock Management
- Track stock quantity
- Stock in / stock out
- Low stock alerts

### Item Management (CRUD)
- Create / Update / Delete items
- Assign categories

### Receipt Printing
- Generate receipt after order
- Print via browser or thermal printer

### Reports
- Daily / monthly reports
- Sales summary
- Top products

### Filter Date & Time
- Filter orders by date range
- Filter reports by time

---

## 3. Project Structure (Single Project)

```
project-root/
│
├── app/                # Laravel backend logic
├── routes/
│   ├── web.php         # Web routes
│   └── api.php         # API routes
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/             # Vue.js app
│   │   ├── components/
│   │   ├── pages/
│   │   ├── router/
│   │   └── app.js
│   └── views/
│       └── app.blade.php
├── public/
├── vite.config.js
└── package.json
```

---

## 4. Tech Stack

- **Backend:** Laravel 10+
- **Frontend:** Vue 3 (with Vite)
- **Database:** MySQL
- **Auth:** Laravel Sanctum

---

## 5. Database Analysis

### Users Table
| Field | Type | Description |
|------|------|-------------|
| id | bigint | Primary key |
| name | string | User name |
| email | string | Login |
| password | string | Hashed |
| role | enum | admin/cashier |

### Categories Table
| Field | Type |
|------|------|
| id | bigint |
| name | string |

### Items Table
| Field | Type |
|------|------|
| id | bigint |
| category_id | bigint |
| name | string |
| price | decimal |
| stock | integer |
| barcode | string |

### Orders Table
| Field | Type |
|------|------|
| id | bigint |
| user_id | bigint |
| total | decimal |
| discount | decimal |
| payment_method | string |
| created_at | timestamp |

### Order Items Table
| Field | Type |
|------|------|
| id | bigint |
| order_id | bigint |
| item_id | bigint |
| quantity | integer |
| price | decimal |

### Payments Table
| Field | Type |
|------|------|
| id | bigint |
| order_id | bigint |
| amount | decimal |
| change | decimal |

### Stock Movements Table
| Field | Type |
|------|------|
| id | bigint |
| item_id | bigint |
| type | enum (in/out) |
| quantity | integer |

---

## 6. Relationships
- User → Orders (1:N)
- Order → Order Items (1:N)
- Item → Order Items (1:N)
- Category → Items (1:N)
- Order → Payment (1:1)

---

## 7. How It Works (Flow)

1. User logs in
2. Vue loads inside Laravel (SPA)
3. User creates order (POS screen)
4. Vue sends request to Laravel API
5. Laravel saves:
   - orders
   - order_items
   - payments
   - update stock
6. Receipt is generated

---

## 8. Notes
- Use API routes (`/api/*`) for Vue requests
- Use Sanctum for authentication
- Use transactions when saving orders
- Keep frontend inside `resources/js`

---

## 9. Future Improvements
- Barcode scanner support
- PWA (offline POS)
- Real-time dashboard
- Multi-branch system
