@extends('layout.default')

@section('title', $entry->title ?? $page )

@section('content')
<p class="text-right"><lw-social type="share"></lw-social> <small>Total: {{ $counter['total'] ?? 0 }} / Today: {{ $counter['today'] ?? 0}} / Yesterday: {{$counter['yesterday'] ?? 0}}</small></p>

{!! $content !!}

<hr />
<p class="text-right small">Last Modified: {{ $entry->updated_at }}</p>


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
@if(count($entry->attachments)!==0)
<hr />
<aside>
    <ul class="fa-ul">
        @foreach ($entry->attachments as $attach)
        <li>
            <font-awesome-icon fas icon="clip">*</font-awesome-icon>
            {!! $attach->name !!}
        </li>
        @endforeach
    </ul>
</aside>
@endif
@endsection