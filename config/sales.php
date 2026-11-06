<?php

return [
    'invoices' => [
        ['SI-0045', '15 Apr 2025', 'ABC Hospital', '৳ 1,25,000', 'Paid'],
        ['SI-0044', '14 Apr 2025', 'Dhaka Medical Center', '৳ 3,50,000', 'Paid'],
        ['SI-0043', '12 Apr 2025', 'City Hospital', '৳ 75,000', 'Pending'],
        ['SI-0042', '10 Apr 2025', 'United Pharma', '৳ 2,10,000', 'Paid'],
        ['SI-0041', '09 Apr 2025', 'Popular Diagnostic', '৳ 95,000', 'Partially Paid'],
    ],
    'orders' => [
        ['SO-0025', '15 Apr 2025', 'ABC Hospital', '৳ 2,50,000', 'Confirmed'],
        ['SO-0024', '14 Apr 2025', 'Dhaka Medical Center', '৳ 1,80,000', 'Pending'],
        ['SO-0023', '12 Apr 2025', 'City Hospital', '৳ 1,60,000', 'Confirmed'],
    ],
    'quotations' => [
        ['QT-0018', '15 Apr 2025', 'ABC Hospital', '৳ 2,50,000', 'Sent'],
        ['QT-0017', '14 Apr 2025', 'United Pharma', '৳ 1,20,000', 'Accepted'],
        ['QT-0016', '12 Apr 2025', 'Popular Diagnostic', '৳ 1,10,000', 'Draft'],
    ],
    'customers' => ['ABC Hospital', 'Dhaka Medical Center', 'City Hospital', 'United Pharma', 'Popular Diagnostic'],
    'products' => [
        ['Patient Monitor (PM-100)', 250000], ['ECG Machine (ECG-12)', 75000],
        ['Ventilator (V-500)', 875000], ['Ultrasound Machine (USG-20)', 1250000],
        ['Infusion Pump (IP-10)', 95000],
    ],
    'top_products' => [
        ['1', 'Patient Monitor (PM-100)', '5', '৳ 2,50,000'],
        ['2', 'Ventilator (V-500)', '3', '৳ 1,80,000'],
        ['3', 'Ultrasound Machine (USG-20)', '2', '৳ 1,60,000'],
        ['4', 'Infusion Pump (IP-10)', '6', '৳ 1,20,000'],
        ['5', 'ECG Machine (ECG-12)', '4', '৳ 1,10,000'],
    ],
];
