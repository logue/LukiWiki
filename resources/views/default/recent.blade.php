@extends('layout.default')

@section('title', $title)

@section('navbar')
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="pageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Page
        </a>
        <div class="dropdown-menu" aria-labelledby="pageDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=new">New</a>
            <a class="dropdown-item disabled" href="#">Edit</a>
            <a class="dropdown-item disabled" href="#">Source</a>
            <a class="dropdown-item disabled" href="#">Attachment</a>
            <a class="dropdown-item disabled" href="#">Backup</a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle active" href="#" id="listDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          List
        </a>
        <div class="dropdown-menu" aria-labelledby="listDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=list">Page List</a>
            <a class="dropdown-item active" href="{{ url('/') }}?action=recent">Recent Changes</a>
        </div>
    </li>
</ul>
@endsection

@section('content')
<ol class="recent">
@foreach ($entries as $name=>$info)
    <li><a href="{{ url($name) }}" data-timestamp="{{ $info['timestamp'] }}">{{ $name }}</a> - {{ date('Y-m-d\TH:i:sP', $info['timestamp']) }}</li>
@endforeach
</ol>
@endsection