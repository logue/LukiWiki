@extends('layout.default')

@section('title', $entry->title ?? $page )

@section('content')
{!! $content !!}

<p class="text-right small">Total: {{ $entry->counter->total ?? 0 }} / Today: {{ $entry->counter->today ?? 0}} / Yesterday: {{$entry->counter->yesterday ?? 0}}</p>
@if($notes)
<hr />
<aside>
    <ul class="fa-ul">
        @foreach ($notes as $no=>$note)
        <li id="note-{{ $no }}">
            <a href="#note-anchor-{{ $no }}" id="note-{{ $no }}" class="fa-li">
                <font-awesome-icon fas icon="thumbtack">*</font-awesome-icon>
                <sup>{{ $no }}</sup>
            </a>
            {!! $note !!}
        </li>
        @endforeach
    </ul>
</aside>
@endif
<hr />
<ul class="fa-ul">
    @foreach ($entry->attachments as $attach)
    <li>
        {!! $attach->name !!}
    </li>
    @endforeach
</ul>
@endsection