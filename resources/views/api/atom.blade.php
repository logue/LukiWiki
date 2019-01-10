<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <id>{{ url('/') }}</id>
    <title>{{ Config::get('lukiwiki.sitename') }}</title>
    <link type="text/html" rel="alternate" href="{{ url('/') }}?action=recent" />
    <link type="application/atom+xml" rel="self" href="{{ url('/') }}?action=atom" />
    @foreach ($entries as $name=>$info)
    <entry>
        <id>{{ url($name) }}</id>
        <title>{{ $name }}</title>
        <updated>{{ date('Y-m-d\TH:i:sP', $info['timestamp']) }}</updated>
    </entry>
    @endforeach
</feed>