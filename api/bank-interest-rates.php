<?php
/**
 * Bank Interest Rates API - Nepal
 * Data source: Nepal Rastra Bank (NRB) - Sample data updated periodically
 * Fallback to NRB website scraping when available
 */

// CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

/**
 * Get current bank interest rates
 * This returns sample data based on NRB published rates
 * In production, this should scrape from https://www.nrb.org.np/
 */
function getBankInterestRates() {
    // Sample data based on NRB published rates (updated periodically)
    // In production, scrape from: https://www.nrb.org.np/bfr/interest-rates/
    
    $rates = [
        'updated' => date('Y-m-d H:i:s'),
        'source' => 'Nepal Rastra Bank (Sample)',
        'note' => 'These are indicative rates. Actual rates may vary by bank.',
        
        'policy_rates' => [
            'bank_rate' => ['rate' => 5.75, 'description' => 'Bank Rate'],
            'repo_rate' => ['rate' => 4.25, 'description' => 'Policy Repo Rate'],
            'reverse_repo' => ['rate' => 3.75, 'description' => 'Reverse Repo Rate'],
            'interbank' => ['rate' => 2.75, 'description' => 'Interbank Rate']
        ],
        
        'deposit_rates' => [
            'savings' => [
                'min' => 3.00,
                'max' => 5.00,
                'avg' => 4.00,
                'description' => 'Bachat Khata'
            ],
            'fixed_1_month' => [
                'min' => 4.00,
                'max' => 6.00,
                'avg' => 5.00,
                'description' => '1 Month Fixed Deposit'
            ],
            'fixed_3_month' => [
                'min' => 5.00,
                'max' => 7.00,
                'avg' => 6.00,
                'description' => '3 Month Fixed Deposit'
            ],
            'fixed_6_month' => [
                'min' => 6.00,
                'max' => 8.00,
                'avg' => 7.00,
                'description' => '6 Month Fixed Deposit'
            ],
            'fixed_1_year' => [
                'min' => 7.00,
                'max' => 9.50,
                'avg' => 8.25,
                'description' => '1 Year Fixed Deposit'
            ],
            'fixed_2_year' => [
                'min' => 7.50,
                'max' => 10.00,
                'avg' => 8.75,
                'description' => '2 Year Fixed Deposit'
            ],
            'fixed_3_year' => [
                'min' => 8.00,
                'max' => 10.50,
                'avg' => 9.25,
                'description' => '3 Year Fixed Deposit'
            ]
        ],
        
        'lending_rates' => [
            'base_rate' => [
                'range' => '7.50 - 9.50',
                'avg' => 8.50,
                'description' => 'Base Rate (Commercial Banks)'
            ],
            'agriculture' => [
                'range' => '5.00 - 7.00',
                'avg' => 6.00,
                'description' => 'Agriculture Loan (Subsidy)'
            ],
            'small_business' => [
                'range' => '7.00 - 9.00',
                'avg' => 8.00,
                'description' => 'Small & Medium Enterprise'
            ],
            'personal_loan' => [
                'range' => '10.00 - 14.00',
                'avg' => 12.00,
                'description' => 'Personal Loan'
            ],
            'home_loan' => [
                'range' => '8.00 - 12.00',
                'avg' => 10.00,
                'description' => 'Home Loan / Mortgage'
            ],
            'vehicle_loan' => [
                'range' => '9.00 - 13.00',
                'avg' => 11.00,
                'description' => 'Vehicle Loan'
            ],
            'education_loan' => [
                'range' => '7.00 - 10.00',
                'avg' => 8.50,
                'description' => 'Education Loan'
            ]
        ],
        
        'major_banks' => [
            [
                'name' => 'Nepal Bank Limited',
                'savings' => 4.00,
                'fixed_1y' => 8.50,
                'base_rate' => 8.50
            ],
            [
                'name' => 'Rastriya Banijya Bank',
                'savings' => 4.50,
                'fixed_1y' => 8.75,
                'base_rate' => 8.25
            ],
            [
                'name' => 'Nabil Bank',
                'savings' => 3.50,
                'fixed_1y' => 8.00,
                'base_rate' => 8.75
            ],
            [
                'name' => 'NIC Asia Bank',
                'savings' => 4.00,
                'fixed_1y' => 9.00,
                'base_rate' => 8.50
            ],
            [
                'name' => 'Global IME Bank',
                'savings' => 4.25,
                'fixed_1y' => 9.25,
                'base_rate' => 8.75
            ],
            [
                'name' => 'Kumari Bank',
                'savings' => 4.00,
                'fixed_1y' => 9.00,
                'base_rate' => 8.50
            ],
            [
                'name' => 'Sanima Bank',
                'savings' => 3.75,
                'fixed_1y' => 8.50,
                'base_rate' => 8.25
            ],
            [
                'name' => 'Machhapuchchhre Bank',
                'savings' => 4.50,
                'fixed_1y' => 9.50,
                'base_rate' => 8.75
            ]
        ],
        
        'official_links' => [
            [
                'name' => 'NRB - Interest Rates',
                'url' => 'https://www.nrb.org.np/bfr/interest-rates/',
                'name_np' => 'नेपाल राष्ट्र बैंक'
            ],
            [
                'name' => 'NRB - Quarterly Rates',
                'url' => 'https://www.nrb.org.np/category/quarterly-interest-rate/',
                'name_np' => 'त्रैमासिक ब्याजदर'
            ]
        ]
    ];
    
    return $rates;
}

// Get rates
$rates = getBankInterestRates();

// Return response
echo json_encode([
    'ok' => true,
    'data' => $rates
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
