# Bug Fix: Customer Udhar / Outstanding Calculation

## Summary
Fixed critical bug where sale outstanding amounts were incorrectly calculated when returns were created. The bug caused the original paid amount to be artificially inflated by including return-related payment records.

## Root Cause
When a SaleReturn was confirmed:
1. `SaleReturnService::adjustCustomerBalance()` created fake `CustomerPayment` records with methods:
   - `return_adjustment` (positive amount)
   - `return_credit` (negative amount)
2. `Sale::getTotalAdditionalPaymentsAttribute()` summed ALL customer payments WITHOUT filtering
3. This made `total_paid = original_paid_amount + return_adjustment`
4. Example bug scenario:
   - Sale: 65,000 | Paid: 50,000 | Return: 14,950
   - BUG: Paid became 50,000 + 14,950 = 64,950
   - Outstanding showed: 65,000 - 64,950 - 14,950 = **-14,950** ❌
   - Should be: 65,000 - 50,000 - 14,950 = **50** ✅

## Impact
- Customer outstanding balances were incorrect on Udhar account pages
- Dashboard showed wrong outstanding totals
- Payment history was confusing with fake "return adjustment" payments
- Made it impossible to track actual customer payments vs. return amounts

## Files Modified

### 1. `app/Models/Sale.php`
**Method**: `getTotalAdditionalPaymentsAttribute()`

**Change**: 
```php
// BEFORE (BUG)
return $this->customerPayments()->sum('amount');

// AFTER (FIXED)
$additionalPayments = $this->customerPayments()
    ->whereNotIn('payment_method', ['return_adjustment', 'return_credit'])
    ->sum('amount');
return (float)($additionalPayments ?? 0);
```

**Why**: Only include actual customer payments, exclude return-related accounting entries.

---

### 2. `app/Services/SaleReturnService.php`
**Method**: `adjustCustomerBalance()`

**Change**:
- REMOVED: All `CustomerPayment::create()` calls for `return_adjustment` and `return_credit`
- KEPT: UdharHistory recording (for audit trail only)

**Before**: 130+ lines creating fake payments
**After**: 10 lines recording history only

**Why**: Return amounts are already tracked via SaleReturn records. No need for fake payments.

---

### 3. `app/Services/UdharService.php`
**Methods**: `getCustomerIndividualBalance()` and `getFamilyBalance()`

**Change**:
```php
// BEFORE (BUG)
$salePayments = CustomerPayment::where('sale_id', $sale->id)->sum('amount');

// AFTER (FIXED)
$salePayments = CustomerPayment::where('sale_id', $sale->id)
    ->whereNotIn('payment_method', ['return_adjustment', 'return_credit'])
    ->sum('amount');
```

**Why**: Exclude return-related payments when calculating "paid" amounts.

---

### 4. `app/Console/Commands/CleanupReturnPaymentRecords.php` (NEW)
**Purpose**: Safe removal of existing incorrect payment records

**Usage**:
```bash
# Preview what will be deleted
php artisan cleanup:return-payment-records --dry-run

# Delete the records
php artisan cleanup:return-payment-records
```

**Safety**: 
- Dry-run mode to preview
- Confirmation required before deletion
- Wrapped in transaction for atomicity

---

### 5. `tests/Unit/SaleOutstandingCalculationTest.php` (NEW)
**Purpose**: Comprehensive test suite for the fix

**Tests**:
1. TEST 1: Partial return (Sale 65k, Paid 50k, Return 14,950) → Outstanding 50
2. TEST 2: Full return (Sale 65k, Paid 50k, Return 65k) → Outstanding -50,000
3. TEST 3: Multiple returns (Sale 65k, Paid 50k, Return1 14,950 + Return2 5,000) → Outstanding -4,950
4. TEST 4: Unpaid sale with return (Sale 65k, Paid 0, Return 14,950) → Outstanding 50,050
5. TEST 5: Fully paid with return (Sale 65k, Paid 65k, Return 14,950) → Outstanding -14,950

---

## Formula Used (NOW CONSISTENT EVERYWHERE)

```
Outstanding = Sale Total - Original Paid Amount - Total Returned Amount
```

This formula is now applied in:
- `Sale::getCurrentRemainingUdharAttribute()`
- `UdharService::getCustomerIndividualBalance()`
- `UdharService::getFamilyBalance()`
- All views displaying Udhar/Outstanding

---

## Data Integrity

### What Was NOT Changed
- Original `sale.paid_amount` - remains the amount paid by customer at time of sale
- `sale.total_amount` - total sale amount
- `SaleReturn.total_return_amount` - total returned amount per return record
- Historical payment records (only fake return payments are removed)

### What Was Changed
- Removed `return_adjustment` and `return_credit` payment records (accounting artifacts)
- Updated all calculation logic to exclude these payments
- Cleaned up UdharService calculations

### Data Corruption Recovery
If existing data shows incorrect outstanding amounts:
1. Run cleanup command to remove fake return payment records
2. Outstanding will auto-recalculate correctly based on:
   - Original `paid_amount` (unchanged)
   - Sum of `SaleReturn.total_return_amount` (from database)
   - No fake payments included

---

## Testing Results

### Test Cases Verified
✅ **TEST 1**: Partial return calculation correct
✅ **TEST 2**: Full return shows customer credit correctly
✅ **TEST 3**: Multiple returns sum correctly
✅ **TEST 4**: Unpaid sale with return calculated correctly
✅ **TEST 5**: Fully paid sale with return shows credit correctly

### Regression Tests Needed
- [ ] Run existing test suite: `php artisan test`
- [ ] Verify Udhar management page displays correctly
- [ ] Verify Dashboard outstanding totals
- [ ] Verify Payment history page (should not show return_adjustment/return_credit now)
- [ ] Verify Customer account pages

---

## Migration Instructions

### For Developers
1. Pull the code changes
2. No database migrations needed (structure unchanged)
3. Run tests: `php artisan test tests/Unit/SaleOutstandingCalculationTest.php`
4. Optional: Run cleanup: `php artisan cleanup:return-payment-records --dry-run`

### For System Administrators
1. If existing data has fake return payments:
   ```bash
   cd /path/to/project
   php artisan cleanup:return-payment-records --dry-run    # Preview
   php artisan cleanup:return-payment-records              # Execute
   ```

2. Clear application cache:
   ```bash
   php artisan cache:clear
   php artisan config:cache
   ```

3. Verify outstanding amounts are now correct on Customer Udhar pages

---

## Verification Checklist

### Before Deployment
- [ ] All tests pass
- [ ] No database errors
- [ ] Code review approved

### After Deployment
- [ ] Customer Udhar pages show correct outstanding
- [ ] Dashboard shows correct total outstanding
- [ ] Individual sales show correct outstanding
- [ ] Multiple returns on same sale calculate correctly
- [ ] Negative outstanding (customer credit) displays correctly
- [ ] Payment history no longer shows return_adjustment/return_credit (if cleaned)
- [ ] Export/Reports show correct figures

---

## Example: Before and After

### Scenario
- Customer "Ahmed" has Sale INV-001: 65,000
- Paid 50,000
- Returns 14,950 worth of goods

### BEFORE (BUG)
| Field | Value |
|-------|-------|
| Total Sales | 65,000 |
| Total Paid | 64,950 ❌ (should be 50,000) |
| Returns | 14,950 |
| Outstanding | -14,950 ❌ (should be 50) |
| Status | Shows as credit, confusing |

**Payment History shown**: return_adjustment +14,950 (misleading)

### AFTER (FIXED)
| Field | Value |
|-------|-------|
| Total Sales | 65,000 |
| Total Paid | 50,000 ✅ |
| Returns | 14,950 |
| Outstanding | 50 ✅ |
| Status | Correctly shows small outstanding balance |

**Payment History shown**: Only actual customer payments (return_adjustment removed)

---

## Questions & Answers

**Q: Will this affect historical data?**
A: No. The original `paid_amount` is never modified. Only the calculation logic changed.

**Q: Do I need to clean up old payments?**
A: Not mandatory, but recommended. Use `--dry-run` first to see what would be deleted.

**Q: What about refunds if customer had credit?**
A: Negative outstanding still shows correctly. The customer can apply credit to new sales or request refund.

**Q: Are there any database schema changes?**
A: No. This is a pure business logic fix.

**Q: What happens if I don't run the cleanup?**
A: Calculations will be correct going forward, but old fake payments will still exist in history. Run cleanup whenever convenient.

---

## References

- Bug Description: See main task
- Test Coverage: `tests/Unit/SaleOutstandingCalculationTest.php`
- Related Files: 
  - Sale model calculations
  - UdharService balance calculations
  - SaleReturnService return logic
