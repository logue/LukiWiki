@extends('layout.default')

@section('content')
<div class="row">
    <div class="col">
        <p class="lead">@yield('code', __('Oh no'))</p>
        <p>@yield('message')</p>
        <p class="text-center">
            <a href="{{ app('router')->has('home') ? route('home') : url('/') }}"
                class="btn btn-lg btn-outline-secondary">{{ __('Go Home') }}</a>
        </p>
    </div>
    <div class="col">
        @yield('image')
    </div>
</div>
@endsection