@extends('errors.layout')

@section('title', __('Server Error'))
@section('code', '500')

@section('image')
<img src="{{ asset('/svg/500.svg') }}" class="img-fluid" />
@endsection
@section('message', __('Whoops, something went wrong on our servers.'))
