<?php

return [
    'orders' => [
        ['PO-0087', 'MedTech Ltd.', '15-04-2025', 'Pending', '৳ 1,25,000'],
        ['PO-0086', 'Philips Bangladesh', '14-04-2025', 'Received', '৳ 3,50,000'],
        ['PO-0085', 'Siemens Healthineers', '12-04-2025', 'Partial', '৳ 2,80,000'],
        ['PO-0084', 'GE Healthcare', '10-04-2025', 'Received', '৳ 4,20,000'],
        ['PO-0083', 'Mindray', '09-04-2025', 'Confirmed', '৳ 1,75,000'],
    ],
    'suppliers' => [
        ['MedTech Ltd.', 'USA', 'Mr. David Smith', '+1 202 555 0188', '৳ 12,50,000', 'Active'],
        ['Philips Bangladesh', 'Netherlands', 'Mr. Rahman', '+880 1711 123456', '৳ 8,20,000', 'Active'],
        ['Siemens Healthineers', 'Germany', 'Ms. Julia Becker', '+49 30 123456', '৳ 6,75,000', 'Active'],
        ['GE Healthcare', 'USA', 'Mr. James Lee', '+1 305 555 0199', '৳ 5,40,000', 'Active'],
        ['Mindray', 'China', 'Mr. Chen Wei', '+86 755 8888 9999', '৳ 4,10,000', 'Active'],
    ],
    'payments' => [
        ['MedTech Ltd.', 'SI-0043', '18-04-2025', '৳ 1,25,000'],
        ['Philips Bangladesh', 'SI-0042', '20-04-2025', '৳ 75,000'],
        ['Siemens Healthineers', 'SI-0041', '22-04-2025', '৳ 2,10,000'],
        ['GE Healthcare', 'SI-0039', '25-04-2025', '৳ 1,50,000'],
        ['Mindray', 'SI-0038', '28-04-2025', '৳ 95,000'],
    ],
    'receipts' => [
        ['GR-0061', 'PO-0086', 'Philips Bangladesh', '16-04-2025', '12', 'Received'],
        ['GR-0060', 'PO-0084', 'GE Healthcare', '13-04-2025', '8', 'Received'],
        ['GR-0059', 'PO-0083', 'Mindray', '10-04-2025', '6', 'Received'],
        ['GR-0058', 'PO-0082', 'Siemens Healthineers', '08-04-2025', '4', 'Partial'],
    ],
    'products' => [
        ['Patient Monitor (PM-100)', '12', '৳ 2,50,000'],
        ['Ventilator (V-500)', '8', '৳ 1,80,000'],
        ['Ultrasound Machine (USG-20)', '6', '৳ 1,60,000'],
        ['Infusion Pump (IP-10)', '10', '৳ 1,20,000'],
        ['ECG Machine (ECG-12)', '7', '৳ 1,10,000'],
    ],
];
