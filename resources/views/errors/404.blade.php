@extends('errors.layout')

@section('code', '404')
@section('title', __('Page Not Found'))

@section('image')
<img src="{{ asset('/svg/404.svg') }}" class="img-fluid" />
@endsection

@section('message', __('Sorry, the page you are looking for could not be found.'))
