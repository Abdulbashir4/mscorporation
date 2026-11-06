<?php

return [
    'modules' => [
        ['Inventory', 'box', 'Manage products, stock, serial numbers, expiry and warehouse.'],
        ['Purchase', 'cart', 'Create purchase orders, manage suppliers and track payments.'],
        ['Sales', 'tag', 'Create quotation, sales orders, invoices and manage customers.'],
        ['Customer / Supplier', 'users', 'Maintain customer and supplier information, credit and due.'],
        ['Accounting', 'calculator', 'Track cash, bank, ledger, receivables, payables and reports.'],
        ['Reports', 'chart', 'View financial, stock, sales and custom reports.'],
    ],
    'stats' => [
        ['Total Sales', 'This Month', '12,50,000', '18', 'cart', 'blue'],
        ['Total Purchase', 'This Month', '9,75,000', '12', 'cart', 'teal'],
        ['Total Stock Value', '', '28,40,000', '5', 'box', 'gold'],
        ['Receivables', 'Customers', '6,20,000', '10', 'users', 'teal'],
        ['Payables', 'Suppliers', '4,10,000', '8', 'money', 'gold'],
    ],
    'stock' => [
        ['Patient Monitor (PM-100)', 2, 'Low Stock', 'red'],
        ['Ventilator (V-500)', 1, 'Low Stock', 'red'],
        ['ECG Machine (ECG-12)', 5, 'Reorder Soon', 'amber'],
        ['Infusion Pump (IP-10)', 3, 'Reorder Soon', 'amber'],
        ['Ultrasound Machine (USG-20)', 12, 'In Stock', 'green'],
    ],
    'activities' => [
        ['New Sales Invoice #SI-0045', 'ABC Hospital', '2 hours ago', 'clipboard', 'green'],
        ['Purchase Order #PO-0087', 'MedTech Ltd.', '4 hours ago', 'cart', 'blue'],
        ['Stock Received #GR-0032', 'Philips (Ventilator)', '6 hours ago', 'box', 'green'],
        ['Payment Received', 'ABC Hospital - ৳ 2,50,000', 'Yesterday', 'money', 'green'],
        ['New Customer Added', 'Dhaka Medical Center', 'Yesterday', 'users', 'blue'],
    ],
    'purchases' => [
        ['PO-0087', 'MedTech Ltd.', '2025-04-15', 'Pending', '1,25,000'],
        ['PO-0086', 'Philips Bangladesh', '2025-04-12', 'Received', '3,50,000'],
        ['PO-0085', 'Siemens Healthineers', '2025-04-08', 'Partial', '2,80,000'],
        ['PO-0084', 'GE Healthcare', '2025-04-05', 'Received', '4,20,000'],
        ['PO-0083', 'Mindray', '2025-04-02', 'Confirmed', '1,75,000'],
    ],
    'invoices' => [
        ['SI-0045', 'ABC Hospital', '2025-04-15', '2,50,000', 'Paid'],
        ['SI-0044', 'Dhaka Medical Center', '2025-04-13', '1,75,000', 'Paid'],
        ['SI-0043', 'City Hospital', '2025-04-10', '3,20,000', 'Pending'],
        ['SI-0042', 'United Pharma', '2025-04-08', '1,10,000', 'Paid'],
        ['SI-0041', 'Popular Diagnostic', '2025-04-05', '95,000', 'Partially Paid'],
    ],
    'finances' => [
        ['Total Income', '12,50,000', '18', 'trend', 'green'],
        ['Total Expense', '8,20,000', '10', 'shield', 'red'],
        ['Net Profit', '4,30,000', '22', 'profit', 'green'],
        ['Cash & Bank Balance', '5,75,000', '15', 'money', 'sky'],
    ],
];
