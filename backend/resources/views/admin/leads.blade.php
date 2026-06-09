<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regency Quiz — Admin</title>
    <style>
        :root {
            --bg: #0b0b15;
            --bg2: #1a0b2e;
            --surface: #161528;
            --border: rgba(255, 255, 255, 0.08);
            --text: #f5f5f5;
            --muted: rgba(255, 255, 255, 0.55);
            --dim: rgba(255, 255, 255, 0.35);
            --pink: #ec4899;
            --yellow: #fbbf24;
            --green: #10b981;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100dvh;
            background: linear-gradient(160deg, var(--bg) 0%, var(--bg2) 50%, var(--bg) 100%);
            color: var(--text);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 32px;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { height: 32px; }
        .tag {
            font-size: 11px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--dim);
            border-left: 1px solid var(--border);
            padding-left: 12px;
        }
        h1 {
            font-size: 28px;
            margin: 0 0 6px;
            font-weight: 700;
        }
        .subtitle { color: var(--muted); margin: 0 0 28px; font-size: 14px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat {
            background: rgba(22, 21, 40, 0.6);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
        }
        .stat .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--dim);
            margin-bottom: 6px;
        }
        .stat .value {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .stat.green .value { color: var(--green); }
        .stat.yellow .value { color: var(--yellow); }
        .stat.pink .value { color: var(--pink); }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 28px 0 16px;
        }
        h2 { font-size: 18px; margin: 0; }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, var(--pink), var(--yellow));
            color: #000;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-download:hover { opacity: 0.92; }
        .btn-download:active { transform: scale(0.98); }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(22, 21, 40, 0.4);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            white-space: nowrap;
        }
        th {
            background: rgba(255, 255, 255, 0.04);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            font-weight: 600;
        }
        tr:last-child td { border-bottom: none; }
        td.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; }
        td.muted { color: var(--muted); }

        .pill {
            display: inline-block;
            font-size: 10px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.08em;
        }
        .pill.green { background: rgba(16, 185, 129, 0.15); color: var(--green); border: 1px solid rgba(16, 185, 129, 0.3); }
        .pill.yellow { background: rgba(251, 191, 36, 0.15); color: var(--yellow); border: 1px solid rgba(251, 191, 36, 0.3); }
        .pill.pink { background: rgba(236, 72, 153, 0.15); color: var(--pink); border: 1px solid rgba(236, 72, 153, 0.3); }
        .pill.sent { background: rgba(16, 185, 129, 0.12); color: var(--green); }
        .pill.dry_run { background: rgba(251, 191, 36, 0.12); color: var(--yellow); }
        .pill.failed { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .pill.queued { background: rgba(255, 255, 255, 0.08); color: var(--muted); }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        footer {
            margin-top: 32px;
            padding: 16px 0;
            border-top: 1px solid var(--border);
            color: var(--dim);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 760px) {
            .actions { flex-direction: column; gap: 12px; align-items: stretch; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="container">

    <header>
        <div class="brand">
            <span style="display:inline-block; width:28px; height:28px; border-radius:50%; background: linear-gradient(135deg, var(--pink), var(--yellow), var(--green));"></span>
            <strong>Regency Casino Mont Parnes</strong>
            <span class="tag">Admin</span>
        </div>
        <span class="tag">Quiz Game</span>
    </header>

    <h1>Συμμετοχές παιχνιδιού</h1>
    <p class="subtitle">Επισκόπηση όλων των leads και εξαγωγή σε CSV.</p>

    <div class="stats">
        <div class="stat">
            <div class="label">Σύνολο leads</div>
            <div class="value">{{ number_format($totalLeads) }}</div>
        </div>
        <div class="stat">
            <div class="label">Σήμερα</div>
            <div class="value">{{ number_format($totalToday) }}</div>
        </div>
        <div class="stat green">
            <div class="label">Persona: Green</div>
            <div class="value">{{ number_format($byPersona['green'] ?? 0) }}</div>
        </div>
        <div class="stat yellow">
            <div class="label">Persona: Yellow</div>
            <div class="value">{{ number_format($byPersona['yellow'] ?? 0) }}</div>
        </div>
        <div class="stat pink">
            <div class="label">Persona: Pink</div>
            <div class="value">{{ number_format($byPersona['pink'] ?? 0) }}</div>
        </div>
    </div>

    @if (!empty($smsStats))
        <div class="stats">
            @foreach (['sent' => 'Sent', 'dry_run' => 'Dry-run', 'failed' => 'Failed', 'queued' => 'Queued'] as $key => $label)
                @if (isset($smsStats[$key]))
                    <div class="stat">
                        <div class="label">SMS · {{ $label }}</div>
                        <div class="value">{{ number_format($smsStats[$key]) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="actions">
        <h2>Τελευταίες {{ $latestLeads->count() }} συμμετοχές</h2>
        <a class="btn-download" href="{{ route('admin.leads.export') }}">
            ⬇ Κατέβασμα CSV (όλα τα leads)
        </a>
    </div>

    @if ($latestLeads->isEmpty())
        <div class="empty">
            <p>Δεν υπάρχουν leads ακόμα. Όταν κάποιος συμπληρώσει τη φόρμα, θα εμφανιστεί εδώ.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ημ/νία</th>
                    <th>Ονοματεπώνυμο</th>
                    <th>Email</th>
                    <th>Τηλέφωνο</th>
                    <th>Persona</th>
                    <th>Κωδικός</th>
                    <th>SMS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($latestLeads as $lead)
                    <tr>
                        <td class="mono muted">#{{ $lead->id }}</td>
                        <td class="muted">{{ optional($lead->created_at)->format('d/m H:i') }}</td>
                        <td>{{ $lead->name }}</td>
                        <td class="mono">{{ $lead->email }}</td>
                        <td class="mono">{{ $lead->phone }}</td>
                        <td>
                            @if ($lead->persona_color)
                                <span class="pill {{ $lead->persona_color }}">{{ $lead->persona_color }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td class="mono">{{ $lead->redemption_code ?? '—' }}</td>
                        <td>
                            @if ($lead->latestSmsLog)
                                <span class="pill {{ $lead->latestSmsLog->status }}">
                                    {{ $lead->latestSmsLog->status }}
                                </span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <footer>
        © {{ now()->year }} Regency Casino Mont Parnes · Admin Panel
    </footer>

</div>
</body>
</html>
