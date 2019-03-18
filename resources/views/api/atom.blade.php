<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <id>{{ url('/') }}</id>
    <title>{{ Config::get('lukiwiki.sitename') }}</title>
    <updated>{{ Carbon\Carbon::parse($entry->updated_at)->format('Y-m-d\TH:i:sP') }}</updated>
    <link href="{{ url('/') }}" />
    <link rel="alternate" type="text/html" href="{{ url(':recent') }}" />
    <link rel="self" type="application/atom+xml" href="{{ url(':api/atom') }}" />
    @foreach ($entries as $entry)
    <entry>
        <title>{{ $entry->name }}</title>
        <link href="{{ url('/') . '/' .str_replace('%2F', '/', rawurlencode($entry->name)) }}" />
        <id>{{ $entry->id }}</id>
        <updated>{{ Carbon\Carbon::parse($entry->updated_at)->format('Y-m-d\TH:i:sP') }}</updated>
        <summary><![CDATA[{{ $entry->description }}]]></summary>
    </entry>
    @endforeach
</feed>