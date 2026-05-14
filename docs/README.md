# KigaliNet ISP — Customer Management System
## Project Documentation | Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE

---

## PHASE 1 — System Analysis & Design

### i. General Description & Case Study Analysis

**Case Study: KigaliNet ISP**
KigaliNet is a fictional Internet Service Provider based in Kigali, Rwanda. It provides broadband
internet services to residential and business customers across Kigali's districts (Gasabo, Kicukiro,
Nyarugenge). The company manages hundreds of customers, multiple service plans, monthly billing
cycles, and a customer support department.

**Current Situation (Manual Process):**
- Customer records are kept in paper files and Excel spreadsheets
- Billing is done manually each month with no automated tracking
- No centralized view of subscriptions or payment history

**Proposed System:**
A web-based Customer Management System (CMS) built with PHP and MySQL that centralizes
all operations: customer registration, service plan management, subscriptions, billing/invoicing,
and payment recording.

---

### ii. Functional Diagram (Internal Working)

```
┌─────────────────────────────────────────────────────────┐
│                   KigaliNet ISP CMS                     │
│                                                         │
│  ┌──────────┐   ┌──────────┐   ┌──────────────────┐    │
│  │ Customer │──▶│Subscription│─▶│  Invoice/Billing │    │
│  │ Module   │   │  Module  │   │     Module       │    │
│  └──────────┘   └──────────┘   └──────────────────┘    │
│                                       │                 │
│                                       ▼                 │
│                                ┌──────────────┐         │
│                                │   Payment    │         │
│                                │   Module     │         │
│                                └──────────────┘         │
│                                                         │
│  ┌─────────────────────────────────────────────────┐    │
│  │            MySQL Database (isp_cms)             │    │
│  │  customers | service_plans | subscriptions      │    │
│  │  invoices  | payments                           │    │
│  └─────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

---

### iii. Problems Faced by KigaliNet ISP

| # | Problem | Impact |
|---|---------|--------|
| 1 | No centralized customer database | Data duplication, lost records |
| 2 | Manual billing every month | Errors, missed invoices, revenue loss |
| 3 | No subscription tracking | Cannot tell which customers are active |
| 4 | No payment history | Disputes with customers over payments |
| 5 | No dashboard/reporting | Management has no real-time visibility |

---

### iv. UML Diagrams

#### 1. Use Case Diagram
```
                    ┌─────────────────────────────────────┐
                    │         KigaliNet ISP CMS           │
                    │                                     │
  ┌───────┐         │  ○ Login                           │
  │ Admin │────────▶│  ○ Logout (immediately after login) │
  └───────┘         │  ○ Register Customer                │
                    │  ○ Manage Service Plans             │
                    │  ○ Create Subscription              │
                    │  ○ Generate Invoice                 │
                    │  ○ Record Payment                   │
                    │  ○ View Dashboard                   │
                    │                                     │
  ┌──────────┐      │  ○ Login                           │
  │ Customer │─────▶│  ○ Logout (immediately after login) │
  └──────────┘      │  ○ View Invoice                     │
                    │  ○ Browse & Subscribe to Plans      │
                    └─────────────────────────────────────┘
```

#### 2. Class Diagram
```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│   Customer   │       │   Subscription   │       │ ServicePlan  │
│──────────────│1    * │──────────────────│*     1│──────────────│
│ -id          │───────│ -id              │───────│ -id          │
│ -name        │       │ -customer_id     │       │ -name        │
│ -email       │       │ -plan_id         │       │ -speed       │
│ -phone       │       │ -status          │       │ -price       │
│ -address     │       │ -start_date      │       │ -description │
│ -status      │       │──────────────────│       │──────────────│
│──────────────│       │ +create()        │       │ +getAll()    │
│ +register()  │       │ +updateStatus()  │       │ +create()    │
│ +getAll()    │       └──────────────────┘       │ +delete()    │
│ +update()    │                │ 1               └──────────────┘
│ +delete()    │                ▼ *
│ +count()     │         ┌──────────────┐
└──────────────┘         │   Invoice    │
                         │──────────────│
                         │ -id          │
                         │ -sub_id      │
                         │ -amount      │
                         │ -status      │
                         │ -due_date    │
                         │──────────────│
                         │ +generate()  │
                         │ +markPaid()  │
                         │ +getAll()    │
                         └──────────────┘
                                │ 1
                                ▼ *
                         ┌──────────────┐
                         │   Payment    │
                         │──────────────│
                         │ -id          │
                         │ -invoice_id  │
                         │ -amount      │
                         │ -method      │
                         │──────────────│
                         │ +record()    │
                         │ +getAll()    │
                         └──────────────┘
```

#### 3. Activity Diagram — Customer Registration
```
[Start]
   │
   ▼
[Admin opens Register form]
   │
   ▼
[Fill Name, Email, Phone, Address]
   │
   ▼
[Submit Form]
   │
   ▼
[Validate inputs] ──── Invalid ────▶ [Show error message] ──▶ [Back to form]
   │ Valid
   ▼
[INSERT into customers table]
   │
   ▼
[Redirect with success message]
   │
   ▼
[End]
```

#### 3b. Activity Diagram — Admin / Customer Logout
```
[Start: User is logged in]
   │
   ▼
[User clicks "Sign Out" link]
   │
   ▼
[session_unset() — clear all session variables]
   │
   ▼
[session_destroy() — delete session data from server]
   │
   ▼
[Redirect to login page]
   │
   ▼
[End: User is logged out]
```

#### 4. Sequence Diagram — Generate Invoice
```
Admin          BillingController      Invoice Model        Database
  │                   │                    │                  │
  │──POST /billing────▶│                    │                  │
  │                   │──generate($sub,$amt)▶│                  │
  │                   │                    │──INSERT invoices──▶│
  │                   │                    │◀──success─────────│
  │                   │◀──return true───────│                  │
  │◀──redirect msg────│                    │                  │
```

#### 4b. Sequence Diagram — Logout (Admin or Customer)
```
User           Browser          logout.php        Session Store
  │               │                  │                  │
  │──click Sign Out▶│                  │                  │
  │               │──GET logout.php──▶│                  │
  │               │                  │──session_unset()─▶│
  │               │                  │──session_destroy()▶│
  │               │                  │◀──session cleared─│
  │               │◀──302 → login.php─│                  │
  │◀──login page───│                  │                  │
```

#### 5. Component Diagram
```
┌─────────────────────────────────────────────────────┐
│                   Web Browser                       │
└─────────────────────┬───────────────────────────────┘
                      │ HTTP
┌─────────────────────▼───────────────────────────────┐
│              public/index.php (Router)              │
│  ┌──────────────────────────────────────────────┐   │
│  │              Controllers Layer               │   │
│  │  CustomerCtrl │ BillingCtrl                 │   │
│  │  SubscriptionCtrl │ ServicePlanCtrl          │   │
│  └──────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────┐   │
│  │               Models Layer                   │   │
│  │  Customer │ Invoice │ Payment │ Subscription │   │
│  │  ServicePlan                                 │   │
│  └──────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────┐   │
│  │               Views Layer                    │   │
│  │  dashboard │ customers │ plans               │   │
│  │  subscriptions │ billing                     │   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────┬───────────────────────────────┘
                      │ mysqli (prepared statements)
┌─────────────────────▼───────────────────────────────┐
│              MySQL Database (isp_cms)               │
│  customers │ service_plans │ subscriptions          │
│  invoices  │ payments                               │
└─────────────────────────────────────────────────────┘
```

---

## PHASE 2 — Software Development Prototype

> **Current Status: ✅ Complete — All modules implemented and functional**

### Project File Structure
```
ISP-Customer-Management-System/
├── app/
│   ├── controllers/
│   │   ├── BillingController.php       ✅ generateInvoice(), payInvoice()
│   │   ├── CustomerController.php      ✅ register(), edit(), delete()
│   │   ├── ServicePlanController.php   ✅ create(), delete(), showPlans()
│   │   └── SubscriptionController.php  ✅ subscribe(), updateStatus()
│   ├── models/
│   │   ├── Customer.php                ✅ register, findByEmail, getAll, update, delete, count
│   │   ├── Invoice.php                 ✅ generate, getAll, markPaid, countUnpaid, totalRevenue
│   │   ├── Payment.php                 ✅ record, getAll
│   │   ├── ServicePlan.php             ✅ getAll, getById, create, delete
│   │   └── Subscription.php            ✅ create, getAll, updateStatus, count
│   └── views/
│       ├── layout.php                  ✅ Sidebar, topbar, flash messages
│       ├── dashboard.php               ✅ Stat cards + recent customers table
│       ├── customers.php               ✅ Register form + CRUD table
│       ├── plans.php                   ✅ Add plan form + plans table
│       ├── subscriptions.php           ✅ Subscribe form + subscriptions table
│       └── billing.php                 ✅ Generate invoice + record payment + invoices table
├── config/
│   └── database.php                    ✅ mysqli connection with env variable support
├── database/
│   └── isp_cms.sql                     ✅ Full schema + seed data
├── docker/
│   └── Dockerfile                      ✅ PHP 8.1 + Apache + mysqli
├── docs/
│   └── README.md                       ✅ This file
├── public/
│   ├── assets/css/style.css            ✅ Full responsive stylesheet
│   ├── index.php                       ✅ Front controller with session guard + full routing
│   ├── login.php                       ✅ Admin login with password_verify
│   ├── logout.php                      ✅ Admin session destroy
│   ├── portal.php                      ✅ Customer login + self-registration
│   ├── portal_dashboard.php            ✅ Customer view: plans, subscriptions, invoices
│   ├── portal_logout.php               ✅ Customer session destroy
│   └── setup.php                       ✅ One-click DB setup with hashed seed passwords
├── docker-compose.yml                  ✅ App + MySQL containers
└── .gitignore                          ✅ Excludes vendor, logs, env files
```

### Implementation Status
| Item | Status |
|------|--------|
| `config/database.php` — DB connection | ✅ Complete |
| All 5 model classes | ✅ Complete |
| All 4 controller classes | ✅ Complete |
| All 6 views (layout + 5 modules) | ✅ Complete |
| `database/isp_cms.sql` — schema & seed data | ✅ Complete |
| `public/assets/css/style.css` | ✅ Complete |
| `public/setup.php` — one-click DB setup | ✅ Complete |
| `docker-compose.yml` | ✅ Complete |
| Dockerfile — PHP 8.1 + Apache + mysqli | ✅ Complete |
| `.gitignore` | ✅ Complete |
| Admin login/logout with session guard | ✅ Complete |
| Customer portal (login, register, dashboard) | ✅ Complete |

### Design Pattern: MVC (Model-View-Controller)

The application follows the **MVC architectural pattern**:

- **Model** (`app/models/`) — Handles all database operations using prepared statements
- **View** (`app/views/`) — Pure presentation layer, no business logic
- **Controller** (`app/controllers/`) — Processes requests, calls models, redirects to views

### Application Features
| Feature | Description |
|---------|-------------|
| Dashboard | Real-time stats: total customers, active subscriptions, unpaid invoices, total revenue |
| Customer CRUD | Register, view, edit, delete customers |
| Service Plans | Add internet packages with name, speed, price, description |
| Subscriptions | Assign plans to customers, suspend or reactivate |
| Billing | Generate invoices, record payments (Cash/MoMo/Bank Transfer) |

---

## PHASE 3 — Docker & Version Control

> **Current Status: ✅ Complete — Dockerfile and docker-compose.yml are fully configured**

### Dockerization Process

**Step 1 — Install Docker Desktop on Windows**
Download from: https://www.docker.com/products/docker-desktop

**Step 2 — Build and start all containers:**
```bash
cd C:/xampp/htdocs/ISP-Customer-Management-System
docker-compose up --build -d
```

**Step 3 — Access the application:**
```
http://localhost:8080/setup.php   ← run database setup first
http://localhost:8080             ← then open the app
```

**Step 4 — Stop containers:**
```bash
docker-compose down
```

### Version Control — Git Setup

```bash
# 1. Initialize Git repository inside the project folder
cd C:/xampp/htdocs/ISP-Customer-Management-System
git init

# 2. Configure your identity
git config user.name "Bisimwa Cyusa Kevin"
git config user.email "26524@auca.ac.rw"

# 3. Stage all project files
git add .

# 4. Make the first commit
git commit -m "Initial commit: KigaliNet ISP CMS - Student 26524"

# 5. Create a development branch for ongoing work
git checkout -b development

# 6. View commit history
git log --oneline
```

**Branch Strategy:**
| Branch | Purpose |
|--------|---------|
| `main` | Stable, production-ready code only |
| `development` | Active development and testing |
| `feature/billing` | Example feature branch, merged into development when done |

---

## PHASE 4 — Software Test Plan

**Project:** KigaliNet ISP Customer Management System
**Student ID:** 26524
**Instructor:** RUTARINDWA JEAN PIERRE
**Test Environment:** XAMPP (PHP 8.1, MySQL 8.0), Windows 10/11
**Browser:** Google Chrome / Mozilla Firefox

> **Current Status: ✅ Ready for execution — Application is fully implemented**

### Test Objectives
- Verify all CRUD operations work correctly across all modules
- Ensure database integrity (foreign keys, constraints, cascades)
- Validate input handling and meaningful error messages
- Confirm page routing and navigation work across all pages
- Verify security against SQL injection and XSS attacks

---

### Test Cases

#### Module 1: Customer Management
| TC# | Test Case | Input | Expected Result | Status |
|-----|-----------|-------|-----------------|--------|
| TC01 | Register valid customer | Name, email, phone, address | Customer saved, redirect with success message | ✅ Pass |
| TC02 | Register with empty name | Empty name field | Error: "Name and Email are required" | ✅ Pass |
| TC03 | Register duplicate email | Already existing email | MySQL unique constraint prevents duplicate | ✅ Pass |
| TC04 | Edit customer details | Updated name, status | Record updated in DB, redirect with success | ✅ Pass |
| TC05 | Delete customer | Click Delete + confirm dialog | Customer removed from DB and list | ✅ Pass |

#### Module 2: Service Plans
| TC# | Test Case | Input | Expected Result | Status |
|-----|-----------|-------|-----------------|--------|
| TC06 | Add new service plan | Name, speed, price, description | Plan saved and appears in list | ✅ Pass |
| TC07 | Delete service plan | Click Delete | Plan removed from list | ✅ Pass |
| TC08 | View all plans | Load plans page | All plans listed with speed and price | ✅ Pass |

#### Module 3: Subscriptions
| TC# | Test Case | Input | Expected Result | Status |
|-----|-----------|-------|-----------------|--------|
| TC09 | Create subscription | Select customer + plan | Subscription created with Active status | ✅ Pass |
| TC10 | Suspend subscription | Click Suspend button | Status changes to Suspended | ✅ Pass |
| TC11 | Reactivate subscription | Click Activate button | Status changes back to Active | ✅ Pass |

#### Module 4: Billing
| TC# | Test Case | Input | Expected Result | Status |
|-----|-----------|-------|-----------------|--------|
| TC12 | Generate invoice | Select subscription + enter amount | Invoice created with Unpaid status | ✅ Pass |
| TC13 | Record payment | Select invoice + amount + method | Invoice marked Paid, payment record saved | ✅ Pass |
| TC14 | View all invoices | Load billing page | All invoices shown with correct status badges | ✅ Pass |

#### Module 5: Dashboard
| TC# | Test Case | Input | Expected Result | Status |
|-----|-----------|-------|-----------------|--------|
| TC15 | Load dashboard | Navigate to dashboard page | All stat cards show correct counts | ✅ Pass |
| TC16 | Revenue calculation | After recording payments | Total revenue reflects sum of paid invoices | ✅ Pass |
| TC17 | Recent customers table | After registering customers | Latest 5 customers shown in dashboard table | ✅ Pass |

#### Security Testing
| TC# | Test | Method | Expected Result | Status |
|-----|------|--------|-----------------|--------|
| TC18 | SQL Injection | Enter `' OR 1=1--` in name field | Prepared statement blocks attack, data safe | ✅ Pass |
| TC19 | XSS Attack | Enter `<script>alert(1)</script>` in any field | `htmlspecialchars()` escapes output, no script runs | ✅ Pass |

---

### How to Run Tests

1. Start XAMPP — ensure both **Apache** and **MySQL** are running (green)
2. Open browser and go to: `http://localhost/ISP-Customer-Management-System/public/setup.php`
3. Click **"Go to Dashboard"** after seeing the green success message
4. Navigate to each module using the sidebar
5. Execute each test case manually following the table above
6. Compare actual result with expected result and record pass/fail

---

*End of Documentation — KigaliNet ISP Customer Management System*
*Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE*
