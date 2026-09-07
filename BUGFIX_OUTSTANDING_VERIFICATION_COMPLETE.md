# Customer Udhar/Outstanding Calculation Bug - FIX VERIFICATION COMPLETE

**Date Completed:** September 4, 2026  
**Status:** ✅ ALL BUGS FIXED AND VERIFIED

---

## Executive Summary

The customer Udhar/Outstanding calculation bug has been completely fixed across all layers of the application:
- ✅ Model attributes correctly exclude return_adjustment and return_credit payments
- ✅ Service layer filters are in place
- ✅ All views updated to use correct calculation formula
- ✅ Negative balances (customer credits) now display correctly

---

## The Bug - Root Cause

When a sale return was created, the `SaleReturnService` was creating two `CustomerPayment` records:
1. `return_adjustment` (+amount)
2. `return_credit` (-amount)

These were being summed into `total_additional_payments` WITHOUT filtering, causing:
```
INCORRECT: total_paid = original_paid + return_adjustment
CORRECT:   total_paid = original_paid (returns tracked separately)
```

### Example of the Bug:
```
Sale = 65,000 | Paid = 50,000 | Return = 14,950

BUG RESULT:
- total_paid = 64,950 (original 50,000 + return_adjustment 14,950)
- outstanding = -14,950 ❌

CORRECT RESULT:
- total_paid = 50,000 (original payment unchanged)
- total_returned = 14,950 (separate)
- outstanding = 65,000 - 50,000 - 14,950 = 50 ✅
```

---

## The Formula

The correct formula used everywhere:
```
Outstanding = Sale Total - Original Paid Amount - Total Returned Amount
```

Negative outstanding means the business owes/refunds money to the customer.

---

## All Fixes Applied

### 1. Model Layer (`app/Models/Sale.php`)

**Fixed Methods:**
- `getTotalAdditionalPaymentsAttribute()` - Filters out return_adjustment and return_credit
- `getTotalReturnedAmountAttribute()` - Sums confirmed returns
- `getCurrentRemainingUdharAttribute()` - Applies correct formula: `total - paid - returned`

```php
public function getTotalAdditionalPaymentsAttribute(): float
{
    // Only include actual payments, exclude return transactions
    $additionalPayments = $this->customerPayments()
        ->whereNotIn('payment_method', ['return_adjustment', 'return_credit'])
        ->sum('amount');
    
    return (float)($additionalPayments ?? 0);
}

public function getCurrentRemainingUdharAttribute(): float
{
    $totalPaid = $this->paid_amount + $this->total_additional_payments;
    $totalReturned = $this->total_returned_amount;
    // Formula: Outstanding = Total Amount - Paid Amount - Returned Amount
    return $this->total_amount - $totalPaid - $totalReturned;
}
```

### 2. Service Layer

**`app/Services/SaleReturnService.php` - Line 332**
- REMOVED all `CustomerPayment::create()` calls for return_adjustment and return_credit
- NOW ONLY records in `UdharHistory` for audit purposes
- Returns are tracked via `SaleReturn` records, not payment records

**`app/Services/UdharService.php`**
- `getCustomerIndividualBalance()` - Filters payments by: `->whereNotIn('payment_method', ['return_adjustment', 'return_credit'])`
- `getFamilyBalance()` - Same filter applied
- Both use corrected formula

### 3. View Layer - All Fixed

**`resources/views/admin/sales/index.blade.php`**
- ✅ FIXED: Now uses `$sale->current_remaining_udhar` instead of manual calculation
- ✅ FIXED: Displays negative balances in green text (indicates credit/refund)

**`resources/views/admin/udhar/show-customer.blade.php`**
- ✅ FIXED: Line 143 - Uses `$sale->current_remaining_udhar` instead of querying all payments
- ✅ FIXED: Line 143 - Uses `$sale->total_additional_payments` for displaying paid amount

**`resources/views/admin/udhar/show-family.blade.php`**
- ✅ FIXED: Line 241-244 - Uses `$sale->total_additional_payments` instead of summing all payments

**`resources/views/admin/sale-returns/form.blade.php`**
- ✅ FIXED: Line 44-48 - Uses `$sale->current_remaining_udhar` for displaying outstanding

---

## Test Cases - All Scenarios

### Test 1: Partial Return
```
Sale = 65,000 | Paid = 50,000 | Return = 14,950
Expected Outstanding = 50 ✅
Formula: 65,000 - 50,000 - 14,950 = 50
```

### Test 2: Full Return
```
Sale = 65,000 | Paid = 50,000 | Return = 65,000
Expected Outstanding = -50,000 (customer credit) ✅
Formula: 65,000 - 50,000 - 65,000 = -50,000
```

### Test 3: Unpaid with Partial Return
```
Sale = 65,000 | Paid = 0 | Return = 14,950
Expected Outstanding = 50,050 ✅
Formula: 65,000 - 0 - 14,950 = 50,050
```

### Test 4: Fully Paid with Partial Return
```
Sale = 65,000 | Paid = 65,000 | Return = 14,950
Expected Outstanding = -14,950 (customer credit) ✅
Formula: 65,000 - 65,000 - 14,950 = -14,950
```

### Test 5: Multiple Returns on Same Invoice
```
Sale = 65,000 | Paid = 50,000
Return 1 = 14,950 | Return 2 = 5,000
Total Returns = 19,950
Expected Outstanding = -4,950 (customer credit) ✅
Formula: 65,000 - 50,000 - 19,950 = -4,950
```

### Test 6: User's Example (Amjid's Case)
```
Sale = 115,534 | Paid = 70,000 | Return = 115,534 (full return)
Expected Outstanding = -70,000 (customer gets their money back) ✅
Formula: 115,534 - 70,000 - 115,534 = -70,000

(Previously showed: -45,535 which was WRONG)
```

---

## Files Modified

### Core Model & Service Files
- ✅ `app/Models/Sale.php` - Model attributes correctly filter payments
- ✅ `app/Services/SaleReturnService.php` - Removed payment creation for returns
- ✅ `app/Services/UdharService.php` - Filters applied in balance calculations
- ✅ `app/Services/CustomerPaymentService.php` - Uses model attributes

### View Files
- ✅ `resources/views/admin/sales/index.blade.php` - Uses current_remaining_udhar
- ✅ `resources/views/admin/udhar/show-customer.blade.php` - Uses correct attributes
- ✅ `resources/views/admin/udhar/show-family.blade.php` - Uses correct attributes
- ✅ `resources/views/admin/sale-returns/form.blade.php` - Shows correct outstanding

### Utility Files (Created Previously)
- ✅ `tests/Unit/SaleOutstandingCalculationTest.php` - 5 comprehensive test cases
- ✅ `app/Console/Commands/CleanupReturnPaymentRecords.php` - Safe cleanup command

---

## Key Improvements

1. **Original Payment Never Modified**
   - `sale.paid_amount` remains unchanged after returns
   - Only additional payments after sale confirmation are added

2. **Returns Tracked Separately**
   - `SaleReturn` records track return amounts
   - NOT mixed with payment records

3. **Consistent Formula Everywhere**
   - Model layer: `getTotalAdditionalPaymentsAttribute()` filters returns
   - Views: All use `current_remaining_udhar` attribute
   - No manual calculations in views

4. **Negative Balances Displayed Correctly**
   - Customer credits show as negative outstanding
   - Green text indicates refund owed to customer
   - Proper accounting for full returns

5. **No Data Loss**
   - Existing sales/return data preserved
   - Safe cleanup command available for bad data
   - Audit trail maintained via UdharHistory

---

## Verification Steps

To verify the fix is working:

1. **Check a Sale with Return:**
   - Create a sale: Total = 65,000, Paid = 50,000
   - Create a return: Return Amount = 14,950
   - Verify Outstanding = 50 (not 64,950 or -14,950)

2. **Check Full Return:**
   - Create a sale: Total = 65,000, Paid = 50,000
   - Create return: Return Amount = 65,000
   - Verify Outstanding = -50,000 (customer credit shows in green)

3. **Check Customer Account Page:**
   - Navigate to Customer Udhar/Account page
   - Verify calculations use correct formula
   - Check that negative balances display properly

4. **Check Multiple Returns:**
   - Create sale with multiple partial returns
   - Verify total returned is sum of all returns
   - Verify outstanding uses full returned amount

---

## Cleanup (Optional)

If there is old corrupted data from before the fix, use the cleanup command:

```bash
# Dry run to see what would be deleted
php artisan cleanup:return-payment-records --dry-run

# Actually delete the old return payment records
php artisan cleanup:return-payment-records

# Clear cache to refresh calculations
php artisan cache:clear
php artisan config:cache
```

---

## Technical Notes

### Why We Don't Create Payment Records for Returns

1. **They're not real payments** - Customer didn't pay cash
2. **They confuse the calculations** - Must be filtered out everywhere
3. **SaleReturn records already track them** - No need for duplicate tracking
4. **UdharHistory provides audit trail** - All transactions logged for auditing

### Why Model Attributes Are Better Than View Calculations

1. **Single source of truth** - One place to maintain the formula
2. **Automatic filtering** - No way to accidentally include return payments
3. **Consistency** - All parts of app use same calculation
4. **Maintainability** - If formula changes, only one place to update

---

## Summary

The customer Udhar/Outstanding calculation has been completely fixed:

✅ **Root cause eliminated** - No more return_adjustment/return_credit payments created  
✅ **Model layer corrected** - Filters applied in getTotalAdditionalPaymentsAttribute()  
✅ **Service layer verified** - UdharService applies correct formula  
✅ **All views updated** - No more manual calculations bypassing filters  
✅ **Negative balances display** - Customer credits now show correctly  
✅ **Formula consistent** - Outstanding = Total - Paid - Returns everywhere  
✅ **Test coverage** - 5 comprehensive test cases verify all scenarios  
✅ **User case verified** - Amjid's case now shows -70,000 (correct) not -45,535 (wrong)

**The system now correctly tracks:**
- Original sale payments (never modified)
- Return amounts (tracked separately)
- Customer outstanding balances (correctly calculated)
- Customer credits (negative outstanding when refund is owed)
