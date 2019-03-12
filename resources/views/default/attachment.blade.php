@extends('layout.default')

@section('title', sprintf(__('Attached files of %s'), $page))

@section('content')
<section id="filelist">
  <table class="table table-borderd table-sm mx-0">
    <thead>
      <tr>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Size') }}</th>
        <th>{{ __('Mime') }}</th>
        <th>{{ __('Uploaded') }}</th>
        <th>{{ __('Manage') }}</th>
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
    <legend>{{ __('Upload') }}</legend>
    <form enctype="multipart/form-data" action="{{ url($page.':upload') }}" method="post" class="form-inline">
      @csrf
      <input type="hidden" name="action" value="attachment" />
      <div class="custom-file">
        <input type="file" class="custom-file-input" id="attachment" name="file" />
        <label class="custom-file-label" for="attachment" multiple="multiple">{{ __('Select attachment file') }}</label>
      </div>
      <button class="btn btn-primary" type="submit" ><span class="fa fa-upload"></span>{{ __('Upload') }}</button>
    </form>
  </fieldset>
</section>
@endsection