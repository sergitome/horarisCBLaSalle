<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Gestió Partits</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shell flex min-h-screen items-center justify-center p-6">
    <div class="panel grid w-full max-w-5xl overflow-hidden lg:grid-cols-[1.1fr,0.9fr]">
        <div class="bg-stone-950 p-10 text-white">
            <p class="text-xs uppercase tracking-[0.35em] text-amber-400">Club Bàsquet</p>
            <h1 class="mt-5 text-5xl font-bold leading-tight">Tot el calendari, equips i imports FBIB en un sol lloc.</h1>
            <p class="mt-5 max-w-xl text-stone-300">Aplicació Laravel amb control d’usuaris, auditoria, cues i gestió operativa del club.</p>
        </div>
        <div class="p-8 sm:p-10">
            <h2 class="text-3xl font-bold">Accés</h2>
            <p class="mt-2 text-sm text-stone-500">Usuari inicial: <strong>admin</strong> / <strong>admin</strong></p>
            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="label">Usuari o email</label>
                    <input class="field" name="login" value="{{ old('login', 'admin') }}" required>
                </div>
                <div>
                    <label class="label">Contrasenya</label>
                    <input class="field" type="password" name="password" value="admin" required>
                </div>
                <label class="flex items-center gap-3 text-sm">
                    <input type="checkbox" name="remember" class="rounded border-stone-300">
                    Recorda’m
                </label>
                <button class="btn-primary w-full justify-center" type="submit">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
