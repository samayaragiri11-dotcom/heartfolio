<?php
/*
 * Heartfolio product catalogue (front-end only, no database).
 * Every page reads products from HERE, so each product has ONE id,
 * ONE name and ONE image. Change something here and it changes everywhere.
 */

$CATEGORIES = [
    'friendship'  => 'Friendship',
    'birthday'    => 'Birthday',
    'love'        => 'Love & Couple',
    'family'      => 'Family',
    'anniversary' => 'Anniversary',
    'travel'      => 'Travel',
    'others'      => 'Others',
];

$PRODUCTS = [
    1 => [
        'name' => 'Best Friends', 'category' => 'friendship', 'price' => 699,
        'image' => 'assets/images/shopbsf.jpeg',
        'thumbs' => ['assets/images/shopbsf.jpeg', 'images/thumb1bsf.png', 'images/thumb2bsf.png', 'images/thumb3bsf.jpeg'],
        'rating' => 4.5, 'reviews' => 24,
        'description' => 'Celebrate your friendship with this beautiful personalized magazine. Perfect for best friends who want to cherish their memories together. Fill it with your favorite photos, stories, and moments that define your friendship.',
    ],
    2 => [
        'name' => 'Friendship Forever', 'category' => 'friendship', 'price' => 699,
        'image' => 'images/shopff.jpg',
        'rating' => 4.5, 'reviews' => 18,
        'description' => 'A keepsake for friendships that have lasted through the years. Collect your trips, inside jokes and milestones in one printed magazine.',
    ],
    3 => [
        'name' => 'Memories Together', 'category' => 'friendship', 'price' => 699,
        'image' => 'assets/images/mtshop.jpeg',
        'rating' => 4.0, 'reviews' => 15,
        'description' => 'Bring together the laughs, adventures and quiet days you have shared. A magazine made for looking back and smiling.',
    ],
    4 => [
        'name' => 'Better Together', 'category' => 'friendship', 'price' => 699,
        'image' => 'images/btshop.jpeg',
        'rating' => 4.5, 'reviews' => 12,
        'description' => 'For the people who make everything better. Celebrate your group with photos, stories and the moments only you understand.',
    ],
    5 => [
        'name' => 'Soul Sisters', 'category' => 'friendship', 'price' => 699,
        'image' => 'images/sisshop.jpeg',
        'rating' => 5.0, 'reviews' => 20,
        'description' => 'A tribute to the sister, by blood or by heart, who knows you best. Fill it with the memories that made your bond.',
    ],
    6 => [
        'name' => 'Our Story', 'category' => 'love', 'price' => 699,
        'image' => 'images/ourstory.jpeg',
        'rating' => 4.5, 'reviews' => 31,
        'description' => 'Tell your love story from the first hello. Dates, trips and little moments, printed in a magazine made just for the two of you.',
    ],
    7 => [
        'name' => 'Birthday Special', 'category' => 'birthday', 'price' => 699,
        'image' => 'assets/images/birthday.jpeg',
        'rating' => 4.5, 'reviews' => 22,
        'description' => 'A birthday gift they will keep forever. Fill it with photos, wishes and messages from the people who love them.',
    ],
    8 => [
        'name' => 'Family Memories', 'category' => 'family', 'price' => 699,
        'image' => 'assets/images/family.jpeg',
        'rating' => 5.0, 'reviews' => 17,
        'description' => 'Holidays, celebrations and everyday moments with the people who matter most, gathered into one family keepsake.',
    ],
    9 => [
        'name' => 'Anniversary Love', 'category' => 'anniversary', 'price' => 699,
        'image' => 'assets/images/ann.jpeg',
        'rating' => 4.5, 'reviews' => 14,
        'description' => 'Celebrate every year you have spent together. Perfect for anniversaries big and small.',
    ],
    10 => [
        'name' => 'Travel Adventures', 'category' => 'travel', 'price' => 699,
        'image' => 'assets/images/travel.jpeg',
        'rating' => 4.5, 'reviews' => 11,
        'description' => 'Turn your trip photos into a travel magazine. Places, people and stories from the road, all in one place.',
    ],
    11 => [
        'name' => 'Special Moments', 'category' => 'others', 'price' => 699,
        'image' => 'images/sisshop.jpeg', // TODO: replace with its own image
        'rating' => 4.0, 'reviews' => 9,
        'description' => 'Graduations, farewells, new babies or anything worth remembering. A magazine for the moments that do not fit a category.',
    ],
    12 => [
        'name' => 'Love Forever', 'category' => 'love', 'price' => 699,
        'image' => 'assets/images/loveforever.jpeg',
        'rating' => 4.5, 'reviews' => 26,
        'description' => 'A romantic keepsake for couples. Share your favorite photos and the words you want to say forever.',
    ],
];

// Fill in shared defaults so every product has the same fields
foreach ($PRODUCTS as $id => &$p) {
    $p['id']     = $id;
    $p['pages']  = $p['pages'] ?? 24;
    $p['size']   = $p['size'] ?? '8.5" x 11"';
    $p['paper']  = $p['paper'] ?? 'Premium Glossy';
    $p['thumbs'] = $p['thumbs'] ?? [$p['image']];
}
unset($p);

if (!function_exists('hf_e')) {
    function hf_e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    function hf_price($amount) {
        return 'Rs. ' . number_format($amount);
    }

    function hf_product($id) {
        global $PRODUCTS;
        return $PRODUCTS[(int)$id] ?? null;
    }

    function hf_category_label($slug) {
        global $CATEGORIES;
        return $CATEGORIES[$slug] ?? 'Others';
    }

    // Prints one product card. Used by index, shop and templates pages.
    function hf_product_card($p, $buttonLabel = 'View Details') {
        ?>
        <div class="product-card">
            <img src="<?php echo hf_e($p['image']); ?>" alt="<?php echo hf_e($p['name']); ?> Magazine">
            <div class="product-card-body">
                <h3><?php echo hf_e($p['name']); ?></h3>
                <p class="price"><?php echo hf_price($p['price']); ?></p>
                <a href="product-details.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-secondary"><?php echo hf_e($buttonLabel); ?></a>
            </div>
        </div>
        <?php
    }
}
