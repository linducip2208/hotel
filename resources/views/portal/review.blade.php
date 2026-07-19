@extends('public.layout')
@section('content')
<div class="max-w-xl mx-auto bg-white border rounded p-6">
    <h1 class="text-2xl font-bold mb-3">Bagaimana pengalaman Anda?</h1>
    @if (session('status')) <div class="bg-green-50 text-green-800 p-2 rounded mb-3">{{ session('status') }}</div> @endif
    <form method="POST">
        @csrf
        <label class="text-sm">Rating (1-5)</label>
        <input type="number" name="rating" min="1" max="5" required class="w-full border rounded p-2 mb-3">
        <label class="text-sm">Komentar</label>
        <textarea name="comment" rows="4" class="w-full border rounded p-2 mb-3"></textarea>
        <button class="bg-primary-600 text-white px-4 py-2 rounded">Submit</button>
    </form>
</div>
@endsection
