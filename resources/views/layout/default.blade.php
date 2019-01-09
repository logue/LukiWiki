<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="generator" content="LukiWiki v0.0.0-alpha" />
    <title>@yield('title') - {{ Config::get('lukiwiki.sitename') }}</title>
    @if(isset($page))
    <link rel="canonical" href="{{ $page ? url($page) : url('/') }}" />
    <link rel="amphtml" href="{{ $page ? url($page) : url('/') }}?action=amp" />
    @endif
    <link rel="alternate" type="application/atom+xml" title="RecentChanges" href="{{ url('/') }}?action=atom" />
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
    <!--[if IE]>
    <link href="{{ asset('css/bootstrap-ie9.css') }}" rel="stylesheet" />
<![endif]-->
    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    @yield('styles')
</head>

<body>
    <div id="app">
        <lw-navbar baseUri="{{ url('/') }}" page="{{ $page }}">
            <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <a class="navbar-brand" href="{{ url('/') }}" ref="name">{{ Config::get('lukiwiki.sitename') }}</a>
            </nav>
        </lw-navbar>
        <main class="container mt-2">
            <h1>{{ $title }}</h1>
            <hr />
            @yield('content')
        </main>
        <footer class="bg-light mt-1">
            <div class="container">
                <p><strong>LukiWiki</strong> v0.0.0-alpha / <small>Process Time: <var>{{ sprintf('%0.3f',
                            microtime(true) -
                            LARAVEL_START) }}</var> sec.</small></p>
            </div>
        </footer>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>