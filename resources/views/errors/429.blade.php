@extends('layout.error')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('image')
<img src="{{ asset('/svg/403.svg') }}" class="img-fluid" />
@endsection

@section('message', __('Sorry, you are making too many requests to our servers.'))