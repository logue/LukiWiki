@extends('errors.layout')

@section('title', __('Forbidden'))
@section('code', '403')

@section('image')
<img src="{{ asset('/svg/403.svg') }}" class="img-fluid" />
@endsection

@section('message', __($exception->getMessage() ?: 'Sorry, you are forbidden from accessing this page.'))
