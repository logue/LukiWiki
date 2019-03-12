<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} - {{ Config::get('lukiwiki.sitename') }}</title>
    @if(isset($page))
    <link rel="canonical" href="{{ $page ? url($page) : url('/') }}" />
    @endif
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.3.0/paper.css" />
    <style>
        @page {
            size: A4
        }
    </style>
</head>

<body class="A4">
    <main id="app" class="sheet padding-10mm mx-auto">
        <article>
            <h1>{{ $page }}</h1>
            <hr />
            {!! $body !!}
        </article>
    </main>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>