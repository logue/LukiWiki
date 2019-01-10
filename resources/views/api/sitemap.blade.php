<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($entries as $name=>$info)
    <url>
        <loc>{{ url($name) }}</loc>
        <lastmod>{{ date('Y-m-d\TH:i:sP', $info['timestamp']) }}</lastmod>
        <xhtml:link rel="alternate" media="only screen and (max-width: 640px)" href="{{ url($name) }}?action=amp" />
    </url>
    @endforeach
</urlset>