<?php
/**
 * SEN-EVENTS — PHPUnit + Cache Clear Runner
 * URL : https://plateforme.senevents.africa/run-tests.php?token=sen2026tests
 * Apache sert ce fichier directement (règle !-f dans .htaccess)
 * SUPPRIMER après usage via Plesk File Manager.
 */

define('SECRET', 'sen2026tests');
define('ROOT',   dirname(__DIR__));
define('PHP',    '/opt/plesk/php/8.4/bin/php');
define('ARTISAN', ROOT . '/artisan');

if (($_GET['token'] ?? '') !== SECRET) {
    http_response_code(403);
    die('Accès refusé. Ajoutez ?token=' . SECRET);
}

$action = $_GET['action'] ?? 'tests';

// ── Action : vider les caches ─────────────────────────────────────────────────
if ($action === 'clear') {
    $results = [];
    foreach (['route:clear', 'view:clear', 'config:clear', 'cache:clear'] as $cmd) {
        exec(PHP . ' ' . escapeshellarg(ARTISAN) . ' ' . $cmd . ' 2>&1', $out, $code);
        $results[$cmd] = ['output' => implode("\n", $out), 'code' => $code];
        $out = [];
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<html><body style="background:#0f172a;color:#e2e8f0;font-family:monospace;padding:2rem">';
    echo '<h2>✅ Caches vidés</h2><pre>';
    foreach ($results as $cmd => $r) {
        $icon = $r['code'] === 0 ? '✓' : '✗';
        echo "$icon $cmd\n  {$r['output']}\n";
    }
    echo '</pre>';
    $url = '?token=' . SECRET . '&suite=' . ($_GET['suite'] ?? 'Feature');
    echo "<a href='$url' style='color:#60a5fa'>→ Lancer les tests</a></body></html>";
    exit;
}

// ── Action : lancer les tests ─────────────────────────────────────────────────
$suite   = $_GET['suite']  ?? 'Feature';
$filter  = $_GET['filter'] ?? '';
$phpunit = ROOT . '/vendor/bin/phpunit';
$config  = ROOT . '/phpunit.xml';

$cmd = PHP . ' ' . escapeshellarg($phpunit)
     . ' --testsuite=' . escapeshellarg($suite)
     . ' --colors=never'
     . ' --configuration=' . escapeshellarg($config);

if ($filter) $cmd .= ' --filter=' . escapeshellarg($filter);

$start = microtime(true);
exec($cmd . ' 2>&1', $output, $exitCode);
$elapsed = round(microtime(true) - $start, 2);
$raw = implode("\n", $output);

preg_match('/(\d+) passed/', $raw, $p);
preg_match('/(\d+) failed/', $raw, $f);
preg_match('/(\d+) error/',  $raw, $e);
preg_match('/Tests: (\d+)/', $raw, $t);

$passed = (int)($p[1] ?? 0);
$failed = (int)($f[1] ?? 0);
$errors = (int)($e[1] ?? 0);
$total  = (int)($t[1] ?? $passed + $failed + $errors);
$ok     = $exitCode === 0;
$token  = SECRET;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>PHPUnit — SEN-EVENTS</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem}
h1{font-size:1.4rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.6rem}
.badge{display:inline-block;padding:3px 12px;border-radius:12px;font-size:.75rem;font-weight:700}
.pass{background:#14532d;color:#86efac}.fail{background:#7f1d1d;color:#fca5a5}
.summary{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
.stat{background:#1e293b;border-radius:10px;padding:1rem 1.5rem;text-align:center;min-width:90px}
.stat .num{font-size:2rem;font-weight:800}
.stat .lbl{font-size:.7rem;text-transform:uppercase;opacity:.6;margin-top:2px}
.green{color:#4ade80}.red{color:#f87171}.yellow{color:#fbbf24}.blue{color:#60a5fa}
.suites{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap}
.suites a,.btn{padding:5px 14px;border-radius:6px;font-size:.8rem;background:#334155;
  color:#e2e8f0;text-decoration:none;border:1px solid #475569}
.suites a:hover,.btn:hover,.suites a.active{background:#3b82f6;border-color:#3b82f6;color:#fff}
.btn-orange{background:#7c2d12;border-color:#9a3412;color:#fdba74}
.btn-orange:hover{background:#9a3412}
.meta{font-size:.75rem;opacity:.5;margin-bottom:1rem}
.output{background:#1e293b;border-radius:10px;padding:1.5rem;
  font-family:'Courier New',monospace;font-size:.78rem;line-height:1.7;
  white-space:pre-wrap;word-break:break-all;max-height:72vh;overflow-y:auto;
  border:1px solid <?= $ok ? '#166534' : '#991b1b' ?>}
.lf{color:#f87171}.lo{color:#4ade80}.lw{color:#fbbf24}
.warn{margin-top:1.5rem;padding:.6rem 1rem;background:#431407;
  border-radius:6px;font-size:.72rem;color:#fdba74}
</style>
</head>
<body>

<h1>
    🧪 PHPUnit — SEN-EVENTS
    <span class="badge <?= $ok ? 'pass' : 'fail' ?>">
        <?= $ok ? '✓ ALL PASSED' : '✗ FAILURES' ?>
    </span>
</h1>

<div class="meta">
    Suite : <strong><?= htmlspecialchars($suite) ?></strong>
    <?= $filter ? ' · Filtre : <strong>' . htmlspecialchars($filter) . '</strong>' : '' ?>
    · Durée : <strong><?= $elapsed ?>s</strong> · Exit : <strong><?= $exitCode ?></strong>
</div>

<div class="summary">
    <div class="stat"><div class="num blue"><?= $total ?></div><div class="lbl">Total</div></div>
    <div class="stat"><div class="num green"><?= $passed ?></div><div class="lbl">Passés</div></div>
    <div class="stat"><div class="num red"><?= $failed ?></div><div class="lbl">Échoués</div></div>
    <div class="stat"><div class="num red"><?= $errors ?></div><div class="lbl">Erreurs</div></div>
</div>

<div class="suites">
    <?php foreach (['Feature', 'Unit'] as $s): ?>
    <a href="?token=<?= $token ?>&suite=<?= $s ?>"
       class="<?= $suite === $s ? 'active' : '' ?>"><?= $s ?></a>
    <?php endforeach; ?>
    <a href="?token=<?= $token ?>&action=clear&suite=<?= $suite ?>"
       class="btn btn-orange">🗑 Vider les caches</a>
</div>

<div class="output"><?php
foreach (explode("\n", htmlspecialchars($raw)) as $line) {
    if (preg_match('/FAIL|Error|Exception/', $line))
        echo '<span class="lf">' . $line . '</span>' . "\n";
    elseif (preg_match('/OK|pass|Tests:|Assertions:/', $line))
        echo '<span class="lo">' . $line . '</span>' . "\n";
    elseif (preg_match('/WARN|skip|risky/i', $line))
        echo '<span class="lw">' . $line . '</span>' . "\n";
    else
        echo $line . "\n";
}
?></div>

<div class="warn">
    ⚠️ Supprimer <code>public/run-tests.php</code> via Plesk File Manager après usage.
</div>
</body>
</html>
