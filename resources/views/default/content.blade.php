@extends('layout.default')

@section('title', $page )

@section('navbar')
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="pageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Page
        </a>
        <div class="dropdown-menu" aria-labelledby="pageDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=new"><i class="far fa-file fa-fw"></i>New</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=edit"><i class="fas fa-edit fa-fw"></i>Edit</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=clone"><i class="fas fa-clone fa-fw"></i>Clone</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=source"><i class="far fa-file-code fa-fw"></i>Source</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=attachment"><i class="fas fa-paperclip fa-fw"></i>Attachment</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=history"><i class="fas fa-history fa-fw"></i>History</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=lock"><i class="fas fa-unlock-alt fa-fw"></i>Lock</a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="listDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          List
        </a>
        <div class="dropdown-menu" aria-labelledby="listDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=list"><i class="far fa-list-alt fa-fw"></i>Page List</a>
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
@if($notes)
<hr />
<aside>
    <ul class="fa-ul">
    @foreach ($notes as $no=>$note)
    <li id="note-{{ $no }}"><a href="#note-anchor-{{ $no }}" id="note-{{ $no }}"><i class="fas fa-thumbtack fa-li"></i><sup>{{ $no }}</sup></a>{!! $note !!}</li>
    @endforeach
    </ul>
</aside>
@endif
@endsection