<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <id>{{ url('/') }}</id>
    <title>{{ Config::get('lukiwiki.sitename') }}</title>
    <link href="{{ url('/') }}" />
    <link type="text/html" rel="alternate" href="{{ url('/:recent') }}" />
    <link type="application/atom+xml" rel="self" href="{{ url('/:api/atom') }}" />
    @foreach ($entries as $entry)
    <entry>
        <title>{{ $entry->name }}</title>
        <link href="{{ url($entry->name) }}" />
        <id>{{ $entry->id }}</id>
        <updated>{{ Carbon\Carbon::parse($entry->updated_at)->format('Y-m-d\TH:i:sP') }}</updated>
        <summary>{{ $entry->description }}</summary>
    </entry>
    @endforeach
</feed>