<?php
// Test : https://plateforme.senevents.africa/debug_stream.php?token=debug2026
if (($_GET['token'] ?? '') !== 'debug2026') { die('Accès refusé'); }

$decryptedUrl = '<div style="position: relative; padding-top: 56.25%;">
  <iframe
    src="https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9d985f33822651c1f13433e673a6/iframe?autoplay=true&poster=https%3A%2F%2Fcustomer-myvckkqiszi5ayhh.cloudflarestream.com%2Fef7a9d985f33822651c1f13433e673a6%2Fthumbnails%2Fthumbnail.jpg%3Ftime%3D%26height%3D600"     loading="lazy"     style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;"     allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"     allowfullscreen="true"   ></iframe> </div>';

echo '<pre>';
echo "1. str_contains &lt;iframe&gt; : " . (str_contains($decryptedUrl, '<iframe') ? 'OUI ✓' : 'NON ✗') . "\n\n";

// Regex 1
if (preg_match('/<iframe[^>]*\bsrc=["\'](https?[^"\']+)["\''][^>]*>/is', $decryptedUrl, $m)) {
    echo "2. Regex1 : OK ✓\n   URL: " . htmlspecialchars($m[1]) . "\n\n";
} else {
    echo "2. Regex1 : FAIL ✗\n\n";
}

// Regex 2
if (preg_match('/\bsrc\s*=\s*["\'](https?[^"\']+)["\']/i', $decryptedUrl, $m)) {
    echo "3. Regex2 fallback : OK ✓\n   URL: " . htmlspecialchars($m[1]) . "\n\n";
} else {
    echo "3. Regex2 fallback : FAIL ✗\n\n";
}

// Test helpers.php decryptVideoUrl avec un vrai contenu chiffré
chdir('/var/www/vhosts/senevents.africa/plateforme.senevents.africa');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Chiffrer le contenu puis déchiffrer via notre fonction
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($decryptedUrl);
$result = decryptVideoUrl(urlencode($encrypted));
echo "4. decryptVideoUrl result:\n";
print_r($result);
echo '</pre>';
