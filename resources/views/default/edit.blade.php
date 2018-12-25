@extends('layout.default')
@section('title', $title)
@section('navbar')
<ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle active" href="#" id="pageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            Page
        </a>
        <div class="dropdown-menu" aria-labelledby="pageDropdown">
            @if($hash !== 0)
            <a class="dropdown-item" href="{{ url('/') }}?action=new">New</a>
            <a class="dropdown-item active" href="{{ url($page) }}?action=edit">Edit</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=source">Source</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=attachment">Attachment</a>
            <a class="dropdown-item" href="{{ url($page) }}?action=history">History</a>
            @else
            <a class="dropdown-item active" href="{{ url('/') }}?action=new">New</a>
            <a class="dropdown-item disabled" href="#">Edit</a>
            <a class="dropdown-item disabled" href="#">Source</a>
            <a class="dropdown-item disabled" href="#">Attachment</a>
            <a class="dropdown-item disabled" href="#">History</a>
            @endif
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="listDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
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
    <input type="hidden" name="action" value="edit" />
    @if($hash !== 0)
    <input type="hidden" name="hash" value="{{ $hash }}" />
    <input type="hidden" name="original" value="{{ $source }}" />
    @endif
    <lw-editor>
        <div class="input-group" slot="page">
            <div class="input-group-prepend">
                <label class="input-group-text" for="pagename-textbox">Page Name</label>
            </div>
            <input type="text" class="form-control" id="pagename-textbox" name="page" value="{{ $page }}" />
        </div>
        <textarea name="body" class="form-control" slot="body">{{ $source }}</textarea>
    </lw-editor>
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
                    <span class="input-group-text" id="password-addon">
                        <i class="fa fa-key"></i>
                    </span>
                </div>
                <input type="password" name="password" class="form-control" aria-label="Password" aria-describedby="password-addon" />
            </div>
        </div>
        <div class="col-3 ml-auto text-right">
            <button type="button" class="btn btn-secondary" name="cancel"><i class="fas fa-ban"></i> Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="far fa-save"></i> Save</button>
        </div>
    </div>
</form>
@endsection