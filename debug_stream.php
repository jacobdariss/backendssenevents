<?php
if (($_GET['token'] ?? '') !== 'debug2026') { die('Acces refuse'); }

echo '<pre style="font:13px monospace;padding:20px;background:#111;color:#eee">';
echo "PHP " . PHP_VERSION . "\n\n";

chdir('/var/www/vhosts/senevents.africa/plateforme.senevents.africa');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Crypt;

// Test 1 : HTML iframe Cloudflare
$html = '<div style="position: relative; padding-top: 56.25%;">
  <iframe
    src="https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9d985f33822651c1f13433e673a6/iframe?autoplay=true&poster=https%3A%2F%2Fcustomer-myvckkqiszi5ayhh.cloudflarestream.com%2Fef7a9d985f33822651c1f13433e673a6%2Fthumbnails%2Fthumbnail.jpg"
    loading="lazy"
    allowfullscreen="true"
  ></iframe>
</div>';

echo "=== Test 1 : iframe HTML ===\n";
try {
    $enc = Crypt::encryptString($html);
    echo "Chiffré OK (longueur: " . strlen($enc) . ")\n";
    $dec = Crypt::decryptString($enc);
    echo "Déchiffré OK (longueur: " . strlen($dec) . ")\n";
    $result = decryptVideoUrl(urlencode($enc));
    echo "decryptVideoUrl: ";
    print_r($result);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Test 2 : URL directe Cloudflare
echo "\n=== Test 2 : URL directe Cloudflare ===\n";
$url = 'https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9d985f33822651c1f13433e673a6/iframe?autoplay=true';
try {
    $enc2 = Crypt::encryptString($url);
    $result2 = decryptVideoUrl(urlencode($enc2));
    echo "decryptVideoUrl: ";
    print_r($result2);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Test 3 : Lire un vrai film Embedded depuis la DB
echo "\n=== Test 3 : Film Embedded en base ===\n";
try {
    $movie = \DB::table('entertainments')
        ->where('video_upload_type', 'Embedded')
        ->whereNotNull('video_url_input')
        ->first();
    if ($movie) {
        echo "Film trouvé: " . $movie->name . "\n";
        echo "video_url_input (50 chars): " . substr($movie->video_url_input, 0, 50) . "\n";
        $result3 = decryptVideoUrl(urlencode($movie->video_url_input));
        echo "decryptVideoUrl: ";
        print_r($result3);
    } else {
        echo "Aucun film Embedded trouvé en base\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

echo '</pre>';
