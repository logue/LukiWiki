@extends('layout.default')

@section('title', sprintf(__('Search Result of %s'), join(' ', $keywords)))

@section('content')
<ul class="entries">
    @foreach ($entries as $entry)
    <li><a href="{{ url('/') . '/' .str_replace('%2F', '/', rawurlencode($entry['name'])) }}">{{ $entry['name'] }}</a></li>
    @endforeach
</ul>
@endsection