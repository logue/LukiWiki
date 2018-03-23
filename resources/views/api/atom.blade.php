<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<feed xmlns="http://www.w3.org/2005/Atom">
    <id>tag:example.com,2012:1234</id>
    <title>{{ $title }}</title>
    <updated>{{ $updated }}</updated>
    <link rel="self" href="{{ url('/') }}?action=atom"/>
    <author><name>author name</name></author>
</feed>