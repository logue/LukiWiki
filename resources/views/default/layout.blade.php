<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <link rel="canonical" href="{{ Request::fullUrl() }}" />
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css" />
<!--[if IE]>
    <link href="{{ asset('css/bootstrap-ie9.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/html5shiv.min.js') }}"></script>
<![endif]-->
    <!--[if lt IE 9]>
    <link href="{{ asset('css/bootstrap-ie8.css') }}" rel="stylesheet" />
<![endif]-->
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <a class="navbar-brand" href="#">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse-content" aria-controls="navbar-collapse-content" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-collapse-content">
            @yield('default.navbar')
            <form class="form-inline my-2 my-lg-0 mr-0" action="{{ url(':search') }}">
                <input type="hidden" name="csrf_token" value="{{ csrf_token() }}" />
                <input type="search" class="form-control mr-sm-2" placeholder="Search" aria-label="Search" />
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </nav>

    <main class="container mt-2" id="app">
        @yield('default.content')
    </main>
    <footer class="bg-light">
        <div class="container">
            @yield('default.footer')
        </div>
    </footer>
    @yield('default.scripts')
</body>

</html>