@extends('layout.error')

@section('title', __('Page Expired'))
@section('code', '419')
@section('image')
<img src="{{ asset('/svg/403.svg') }}" class="img-fluid" />
@endsection

@section('message', __('Sorry, your session has expired. Please refresh and try again.'))
