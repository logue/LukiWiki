@extends('layout.default')

@section('title', __('File attached to :page', ['page'=>$page]))

@section('content')
<section id="filelist">
  <table class="table table-borderd table-sm mx-0">
    <caption>Attachments</caption>
    <thead>
      <tr>
        <th scope="col">{{ __('Name') }}</th>
        <th scope="col">{{ __('Size') }}</th>
        <th scope="col">{{ __('Mime') }}</th>
        <th scope="col">{{ __('Uploaded') }}</th>
        <th scope="col">{{ __('Manage') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($attachments as $attachment)
      <tr>
        <td><a href="{{ url($page.':attachments/'.$attachment->name) }}" title="{{ $attachment->hash }}">{{
            $attachment->name }}</a></td>
        <td>{{ $attachment->size }}</td>
        <td>{{ $attachment->mime }}</td>
        <td>{{ $attachment->updated_at }}</td>
        <td>
          <lw-manage-attach id="{{ $attachment->id }}"></lw-manage-attach>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>

<section id="upload">
  <h2>{{ __('Attach to this page') }}</h2>
  <lw-attach-form>
    <form action="{{ url($page.':upload') }}" method="post" enctype="multipart/form-data">
      @csrf
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <font-awesome-icon fas icon="paperclip"></font-awesome-icon>
          </div>
        </div>
        <div class="custom-file">
          <input type="file" class="custom-file-input" id="attachment" aria-describedby="upload" name="file">
          <label class="custom-file-label" for="attachment">{{ __('Please select the file you want to attach.')
            }}</label>
        </div>
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit" id="upload">
            <font-awesome-icon fas icon="file-upload"></font-awesome-icon>
            {{ __('Upload') }}
          </button>
        </div>
      </div>
    </form>
  </lw-attach-form>
</section>
@endsection
