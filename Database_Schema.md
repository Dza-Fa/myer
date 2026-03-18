# Database Schema - Myer

## 1. Prinsip Desain
- **Normalisasi**: Hindari duplikasi data
- **Relasi jelas**: Foreign key
- **Scalable**: Mudah ditambah fitur
- **Aman untuk multi-user**
- **Mendukung fitur future**: Budget, report, recurring

## 2. Daftar Tabel Utama
| Tabel | Deskripsi |
|-------|-----------|
| `users` | Pengguna |
| `accounts` | Akun keuangan |
| `categories` | Kategori transaksi |
| `transactions` | Transaksi (CORE) |
| `budgets` | Budget bulanan |
| `budget_items` | Item budget per kategori |
| `tags` | Tag (opsional) |
| `transaction_tags` | Relasi tag-transaksi (opsional) |
| `recurring_transactions` | Transaksi berulang (advanced) |

## 3. Detail Schema Inti

### users
```sql
id (PK)
name
email (unique)
password
created_at
updated_at
```

### accounts
```sql
id (PK)
user_id (FK → users.id)
name
type (cash, bank, e-wallet, dll)
balance (cached, optional)
created_at
updated_at
```

### categories
```sql
id (PK)
user_id (FK → users.id)
name
type (income / expense)
parent_id (nullable, FK → categories.id)
created_at
updated_at
```

### transactions (CORE TABLE)
```sql
id (PK)
user_id (FK)
account_id (FK → accounts.id)
category_id (FK → categories.id)
type (income / expense)
amount (decimal)
date (date)
notes (nullable)
created_at
updated_at
```

### budgets
```sql
id (PK)
user_id (FK)
month (integer)
year (integer)
created_at
updated_at
```

### budget_items
```sql
id (PK)
budget_id (FK → budgets.id)
category_id (FK → categories.id)
amount (limit budget)
created_at
updated_at
```

## 4. Schema Opsional (Direkomendasikan)
### tags
```sql
id (PK)
user_id (FK)
name
```

### transaction_tags
```sql
transaction_id (FK)
tag_id (FK)
```

### recurring_transactions
```sql
id (PK)
user_id (FK)
account_id (FK)
category_id (FK)
type
amount
interval (monthly, weekly, dll)
next_run_date
created_at
```

## 5. Relasi Antar Tabel (ERD Simplified)
```
users
├── accounts
├── categories
├── transactions
├── budgets
└── tags

budgets
└── budget_items

transactions
├── accounts
├── categories
└── transaction_tags → tags
```

## 6. Keputusan Penting
- **type di transactions**: Lebih fleksibel daripada tabel terpisah
- **budget_items**: Budget per kategori
- **user_id di semua tabel**: Isolasi data multi-user
- **decimal untuk amount**: Hindari error pembulatan

## 7. Indexing (Wajib untuk Performa)
```
transactions: user_id, date, category_id, account_id
budgets: user_id
```

## 8. Edge Cases
- Saldo negatif: Diperbolehkan
- Delete: Gunakan soft delete (opsional)
- Data besar: Pagination + indexing

## 9. Siap untuk Fitur Masa Depan
- Report kompleks
- Insight
- Multi-account
- Budgeting lanjutan
- Recurring system

