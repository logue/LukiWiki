<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    @foreach ($entries as $entry)
    <url>
        <loc>{{ url($entry->name) }}</loc>
        <lastmod>{{ $entry->updated_at }}</lastmod>
    </url>
    @endforeach
</urlset>