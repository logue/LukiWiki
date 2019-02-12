@extends('layout.default')

@section('content')
<ul class="entries">
    @foreach ($entries as $entry=>$updated)
    <li><a href="{{ url('/') . '/' .str_replace('%2F', '/', rawurlencode($entry)) }}"
            datetime="{{ $updated }}">{{ $entry }}</a></li>
    @endforeach
</ul>
@endsection