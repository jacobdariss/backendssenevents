<?php
/**
 * SEN-EVENTS — PHPUnit Runner (streaming, par fichier)
 * URL : https://plateforme.senevents.africa/run-tests.php?token=sen2026tests
 * SUPPRIMER après usage via Plesk File Manager.
 */

define('SECRET',  'sen2026tests');
define('ROOT',    __DIR__);
define('PHP',     '/opt/plesk/php/8.4/bin/php');
define('ARTISAN', ROOT . '/artisan');
define('PHPUNIT', ROOT . '/vendor/bin/phpunit');
define('CONFIG',  ROOT . '/phpunit.xml');

set_time_limit(0);
ini_set('max_execution_time', 0);

if (($_GET['token'] ?? '') !== SECRET) {
    http_response_code(403);
    die('Acces refuse. Ajoutez ?token=' . SECRET);
}

$token  = SECRET;
$action = $_GET['action'] ?? '';

if ($action === 'clear') {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <style>body{background:#0f172a;color:#e2e8f0;font-family:monospace;padding:2rem}
    .ok{color:#4ade80}.err{color:#f87171}a{color:#60a5fa;}</style></head><body>';
    echo '<h2>Vidage des caches...</h2><pre>';
    flush();
    foreach (['route:clear','view:clear','config:clear','cache:clear'] as $cmd) {
        exec(PHP . ' ' . escapeshellarg(ARTISAN) . " $cmd 2>&1", $out, $code);
        $icon = $code === 0 ? '<span class="ok">OK</span>' : '<span class="err">ERR</span>';
        echo "$icon $cmd\n"; flush(); $out = [];
    }
    echo '</pre><br><a href="?token=' . $token . '">Retour</a></body></html>';
    exit;
}

$testFiles = glob(ROOT . '/tests/Feature/**/*Test.php');
sort($testFiles);
$selectedFile = $_GET['file'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>PHPUnit SEN-EVENTS</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:1.5rem}
h1{font-size:1.2rem;margin-bottom:1rem}
.files{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
.files a{padding:4px 10px;border-radius:5px;font-size:.72rem;background:#1e293b;
  color:#94a3b8;text-decoration:none;border:1px solid #334155}
.files a:hover,.files a.active{background:#3b82f6;color:#fff;border-color:#3b82f6}
.files a.clr{background:#7c2d12;color:#fdba74;border-color:#9a3412}
.output{background:#1e293b;border-radius:8px;padding:1.2rem;
  font-family:'Courier New',monospace;font-size:.78rem;line-height:1.6;
  white-space:pre-wrap;word-break:break-all;height:68vh;overflow-y:auto;
  border:1px solid #334155;margin-top:.8rem}
.lf{color:#f87171}.lo{color:#4ade80}.lw{color:#fbbf24}
.bar{display:flex;gap:.8rem;margin-top:.8rem;flex-wrap:wrap}
.stat{background:#1e293b;padding:.6rem 1rem;border-radius:7px;text-align:center;min-width:75px}
.stat b{display:block;font-size:1.5rem;font-weight:800}
.g{color:#4ade80}.r{color:#f87171}.bl{color:#60a5fa}
.badge{padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:700}
.pass{background:#14532d;color:#86efac}.fail{background:#7f1d1d;color:#fca5a5}
.warn{margin-top:.8rem;padding:.4rem .8rem;background:#431407;border-radius:5px;font-size:.68rem;color:#fdba74}
</style>
</head>
<body>
<h1>PHPUnit SEN-EVENTS</h1>
<div class="files">
<?php foreach ($testFiles as $f):
    $name  = basename($f, '.php');
    $short = str_replace('Test', '', $name);
?>
<a href="?token=<?= $token ?>&file=<?= urlencode($f) ?>"
   class="<?= $selectedFile===$f?'active':'' ?>"><?= htmlspecialchars($short) ?></a>
<?php endforeach; ?>
<a href="?token=<?= $token ?>&action=clear" class="files a clr" style="background:#7c2d12;color:#fdba74;border:1px solid #9a3412;padding:4px 10px;border-radius:5px;font-size:.72rem;text-decoration:none">Caches</a>
</div>
<?php if (!$selectedFile): ?>
<p style="opacity:.5;font-size:.8rem">Choisir un fichier ci-dessus</p>
<?php else:
    $cmd = PHP . ' ' . escapeshellarg(PHPUNIT)
         . ' --colors=never --configuration=' . escapeshellarg(CONFIG)
         . ' ' . escapeshellarg($selectedFile);

    echo '<div id="out" class="output">';
    flush(); ob_flush();

    $lines = []; $start = microtime(true);
    $proc  = popen($cmd . ' 2>&1', 'r');
    while (!feof($proc)) {
        $line = fgets($proc, 1024);
        if ($line === false) break;
        $lines[] = rtrim($line);
        $esc = htmlspecialchars(rtrim($line));
        if (preg_match('/FAIL|Error|Exception/', $line))      echo '<span class="lf">'.$esc.'</span>'."\n";
        elseif (preg_match('/OK|pass|Tests:|Assertions:/', $line)) echo '<span class="lo">'.$esc.'</span>'."\n";
        elseif (preg_match('/WARN|skip|risky/i', $line))      echo '<span class="lw">'.$esc.'</span>'."\n";
        else echo $esc."\n";
        flush(); ob_flush();
    }
    $exitCode = pclose($proc);
    $elapsed  = round(microtime(true) - $start, 2);
    $raw = implode("\n", $lines);

    preg_match('/(\d+) passed/', $raw, $p);
    preg_match('/(\d+) failed/', $raw, $f2);
    preg_match('/(\d+) error/',  $raw, $e2);
    preg_match('/Tests: (\d+)/', $raw, $t);
    $passed = (int)($p[1]??0); $failed=(int)($f2[1]??0);
    $errors=(int)($e2[1]??0);  $total=(int)($t[1]??$passed+$failed+$errors);
    $ok = ($failed+$errors===0);

    echo '</div>';
    echo '<div class="bar">';
    echo '<div class="stat"><b class="bl">'.$total.'</b><small>Total</small></div>';
    echo '<div class="stat"><b class="g">'.$passed.'</b><small>Passes</small></div>';
    echo '<div class="stat"><b class="r">'.$failed.'</b><small>Echoues</small></div>';
    echo '<div class="stat"><b class="r">'.$errors.'</b><small>Erreurs</small></div>';
    echo '<div class="stat"><span class="badge '.($ok?'pass':'fail').'">'.($ok?'PASSED':'FAILED').'</span>';
    echo '<br><small style="opacity:.5">'.$elapsed.'s</small></div></div>';
endif; ?>
<div class="warn">Supprimer run-tests.php via Plesk File Manager apres usage.</div>
<script>
const o=document.getElementById('out');
if(o){new MutationObserver(()=>o.scrollTop=o.scrollHeight).observe(o,{childList:true,subtree:true});}
</script>
</body></html>
