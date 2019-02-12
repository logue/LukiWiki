@extends('layout.default')

@section('content')
<section id="filelist">
  <table class="table table-borderd table-sm mx-0">
    <thead>
      <tr>
        <th>Name</th>
        <th>Size</th>
        <th>Mime</th>
        <th>Uploaded</th>
        <th>Manage</th>
      </tr>
    </thead>
    <tbody>
      @foreach($attachments as $attachment)
      <tr>
        <td><a href="{{ url($page.':attachments/'.$attachment->name) }}" title="{{ $attachment->hash }}">{{ $attachment->name }}</a></td>
        <td>{{$attachment->size}}</td>
        <td>{{$attachment->mime}}</td>
        <td>{{$attachment->updated_at}}</td>
        <td></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
<section id="upload">
  <fieldset>
    <legend>Upload</legend>
    <form enctype="multipart/form-data" action="{{ url('/:upload') }}" method="post" class="form-inline">
      @csrf
      <input type="hidden" name="action" value="attachment" />
      <input type="hidden" name="page" value="{{ $page }}" />
      <div class="custom-file">
        <input type="file" class="custom-file-input" id="attachment">
        <label class="custom-file-label" for="attachment" multiple="multiple">Select attachment file</label>
      </div>
      <button class="btn btn-primary" type="submit"><span class="fa fa-upload"></span>アップロード</button>
    </form>
  </fieldset>
</section>
@endsection