# Reports Module Specification

## Overview
Comprehensive reporting system for the inventory management system with 7 main report categories.

---

## 1. Sales Report

### 1.1 Daily Sales Report
**Purpose:** Track daily sales transactions with detailed breakdown

**Filters:**
- Date Range (from - to)
- Warehouse
- Customer
- Payment Status (paid, partial, unpaid)
- Status (draft, confirmed, cancelled)

**Columns:**
- Invoice Number
- Date
- Customer Name
- Warehouse
- Total Items (quantity)
- Subtotal
- Discount
- Total Amount
- Paid Amount
- Balance/Due
- Payment Status
- Status

**Summary Section:**
- Total Sales Amount
- Total Paid Amount
- Total Outstanding
- Total Discount Given
- Number of Invoices
- Average Sale Value

**Export Options:**
- PDF
- Excel
- Print

---

### 1.2 Product-Wise Sales Report
**Purpose:** Analyze sales performance by product

**Filters:**
- Date Range
- Warehouse
- Category
- Product
- Minimum Quantity

**Columns:**
- Product Name
- SKU
- Category
- Total Quantity Sold
- Total Sales Amount
- Average Unit Price
- Total Discount
- Net Sales Amount
- Number of Transactions

**Summary:**
- Total Products Sold
- Total Quantity
- Total Revenue
- Top 10 Products

---

### 1.3 Customer-Wise Sales Report
**Purpose:** Track sales by customer

**Filters:**
- Date Range
- Warehouse
- Customer
- Minimum Sales Amount

**Columns:**
- Customer Name
- Phone
- Total Invoices
- Total Quantity
- Total Sales Amount
- Total Paid
- Outstanding Balance
- Last Purchase Date

**Summary:**
- Total Customers
- Total Sales
- Average Sale per Customer
- Top 10 Customers

---

### 1.4 Warehouse-Wise Sales Report
**Purpose:** Compare sales performance across warehouses

**Filters:**
- Date Range

**Columns:**
- Warehouse Name
- Location
- Total Invoices
- Total Items Sold
- Total Sales Amount
- Total Collections
- Outstanding Amount
- Average Sale Value

**Summary:**
- Overall Total Sales
- Best Performing Warehouse
- Warehouse Contribution %

---

## 2. Purchase Report

### 2.1 Daily Purchase Report
**Purpose:** Track purchase transactions

**Filters:**
- Date Range
- Warehouse
- Supplier
- Payment Status
- Status

**Columns:**
- PO Number
- Date
- Supplier Name
- Warehouse
- Total Items
- Subtotal
- Discount
- Total Amount
- Paid Amount
- Balance/Payable
- Payment Status
- Status

**Summary:**
- Total Purchase Amount
- Total Paid Amount
- Total Payable
- Number of POs
- Average Purchase Value

---

### 2.2 Supplier-Wise Purchase Report
**Purpose:** Analyze purchases by supplier

**Filters:**
- Date Range
- Supplier
- Minimum Purchase Amount

**Columns:**
- Supplier Name
- Company Name
- Phone
- Total POs
- Total Quantity
- Total Purchase Amount
- Total Paid
- Outstanding Payable
- Last Purchase Date

**Summary:**
- Total Suppliers
- Total Purchases
- Average Purchase per Supplier
- Top 10 Suppliers

---

### 2.3 Product-Wise Purchase Report
**Purpose:** Track product purchases

**Filters:**
- Date Range
- Supplier
- Category
- Product
- Minimum Quantity

**Columns:**
- Product Name
- SKU
- Category
- Total Quantity Purchased
- Total Purchase Amount
- Average Unit Cost
- Total Discount
- Net Purchase Amount
- Number of Transactions
- Last Purchase Date

**Summary:**
- Total Products Purchased
- Total Quantity
- Total Cost
- Most Purchased Products

---

## 3. Invoice Report

### 3.1 All Invoices Report
**Purpose:** Comprehensive list of all sales and purchase invoices

**Filters:**
- Type (Sales / Purchase / Both)
- Date Range
- Warehouse
- Status
- Payment Status

**Columns:**
- Invoice/PO Number
- Type (Sale/Purchase)
- Date
- Party (Customer/Supplier)
- Warehouse
- Total Amount
- Paid Amount
- Balance
- Payment Status
- Status

**Summary:**
- Total Sales Invoices
- Total Purchase Invoices
- Total Sales Amount
- Total Purchase Amount
- Net Cash Flow
- Outstanding Receivables
- Outstanding Payables

**Export Options:**
- PDF (detailed list)
- Excel (with all data)
- Print

---

## 4. Inventory Report

### 4.1 Current Stock Report
**Purpose:** Real-time inventory levels

**Filters:**
- Warehouse
- Category
- Product
- Low Stock Only (checkbox)
- Show Zero Stock (checkbox)

**Columns:**
- Product Name
- SKU
- Category
- Warehouse
- Current Stock
- Minimum Stock Level
- Stock Value (quantity × avg cost)
- Status (Normal / Low / Out of Stock)

**Summary:**
- Total Products
- Total Stock Value
- Low Stock Items Count
- Out of Stock Items Count
- Categories Summary

**Color Coding:**
- 🟢 Normal Stock (>= minimum level)
- 🟡 Low Stock (< minimum level but > 0)
- 🔴 Out of Stock (= 0)

---

### 4.2 Warehouse Stock Report
**Purpose:** Compare stock across warehouses

**Filters:**
- Product
- Category

**Columns:**
- Product Name
- SKU
- Category
- [Warehouse 1] Qty
- [Warehouse 2] Qty
- [Warehouse N] Qty
- Total Quantity
- Total Value

**Summary:**
- Warehouse-wise totals
- Product distribution
- Stock concentration

---

### 4.3 Stock Movements Report
**Purpose:** Track all stock movements

**Filters:**
- Date Range
- Warehouse
- Product
- Movement Type
- Reference Document

**Movement Types:**
- Opening Stock
- Purchase
- Sale
- Customer Return
- Supplier Return
- Transfer In
- Transfer Out
- Adjustment In
- Adjustment Out
- Damaged
- Expired

**Columns:**
- Date
- Reference #
- Movement Type
- Product Name
- Warehouse
- Quantity In
- Quantity Out
- Balance After
- Unit Cost
- Total Value
- Remarks

**Summary:**
- Total In
- Total Out
- Net Movement
- Closing Balance

---

## 5. Customer Report

### 5.1 Customer Outstanding Report
**Purpose:** Track customer balances

**Filters:**
- Status (Active/Inactive)
- Minimum Balance
- Sort By (Balance/Name/Last Purchase)

**Columns:**
- Customer Name
- Phone
- Company Name
- Total Sales
- Total Paid
- Outstanding Balance
- Credit Limit
- Available Credit
- Last Sale Date
- Days Outstanding (average)

**Summary:**
- Total Customers
- Total Outstanding
- Average Outstanding
- Overdue Amount (>30 days)
- Top 10 Debtors

**Color Coding:**
- 🟢 Within Credit Limit
- 🟡 Near Credit Limit (>80%)
- 🔴 Exceeded Credit Limit

---

### 5.2 Customer Payment History
**Purpose:** Detailed payment tracking for a customer

**URL:** `/admin/reports/customer/{id}/payment-history`

**Filters:**
- Date Range
- Payment Method

**Columns:**
- Date
- Receipt Number
- Invoice Number
- Amount Paid
- Payment Method
- Reference
- Received By
- Notes

**Summary:**
- Total Payments
- Payment Method Breakdown
- Average Payment Amount

---

### 5.3 Customer Ledger
**Purpose:** Complete transaction history

**URL:** `/admin/reports/customer/{id}/ledger`

**Filters:**
- Date Range

**Columns:**
- Date
- Transaction Type
- Reference Number
- Description
- Debit (Sale)
- Credit (Payment/Return)
- Balance

**Summary:**
- Opening Balance
- Total Debits
- Total Credits
- Closing Balance

---

## 6. Supplier Report

### 6.1 Supplier Outstanding Report
**Purpose:** Track supplier payables

**Filters:**
- Status (Active/Inactive)
- Minimum Balance
- Sort By (Balance/Name/Last Purchase)

**Columns:**
- Supplier Name
- Phone
- Company Name
- Total Purchases
- Total Paid
- Outstanding Payable
- Credit Terms
- Last Purchase Date
- Days Outstanding

**Summary:**
- Total Suppliers
- Total Payables
- Average Payable
- Overdue Payables
- Top 10 Creditors

---

### 6.2 Supplier Payment History
**Purpose:** Track payments to supplier

**URL:** `/admin/reports/supplier/{id}/payment-history`

**Filters:**
- Date Range
- Payment Method

**Columns:**
- Date
- Payment Number
- PO Number
- Amount Paid
- Payment Method
- Reference
- Paid By
- Notes

**Summary:**
- Total Payments
- Payment Method Breakdown
- Average Payment Amount

---

### 6.3 Supplier Ledger
**Purpose:** Complete transaction history

**URL:** `/admin/reports/supplier/{id}/ledger`

**Filters:**
- Date Range

**Columns:**
- Date
- Transaction Type
- Reference Number
- Description
- Debit (Payment/Return)
- Credit (Purchase)
- Balance

**Summary:**
- Opening Balance
- Total Debits
- Total Credits
- Closing Balance

---

## 7. Profit & Loss Report

### 7.1 Comprehensive P&L Statement
**Purpose:** Financial performance analysis

**Filters:**
- Date Range (Monthly/Quarterly/Yearly/Custom)
- Warehouse
- Comparison Mode (Period vs Period)

**Structure:**

#### Revenue Section
```
Sales Revenue
  Gross Sales                    XXX,XXX
  Less: Sales Returns           (XX,XXX)
  Less: Sales Discounts         (XX,XXX)
  ─────────────────────────────────────
  Net Sales Revenue              XXX,XXX
```

#### Cost of Goods Sold (COGS)
```
Opening Inventory               XX,XXX
Add: Purchases                 XXX,XXX
Less: Purchase Returns         (XX,XXX)
Less: Purchase Discounts       (XX,XXX)
Less: Closing Inventory        (XX,XXX)
─────────────────────────────────────
Cost of Goods Sold             XXX,XXX
```

#### Gross Profit
```
Net Sales Revenue              XXX,XXX
Less: Cost of Goods Sold      (XXX,XXX)
─────────────────────────────────────
Gross Profit                   XX,XXX
Gross Profit Margin               XX%
```

#### Operating Expenses
```
(Future: when expense management added)
Salaries & Wages               XX,XXX
Rent                           XX,XXX
Utilities                      XX,XXX
Transportation                 XX,XXX
Other Expenses                 XX,XXX
─────────────────────────────────────
Total Operating Expenses       XX,XXX
```

#### Net Profit
```
Gross Profit                   XX,XXX
Less: Operating Expenses      (XX,XXX)
─────────────────────────────────────
Net Profit                     XX,XXX
Net Profit Margin                 XX%
```

**Key Metrics:**
- Gross Profit Margin = (Gross Profit / Net Sales) × 100
- Net Profit Margin = (Net Profit / Net Sales) × 100
- Inventory Turnover Ratio = COGS / Average Inventory
- Average Sale Value
- Average Purchase Value

**Comparison View:**
- Side-by-side period comparison
- Growth % calculation
- Variance analysis
- Trend indicators (↑ ↓ →)

**Visual Elements:**
- Revenue vs Cost chart
- Profit trend line graph
- Expense breakdown pie chart
- Monthly comparison bar chart

---

## Technical Implementation

### Controller Structure
```
ReportsController
├── Sales Reports
│   ├── salesIndex()
│   ├── dailySales()
│   ├── productWiseSales()
│   ├── customerWiseSales()
│   └── warehouseSales()
├── Purchase Reports
│   ├── purchaseIndex()
│   ├── purchases()
│   ├── supplierWisePurchases()
│   └── productWisePurchases()
├── Invoice Report
│   └── invoiceReport()
├── Inventory Reports
│   ├── inventoryIndex()
│   ├── currentStock()
│   ├── warehouseStock()
│   └── stockMovements()
├── Customer Reports
│   ├── customerIndex()
│   ├── customerOutstanding()
│   ├── customerPaymentHistory()
│   └── customerLedger()
├── Supplier Reports
│   ├── supplierIndex()
│   ├── supplierOutstanding()
│   ├── supplierPaymentHistory()
│   └── supplierLedger()
└── Financial Reports
    └── profitLoss()
```

### Service Structure
```
ReportService
├── getSalesReport()
├── getProductWiseSalesReport()
├── getCustomerWiseSalesReport()
├── getWarehouseSalesReport()
├── getPurchaseReport()
├── getSupplierWisePurchaseReport()
├── getProductWisePurchaseReport()
├── getInvoiceReport()
├── getInventoryReport()
├── getWarehouseStockReport()
├── getStockMovementReport()
├── getCustomerOutstandingReport()
├── getCustomerPaymentHistory()
├── getCustomerLedger()
├── getSupplierOutstandingReport()
├── getSupplierPaymentHistory()
├── getSupplierLedger()
└── getProfitLossReport()
```

### Routes Structure
```
/admin/reports/
├── sales/
│   ├── / (index)
│   ├── /daily
│   ├── /product-wise
│   ├── /customer-wise
│   └── /warehouse-wise
├── purchase/
│   ├── / (index)
│   ├── /purchases
│   ├── /supplier-wise
│   └── /product-wise
├── invoices
├── inventory/
│   ├── / (index)
│   ├── /current-stock
│   ├── /warehouse-stock
│   └── /stock-movements
├── customer/
│   ├── / (index)
│   ├── /outstanding
│   ├── /{id}/payment-history
│   └── /{id}/ledger
├── supplier/
│   ├── / (index)
│   ├── /outstanding
│   ├── /{id}/payment-history
│   └── /{id}/ledger
└── profit-loss
```

### View Structure
```
resources/views/admin/reports/
├── sales/
│   ├── index.blade.php
│   ├── daily.blade.php
│   ├── product-wise.blade.php
│   ├── customer-wise.blade.php
│   └── warehouse-wise.blade.php
├── purchase/
│   ├── index.blade.php
│   ├── purchases.blade.php
│   ├── supplier-wise.blade.php
│   └── product-wise.blade.php
├── invoices.blade.php
├── inventory/
│   ├── index.blade.php
│   ├── current-stock.blade.php
│   ├── warehouse-stock.blade.php
│   └── stock-movements.blade.php
├── customer/
│   ├── index.blade.php
│   ├── outstanding.blade.php
│   ├── payment-history.blade.php
│   └── ledger.blade.php
├── supplier/
│   ├── index.blade.php
│   ├── outstanding.blade.php
│   ├── payment-history.blade.php
│   └── ledger.blade.php
└── profit-loss.blade.php
```

---

## Common Features for All Reports

### Filter Panel
- Collapsible filter section at top
- Apply Filters button
- Reset Filters button
- Remember last used filters (session)

### Data Table Features
- Pagination (15, 25, 50, 100, All)
- Search/Filter within results
- Sort by column (↑↓)
- Column show/hide toggle
- Responsive design

### Export Options
- **PDF:** Formatted with company header, page numbers
- **Excel:** All data with formulas
- **Print:** Print-friendly view with CSS

### Summary Cards
- Displayed above table
- Key metrics with icons
- Color-coded indicators
- Comparison with previous period

### Date Range Presets
- Today
- Yesterday
- This Week
- Last Week
- This Month
- Last Month
- This Quarter
- Last Quarter
- This Year
- Last Year
- Custom Range

### Permission System
- `reports.view` - View all reports
- `reports.export` - Export reports
- Warehouse-level filtering based on user permissions

---

## UI/UX Guidelines

### Color Scheme
- **Positive Values:** Green (#198754)
- **Negative Values:** Red (#dc3545)
- **Neutral:** Blue (#0d6efd)
- **Warning:** Orange (#fd7e14)

### Icons (Bootstrap Icons)
- Sales: `bi-graph-up`
- Purchase: `bi-bag-check`
- Invoice: `bi-file-earmark-text`
- Inventory: `bi-box-seam`
- Customer: `bi-people`
- Supplier: `bi-truck`
- Profit/Loss: `bi-cash-coin`
- Export PDF: `bi-file-pdf`
- Export Excel: `bi-file-excel`
- Print: `bi-printer`

### Responsive Breakpoints
- Mobile: < 768px (stacked cards)
- Tablet: 768px - 1024px (2 columns)
- Desktop: > 1024px (full layout)

---

## Performance Considerations

1. **Indexing:** Ensure proper database indexes on date columns, foreign keys
2. **Caching:** Cache report results for 5 minutes
3. **Pagination:** Always paginate large datasets
4. **Lazy Loading:** Load charts/graphs after main data
5. **Query Optimization:** Use eager loading for relationships
6. **Background Jobs:** Heavy reports (>10k records) use queue

---

## Future Enhancements

1. **Scheduled Reports:** Email reports daily/weekly/monthly
2. **Custom Report Builder:** Drag-drop columns
3. **Dashboard Widgets:** Add reports to dashboard
4. **Comparison Mode:** Compare two periods side-by-side
5. **Charts & Graphs:** Visual representation
6. **Expense Management:** Full expense tracking module
7. **Multi-Currency:** Support for multiple currencies
8. **Advanced Filters:** Save filter presets

---

## Testing Checklist

- [ ] All filters work correctly
- [ ] Pagination works
- [ ] Export to PDF works
- [ ] Export to Excel works
- [ ] Print view is properly formatted
- [ ] Calculations are accurate
- [ ] Summary totals match detail rows
- [ ] Permission checks work
- [ ] Warehouse filtering works
- [ ] Date range works correctly
- [ ] Responsive on mobile
- [ ] Performance is acceptable (<3 sec)
- [ ] Empty state handling
- [ ] Error handling

---

## Priority Implementation Order

1. **Phase 1 (High Priority):**
   - Sales Report (Daily)
   - Purchase Report (Daily)
   - Current Stock Report
   - Customer Outstanding
   - Supplier Outstanding

2. **Phase 2 (Medium Priority):**
   - Invoice Report
   - Product-wise Sales
   - Product-wise Purchase
   - Stock Movements
   - Profit & Loss

3. **Phase 3 (Low Priority):**
   - Customer-wise Sales
   - Supplier-wise Purchase
   - Warehouse-wise Reports
   - Customer/Supplier Ledgers
   - Advanced Features

---

**End of Specification**
