@extends('layout.default')

@section('content')
<form action="{{ url('/') }}" method="POST">
    @csrf
    <input type="hidden" name="action" value="edit" />
    <lw-editor>
        <div class="input-group" slot="header">
            <div class="input-group-prepend">
                <label class="input-group-text" for="pagename-textbox">Page Name</label>
            </div>
            <input type="text" class="form-control" id="pagename-textbox" name="page" value="{{ $page }}" />
        </div>
        <textarea name="body" class="form-control" slot="body">{{ $source }}</textarea>
        <div class="form-row align-items-center mt-1" slot="footer">
            <div class="col">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="keeep-timestamp" id="keep-timestamp-checkbox">
                    <label class="custom-control-label" for="keep-timestamp-checkbox">Keep timestamp</label>
                </div>
            </div>
            <div class="col">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="password-addon">
                            Password
                        </span>
                    </div>
                    <input type="password" name="password" class="form-control" aria-label="Password" aria-describedby="password-addon" />
                </div>
            </div>
            <div class="col-4 ml-auto mr-0">
                <button type="submit" class="btn btn-primary" name="action" value="submit">Save</button>
                <button type="submit" class="btn btn-secondary" name="action" value="cancel">Cancel</button>
            </div>
        </div>
    </lw-editor>
</form>
@endsection