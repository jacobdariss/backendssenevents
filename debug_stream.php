<?php
if (($_GET['token'] ?? '') !== 'debug2026') { die('Acces refuse'); }

$html = '<div style="position: relative; padding-top: 56.25%;">
  <iframe
    src="https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9d985f33822651c1f13433e673a6/iframe?autoplay=true"
    loading="lazy"
    style="border: none;"
    allowfullscreen="true"
  ></iframe>
</div>';

echo '<pre style="font-family:monospace;padding:20px">';
echo "PHP " . PHP_VERSION . "\n\n";

echo "1. str_contains iframe: " . (str_contains($html, '<iframe') ? 'OUI' : 'NON') . "\n\n";

$r1 = '/<iframe[^>]*\bsrc=["\x27](https?[^"\x27]+)["\x27][^>]*>/is';
if (preg_match($r1, $html, $m)) {
    echo "2. Regex1: OK\n   " . htmlspecialchars($m[1]) . "\n\n";
} else {
    echo "2. Regex1: FAIL\n\n";
}

$r2 = '/\bsrc\s*=\s*["\x27](https?[^"\x27]+)["\x27]/i';
if (preg_match($r2, $html, $m)) {
    echo "3. Regex2: OK\n   " . htmlspecialchars($m[1]) . "\n\n";
} else {
    echo "3. Regex2: FAIL\n\n";
}

// Test avec le vrai decryptVideoUrl
chdir('/var/www/vhosts/senevents.africa/plateforme.senevents.africa');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($html);
$result = decryptVideoUrl(urlencode($encrypted));
echo "4. decryptVideoUrl:\n";
print_r($result);

echo "\n\n--- Test URL directe Cloudflare ---\n";
$directUrl = 'https://customer-myvckkqiszi5ayhh.cloudflarestream.com/ef7a9/iframe';
$encrypted2 = Crypt::encryptString($directUrl);
$result2 = decryptVideoUrl(urlencode($encrypted2));
echo "decryptVideoUrl (URL directe):\n";
print_r($result2);

echo '</pre>';
