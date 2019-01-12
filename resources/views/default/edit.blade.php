@extends('layout.default')

@section('content')
<form action="{{ url('/') }}" method="POST">
    @csrf
    <input type="hidden" name="hash" value="{{ $hash }}" />
    <input type="hidden" name="original" value="{{ $source }}" />
    <lw-editor>
        <div class="input-group" slot="header">
            <div class="input-group-prepend">
                <label class="input-group-text" for="pagename-textbox">Page Name</label>
            </div>
            <input type="text" class="form-control" id="pagename-textbox" name="page" value="{{ $page }}" />
        </div>
        <textarea name="source" class="form-control" slot="body">{{ $source }}</textarea>
        <div class="text-right" slot="footer">
            <button type="submit" class="btn btn-primary" name="action" value="save">Save</button>
            <button type="submit" class="btn btn-secondary" name="action" value="cancel">Cancel</button>
        </div>
    </lw-editor>
</form>
@endsection