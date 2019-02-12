@extends('layout.default')

@section('content')
<ol class="recent">
    @foreach ($entries as $entry)
    <li><a href="{{ url($entry->name) }}" title="{{ $entry->name }}" daytime="{{ $entry->updated_at }}" v-b-tooltip>{{
            $entry->name }}</a> - {{
        $entry->updated_at }}</li>
    @endforeach
</ol>
@endsection