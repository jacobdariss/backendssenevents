<?php
/**
 * SEN-EVENTS — PHPUnit Runner
 * Accessible via : https://plateforme.senevents.africa/run-tests.php
 * SUPPRIMER CE FICHIER après utilisation.
 */

// Sécurité basique — token requis dans l'URL
define('SECRET', 'sen2026tests');
if (($_GET['token'] ?? '') !== SECRET) {
    http_response_code(403);
    die('Accès refusé. Ajoutez ?token=' . SECRET . ' à l\'URL.');
}

$suite  = $_GET['suite'] ?? 'Feature';
$filter = $_GET['filter'] ?? '';

// Chemin vers PHP et PHPUnit
$php     = '/opt/plesk/php/8.4/bin/php';
$phpunit = __DIR__ . '/vendor/bin/phpunit';
$config  = __DIR__ . '/phpunit.xml';

// Construction de la commande
$cmd = escapeshellcmd($php) . ' '
     . escapeshellarg($phpunit)
     . ' --testsuite=' . escapeshellarg($suite)
     . ' --colors=never'
     . ' --configuration=' . escapeshellarg($config);

if ($filter) {
    $cmd .= ' --filter=' . escapeshellarg($filter);
}

// Exécution
$start  = microtime(true);
exec($cmd . ' 2>&1', $output, $exitCode);
$elapsed = round(microtime(true) - $start, 2);

// Parsing des résultats
$raw    = implode("\n", $output);
$passed = preg_match('/(\d+) passed/', $raw, $m) ? (int)$m[1] : 0;
$failed = preg_match('/(\d+) failed/', $raw, $m) ? (int)$m[1] : 0;
$errors = preg_match('/(\d+) error/', $raw, $m) ? (int)$m[1] : 0;
$skipped= preg_match('/(\d+) skipped/', $raw, $m) ? (int)$m[1] : 0;
$total  = preg_match('/Tests: (\d+)/', $raw, $m) ? (int)$m[1] : ($passed + $failed + $errors);
$ok     = $exitCode === 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>PHPUnit — SEN-EVENTS</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
    h1 { font-size: 1.4rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: .6rem; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: .75rem; font-weight: 700; }
    .pass { background: #14532d; color: #86efac; }
    .fail { background: #7f1d1d; color: #fca5a5; }

    .summary {
        display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;
    }
    .stat {
        background: #1e293b; border-radius: 10px; padding: 1rem 1.5rem;
        text-align: center; min-width: 100px;
    }
    .stat .num { font-size: 2rem; font-weight: 800; }
    .stat .lbl { font-size: .7rem; text-transform: uppercase; opacity: .6; margin-top: 2px; }
    .green { color: #4ade80; }
    .red   { color: #f87171; }
    .yellow{ color: #fbbf24; }
    .blue  { color: #60a5fa; }

    .output {
        background: #1e293b; border-radius: 10px; padding: 1.5rem;
        font-family: 'Courier New', monospace; font-size: .8rem;
        line-height: 1.6; white-space: pre-wrap; word-break: break-all;
        max-height: 70vh; overflow-y: auto;
        border: 1px solid <?= $ok ? '#166534' : '#991b1b' ?>;
    }
    .meta { font-size: .75rem; opacity: .5; margin-bottom: 1rem; }

    /* Coloration syntaxique basique */
    .line-fail   { color: #f87171; }
    .line-ok     { color: #4ade80; }
    .line-warn   { color: #fbbf24; }

    .suites {
        display: flex; gap: .5rem; margin-bottom: 1rem; flex-wrap: wrap;
    }
    .suites a {
        padding: 5px 14px; border-radius: 6px; font-size: .8rem;
        background: #334155; color: #e2e8f0; text-decoration: none;
        border: 1px solid #475569;
    }
    .suites a:hover, .suites a.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }
</style>
</head>
<body>

<h1>
    🧪 PHPUnit — SEN-EVENTS
    <span class="badge <?= $ok ? 'pass' : 'fail' ?>">
        <?= $ok ? '✓ PASSED' : '✗ FAILED' ?>
    </span>
</h1>

<div class="meta">
    Suite : <strong><?= htmlspecialchars($suite) ?></strong>
    <?= $filter ? ' · Filtre : <strong>' . htmlspecialchars($filter) . '</strong>' : '' ?>
    · Durée : <strong><?= $elapsed ?>s</strong>
    · Exit code : <strong><?= $exitCode ?></strong>
</div>

<div class="summary">
    <div class="stat"><div class="num blue"><?= $total ?></div><div class="lbl">Total</div></div>
    <div class="stat"><div class="num green"><?= $passed ?></div><div class="lbl">Passés</div></div>
    <div class="stat"><div class="num red"><?= $failed ?></div><div class="lbl">Échoués</div></div>
    <div class="stat"><div class="num red"><?= $errors ?></div><div class="lbl">Erreurs</div></div>
    <div class="stat"><div class="num yellow"><?= $skipped ?></div><div class="lbl">Ignorés</div></div>
</div>

<p style="margin-bottom:.8rem;font-size:.8rem;opacity:.6">Lancer une suite :</p>
<div class="suites">
    <?php foreach (['Feature', 'Unit', 'all'] as $s): ?>
    <a href="?token=<?= SECRET ?>&suite=<?= $s ?>"
       class="<?= $suite === $s ? 'active' : '' ?>">
        <?= $s ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="output"><?php
$lines = explode("\n", htmlspecialchars($raw));
foreach ($lines as $line) {
    if (preg_match('/FAIL|Error|Exception|✗|×/', $line)) {
        echo '<span class="line-fail">' . $line . '</span>' . "\n";
    } elseif (preg_match('/PASS|OK|✓|\./', $line)) {
        echo '<span class="line-ok">' . $line . '</span>' . "\n";
    } elseif (preg_match('/WARN|skip|risky/i', $line)) {
        echo '<span class="line-warn">' . $line . '</span>' . "\n";
    } else {
        echo $line . "\n";
    }
}
?></div>

<p style="margin-top:1rem;font-size:.7rem;color:#f87171;opacity:.7">
    ⚠️ Supprimer <code>public/run-tests.php</code> après utilisation.
</p>

</body>
</html>
