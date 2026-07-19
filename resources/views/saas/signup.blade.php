<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Sign up — {{ config('app.name') }}</title>@vite(['resources/css/app.css'])</head>
<body class="bg-gray-50 min-h-screen">
<main class="max-w-2xl mx-auto p-8">
    <h1 class="text-3xl font-bold mb-2">Mulai Trial 14 Hari</h1>
    <p class="text-gray-600 mb-6">Tidak perlu kartu kredit. Cancel kapan saja.</p>
    @if ($errors->any())<div class="bg-red-50 text-red-800 p-3 rounded mb-3 text-sm">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('saas.signup') }}" class="bg-white border rounded p-6 space-y-3">
        @csrf
        <div>
            <label class="text-sm">Nama Hotel / Company</label>
            <input type="text" name="company_name" required class="w-full border rounded p-2">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="text-sm">Nama Owner</label><input type="text" name="owner_name" required class="w-full border rounded p-2"></div>
            <div><label class="text-sm">WhatsApp</label><input type="tel" name="owner_phone" class="w-full border rounded p-2"></div>
        </div>
        <div><label class="text-sm">Email</label><input type="email" name="owner_email" required class="w-full border rounded p-2"></div>
        <div>
            <label class="text-sm">Subdomain</label>
            <div class="flex">
                <input type="text" name="slug" required pattern="[a-z0-9-]{3,30}" placeholder="hotel-mandala" class="border rounded-l p-2 flex-1">
                <span class="bg-gray-200 px-3 flex items-center rounded-r">.hotelhub.id</span>
            </div>
        </div>
        <div>
            <label class="text-sm">Pilih Plan</label>
            <select name="plan_id" required class="w-full border rounded p-2">
                @foreach ($plans as $p) <option value="{{ $p->id }}">{{ $p->name }} — Rp {{ number_format($p->per_room_price_idr ?? 0, 0, ',', '.') }}/kamar/bln</option> @endforeach
            </select>
        </div>
        <button class="w-full bg-primary-600 text-white py-2 rounded">Mulai Trial</button>
    </form>
</main>
</body></html>
