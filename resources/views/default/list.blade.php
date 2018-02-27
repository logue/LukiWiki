@extends('layout.default')

@section('title', $title)

@section('content')
<ul>
@foreach ($pages as $page)
    <li><a href="{{ url($page) }}">{{ $page }}</a></li>
@endforeach
</ul>
@endsection