@extends('layout.default')

@section('title', $page )

@section('navbar')
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="pageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Page
        </a>
        <div class="dropdown-menu" aria-labelledby="pageDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=new"><i class="far fa-file"></i> New</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=edit"><i class="fas fa-edit"></i> Edit</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=clone"><i class="fas fa-clone"></i> Clone</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=source"><i class="far fa-file-code"></i> Source</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=attachment"><i class="fas fa-paperclip"></i> Attachment</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=history"><i class="fas fa-history"></i> History</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=lock"><i class="fas fa-unlock-alt"></i> Lock</a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="listDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          List
        </a>
        <div class="dropdown-menu" aria-labelledby="listDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=list"><i class="far fa-list-alt"></i> Page List</a>
            <a class="dropdown-item" href="{{ url('/') }}?action=recent">Recent Changes</a>
            <a class="dropdown-item" href="{{ url('/') }}?action=recent">Recent Changes</a>
        </div>
    </li>
</ul>
<span class="navbar-text mx-2">
    <i class="fas fa-unlock"></i>
</span>
@endsection

@section('content')
{!! $content !!}
@endsection