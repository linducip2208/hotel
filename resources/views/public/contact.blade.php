@extends('public.layout')
@section('content')<h1 class="text-2xl font-bold mb-4">Contact</h1><p>{{ $property?->owner_email }} · {{ $property?->owner_phone }}</p>@endsection
