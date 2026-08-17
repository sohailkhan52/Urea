# Quick Start Guide - Purchase & Sales Features

## 🚀 Getting Started

### Access the Features

**Purchase Management**
- URL: `http://localhost:8000/admin/purchases`
- Permission Required: `purchases.view`

**Sales Management**
- URL: `http://localhost:8000/admin/sales`
- Permission Required: `sales.view`

---

## 📋 Creating a Purchase

### Step 1: Navigate to Purchases
- Click "Purchases" in the sidebar → Transactions section
- Click "Create Purchase" button

### Step 2: Fill Basic Information
- **Supplier**: Select from dropdown
- **Warehouse**: Select destination warehouse
- **Purchase Date**: Enter date
- **Notes**: Optional notes

### Step 3: Add Line Items
- Click "Add Product" button
- Select product from dropdown
- Enter quantity
- Enter unit price
- System auto-calculates line total
- Click "Add" to add item
- Repeat for more items

### Step 4: Add Expenses (Optional)
- Discount: Enter discount amount
- Transport Cost: Enter shipping cost
- Other Expenses: Enter any additional costs
- System auto-calculates grand total

### Step 5: Save and Confirm
- Click "Create Purchase" to save as draft
- Click "Confirm" button to finalize
  - This INCREASES warehouse stock
  - Cannot be undone (stock is locked in)

---

## 📊 Creating a Sale

### Step 1: Navigate to Sales
- Click "Sales" in the sidebar → Transactions section
- Click "Create Sale" button

### Step 2: Fill Basic Information
- **Customer**: Select from dropdown (optional - can be walk-in)
- **Warehouse**: Select source warehouse
- **Sale Date**: Enter date
- **Payment Method**: Select method (optional)
- **Notes**: Optional notes

### Step 3: Add Line Items
- Click "Add Product" button
- Select product from dropdown
- System shows **Available Stock** for this warehouse
- Enter quantity (must be ≤ available stock)
- Enter unit price
- Enter item discount (optional)
- System auto-calculates line total
- Click "Add" to add item
- Repeat for more items

### Step 4: Review and Confirm
- Click "Create Sale" to save as draft
- Click "Confirm" button to finalize
  - System validates stock availability
  - This DECREASES warehouse stock
  - Cannot be undone (stock is locked in)

### Step 5: Record Payment (Optional)
- Click "Record Payment" button
- Enter payment amount
- Select payment method
- Enter optional reference number
- Click "Save"
- Payment status updates automatically

---

## 💰 Payment Tracking

### Payment Statuses
- **Unpaid**: Paid amount = 0
- **Partially Paid**: 0 < Paid amount < Total
- **Paid**: Paid amount = Total

### Recording Payment
1. Go to Sale details
2. Click "Record Payment"
3. Enter amount paid
4. Select payment method:
   - Cash
   - Bank Transfer
   - Easypaisa
   - Jazz Cash
   - Cheque
   - Other
5. Enter reference number (for cheque/transfer)
6. Add optional notes
7. Click "Save"

### Partial Payment Example
- Total Amount: Rs. 10,000
- First Payment: Rs. 4,000 → Partially Paid
- Second Payment: Rs. 6,000 → Paid (full)

---

## 📄 Printing Invoices

### Purchase Invoice
1. Go to Purchase details (Show page)
2. Click "Print Invoice" button
3. Click browser print button (Ctrl+P)
4. Select printer or save as PDF
5. Print-friendly format (no sidebar/navbar)

### Sales Invoice
1. Go to Sale details (Show page)
2. Click "Print Invoice" button
3. Click browser print button (Ctrl+P)
4. Select printer or save as PDF
5. A4 paper size optimized

---

## 🔍 Searching and Filtering

### Purchase Search
- **Search Box**: Type purchase number or supplier name
- **Filter by Supplier**: Click dropdown → select supplier
- **Filter by Warehouse**: Click dropdown → select warehouse
- **Filter by Status**: Click dropdown → select draft/confirmed/cancelled
- **Date Range**: Enter from/to dates
- Click "Clear All" to reset

### Sales Search
- **Search Box**: Type invoice number or customer name
- **Filter by Customer**: Click dropdown → select customer
- **Filter by Warehouse**: Click dropdown → select warehouse
- **Filter by Payment Status**: Click dropdown → select status
- **Date Range**: Enter from/to dates
- Click "Clear All" to reset

---

## ⚠️ Common Errors & Solutions

### "This purchase cannot be confirmed"
- **Problem**: Trying to confirm a confirmed purchase
- **Solution**: Only draft purchases can be confirmed
- **Action**: Create new purchase instead

### "Cannot confirm sale without items"
- **Problem**: No products added to sale
- **Solution**: Add at least one product before confirming
- **Action**: Click "Add Product" and select items

### "Insufficient stock for [Product]"
- **Problem**: Trying to sell more than available
- **Solution**: Available stock is shown when adding items
- **Action**: Reduce quantity or select different warehouse

### "Only draft sales can be confirmed"
- **Problem**: Trying to confirm already-confirmed sale
- **Solution**: Confirmed sales cannot be re-confirmed
- **Action**: This is correct behavior for data integrity

---

## 📊 Stock Movement Flow

### Understanding Inventory Changes

**After Purchase Confirmation**:
```
Warehouse: Main Warehouse
Product: Sona Urea
Before: 100 bags
Purchase: 500 bags
After: 600 bags
Status: Stock INCREASED ✅
```

**After Sale Confirmation**:
```
Warehouse: Main Warehouse
Product: Sona Urea
Before: 600 bags
Sale: 50 bags
After: 550 bags
Status: Stock DECREASED ✅
```

### Checking Stock History
1. Go to "Inventory" menu
2. Select warehouse and product
3. Click "View Movements"
4. See all stock in/out transactions
5. View running balance

---

## 🔐 Permissions

### Required Permissions for Operations

| Operation | Permission |
|-----------|-----------|
| View purchases | `purchases.view` |
| Create purchase | `purchases.create` |
| Edit draft purchase | `purchases.update` |
| Confirm purchase | `purchases.approve` |
| Cancel purchase | `purchases.cancel` |
| View sales | `sales.view` |
| Create sale | `sales.create` |
| Edit draft sale | `sales.update` |
| Confirm sale | `sales.approve` |
| Cancel sale | `sales.cancel` |

### User Roles
- **Super Admin**: All permissions
- **Admin**: All permissions
- **Custom Roles**: Can be assigned specific permissions

---

## 💡 Tips & Best Practices

### When Creating Purchases
✅ Always select correct warehouse
✅ Double-check supplier details
✅ Enter accurate quantities
✅ Verify unit prices before confirming
✅ Save as draft first, review, then confirm

### When Creating Sales
✅ Check warehouse has sufficient stock
✅ Confirm customer credit limit if on account
✅ Verify unit prices with sales team
✅ Save as draft to make changes
✅ Confirm only when prices are final

### Payment Recording
✅ Record payment immediately after receiving
✅ Keep reference numbers for bank transfers
✅ Note cheque numbers for cheque payments
✅ Use notes field for any special info

### Printing Invoices
✅ Use "Print Preview" to check format first
✅ Save as PDF for record keeping
✅ Print on official company letterhead
✅ Keep printed copies for audit trail

---

## 🛠️ Troubleshooting

### Stock Not Updating After Confirmation

**Check**:
1. Is purchase/sale status "confirmed"?
2. Did you click "Confirm" button?
3. Check warehouse inventory page
4. Verify stock movements exist

**Solution**:
- Navigate to Inventory page
- Search for product and warehouse
- Check if stock movements appear
- If not, contact administrator

### Cannot Find Product in Dropdown

**Check**:
1. Is product marked as "Active"?
2. Is product in correct company?

**Solution**:
- Go to Products menu
- Search for product
- Check if status is "Active"
- Activate if needed
- Try creating sale/purchase again

### Payment Status Not Updating

**Check**:
1. Did you click "Save" on payment form?
2. Is payment amount ≤ total amount?

**Solution**:
- Go to Sale details
- Click "Record Payment"
- Enter correct amount
- Click "Save"
- Refresh page to see updated status

---

## 📞 Support

For issues or questions:
1. Check this quick start guide
2. Review detailed documentation
3. Contact system administrator
4. Check database logs if needed

---

## 📅 Example Workflow

### Complete Transaction Example

```
Monday 9:00 AM
1. Create Purchase PO-001
   - Supplier: Agri Supplies
   - Warehouse: Main
   - 500 bags Sona Urea @ Rs. 100
   Status: DRAFT

Monday 10:00 AM
2. Confirm Purchase
   - Stock updates: 0 → 500 bags
   - Status: CONFIRMED
   - Stock movement: +500

Monday 11:00 AM
3. Create Sale INV-001
   - Customer: Muhammad Ahmed
   - Warehouse: Main
   - 50 bags Sona Urea @ Rs. 150
   Status: DRAFT

Monday 12:00 PM
4. Confirm Sale
   - Validates: 500 bags available ✓
   - Stock updates: 500 → 450 bags
   - Status: CONFIRMED
   - Stock movement: -50

Monday 1:00 PM
5. Record Payment
   - Payment: Rs. 7,500 (50 × 150)
   - Method: Cash
   - Status: PAID

Monday 2:00 PM
6. Print Sale Invoice
   - Format: A4 professional
   - Include: Items, prices, totals
   - Save as PDF

Final Stock: 450 bags in Main Warehouse
```

---

**For complete technical documentation, see: PURCHASE_SALES_IMPLEMENTATION.md**
