<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $currentFile['title'] }} — {{ config('app.name') }} Docs</title>
    <meta name="description" content="Documentation for {{ config('app.name') }}">
    <style>
        :root {
            --sidebar-width: 280px;
            --toc-width: 220px;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #ffffff;
            --bg-soft: #f8fafc;
            --bg-code: #0f172a;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; scroll-padding-top: 80px; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.65;
            font-size: 16px;
        }

        /* TOP BAR */
        .topbar {
            position: sticky; top: 0; z-index: 50;
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 20px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: saturate(180%) blur(10px);
            border-bottom: 1px solid var(--border);
        }
        .topbar h1 { margin: 0; font-size: 16px; font-weight: 700; }
        .topbar h1 a { color: inherit; text-decoration: none; }
        .topbar small { color: var(--text-muted); margin-left: 8px; }
        .topbar nav a { font-size: 13px; color: var(--text-muted); text-decoration: none; margin-left: 16px; }
        .topbar nav a:hover { color: var(--primary); }

        .menu-toggle { display: none; background: none; border: 1px solid var(--border); border-radius: 6px; padding: 6px 10px; cursor: pointer; }

        /* LAYOUT */
        .layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) minmax(0, 1fr) var(--toc-width);
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 60px);
        }

        /* LEFT SIDEBAR (file list) */
        .sidebar {
            position: sticky; top: 60px; align-self: start;
            height: calc(100vh - 60px);
            overflow-y: auto;
            padding: 24px 16px;
            border-right: 1px solid var(--border);
            background: var(--bg-soft);
        }
        .sidebar h3 {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--text-muted); margin: 0 0 12px;
            font-weight: 700;
        }
        .sidebar ol { list-style: none; padding: 0; margin: 0 0 24px; }
        .sidebar li { margin: 0; }
        .sidebar a {
            display: block; padding: 7px 12px; border-radius: 6px;
            color: var(--text); text-decoration: none;
            font-size: 13.5px; line-height: 1.4;
            transition: background 0.12s;
        }
        .sidebar a:hover { background: rgba(37,99,235,0.08); color: var(--primary); }
        .sidebar a.active { background: var(--primary); color: white; font-weight: 600; }
        .sidebar a .num { color: var(--text-muted); margin-right: 8px; font-variant-numeric: tabular-nums; }
        .sidebar a.active .num { color: rgba(255,255,255,0.8); }

        /* MAIN CONTENT */
        .content {
            padding: 32px 48px;
            max-width: 820px;
            min-width: 0;
        }
        .content h1 {
            margin-top: 0; padding-bottom: 12px;
            border-bottom: 2px solid var(--border);
            font-size: 32px; font-weight: 800;
        }
        .content h2 {
            margin-top: 40px; padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            font-size: 24px;
        }
        .content h3 { margin-top: 32px; font-size: 19px; }
        .content h4 { margin-top: 24px; font-size: 16px; color: var(--text-muted); }
        .content p { margin: 12px 0; }
        .content a { color: var(--primary); text-decoration: none; }
        .content a:hover { text-decoration: underline; }
        .content a.anchor {
            color: var(--text-muted); opacity: 0; margin-left: 6px;
            font-weight: normal;
            transition: opacity 0.15s;
            text-decoration: none;
        }
        .content h1:hover .anchor, .content h2:hover .anchor,
        .content h3:hover .anchor, .content h4:hover .anchor { opacity: 1; }
        .content code {
            background: rgba(15,23,42,0.06);
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.88em;
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
        }
        .content pre {
            background: var(--bg-code);
            color: #e2e8f0;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.55;
        }
        .content pre code { background: transparent; color: inherit; padding: 0; font-size: inherit; }
        .content blockquote {
            border-left: 4px solid var(--primary);
            padding: 4px 16px;
            margin: 16px 0;
            background: rgba(37,99,235,0.05);
            color: var(--text-muted);
            border-radius: 0 6px 6px 0;
        }
        .content blockquote p { margin: 8px 0; }
        .content table {
            border-collapse: collapse; width: 100%; margin: 16px 0; font-size: 14px;
            display: block; overflow-x: auto;
        }
        .content thead { background: var(--bg-soft); }
        .content th, .content td {
            border: 1px solid var(--border);
            padding: 8px 12px;
            text-align: left;
        }
        .content th { font-weight: 600; }
        .content tr:nth-child(even) td { background: var(--bg-soft); }
        .content ul, .content ol { padding-left: 24px; }
        .content li { margin: 4px 0; }
        .content hr { border: none; border-top: 1px solid var(--border); margin: 32px 0; }
        .content img { max-width: 100%; border-radius: 8px; }

        /* RIGHT TOC */
        .toc {
            position: sticky; top: 60px; align-self: start;
            height: calc(100vh - 60px);
            overflow-y: auto;
            padding: 32px 16px;
            border-left: 1px solid var(--border);
        }
        .toc h3 {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--text-muted); margin: 0 0 12px;
            font-weight: 700;
        }
        .toc ul { list-style: none; padding: 0; margin: 0; }
        .toc li.lvl-3 { padding-left: 12px; }
        .toc a {
            display: block;
            padding: 4px 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 12.5px;
            line-height: 1.4;
            border-left: 2px solid transparent;
            transition: all 0.12s;
        }
        .toc a:hover { color: var(--primary); border-left-color: var(--primary); }
        .toc a.active { color: var(--primary); border-left-color: var(--primary); font-weight: 600; }

        /* PAGINATION */
        .pager {
            margin-top: 48px; padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: space-between; gap: 16px;
        }
        .pager a {
            flex: 1; padding: 12px 16px; border: 1px solid var(--border);
            border-radius: 8px;
            text-decoration: none; color: var(--text);
            transition: all 0.15s;
        }
        .pager a:hover { border-color: var(--primary); background: var(--bg-soft); }
        .pager .label { color: var(--text-muted); font-size: 12px; display: block; margin-bottom: 2px; }
        .pager .next { text-align: right; }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .layout { grid-template-columns: var(--sidebar-width) minmax(0, 1fr); }
            .toc { display: none; }
            .content { padding-right: 24px; }
        }
        @media (max-width: 768px) {
            .menu-toggle { display: inline-block; }
            .layout { grid-template-columns: minmax(0, 1fr); }
            .sidebar {
                position: fixed; top: 60px; left: 0;
                width: 280px; max-width: 85%;
                height: calc(100vh - 60px);
                z-index: 40;
                transform: translateX(-110%);
                transition: transform 0.25s ease-out;
                box-shadow: 4px 0 24px rgba(0,0,0,0.08);
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-backdrop {
                display: none; position: fixed; inset: 60px 0 0 0;
                background: rgba(0,0,0,0.4); z-index: 30;
            }
            .sidebar-backdrop.open { display: block; }
            .content { padding: 20px 16px; }
            .topbar nav a { display: none; }
        }
    </style>
</head>
<body>

<header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button class="menu-toggle" id="menuToggle" aria-label="Menu">☰</button>
        <h1><a href="/docs">{{ config('app.name') }}</a><small>Docs</small></h1>
    </div>
    <nav>
        <a href="/">← Back to App</a>
        <a href="/docs/{{ $currentSlug }}.md" target="_blank">View raw</a>
        <a href="https://github.com/your-org/hotel" target="_blank">GitHub</a>
    </nav>
</header>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="layout">
    {{-- LEFT: file list --}}
    <aside class="sidebar" id="sidebar">
        <h3>Documentation</h3>
        <ol>
            @foreach ($files as $f)
                <li>
                    <a href="/docs/{{ $f['slug'] }}" class="{{ $f['slug'] === $currentSlug ? 'active' : '' }}">
                        <span class="num">{{ str_pad($f['order'], 2, '0', STR_PAD_LEFT) }}</span>
                        {{ \Illuminate\Support\Str::after($f['title'], '— ') ?: $f['title'] }}
                    </a>
                </li>
            @endforeach
        </ol>
        <h3 style="margin-top:24px">Resources</h3>
        <a href="/" style="font-size:13.5px;display:block;padding:7px 12px;border-radius:6px;color:var(--text);text-decoration:none">🏠 Home</a>
        <a href="/setup/wizard" style="font-size:13.5px;display:block;padding:7px 12px;border-radius:6px;color:var(--text);text-decoration:none">🚀 Setup Wizard</a>
        <a href="/api/v1/openapi.json" style="font-size:13.5px;display:block;padding:7px 12px;border-radius:6px;color:var(--text);text-decoration:none" target="_blank">📡 API Spec</a>
        <a href="/admin" style="font-size:13.5px;display:block;padding:7px 12px;border-radius:6px;color:var(--text);text-decoration:none">🛡 Admin</a>
    </aside>

    {{-- MAIN: rendered markdown --}}
    <main class="content">
        {!! $html !!}

        {{-- prev/next pagination --}}
        @php
            $idx = collect($files)->search(fn ($f) => $f['slug'] === $currentSlug);
            $prev = $idx > 0 ? $files[$idx - 1] : null;
            $next = $idx < count($files) - 1 ? $files[$idx + 1] : null;
        @endphp
        <nav class="pager">
            @if ($prev)
                <a href="/docs/{{ $prev['slug'] }}">
                    <span class="label">← Previous</span>
                    {{ \Illuminate\Support\Str::after($prev['title'], '— ') ?: $prev['title'] }}
                </a>
            @else
                <span></span>
            @endif
            @if ($next)
                <a href="/docs/{{ $next['slug'] }}" class="next">
                    <span class="label">Next →</span>
                    {{ \Illuminate\Support\Str::after($next['title'], '— ') ?: $next['title'] }}
                </a>
            @endif
        </nav>
    </main>

    {{-- RIGHT: in-page TOC --}}
    @if (! empty($headings))
    <aside class="toc">
        <h3>On This Page</h3>
        <ul>
            @foreach ($headings as $h)
                <li class="lvl-{{ $h['level'] }}"><a href="#{{ $h['id'] }}" data-target="{{ $h['id'] }}">{{ $h['text'] }}</a></li>
            @endforeach
        </ul>
    </aside>
    @endif
</div>

<script>
// Mobile sidebar toggle
const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebarBackdrop');
toggle?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    backdrop.classList.toggle('open');
});
backdrop?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    backdrop.classList.remove('open');
});

// Highlight TOC active section on scroll
const tocLinks = document.querySelectorAll('.toc a');
const headings = Array.from(document.querySelectorAll('.content h2, .content h3'));
function updateActiveTocItem() {
    let current = null;
    for (const h of headings) {
        if (h.getBoundingClientRect().top < 120) current = h.id;
        else break;
    }
    tocLinks.forEach(l => l.classList.toggle('active', l.dataset.target === current));
}
window.addEventListener('scroll', updateActiveTocItem, { passive: true });
updateActiveTocItem();

// Auto-scroll active sidebar item into view on load
const activeSidebarLink = document.querySelector('.sidebar a.active');
if (activeSidebarLink) {
    activeSidebarLink.scrollIntoView({ block: 'center', behavior: 'instant' });
}
</script>

</body>
</html>
