<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="generator" content="LukiWiki v{{ \App\LukiWiki\Parser::VERSION }}" />
    <title>@yield('title') - {{ Config::get('lukiwiki.sitename') }}</title>
    @if(isset($page))
    <link rel="canonical" href="{{ $page ? url($page) : url('/') }}" />
    <link rel="amphtml" href="{{ $page ? url($page) : url('/') }}:amp" />
    <link rel="print" href="{{ $page ? url($page) : url('/') }}:print" />
    @endif
    <link rel="alternate" type="application/atom+xml" title="RecentChanges" href="{{ url('/api/:atom') }}" />
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
    @yield('styles')
</head>

<body class="h-100">
    <div id="app" class="d-flex flex-column h-100">
        <header>
            <lw-navbar brand="{{ Config::get('lukiwiki.sitename') }}" base-uri="{{ url('/') }}"
                page="{{ $page ?? '' }}">
                <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                    <a class="navbar-brand" href="{{ url('/') }}">{{ Config::get('lukiwiki.sitename') }}</a>
                </nav>
            </lw-navbar>
        </header>
        <main role="main" class="flex-shrink-0">
            <div class="container py-2">
                @if(Session::has('message'))
                <b-alert show dismissible>{{ session('message') }}</b-alert>
                @endif
                <h1>@yield('title')</h1>
                <hr />
                @yield('content')
            </div>
        </main>
        <footer class="bg-light mt-auto py-3">
            <div class="container">
                <p><strong>LukiWiki</strong> v{{ \App\LukiWiki\Parser::VERSION }} / <small>Process Time: <var>{{
                            sprintf('%0.3f', microtime(true) - LARAVEL_START) }}</var> sec.</small></p>
            </div>
        </footer>
    </div>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>