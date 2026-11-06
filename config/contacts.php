<?php

return [
    'customers' => [
        ['id' => 'C-001', 'name' => 'ABC Hospital', 'initials' => 'AH', 'type' => 'Hospital', 'phone' => '+880 1711 123456', 'email' => 'info@abchospital.com', 'due' => 250000, 'color' => 'blue', 'address' => '123, Kakrail, Dhaka-1000, Bangladesh', 'contact' => 'Dr. Rahman', 'designation' => 'Procurement Manager', 'orders' => 12, 'total' => 850000],
        ['id' => 'C-002', 'name' => 'MedTech Ltd.', 'initials' => 'MC', 'type' => 'Dealer', 'phone' => '+880 1812 654321', 'email' => 'sales@medtech.com', 'due' => 175000, 'color' => 'purple', 'orders' => 9, 'total' => 675000],
        ['id' => 'C-003', 'name' => 'City Hospital', 'initials' => 'CH', 'type' => 'Hospital', 'phone' => '+880 1917 888888', 'email' => 'procurement@cityhospital.com', 'due' => 95000, 'color' => 'purple', 'orders' => 8, 'total' => 520000],
        ['id' => 'C-004', 'name' => 'United Pharma', 'initials' => 'UH', 'type' => 'Pharmacy', 'phone' => '+880 1700 111222', 'email' => 'info@unitedpharma.com', 'due' => 120000, 'color' => 'blue', 'orders' => 7, 'total' => 480000],
        ['id' => 'C-005', 'name' => 'Dhaka Medical Center', 'initials' => 'DR', 'type' => 'Hospital', 'phone' => '+880 1888 777666', 'email' => 'purchase@dhakamedical.com', 'due' => 65000, 'color' => 'purple', 'orders' => 6, 'total' => 340000],
        ['id' => 'C-006', 'name' => 'Surgical Point', 'initials' => 'SP', 'type' => 'Dealer', 'phone' => '+880 1912 345678', 'email' => 'contact@surgicalpoint.com', 'due' => 40000, 'color' => 'amber', 'orders' => 4, 'total' => 220000],
        ['id' => 'C-007', 'name' => 'Royal Health Care', 'initials' => 'RG', 'type' => 'Clinic', 'phone' => '+880 1715 999888', 'email' => 'info@royalhealthcare.com', 'due' => 0, 'color' => 'green', 'orders' => 3, 'total' => 180000],
        ['id' => 'C-008', 'name' => 'New Modern Hospital', 'initials' => 'NM', 'type' => 'Hospital', 'phone' => '+880 1820 333444', 'email' => 'admin@newmodern.com', 'due' => 310000, 'color' => 'green', 'orders' => 5, 'total' => 290000],
    ],
    'suppliers' => [
        ['id' => 'S-001', 'name' => 'Philips Bangladesh', 'initials' => 'PB', 'type' => 'Manufacturer', 'phone' => '+880 1711 123456', 'email' => '', 'due' => 175000, 'color' => 'blue', 'contact' => 'Mr. Rahman', 'orders' => 10, 'total' => 580000],
        ['id' => 'S-002', 'name' => 'Siemens Healthineers', 'initials' => 'SH', 'type' => 'Manufacturer', 'phone' => '+49 30 123456', 'email' => '', 'due' => 210000, 'color' => 'purple', 'contact' => 'Ms. Julia Becker', 'orders' => 8, 'total' => 420000],
        ['id' => 'S-003', 'name' => 'Mindray', 'initials' => 'MY', 'type' => 'Manufacturer', 'phone' => '+86 755 8888 9999', 'email' => '', 'due' => 95000, 'color' => 'purple', 'contact' => 'Mr. Chen Wei', 'orders' => 7, 'total' => 375000],
        ['id' => 'S-004', 'name' => 'GE Healthcare', 'initials' => 'GH', 'type' => 'Distributor', 'phone' => '+1 305 555 0199', 'email' => '', 'due' => 150000, 'color' => 'blue', 'contact' => 'Mr. James Lee', 'orders' => 6, 'total' => 290000],
        ['id' => 'S-005', 'name' => 'MedTech Ltd.', 'initials' => 'ML', 'type' => 'Local Supplier', 'phone' => '+1 202 555 0188', 'email' => 'sales@medtech.com', 'due' => 125000, 'color' => 'blue', 'contact' => 'Mr. David Smith', 'orders' => 5, 'total' => 245000],
    ],
    'activities' => [
        ['Invoice #SI-0045 created', 'ABC Hospital · ৳ 1,25,000', '2025-04-15', '11:30 AM', 'clipboard', 'green'],
        ['Payment received', 'ABC Hospital · ৳ 1,00,000', '2025-04-12', '03:15 PM', 'money', 'green'],
        ['New purchase order', 'MedTech Ltd. · PO-0087', '2025-04-10', '10:45 AM', 'cart', 'orange'],
        ['Supplier added', 'Philips Bangladesh', '2025-04-08', '02:20 PM', 'users', 'blue'],
    ],
];
