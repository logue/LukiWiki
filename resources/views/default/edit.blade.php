@extends('layout.default')

@if(!empty($source))
@section('title', sprintf(__('Edit %s'), $page) )
@else
@section('title', __('Create new page') )
@endif

@section('content')
<form action="{{ url($page) }}" method="POST">
    @csrf
    <input type="hidden" name="hash" value="{{ $hash ?? 0 }}" />
    <input type="hidden" name="origin" value="{{ $source ?? '' }}" />
    <lw-editor>
        <div class="input-group" slot="header">
            <div class="input-group-prepend">
                <label class="input-group-text" for="txt_page">Page Name</label>
            </div>
            <input type="text" class="form-control" id="txt_page" name="page" value="{{ $page }}" />
        </div>
        <textarea name="source" class="form-control" slot="body">{{ $source ?? '' }}</textarea>
        <div class="text-right" slot="footer">
            <button type="submit" class="btn btn-primary" name="action" value="save">Save</button>
            <button type="submit" class="btn btn-secondary" name="action" value="cancel">Cancel</button>
        </div>
    </lw-editor>
</form>
@endsection