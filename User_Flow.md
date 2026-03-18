# User Flow - Myer

## 1. Global User Flow
```
Start → Register/Login → Dashboard → (Kelola Data)
                              ├── Account
                              ├── Category  
                              ├── Transaction
                              ├── Budget
                         → Report → Logout
```

## 2. Flow Utama

### A. Authentication
```
Landing → Register/Login → Validasi → Dashboard
```

### B. Setup Awal (First-Time)
```
Login pertama → Prompt buat akun & kategori → Dashboard
```

### C. Account Management
```
Dashboard → Accounts → List → Add/Edit/Delete
```

### D. Transaction (Paling Penting)
```
Dashboard/Transactions → Add → Type(Income/Expense) → Amount/Account/Category/Date/Notes → Save
```
**Target:** < 5 detik

### E. Budget
```
Budget Page → Create → Kategori + Limit → Track progress
```

### F. Dashboard
```
Login → Total balance + Income/Expense + Recent + Budget status
```

### G. Report
```
Report → Pilih periode → Generate → Income/Expense + Breakdown → Export
```

### H. Search/Filter
```
Transactions → Filter (Date/Category/Account) → Hasil
```

## 3. Navigation Structure
```
Dashboard
Accounts
Transactions
Categories
Budgets
Reports
Settings
```

## 4. State Handling
- **Empty:** \"Belum ada data\" + tombol add
- **Error:** Pesan jelas
- **Loading:** Indicator

## 5. Critical UX Rules
- Add transaction selalu mudah diakses (floating button)
- Dashboard informatif tanpa klik
- Navigasi ≤ 3 level

## 6. Output Teknis
### Routes (Laravel)
```
/dashboard
/accounts
/transactions
/budgets
/reports
```

### Controllers
- AccountController
- TransactionController
- BudgetController

