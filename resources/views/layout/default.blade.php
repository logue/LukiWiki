<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="generator" content="LukiWiki v{{ \App\LukiWiki\Parser::VERSION }}" />
    <title>@yield('title') - {{ Config::get('app.name') }}</title>
@if(isset($page))
    <link rel="canonical" href="{{ url('/'.str_replace('%2F', '/', rawurlencode($page)) ) }}" />
    <link rel="amphtml" href="{{ url('/'.str_replace('%2F', '/', rawurlencode($page)) .':amp' )  }}" />
    <link rel="archives" href="{{ url('/'.str_replace('%2F', '/', rawurlencode($page)) .':history' ) }}" />
    <link rel="print" href="{{ url('/'.str_replace('%2F', '/', rawurlencode($page)) .':print' ) }}" />
@endif
    <link rel="home" href="{{ url('') }}" />
    <link rel="search" type="application/opensearchdescription+xml" href="{{ url(':api/opensearch') }}">
    <link rel="sitemap" type="application/xml" href="{{ url(':api/sitemap') }}">
    <link rel="alternate" type="application/atom+xml" href="{{ url(':api/atom') }}" />
    <link rel="stylesheet" type="text/css" href="{{ mix('css/'.Config::get('lukiwiki.theme').'.css') }}" />
    @yield('styles')
</head>

<body class="h-100">
    <div id="app" class="d-flex flex-column h-100">
        <header>
            <lw-navbar brand="{{ Config::get('app.name') }}" base-uri="{{ url('/') }}"
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
                <!--nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Library</li>
                    </ol>
                </nav -->
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