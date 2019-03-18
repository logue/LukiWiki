@extends('errors.layout')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('image')
<img src="{{ asset('/svg/503.svg') }}" class="img-fluid" />
@endsection

@section('message', __($exception->getMessage() ?: 'Sorry, we are doing some maintenance. Please check back soon.'))
