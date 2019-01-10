@extends('layout.default')

@section('content')
<ol class="recent">
    @foreach ($entries as $name=>$info)
    <li><a href="{{ url($name) }}" title="{{ $name }}" data-timestamp="{{ $info['timestamp'] }}" v-lw-passage
            v-b-tooltip>{{
            $name }}</a> - {{
        \Carbon\Carbon::createFromTimestamp($info['timestamp'])->format('Y-m-d H:i:s') }}</li>
    @endforeach
</ol>
@endsection