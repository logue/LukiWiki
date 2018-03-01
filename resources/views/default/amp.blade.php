<!DOCTYPE html>
<html ⚡>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('lukiwiki.sitename') }}</title>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <link rel="canonical" href="{{ url($page) }}" />
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <style amp-custom="">{!! str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', \File::get(public_path('css/amp.css'))) !!}</style>
</head>

<body>
    <header>
        <h1><a class="navbar-brand" href="{{ url('/') }}">{{ Config::get('lukiwiki.sitename') }}</a></h1>
    </header>

    <amp-sidebar id="header-sidebar" class="ampstart-sidebar px3" layout="nodisplay">
        <div class="flex justify-start items-center ampstart-sidebar-header">
            <div role="button" aria-label="close sidebar" on="tap:header-sidebar.toggle" tabindex="0" class="ampstart-navbar-trigger items-start">✕</div>
        </div>
        <nav class="ampstart-sidebar-nav ampstart-nav">
            @yield('amp.sidebar')
        </nav>
    </amp-sidebar>

    <main class="container" id="content" role="main">
        <h1>{{ $page }}</h1>
        {!! $content !!}
    </main>
    
    <footer class="bg-light">
        <div class="container">
            @yield('footer')
        </div>
    </footer>
</body>

</html>