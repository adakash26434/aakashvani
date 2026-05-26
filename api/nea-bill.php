<?php
/**
 * NEA Electricity Bill Info API
 * Nepal Electricity Authority bill information and rates
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$scNumber = $_GET['sc'] ?? null; // SC Number
$customerId = $_GET['customer'] ?? null; // Customer ID

try {
    // NEA doesn't have public API, so we provide bill info and rates
    $data = [
        'ok' => true,
        'rates' => getNEARates(),
        'info' => getNEAInfo(),
        'payment_methods' => getNEAPaymentMethods(),
        'customer_bill' => $scNumber ? getSampleBill($scNumber) : null,
    ];
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getNEARates(): array {
    return [
        'residential' => [
            'name' => 'आवासीय (Residential)',
            'unit_rate' => [
                ['min' => 0, 'max' => 20, 'rate' => 8.00],
                ['min' => 21, 'max' => 30, 'rate' => 9.50],
                ['min' => 31, 'max' => 50, 'rate' => 10.00],
                ['min' => 51, 'max' => 100, 'rate' => 11.50],
                ['min' => 101, 'max' => 150, 'rate' => 12.50],
                ['min' => 151, 'max' => 250, 'rate' => 13.50],
                ['min' => 251, 'max' => null, 'rate' => 14.50],
            ],
            'service_charge' => 50,
            'demand_charge' => 30,
        ],
        'commercial' => [
            'name' => 'व्यावसायिक (Commercial)',
            'unit_rate' => [
                ['min' => 0, 'max' => 100, 'rate' => 12.50],
                ['min' => 101, 'max' => 200, 'rate' => 13.50],
                ['min' => 201, 'max' => null, 'rate' => 14.50],
            ],
            'service_charge' => 80,
            'demand_charge' => 60,
        ],
        'industrial' => [
            'name' => 'औद्योगिक (Industrial)',
            'unit_rate' => [
                ['min' => 0, 'max' => null, 'rate' => 11.50],
            ],
            'service_charge' => 100,
            'demand_charge' => 80,
        ],
    ];
}

function getNEAInfo(): array {
    return [
        'name' => 'नेपाल विद्युत प्राधिकरण',
        'name_en' => 'Nepal Electricity Authority',
        'hotline' => '166001-44-444',
        'email' => 'info@nea.org.np',
        'website' => 'https://www.nea.org.np',
        'headquarters' => 'Kathmandu',
        'distribution_centers' => [
            'Kathmandu', 'Pokhara', 'Butwal', 'Biratnagar', 'Nepalgunj',
            'Dharan', 'Hetauda', 'Bharatpur', 'Janakpur', 'Mahendranagar',
        ],
    ];
}

function getNEAPaymentMethods(): array {
    return [
        ['name' => 'eSewa', 'type' => 'Digital Wallet'],
        ['name' => 'Khalti', 'type' => 'Digital Wallet'],
        ['name' => 'IME Pay', 'type' => 'Digital Wallet'],
        ['name' => 'Nepal Investment Bank', 'type' => 'Bank'],
        ['name' => 'Nabil Bank', 'type' => 'Bank'],
        ['name' => 'Global IME Bank', 'type' => 'Bank'],
        ['name' => 'NIC Asia Bank', 'type' => 'Bank'],
        ['name' => 'NEA Counter', 'type' => 'Counter'],
    ];
}

function getSampleBill(string $scNumber): array {
    // Sample bill data (actual bills require NEA API access)
    $units = rand(50, 300);
    $rate = 11.50;
    $energyCharge = $units * $rate;
    $serviceCharge = 50;
    $demandCharge = 30;
    $total = $energyCharge + $serviceCharge + $demandCharge;
    
    return [
        'sc_number' => $scNumber,
        'customer_name' => 'Sample Customer',
        'customer_address' => 'Kathmandu',
        'billing_month' => date('F Y'),
        'due_date' => date('Y-m-d', strtotime('+15 days')),
        'units_consumed' => $units,
        'energy_charge' => $energyCharge,
        'service_charge' => $serviceCharge,
        'demand_charge' => $demandCharge,
        'total_amount' => $total,
        'status' => 'Unpaid',
        'note' => 'This is sample data. Actual bill requires SC number verification with NEA.',
    ];
}
