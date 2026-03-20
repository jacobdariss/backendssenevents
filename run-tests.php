<?php
/**
 * SEN-EVENTS — PHPUnit Runner (streaming, fichier par fichier)
 * URL : https://plateforme.senevents.africa/run-tests.php?token=sen2026tests
 * SUPPRIMER après usage via Plesk File Manager.
 */

define('SECRET',  'sen2026tests');
define('ROOT',    __DIR__);
define('PHP',     '/opt/plesk/php/8.4/bin/php');
define('ARTISAN', ROOT . '/artisan');
define('PHPUNIT', ROOT . '/vendor/bin/phpunit');

set_time_limit(0);
ignore_user_abort(true);

if (($_GET['token'] ?? '') !== SECRET) {
    http_response_code(403);
    die('Acces refuse. ?token=' . SECRET);
}

$action = $_GET['action'] ?? 'menu';
$token  = SECRET;

// Vider les caches
if ($action === 'clear') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<html><body style="background:#0f172a;color:#e2e8f0;font-family:monospace;padding:2rem">';
    echo '<h2 style="color:#4ade80">Caches Laravel</h2><pre>';
    foreach (['route:clear','view:clear','config:clear','cache:clear'] as $cmd) {
        exec(PHP . ' ' . escapeshellarg(ARTISAN) . ' ' . $cmd . ' 2>&1', $out, $code);
        echo ($code === 0 ? '+ ' : '! ') . $cmd . "\n  " . implode("\n  ", $out) . "\n";
        $out = [];
        flush();
    }
    echo '</pre><br><a href="?token=' . $token . '" style="color:#60a5fa">Retour</a></body></html>';
    exit;
}

// Lister les fichiers
function getFiles(string $dir): array {
    if (!is_dir($dir)) return [];
    $files = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iter as $file)
        if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php'))
            $files[] = $file->getPathname();
    sort($files);
    return $files;
}

// Lancer un fichier
if ($action === 'run') {
    $file  = $_GET['file'] ?? '';
    if (!$file || !file_exists($file) || !str_starts_with($file, ROOT . '/tests/')) {
        die('Fichier invalide');
    }
    $short = str_replace(ROOT . '/tests/', '', $file);
    $cmd   = PHP . ' ' . escapeshellarg(PHPUNIT)
           . ' --colors=never'
           . ' --configuration=' . escapeshellarg(ROOT . '/phpunit.xml')
           . ' ' . escapeshellarg($file)
           . ' 2>&1';

    header('Content-Type: text/html; charset=utf-8');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);
    if (ob_get_level()) ob_end_flush();

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($short) . '</title>';
    echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0f172a;color:#e2e8f0;font-family:"Courier New",monospace;padding:1.5rem;font-size:.82rem;line-height:1.7}h1{font-family:system-ui;font-size:1rem;color:#94a3b8;margin-bottom:.8rem}a{color:#60a5fa}.ok{color:#4ade80}.fail{color:#f87171}.warn{color:#fbbf24}.box{margin-top:1rem;padding:.8rem 1rem;background:#1e293b;border-radius:8px;font-family:system-ui;font-size:.85rem}</style>';
    echo '</head><body>';
    echo '<h1>PHPUnit : ' . htmlspecialchars($short) . '</h1>';
    echo '<a href="?token=' . $token . '">&larr; Menu</a><br><br><pre>';
    flush();

    $start = microtime(true);
    $proc  = popen($cmd, 'r');
    $all   = '';
    while (!feof($proc)) {
        $line = fgets($proc, 512);
        if ($line === false) continue;
        $all .= $line;
        $esc  = htmlspecialchars($line);
        if (preg_match('/FAIL|Error|Exception/', $line))     echo '<span class="fail">' . $esc . '</span>';
        elseif (preg_match('/OK|pass|Tests:|Assert/i',$line)) echo '<span class="ok">'  . $esc . '</span>';
        elseif (preg_match('/WARN|skip/i', $line))            echo '<span class="warn">' . $esc . '</span>';
        else                                                   echo $esc;
        flush();
    }
    $code    = pclose($proc);
    $elapsed = round(microtime(true) - $start, 2);

    preg_match('/(\d+) passed/', $all, $p);
    preg_match('/(\d+) failed/', $all, $f);
    preg_match('/Tests: (\d+)/', $all, $t);
    $passed = (int)($p[1] ?? 0);
    $failed = (int)($f[1] ?? 0);
    $total  = (int)($t[1] ?? $passed + $failed);
    $ok     = $code === 0;

    echo '</pre><div class="box">';
    echo '<strong style="color:' . ($ok ? '#4ade80' : '#f87171') . '">' . ($ok ? 'PASSED' : 'FAILED') . '</strong>';
    echo ' &mdash; ' . $total . ' tests &mdash; ' . $elapsed . 's';
    echo '</div><br><a href="?token=' . $token . '">&larr; Menu</a></body></html>';
    flush();
    exit;
}

// Menu
$featureFiles = getFiles(ROOT . '/tests/Feature');
$unitFiles    = getFiles(ROOT . '/tests/Unit');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>PHPUnit Runner — SEN-EVENTS</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem}
h1{font-size:1.3rem;margin-bottom:1.5rem}
h2{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:1.5rem 0 .7rem}
.btn{display:inline-block;padding:6px 14px;border-radius:6px;font-size:.78rem;background:#1e293b;
     color:#93c5fd;text-decoration:none;border:1px solid #334155;margin:.25rem}
.btn:hover{background:#2563eb;border-color:#2563eb;color:#fff}
.btn-red{background:#7f1d1d;border-color:#991b1b;color:#fca5a5}
.btn-red:hover{background:#991b1b;color:#fff}
.warn{margin-top:2rem;padding:.6rem 1rem;background:#431407;border-radius:6px;font-size:.7rem;color:#fdba74}
</style>
</head>
<body>
<h1>🧪 PHPUnit Runner — SEN-EVENTS</h1>

<a href="?token=<?= $token ?>&action=clear" class="btn btn-red">🗑 Vider les caches (run d'abord)</a>

<h2>Feature (<?= count($featureFiles) ?> fichiers)</h2>
<?php foreach ($featureFiles as $f): ?>
<a href="?token=<?= $token ?>&action=run&file=<?= urlencode($f) ?>" class="btn">
    <?= htmlspecialchars(basename($f)) ?>
</a>
<?php endforeach; ?>

<h2>Unit (<?= count($unitFiles) ?> fichiers)</h2>
<?php foreach ($unitFiles as $f): ?>
<a href="?token=<?= $token ?>&action=run&file=<?= urlencode($f) ?>" class="btn">
    <?= htmlspecialchars(basename($f)) ?>
</a>
<?php endforeach; ?>

<div class="warn">⚠️ Supprimer <code>run-tests.php</code> via Plesk File Manager après usage.</div>
</body>
</html>
