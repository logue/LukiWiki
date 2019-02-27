@extends('layout.default')

@section('content')
<form action="{{ url($page) }}" method="POST">
    @csrf
    <input type="hidden" name="hash" value="{{ $hash }}" />
    <lw-merge>
        <input type="hidden" name="origin" value="{{ $origin }}" slot="origin" />
        <div class="row">
            <div class="col-sm">
                <textarea name="remote" class="form-control" slot="remote">{{ $remote }}</textarea>
            </div>
            <div class="col-sm">
                <textarea name="local" class="form-control" slot="local">{{ $local }}</textarea>
            </div>
        </div>
        <div class="text-right" slot="footer">
            <button type="submit" class="btn btn-primary" name="action" value="save">Save</button>
            <button type="submit" class="btn btn-secondary" name="action" value="cancel">Cancel</button>
        </div>
    </lw-merge>
</form>
@endsection