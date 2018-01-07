<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>{{ config('app.name') }}</title>
	<link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css" />
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
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault"
		 aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarsExampleDefault">
			@if (Route::has('login'))
			<ul class="navbar-nav mr-auto">
				@auth
				<li class="nav-link active">
					<a href="{{ url('/home') }}">Home
						<span class="sr-only">(current)</span>
					</a>
				</li>
				@else
				<li class="nav-item">
					<a class="nav-link" href="{{ route('login') }}">Login</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{ route('register') }}">Register</a>]
				</li>
				@endauth
			</ul>
			<form class="form-inline my-2 my-lg-0">
				<input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
				<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
			</form>
		</div>
		@endif
	</nav>
	<main role="main" class="container">
		<div class="jumbotron text-center mt-3">
			<h1 class="display-4">Laravel</h1>
			<p class="display-1">
				<i class="fab fa-laravel"></i>
			</p>
			<p class="lead">Laravel + Bootstrap4 + Font Awasome Test.</p>

		</div>

		<ul class="list-inline">
			<li class="list-inline-item">
				<a href="https://laravel.com/docs">Documentation</a>
			</li>
			<li class="list-inline-item">
				<a href="https://laracasts.com">Laracasts</a>
			</li>
			<li class="list-inline-item">
				<a href="https://laravel-news.com">News</a>
			</li>
			<li class="list-inline-item">
				<a href="https://forge.laravel.com">Forge</a>
			</li>
			<li class="list-inline-item">
				<a href="https://github.com/laravel/laravel">GitHub</a>
			</li>
		</ul>
	</main>
	<footer class="bg-light">
		<div class="container">
			<span class="text-muted">Place sticky footer content here.</span>
		</div>
	</footer>
	<script src="{{asset('js/app.js')}}"></script>
</body>

</html>