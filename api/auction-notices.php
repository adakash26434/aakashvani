<?php
/**
 * Bank/Sahakari/Government Auction Notices API
 * Data source: Nepal Rastra Bank, Commercial Banks, Sahakari Sansthan
 * Returns auction notices for property, vehicles, loans, etc.
 */

// CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=1800'); // 30 min cache

/**
 * Get auction notices
 * This returns sample data based on typical auction notices
 * In production, scrape from bank websites and NRB
 */
function getAuctionNotices() {
    // Sample auction notices - In production, fetch from:
    // - NRB: https://www.nrb.org.np/auction-notices/
    // - Nabil: https://www.nabilbank.com/auction
    // - Everest: https://everestbankltd.com/auction-notices/
    // - Nepal Bank: https://www.nepalbank.com.np/notice-board
    
    $notices = [
        [
            'id' => 'AUCTION001',
            'title' => 'काठमाडौं मा रहेको जग्गा तथा भवन लिलामी सूचना',
            'institution' => 'नेपाल राष्ट्र बैंक',
            'type' => 'property',
            'property_type' => 'जग्गा र भवन',
            'location' => 'काठमाडौं',
            'auction_date' => '2083/02/15',
            'auction_time' => '११:०० AM',
            'minimum_bid' => 'रु २,५०,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/02/10',
            'contact' => '०१-४४१३६३२६',
            'source_url' => 'https://www.nrb.org.np/auction-notices/',
            'description' => 'नेपाल राष्ट्र बैंकले काठमाडौंमा रहेको जग्गा तथा भवन लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION002',
            'title' => 'गाडी लिलामी सूचना - नाबिल बैंक',
            'institution' => 'नाबिल बैंक लिमिटेड',
            'type' => 'vehicle',
            'property_type' => 'गाडी',
            'location' => 'ललितपुर',
            'auction_date' => '2083/02/20',
            'auction_time' => '२:०० PM',
            'minimum_bid' => 'रु १५,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/02/18',
            'contact' => '०१-४४२२४०५०',
            'source_url' => 'https://www.nabilbank.com/auction',
            'description' => 'नाबिल बैंकले डिफल्ट ऋणमा रहेको गाडी लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION003',
            'title' => 'श्री लुम्बिनी सहकारी लिमिटेड - जग्गा लिलामी',
            'institution' => 'लुम्बिनी सहकारी लिमिटेड',
            'type' => 'property',
            'property_type' => 'जग्गा',
            'location' => 'भक्तपुर',
            'auction_date' => '2083/02/25',
            'auction_time' => '१२:०० PM',
            'minimum_bid' => 'रु ८०,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/02/22',
            'contact' => '०१-६६१४२३००',
            'source_url' => '#',
            'description' => 'लुम्बिनी सहकारीले भक्तपुरमा रहेको जग्गा लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION004',
            'title' => 'एभरेस्ट बैंक - भवन लिलामी सूचना',
            'institution' => 'एभरेस्ट बैंक लिमिटेड',
            'type' => 'property',
            'property_type' => 'भवन',
            'location' => 'पोखरा',
            'auction_date' => '2083/03/01',
            'auction_time' => '११:३० AM',
            'minimum_bid' => 'रु ३५,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/02/28',
            'contact' => '०६१-५२०४३२',
            'source_url' => 'https://everestbankltd.com/auction-notices/',
            'description' => 'एभरेस्ट बैंकले पोखरामा रहेको भवन लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION005',
            'title' => 'नेपाल बैंक - गोल्ड लिलामी सूचना',
            'institution' => 'नेपाल बैंक लिमिटेड',
            'type' => 'gold',
            'property_type' => 'सुन',
            'location' => 'काठमाडौं',
            'auction_date' => '2083/03/05',
            'auction_time' => '१:०० PM',
            'minimum_bid' => 'रु ५,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/03/03',
            'contact' => '०१-४२१३६३२',
            'source_url' => 'https://www.nepalbank.com.np/notice-board',
            'description' => 'नेपाल बैंकले डिफल्ट ऋणमा रहेको सुन लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION006',
            'title' => 'सरकारी कार्यालय भवन लिलामी - श्री ५ को सरकार',
            'institution' => 'सरकारी सेवा आयोग',
            'type' => 'government',
            'property_type' => 'भवन',
            'location' => 'काठमाडौं',
            'auction_date' => '2083/03/10',
            'auction_time' => '१०:०० AM',
            'minimum_bid' => 'रु ५०,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/03/08',
            'contact' => '०१-४४१३६३२६',
            'source_url' => '#',
            'description' => 'सरकारी सेवा आयोगले काठमाडौंमा रहेको सरकारी भवन लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION007',
            'title' => 'कुमारी सहकारी - गाडी लिलामी',
            'institution' => 'कुमारी सहकारी बैंक',
            'type' => 'vehicle',
            'property_type' => 'गाडी',
            'location' => 'बिराटनगर',
            'auction_date' => '2083/03/15',
            'auction_time' => '३:०० PM',
            'minimum_bid' => 'रु १२,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/03/12',
            'contact' => '०२१-५२१४३२',
            'source_url' => '#',
            'description' => 'कुमारी सहकारी बैंकले बिराटनगरमा रहेको गाडी लिलामीमा राख्ने सूचना दिएको छ।'
        ],
        [
            'id' => 'AUCTION008',
            'title' => 'NIC एशिया बैंक - जग्गा लिलामी',
            'institution' => 'NIC एशिया बैंक',
            'type' => 'property',
            'property_type' => 'जग्गा',
            'location' => 'चितवन',
            'auction_date' => '2083/03/20',
            'auction_time' => '११:०० AM',
            'minimum_bid' => 'रु ६०,००,०००',
            'status' => 'upcoming',
            'last_date_bid' => '2083/03/18',
            'contact' => '०५६-५२०४३२',
            'source_url' => '#',
            'description' => 'NIC एशिया बैंकले चितवनमा रहेको जग्गा लिलामीमा राख्ने सूचना दिएको छ।'
        ]
    ];
    
    return $notices;
}

// Get notices
$notices = getAuctionNotices();

// Filter by type if requested
$type = $_GET['type'] ?? '';
if ($type && in_array($type, ['property', 'vehicle', 'gold', 'government'])) {
    $notices = array_filter($notices, function($n) use ($type) {
        return $n['type'] === $type;
    });
    $notices = array_values($notices);
}

// Return response
echo json_encode([
    'ok' => true,
    'count' => count($notices),
    'data' => $notices,
    'source' => 'Sample Data - Update with real scraping',
    'note' => 'In production, scrape from NRB and bank websites'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
