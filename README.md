# 🕒 Work Time Tracker

A simple Symfony-based REST API for tracking employee work time, with support for overtime calculation and configurable system settings.

---

## ✅ Features

- Register employees with UUID identifiers
- Log work time entries (start & end time)
- Prevent overlapping or duplicate entries per day
- Daily/monthly work time summary with automatic:
  - 30-minute rounding
  - overtime calculation
  - pay breakdown
- Configurable work norm and pay rates via database
- Full validation and error handling
- Clean architecture with separation of concerns and PSR-12 compliance

---

## 🚀 Installation

### Requirements

- PHP 8.4+
- Composer
- Symfony CLI (optional)
- Docker + Docker Compose (MariaDB is included)

### Setup
> ℹ️ **Important:** Create a `.env.local` file and set the following environment variables:
```
APP_ENV=dev
APP_SECRET=your_custom_secret
DATABASE_URL=mysql://user:password@db:3306/your_database_name
```

```bash
git clone https://github.com/your-name/work-time-tracker.git
cd work-time-tracker

composer install

# Start Docker environment
docker-compose up -d

# Run database migrations
php bin/console doctrine:migrations:migrate

# Seed default configuration
php bin/console doctrine:fixtures:load --env=dev
```

---

## 🧱 Architecture Overview

```
src/
├── Controller/              # API endpoints
├── DTO/                     # Data Transfer Objects for validation
├── Entity/                  # Doctrine entities
├── Repository/              # Custom repository logic
├── Service/                 # Business logic & rules
└── DataFixtures/            # Database seeders for development
```

---

## 🧠 Applied Design Patterns

The project uses the following key design patterns and architectural concepts:

| Pattern | Where | Why |
|--------|-------|-----|
| **DTO (Data Transfer Object)** | `WorkTimeEntryRequest`, `WorkTimeSummaryRequest` | Isolates HTTP request data and enables strong validation |
| **Service Layer** | `WorkTimeService`, `WorkTimeSummaryService` | Keeps controllers slim and encapsulates business rules |
| **Repository Pattern** | Custom repositories for `Employee` and `WorkTimeEntry` | Encapsulates database queries and reusable access logic |
| **Dependency Injection** | Injected services across controllers and business logic | Decouples components and promotes testability |
| **Validation Constraints** | Symfony Validator component + PHP attributes | Centralizes and standardizes input validation |
| **Configuration Entity** | `SystemWorkSettings` | Stores persistent system-wide configuration (e.g., rates, norms) |
| **Separation of Concerns** | Structure of `src/` | Domain logic is cleanly separated into layers (Controller, Service, Entity, DTO, Repository) |

---

## 🗃 Example API Requests

### ✅ Register employee

```http
POST /employee
{
  "firstName": "Karol",
  "lastName": "Szabat"
}
```

### ⏱ Register work time

```http
POST /work-time
{
  "employee_uuid": "uuid",
  "start_time": "2025-04-20 08:00",
  "end_time": "2025-04-20 14:00"
}
```

### 📊 Summary for a day

```http
GET /summary?employee_uuid=uuid&date=2025-04-20
```

### 📊 Summary for a month

```http
GET /summary?employee_uuid=uuid&date=2025-04
```

---

## 🧼 Code Quality

- ✅ PSR-12 formatting (auto-applied via PHPStorm)
- ✅ PHPStan level 6
- ✅ Doctrine best practices
- ✅ Symphony best practices (DependencyInjection, ENV separation, config)

---

## 🧠 Author Notes

This project was implemented as part of a recruitment task. The architecture was intentionally designed to be clean, scalable and easy to test or extend. Core logic is split into maintainable, testable and focused services with minimal controller responsibility.