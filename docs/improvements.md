# Codebase Improvement Suggestions

## Critical

### ~~SQL Injection in `TransactionRepository::findByIds()`~~ ✅ Done
~~**File:** `src/Repository/TransactionRepository.php:196-207`~~

~~String interpolation `'t.id IN('.implode(',', $ids).')'` instead of a parameterized query. The correct parameterized version is commented out directly below it — uncomment and remove the unsafe version.~~

---

## High Priority

### ~~Security: `system()` in BackupDb~~ ✅ Done
~~**File:** `src/Controller/Admin/BackupDb.php:31`~~

~~`system()` exposes DB credentials in process arguments. Replace with Symfony's `Process` component.~~

### ~~Security: Deposit JSON leak~~ ✅ Done
~~**File:** `src/Controller/Deposits/Lookup.php`~~

~~Returns the full entity as JSON without filtering sensitive fields. Introduce a DTO to control what gets serialized.~~

### ~~Mail controller duplication~~ ✅ Done
~~**Files:** `src/Controller/Mail/` (5 controllers, 56–108 lines each)~~

~~All five controllers follow the same pattern: loop stores → generate PDF → send email → add flash messages. Extract a `BulkMailService` to eliminate the duplication.~~

### ~~Download controller duplication~~ ✅ Done
~~**Files:** `src/Controller/Download/Transactions.php`, `src/Controller/Download/Planillas.php`~~

~~Both repeat the same PDF generation options inline. Extract a `PdfGenerationService`.~~

---

## Medium Priority

### ~~Magic number payment method IDs~~ ✅ Done
~~**Files:** `src/Service/DepositImporter.php:29`, `src/Controller/Admin/CollectRent.php:49`, `src/Entity/Deposit.php:51`~~

~~`find(1)` (BAR) and `find(2)` (BANK) are hardcoded in three places. Centralise with a `PaymentMethodId` enum.~~

### ~~Business logic in `Contract` entity~~ ✅ Done
~~**File:** `src/Entity/Contract.php:168-185`~~

~~`setValuesFromStore()` hydrates a Contract from a Store and is only called from one controller. Move to a `ContractFactory` service.~~

### ~~Transaction creation in controllers~~ ✅ Done
~~**Files:** `src/Controller/Admin/PayDay.php`, `src/Controller/Admin/CollectRent.php`~~

~~Both controllers create Transaction entities inline with long setter chains. Extract a `TransactionFactory` service to improve testability.~~

### ~~N+1 query risk in `PayrollHelper`~~ ✅ Done
~~**File:** `src/Service/PayrollHelper.php:44-63`~~

~~Loops over stores and fires `getSaldoALaFecha()` + `findMonthPayments()` separately per iteration. Added `getSaldoALaFechaByStores()` and `findMonthPaymentsByStores()` batch methods to `TransactionRepository`, reducing N+1 queries to 2 queries total.~~

---

## Low Priority / Housekeeping

### Incomplete implementations
- **`src/Controller/Mail/PaymentsAccountant.php:44,54`** — Two `@todo` comments: PDF attachment not wired, redirect target missing.
- **`src/Security/GoogleIdentityAuthenticator.php:84,92`** — Two `@todo` comments for Google ID fetch/update logic.

### `Gender` enum not translatable
**File:** `src/Type/Gender.php:27`

Has a `@TODO This should be translatable!` comment. Use Symfony's `TranslatableMessage` for gender label strings.

### ~~Scattered date handling~~ ✅ Done
~~**17+ files** use `date()`, `new DateTime()`, and `strtotime()` directly.~~ Injected `Symfony\Component\Clock\ClockInterface` into `TransactionRepository`, `AppExtension`, and 13 controllers/commands. Tests updated to use `MockClock`.

### ~~Missing unit tests~~ ✅ Done
- ~~`src/Service/DepositImporter.php` — CSV parsing logic has no unit tests.~~ Added `DepositImporterTest` with 8 tests.
- ~~`src/Service/PayrollHelper.php` — Date boundary conditions not covered.~~ Already covered by existing PayrollHelperTest (Jan/Feb/Dec/May boundary tests).
- ~~`src/Repository/TransactionRepository.php` — `getPagosPorAno()` and `checkChargementRequired()` lack specific test cases.~~ Added structure and edge case tests.
