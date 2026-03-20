<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPUnit — SEN-EVENTS</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
h1 { font-size: 1.4rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: .6rem; }
.badge { display: inline-block; padding: 3px 12px; border-radius: 12px; font-size: .75rem; font-weight: 700; }
.pass  { background: #14532d; color: #86efac; }
.fail  { background: #7f1d1d; color: #fca5a5; }
.summary { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.stat { background: #1e293b; border-radius: 10px; padding: 1rem 1.5rem; text-align: center; min-width: 90px; }
.stat .num { font-size: 2rem; font-weight: 800; }
.stat .lbl { font-size: .7rem; text-transform: uppercase; opacity: .6; margin-top: 2px; }
.green { color: #4ade80; } .red { color: #f87171; } .yellow { color: #fbbf24; } .blue { color: #60a5fa; }
.suites { display: flex; gap: .5rem; margin-bottom: 1rem; flex-wrap: wrap; }
.suites a { padding: 5px 14px; border-radius: 6px; font-size: .8rem; background: #334155; color: #e2e8f0; text-decoration: none; border: 1px solid #475569; }
.suites a:hover, .suites a.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }
.meta { font-size: .75rem; opacity: .5; margin-bottom: 1rem; }
.output { background: #1e293b; border-radius: 10px; padding: 1.5rem; font-family: 'Courier New', monospace; font-size: .78rem; line-height: 1.7; white-space: pre-wrap; word-break: break-all; max-height: 70vh; overflow-y: auto; border: 1px solid {{ $ok ? '#166534' : '#991b1b' }}; }
.line-fail { color: #f87171; } .line-ok { color: #4ade80; } .line-warn { color: #fbbf24; }
.warn-box { margin-top: 1.5rem; padding: .6rem 1rem; background: #431407; border-radius: 6px; font-size: .72rem; color: #fdba74; }
</style>
</head>
<body>

<h1>
    🧪 PHPUnit — SEN-EVENTS
    <span class="badge {{ $ok ? 'pass' : 'fail' }}">{{ $ok ? '✓ ALL PASSED' : '✗ FAILURES' }}</span>
</h1>

<div class="meta">
    Suite&nbsp;: <strong>{{ $suite }}</strong>
    @if($filter) &nbsp;·&nbsp; Filtre&nbsp;: <strong>{{ $filter }}</strong> @endif
    &nbsp;·&nbsp; Durée&nbsp;: <strong>{{ $elapsed }}s</strong>
    &nbsp;·&nbsp; Exit&nbsp;: <strong>{{ $exitCode }}</strong>
</div>

<div class="summary">
    <div class="stat"><div class="num blue">{{ $total }}</div><div class="lbl">Total</div></div>
    <div class="stat"><div class="num green">{{ $passed }}</div><div class="lbl">Passés</div></div>
    <div class="stat"><div class="num red">{{ $failed }}</div><div class="lbl">Échoués</div></div>
    <div class="stat"><div class="num red">{{ $errors }}</div><div class="lbl">Erreurs</div></div>
</div>

<p style="margin-bottom:.6rem;font-size:.8rem;opacity:.6">Changer de suite :</p>
<div class="suites">
    @foreach($suites as $s)
    <a href="/dev-run-tests?token={{ $token }}&suite={{ $s }}"
       class="{{ $suite === $s ? 'active' : '' }}">{{ $s }}</a>
    @endforeach
</div>

<div class="output">@foreach(explode("\n", $raw) as $line)@if(preg_match('/FAIL|Error|Exception/', $line))<span class="line-fail">{{ $line }}</span>
@elseif(preg_match('/OK|pass|✓/', $line))<span class="line-ok">{{ $line }}</span>
@elseif(preg_match('/WARN|skip/i', $line))<span class="line-warn">{{ $line }}</span>
@else{{ $line }}
@endif@endforeach</div>

<div class="warn-box">⚠️ Supprimer la route <code>/dev-run-tests</code> dans <code>routes/web.php</code> après usage.</div>

</body>
</html>
