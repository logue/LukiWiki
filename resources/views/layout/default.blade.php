<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="generator" content="LukiWiki v{{ \App\LukiWiki\Parser::VERSION }}" />
    <title>{{ $title }} - {{ Config::get('lukiwiki.sitename') }}</title>
    @if(isset($page))
    <link rel="canonical" href="{{ $page ? url($page) : url('/') }}" />
    <link rel="amphtml" href="{{ $page ? url($page) : url('/') }}?action=amp" />
    @endif
    <link rel="alternate" type="application/atom+xml" title="RecentChanges" href="{{ url('/') }}?action=atom" />
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
    @yield('styles')
</head>

<body>
    <div id="app">
        <lw-navbar brand="{{ Config::get('lukiwiki.sitename') }}" baseUri="{{ url('/') }}" page="{{ $page ?? '' }}">
            <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <a class="navbar-brand" href="{{ url('/') }}">{{ Config::get('lukiwiki.sitename') }}</a>
            </nav>
        </lw-navbar>
        <main class="container py-2">
@if(Session::has('message'))
            <b-alert show dismissible>{{ session('message') }}</b-alert>
@endif
            <h1>{{ $title }}</h1>
            <hr />
            @yield('content')
        </main>
        <footer class="bg-light">
            <div class="container">
                <p><strong>LukiWiki</strong> v{{ \App\LukiWiki\Parser::VERSION }} / <small>Process Time: <var>{{
                            sprintf('%0.3f', microtime(true) - LARAVEL_START) }}</var> sec.</small></p>
            </div>
        </footer>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>