<!DOCTYPE html>
<html ⚡>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title')</title>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <link rel="canonical" href="{{ Request::fullUrl() }}" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        @yeilds('amp.header');
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
</head>

<body>
    <header class="ampstart-headerbar fixed flex justify-start items-center top-0 left-0 right-0 pl2 pr4 ">
        <div role="button" aria-label="open sidebar" on="tap:header-sidebar.toggle" tabindex="0" class="ampstart-navbar-trigger pr2">☰</div>
        {{ config('lukiwiki.sitename') }}
    </header>

    <amp-sidebar id="header-sidebar" class="ampstart-sidebar px3" layout="nodisplay">
        <div class="flex justify-start items-center ampstart-sidebar-header">
            <div role="button" aria-label="close sidebar" on="tap:header-sidebar.toggle" tabindex="0" class="ampstart-navbar-trigger items-start">✕</div>
        </div>
        <nav class="ampstart-sidebar-nav ampstart-nav">
            @yield('amp.sidebar')
        </nav>
    </amp-sidebar>

    <main id="content" role="main">
        @yield('amp.content')
    </main>
    
    <footer class="ampstart-footer flex flex-column items-center px3">
        @yield('amp.footer')
    </footer>
</body>

</html>