<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Wizard — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
<main class="max-w-2xl mx-auto p-8">
    <h1 class="text-2xl font-bold mb-1">{{ config('app.name') }} — Setup Wizard</h1>
    <p class="text-sm text-gray-600 mb-6">Sekitar 5 menit untuk pair lisensi dan setup property pertama.</p>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    @if ($step === 'pair')
        <section class="bg-white rounded shadow-sm p-6 border">
            <h2 class="font-semibold mb-3">Step 1 — Lisensi</h2>
            <p class="text-sm text-gray-600 mb-4">Masukkan kunci lisensi dari email pembelian.</p>
            <form method="POST" action="{{ route('setup.wizard.pair') }}">
                @csrf
                <input type="text" name="license_key" placeholder="HMS-XXXXX-XXXXX-XXXXX-XXXXX"
                    class="w-full border rounded p-2 font-mono uppercase" autofocus required>
                <button class="mt-3 bg-primary-600 text-white px-4 py-2 rounded">Pair Lisensi</button>
            </form>
        </section>

    @elseif ($step === 'property')
        <section class="bg-white rounded shadow-sm p-6 border">
            <h2 class="font-semibold mb-3">Step 2 — Profil Property</h2>
            <form method="POST" action="{{ route('setup.wizard.property') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Nama Hotel</label>
                    <input type="text" name="name" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Legal Name</label>
                    <input type="text" name="legal_name" class="w-full border rounded p-2">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Region Code (PB1)</label>
                        <input type="text" name="region_code" placeholder="ID-BA-BD" required class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Province</label>
                        <input type="text" name="province" class="w-full border rounded p-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">City</label>
                    <input type="text" name="city" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Alamat</label>
                    <input type="text" name="address_line1" class="w-full border rounded p-2">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium">Total Kamar</label>
                        <input type="number" name="total_rooms" required min="1" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Bintang (1-5)</label>
                        <input type="number" name="star_rating" min="1" max="5" class="w-full border rounded p-2">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_pkp" value="1"> Sudah PKP (PPN)
                </label>
                <div>
                    <label class="block text-sm font-medium">NPWP (opsional)</label>
                    <input type="text" name="npwp" class="w-full border rounded p-2">
                </div>
                <button class="bg-primary-600 text-white px-4 py-2 rounded">Simpan</button>
            </form>
        </section>

    @elseif ($step === 'admin')
        <section class="bg-white rounded shadow-sm p-6 border">
            <h2 class="font-semibold mb-3">Step 3 — Buat Admin User</h2>
            <form method="POST" action="{{ route('setup.wizard.admin') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Nama</label>
                    <input type="text" name="name" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Password (min 10 karakter)</label>
                    <input type="password" name="password" required minlength="10" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border rounded p-2">
                </div>
                <button class="bg-primary-600 text-white px-4 py-2 rounded">Buat Admin & Selesai</button>
            </form>
        </section>

    @else
        <section class="bg-white rounded shadow-sm p-6 border">
            <h2 class="font-semibold text-green-700">Setup selesai 🎉</h2>
            <a href="/panel" class="inline-block mt-3 bg-primary-600 text-white px-4 py-2 rounded">Masuk Panel</a>
        </section>
    @endif

    <p class="text-xs text-gray-500 mt-6">
        Status license:
        <code class="bg-gray-100 px-1">{{ $local?->status ?? 'unpaired' }}</code>
    </p>
</main>
</body>
</html>
