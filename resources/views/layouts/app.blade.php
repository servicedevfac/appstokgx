@extends('layouts.base')

@section('content')
    @isset($header)
        <header class="bg-white shadow-sm rounded mb-3 p-3">
            {{ $header }}
        </header>
    @endisset

    <main>
        {{ $slot }}
    </main>
@endsection

