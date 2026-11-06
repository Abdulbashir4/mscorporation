<?php

return [
    'vouchers' => [
        ['15 Apr 2025', 'SI-0045', 'Sales Invoice', 'ABC Hospital', 'Medical Equipment', '৳ 1,25,000', 'Posted'],
        ['14 Apr 2025', 'PP-0032', 'Purchase Payment', 'MedTech Ltd.', 'Ventilator', '৳ 3,50,000', 'Posted'],
        ['12 Apr 2025', 'CR-0018', 'Customer Receipt', 'City Hospital', 'Payment Received', '৳ 75,000', 'Posted'],
        ['10 Apr 2025', 'SP-0021', 'Supplier Payment', 'MedTech Ltd.', 'Advance Payment', '৳ 2,10,000', 'Posted'],
        ['09 Apr 2025', 'JE-004', 'Journal Entry', '—', 'Stock Adjustment', '৳ 45,000', 'Posted'],
    ],
    'bank' => [
        ['14 Apr 2025', 'BT-0032', 'Bank Transfer', 'MedTech Ltd.', 'Purchase Payment', '৳ 3,50,000', 'Posted'],
        ['12 Apr 2025', 'BT-0031', 'Bank Deposit', 'City Hospital', 'Customer Receipt', '৳ 75,000', 'Posted'],
        ['10 Apr 2025', 'BT-0030', 'Bank Transfer', 'MedTech Ltd.', 'Advance Payment', '৳ 2,10,000', 'Posted'],
    ],
    'journals' => [
        ['09 Apr 2025', 'JE-004', 'Journal Entry', '—', 'Stock Adjustment', '৳ 45,000', 'Posted'],
    ],
    'reconcile' => [
        ['14 Apr 2025', 'BT-0032', 'Bank Transfer', 'MedTech Ltd.', 'Purchase Payment', '৳ 3,50,000', 'Matched'],
        ['12 Apr 2025', 'BT-0031', 'Bank Deposit', 'City Hospital', 'Customer Receipt', '৳ 75,000', 'Matched'],
    ],
    'accounts' => [
        ['Cash in Hand', '৳ 75,000', 'money', 'blue'],
        ['Bank Account', '৳ 6,45,000', 'bank', 'blue'],
        ['Accounts Receivable', '৳ 3,20,000', 'clipboard', 'blue'],
        ['Accounts Payable', '৳ 2,10,000', 'money', 'red'],
        ['Stock Adjustment', '৳ 45,000', 'upload', 'blue'],
    ],
    'transactions' => [
        ['Sale Invoice #SI-0045', 'ABC Hospital', '৳ 1,25,000', '2025-04-15', 'clipboard', 'green'],
        ['Purchase Payment #PP-0032', 'Philips (Ventilator)', '৳ 3,50,000', '2025-04-14', 'cart', 'blue'],
        ['Customer Receipt #CR-0018', 'City Hospital', '৳ 75,000', '2025-04-12', 'download', 'orange'],
        ['Supplier Payment #SP-0021', 'MedTech Ltd.', '৳ 2,10,000', '2025-04-10', 'clipboard', 'green'],
        ['Journal Entry #JE-004', 'Adjustment', '৳ 45,000', '2025-04-09', 'calculator', 'purple'],
    ],
];
