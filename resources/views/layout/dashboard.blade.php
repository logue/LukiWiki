<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="generator" content="LukiWiki v{{ \App\LukiWiki\Parser::VERSION }}" />
  <meta name="robots" content="noindex, nofollow" />
  <title>@yield('title') - {{ Config::get('app.name') }}</title>
  <link rel="stylesheet" type="text/css" href="{{ mix('css/'.Config::get('lukiwiki.theme').'.css') }}" />
  <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}" type="text/css" />
  @yield('styles')
</head>

<body>
  <div id="app">
    <nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="{{ url('/') }}">{{ Config::get('app.name') }}</a>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="{{ url(':dashboard') }}">
                  ダッシュボード <span class="sr-only">(current)</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ url(':dashboard/interwiki') }}">
                  InterWiki設定
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ url(':dashboard/convert') }}">
                  変換
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ url(':dashboard/users') }}">
                  ユーザ一覧
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="{{ url(':dashboard/packages') }}">
                  パッケージの一覧・更新
                </a>
              </li>
            </ul>

            <!--h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>Saved reports</span>
              <a class="d-flex align-items-center text-muted" href="#">
                <span data-feather="plus-circle"></span>
              </a>
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Current month
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Last quarter
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Social engagement
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  Year-end sale
                </a>
              </li>
            </ul-->
          </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
          @if(Session::has('message'))
          <b-alert show dismissible>{{ session('message') }}</b-alert>
          @endif
          <h1 class="h2">{{ $title }}</h1>
          <hr />
          @yield('content')
        </main>
      </div>
    </div>
  </div>
  <script src="{{ mix('js/manifest.js') }}"></script>
  <script src="{{ mix('js/vendor.js') }}"></script>
  <script src="{{ mix('js/app.js') }}"></script>
  @yield('scripts')
</body>

</html>
