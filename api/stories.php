<?php
/**
 * Nepali Stories API
 * Endpoint: /api/stories.php
 * Method: GET
 * 
 * Parameters:
 * - category: Filter by category (optional)
 * - limit: Number of stories to return (default: 20)
 * - offset: Pagination offset (default: 0)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

$category = $_GET['category'] ?? null;
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);

function getStories(?string $category = null, int $limit = 20, int $offset = 0): array {
    // Sample Nepali stories data
    $allStories = [
        [
            'id' => 1,
            'title' => 'एक बूढो र उसको बाघ',
            'title_en' => 'An Old Man and His Tiger',
            'category' => 'moral',
            'category_ne' => 'नैतिक कथा',
            'author' => 'लोक कथा',
            'author_en' => 'Folk Tale',
            'content' => 'एक समयको कुरा थियो, एक बूढो मानिस थियो। उसले एउटा बाघ पालन गर्थ्यो। बाघले उसको सबै काम गर्थ्यो। एक दिन बूढोले बाघलाई भन्यो, "म तिमीलाई जंगलमा छोड्छु।" बाघले भन्यो, "मलाई जंगल मन पर्छ।" बूढोले बाघलाई जंगलमा छोड्यो। बाघ खुसी भयो।',
            'content_en' => 'Once upon a time, there was an old man. He kept a tiger. The tiger did all his work. One day the old man said to the tiger, "I will leave you in the jungle." The tiger said, "I like the jungle." The old man left the tiger in the jungle. The tiger was happy.',
            'tags' => ['बाघ', 'बूढो', 'जंगल'],
            'tags_en' => ['tiger', 'old man', 'jungle'],
            'reading_time' => 5,
            'views' => 1250,
            'created_at' => '2024-01-15',
            'image_url' => 'https://images.unsplash.com/photo-1561731216-c3a4d99437d5?w=400',
        ],
        [
            'id' => 2,
            'title' => 'चित्रको रहस्य',
            'title_en' => 'The Mystery of the Picture',
            'category' => 'mystery',
            'category_ne' => 'रहस्य',
            'author' => 'रहस्य कथा',
            'author_en' => 'Mystery Story',
            'content' => 'एक चित्र थियो। त्यो चित्रमा एउटा रहस्य थियो। कसैले पनि त्यो रहस्य बुझ्न सकेन। एक दिन एक बालकले त्यो चित्र हेर्यो। उसले रहस्य बुझ्यो। त्यो रहस्य थियो - चित्रमा लुकेको एउटा सन्देश थियो।',
            'content_en' => 'There was a picture. That picture had a mystery. No one could understand that mystery. One day a child looked at that picture. He understood the mystery. The mystery was - there was a hidden message in the picture.',
            'tags' => ['चित्र', 'रहस्य', 'बालक'],
            'tags_en' => ['picture', 'mystery', 'child'],
            'reading_time' => 8,
            'views' => 890,
            'created_at' => '2024-01-20',
            'image_url' => 'https://images.unsplash.com/photo-1578926288207-a90a5366759d?w=400',
        ],
        [
            'id' => 3,
            'title' => 'साथीको मित्रता',
            'title_en' => 'Friendship of Friends',
            'category' => 'moral',
            'category_ne' => 'नैतिक कथा',
            'author' => 'नैतिक कथा',
            'author_en' => 'Moral Story',
            'content' => 'दुई साथी थिए। उनीहरू धेरै मित्र थिए। एक दिन उनीहरू बिचमा झगडा भयो। तर उनीहरूले फेरि मिले। उनीहरूले सिकाए - साचो मित्र कहिल्यै छोड्दैन।',
            'content_en' => 'There were two friends. They were very close friends. One day they had a fight. But they made up again. They learned - true friends never leave.',
            'tags' => ['साथी', 'मित्रता', 'साचो'],
            'tags_en' => ['friend', 'friendship', 'true'],
            'reading_time' => 4,
            'views' => 2100,
            'created_at' => '2024-01-25',
            'image_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400',
        ],
        [
            'id' => 4,
            'title' => 'ज्ञानको महत्व',
            'title_en' => 'Importance of Knowledge',
            'category' => 'educational',
            'category_ne' => 'शैक्षिक',
            'author' => 'शैक्षिक कथा',
            'author_en' => 'Educational Story',
            'content' => 'एक विद्यार्थी थियो। उसले धेरै पढाइयो। उसले ज्ञान प्राप्त गर्यो। उसले आफ्नो जीवन सफल बनायो। उसले सिकायो - ज्ञान सबैभन्दा ठूलो धन हो।',
            'content_en' => 'There was a student. He studied a lot. He gained knowledge. He made his life successful. He learned - knowledge is the greatest wealth.',
            'tags' => ['ज्ञान', 'विद्यार्थी', 'सफलता'],
            'tags_en' => ['knowledge', 'student', 'success'],
            'reading_time' => 6,
            'views' => 1560,
            'created_at' => '2024-02-01',
            'image_url' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400',
        ],
        [
            'id' => 5,
            'title' => 'समुद्रको रहस्य',
            'title_en' => 'Mystery of the Ocean',
            'category' => 'adventure',
            'category_ne' => 'साहसिक',
            'author' => 'साहसिक कथा',
            'author_en' => 'Adventure Story',
            'content' => 'एक माझी थियो। उसले समुद्रको रहस्य खोज्यो। उसले समुद्रमा धेरै कुरा देख्यो। उसले एउटा टापु पनि फेला पार्यो। उसको साहसिक यात्रा सफल भयो।',
            'content_en' => 'There was a sailor. He searched for the mystery of the ocean. He saw many things in the ocean. He also found an island. His adventure journey was successful.',
            'tags' => ['समुद्र', 'माझी', 'साहस'],
            'tags_en' => ['ocean', 'sailor', 'adventure'],
            'reading_time' => 10,
            'views' => 780,
            'created_at' => '2024-02-05',
            'image_url' => 'https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=400',
        ],
        [
            'id' => 6,
            'title' => 'बुद्धिमान बालक',
            'title_en' => 'Wise Child',
            'category' => 'moral',
            'category_ne' => 'नैतिक कथा',
            'author' => 'नैतिक कथा',
            'author_en' => 'Moral Story',
            'content' => 'एक बालक थियो। उसको बुद्धि धेरै थियो। उसले सधै सही निर्णय गर्थ्यो। सबैले उसको प्रशंसा गरे। उसले सिकायो - बुद्धि धेरै महत्वपूर्ण छ।',
            'content_en' => 'There was a child. He was very wise. He always made the right decision. Everyone praised him. He learned - wisdom is very important.',
            'tags' => ['बालक', 'बुद्धि', 'ज्ञान'],
            'tags_en' => ['child', 'wisdom', 'knowledge'],
            'reading_time' => 5,
            'views' => 1890,
            'created_at' => '2024-02-10',
            'image_url' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400',
        ],
        [
            'id' => 7,
            'title' => 'राजा र रानी',
            'title_en' => 'King and Queen',
            'category' => 'historical',
            'category_ne' => 'ऐतिहासिक',
            'author' => 'ऐतिहासिक कथा',
            'author_en' => 'Historical Story',
            'content' => 'एक राजा थियो। उसकी रानी पनि थिइन्। उनीहरूले आफ्नो राज्य राम्रोसँग चलाए। जनता खुसी थिए। उनीहरूले सिकाए - राम्रो शासनले जनता खुसी बनाउँछ।',
            'content_en' => 'There was a king. He had a queen. They ruled their kingdom well. The people were happy. They learned - good governance makes people happy.',
            'tags' => ['राजा', 'रानी', 'राज्य'],
            'tags_en' => ['king', 'queen', 'kingdom'],
            'reading_time' => 7,
            'views' => 1120,
            'created_at' => '2024-02-15',
            'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400',
        ],
        [
            'id' => 8,
            'title' => 'किसानको मेहनत',
            'title_en' => 'Farmer\'s Hard Work',
            'category' => 'moral',
            'category_ne' => 'नैतिक कथा',
            'author' => 'नैतिक कथा',
            'author_en' => 'Moral Story',
            'content' => 'एक किसान थियो। उसले धेरै मेहनत गर्थ्यो। उसको खेतमा धेरै फसल उत्पादन भयो। उसले सिकायो - मेहनतले सफलता प्राप्त हुन्छ।',
            'content_en' => 'There was a farmer. He worked very hard. His field produced a lot of crops. He learned - hard work brings success.',
            'tags' => ['किसान', 'मेहनत', 'फसल'],
            'tags_en' => ['farmer', 'hard work', 'crops'],
            'reading_time' => 4,
            'views' => 2340,
            'created_at' => '2024-02-20',
            'image_url' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=400',
        ],
    ];
    
    // Filter by category
    if ($category) {
        $allStories = array_filter($allStories, function($s) use ($category) {
            return strtolower($s['category']) === strtolower($category) || strtolower($s['category_ne']) === strtolower($category);
        });
    }
    
    // Sort by views (most popular first)
    usort($allStories, function($a, $b) {
        return $b['views'] - $a['views'];
    });
    
    // Apply pagination
    return array_slice(array_values($allStories), $offset, $limit);
}

function getStoryCategories(): array {
    return [
        ['name' => 'moral', 'name_ne' => 'नैतिक कथा'],
        ['name' => 'mystery', 'name_ne' => 'रहस्य'],
        ['name' => 'educational', 'name_ne' => 'शैक्षिक'],
        ['name' => 'adventure', 'name_ne' => 'साहसिक'],
        ['name' => 'historical', 'name_ne' => 'ऐतिहासिक'],
    ];
}

// Route handler
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'categories':
        echo json_encode(['categories' => getStoryCategories()], JSON_UNESCAPED_UNICODE);
        break;
    case 'list':
    default:
        $stories = getStories($category, $limit, $offset);
        $stories[] = [
            'source' => 'Sample Nepali Stories',
            'source_url' => 'https://www.hamropatro.com',
            'note' => 'नेपाली कथाहरू नमूना डाटा। Admin द्वारा थपिएको कथाहरू पनि देखाइनेछ।',
        ];
        echo json_encode(['stories' => $stories, 'total' => count($stories)], JSON_UNESCAPED_UNICODE);
        break;
}
