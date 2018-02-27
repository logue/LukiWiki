@extends('layout.default')

@section('title', $page )

@section('navbar')
<ul class="navbar-nav mr-auto">
    <li class="nav-item">
        <a class="nav-link" href="{{ url($page) }}?action=edit">Edit</a>
    </li>
    <li class="nav-item">
        <a class="nav-link disabled" href="{{ url($page) }}?action=lock">Lock</a>
    </li>
    <li class="nav-item">
        <a class="nav-link disabled" href="{{ url($page) }}?action=attachment">Attachment</a>
    </li>
</ul>
<ul class="navbar-nav ml-auto mr-1">
    <li class="nav-item">
        <a class="nav-link disabled" href="{{ url('/') }}?action=new">New</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ url($page) }}?action=edit">Edit</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ url($page) }}?action=backup">Backup</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          List
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=list">Page List</a>
            <a class="dropdown-item" href="{{ url('/') }}?action=recent">Recent Changes</a>
        </div>
    </li>
</ul>
@endsection

@section('content')
{!! $content !!}
@endsection