@extends('layout.default')

@section('content')
<ul class="entries">
    @foreach ($entries as $entry)
    <li><a href="{{ url($entry->name) }}" title="{{ $entry->name }}" data-timestamp="{{ $entry->updated_at }}" v-lw-passage
            v-b-tooltip>{{ $entry->name }}</a></li>
    @endforeach
</ul>
@endsection