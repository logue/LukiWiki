@extends('errors.layout')

@section('title', __('Unauthorized'))
@section('code', '401')

@section('image')
<img src="{{ asset('/svg/403.svg') }}" class="img-fluid" />
@endsection

@section('message', __('Sorry, you are not authorized to access this page.'))