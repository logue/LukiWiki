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
            <a class="dropdown-item active" href="{{ url($page) }}?action=edit">Edit</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=source">Source</a>
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
<form action="{{ url('/') }}" method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}" />
    <input type="hidden" name="page" value="{{ $page }}" />
    <input type="hidden" name="action" value="edit" />
    <div class="form-group row">
        <label for="pagename-textbox" class="col-sm-2 col-form-label col-form-label-sm"></label>
        <div class="col-sm-10">
            
        </div>
    </div>
    <div class="btn-toolbar justify-content-between mb-1" role="toolbar" aria-label="Toolbar with button groups">
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text" id="btnGroupAddon2">Page Name</div>
            </div>
            <input type="text" class="form-control form-control-sm" id="pagename-textbox" readonly="readonly" name="page" value="{{ $page }}" />
        </div>
        <div class="btn-group" role="group" aria-label="First group">
            <button class="btn btn-outline-secondary btn-sm replace" title="Bold" name="b"><i class="fa fa-bold"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Italic" name="i"><i class="fa fa-italic"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Strike" name="s"><i class="fa fa-strikethrough"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Underline" name="u"><i class="fa fa-underline"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Code" name="code"><i class="fa fa-code"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Quotation" name="q"><i class="fa fa-quote-left"></i></button>
        </div>
        <div class="btn-group" role="group" aria-label="First group">
            <button class="btn btn-outline-secondary btn-sm replace" title="Insert Link" name="url"><i class="fa fa-link"></i></button>
            <button class="btn btn-outline-secondary btn-sm replace" title="Font size" name="size"><i class="fa fa-text-height"></i></button>
            <button class="btn btn-outline-secondary btn-sm insert" title="Color" name="color">color</button>
        </div>
        <div class="btn-group" role="group" aria-label="First group">
            <button class="btn btn-outline-secondary btn-sm insert" title="Line break" name="br">⏎</button>
        </div>
        <div class="btn-group" role="group" aria-label="First group">
            <button class="btn btn-outline-secondary btn-sm replace" title="Convert character reference" name="ncr">&amp;#</button>
            <button class="btn btn-outline-secondary btn-sm insert" title="Hint" name="hint"><i class="fa fa-question-circle"></i></button>
        </div>
        <button class="btn btn-outline-secondary btn-sm disabled pull-right hidden" id="indicator"><span class="fa fa-refresh fa-spin"></span></button>
    </div>
    <div class="form-group">
        <textarea class="form-control" id="source" rows="20" name="source" id="source">{{$source or ''}}</textarea>
    </div>
    <div class="form-row align-items-center mt-1">
        <div class="col-auto">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="keeep-timestamp" id="keep-timestamp-checkbox">
                <label class="custom-control-label" for="keep-timestamp-checkbox">Keep timestamp</label>
            </div>
        </div>
        <div class="col-auto">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="password-addon"><i class="fa fa-key"></i></span>
                </div>
                <input type="password" name="password" class="form-control" aria-label="Password" aria-describedby="password-addon" />
            </div>
        </div>
        <div class="col-auto ml-auto mr-0">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
window.CodeMirror.fromTextArea(document.getElementById('source'), {
  lineNumbers: true,
  styleActiveLine: true,
  matchBrackets: true
})
$('.CodeMirror').addClass('form-control px-0 py-0 my-0 mx-auto')
</script>
@endsection