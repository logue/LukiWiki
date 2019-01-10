@extends('layout.default')

@section('content')
<ul class="entries">
    @foreach ($entries as $name=>$info)
    <li><a href="{{ url($name) }}" title="{{ $name }}" data-timestamp="{{ $info['timestamp'] }}" v-lw-passage
            v-b-tooltip>{{ $name }}</a></li>
    @endforeach
</ul>
@endsection