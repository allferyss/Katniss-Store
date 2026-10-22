<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · Katniss Store</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0b0b14;color:#cbd5e1;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;padding:24px;text-align:center}
        .code{font-size:64px;font-weight:800;color:#8b5cf6;margin:0}
        h1{color:#fff;font-size:22px;margin:8px 0}
        p{max-width:420px;margin:0 auto 20px;color:#94a3b8;line-height:1.6}
        a{display:inline-block;background:#7c3aed;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600}
        a:focus-visible{outline:2px solid #a78bfa;outline-offset:2px}
    </style>
</head>
<body>
    <main>
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </main>
</body>
</html>
