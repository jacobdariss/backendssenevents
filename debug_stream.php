<?php
if (($_GET['token'] ?? '') !== 'debug2026') { die('Acces refuse'); }
echo '<pre style="font:13px monospace;padding:20px;background:#111;color:#eee">';
echo "PHP " . PHP_VERSION . "\n\n";

chdir('/var/www/vhosts/senevents.africa/plateforme.senevents.africa');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

$html = '<div style="position: relative; padding-top: 56.25%;">
  <iframe
    src="https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9d985f33822651c1f13433e673a6/iframe?autoplay=true&poster=https%3A%2F%2Fcustomer-myvckkqiszi5ayhh.cloudflarestream.com%2Fef7a9d985f33822651c1f13433e673a6%2Fthumbnails%2Fthumbnail.jpg"
    loading="lazy" allowfullscreen="true"></iframe>
</div>';

$enc = Crypt::encryptString($html);
$encUrl = urlencode($enc);
$cleanUrl = stripslashes($encUrl);
$decrypted = Crypt::decryptString(urldecode($cleanUrl));

echo "Déchiffré = HTML? " . (str_contains($decrypted, '<iframe') ? 'OUI' : 'NON') . "\n";
echo "Longueur décryptée: " . strlen($decrypted) . "\n\n";

// Tester chaque étape manuellement
echo "--- Étape par étape ---\n";

// 1. YouTube
preg_match("/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^\"&?\/ ]{11})/", $decrypted, $yt);
echo "1. YouTube: " . (isset($yt[1]) ? "MATCH: ".$yt[1] : "non") . "\n";

// 2. Vimeo
preg_match("/player\.vimeo\.com\/video\/(\d+)/", $decrypted, $vi);
echo "2. Vimeo: " . (isset($vi[1]) ? "MATCH" : "non") . "\n";

// 3. HLS
echo "3. HLS: " . (preg_match('/\.m3u8$/', $decrypted) ? "MATCH" : "non") . "\n";

// 4. workers.dev etc
echo "4. CDN: " . (preg_match('/\.(workers\.dev|cloudfront\.net|amazonaws\.com|koyeb\.app)/', $decrypted) ? "MATCH" : "non") . "\n";

// 5. Storage::exists
$filePath = str_replace(url('/storage'), 'public', $decrypted);
echo "5. filePath pour Storage: " . substr($filePath, 0, 80) . "\n";
try {
    $exists = Storage::exists($filePath);
    echo "   Storage::exists: " . ($exists ? "OUI" : "non") . "\n";
} catch (Exception $e) {
    echo "   Storage::exists EXCEPTION: " . $e->getMessage() . "\n";
}

// 6. iframe check
echo "6. str_contains iframe: " . (str_contains($decrypted, '<iframe') ? "OUI" : "non") . "\n";
$r1 = '/<iframe[^>]*\bsrc=["\'](https?[^"\']+)["\']/is';
if (preg_match($r1, $decrypted, $m)) {
    echo "   Regex iframe: OK → " . substr($m[1], 0, 80) . "\n";
} else {
    echo "   Regex iframe: FAIL\n";
    // Afficher le début du HTML pour diagnostic
    echo "   HTML début: " . htmlspecialchars(substr($decrypted, 0, 200)) . "\n";
}

// 7. filter_var
echo "7. filter_var URL: " . (filter_var($decrypted, FILTER_VALIDATE_URL) ? "MATCH" : "non") . "\n";

echo '</pre>';
