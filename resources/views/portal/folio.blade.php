@extends('public.layout')
@section('content')
<div class="max-w-2xl mx-auto bg-white border rounded p-6">
    <h1 class="text-2xl font-bold mb-3">Folio — {{ $reservation->ref }}</h1>
    @foreach ($reservation->folios as $folio)
        <h2 class="font-semibold mt-4">{{ $folio->folio_no }}</h2>
        <table class="w-full text-sm">
            <thead><tr><th class="text-left">Description</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
            @foreach ($folio->charges as $c)
                <tr><td class="py-1">{{ $c->description }}</td><td class="py-1 text-right">{{ number_format($c->amount, 0, ',', '.') }}</td></tr>
            @endforeach
            </tbody>
        </table>
        <p class="font-bold mt-2">Balance: Rp {{ number_format($folio->balance, 0, ',', '.') }}</p>
    @endforeach
</div>
@endsection
