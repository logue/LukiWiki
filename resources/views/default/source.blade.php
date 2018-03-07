@extends('layout.default')

@section('title', $title)

@section('navbar')
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle active" href="#" id="pageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Page
        </a>
        <div class="dropdown-menu" aria-labelledby="pageDropdown">
            <a class="dropdown-item" href="{{ url($page) }}?action=new">New</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=edit">Edit</a>
            <a class="dropdown-item active" href="{{ url($page) }}?action=source">Source</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=attachment">Attachment</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=backup">Backup</a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="listDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          List
        </a>
        <div class="dropdown-menu" aria-labelledby="listDropdown">
            <a class="dropdown-item" href="{{ url('/') }}?action=list">Page List</a>
            <a class="dropdown-item" href="{{ url('/') }}?action=recent">Recent Changes</a>
        </div>
    </li>
</ul>
<span class="navbar-text mx-2">
    <i class="fas fa-unlock"></i>
</span>
@endsection

@section('content')
<textarea id="source" class="form-control" cols="20">
{{ $source or '' }}
</textarea>
@endsection

@section('scripts')
<script>
window.CodeMirror.fromTextArea(document.getElementById('source'), {
  lineNumbers: true,
  styleActiveLine: true,
  matchBrackets: true,
  readOnly: true,
  height: 'auto',
  mode: 'text/lukiwiki'
})
$('.CodeMirror').addClass('form-control px-0 py-0 my-0 mx-auto').css('height', 'auto')
</script>
@endsection