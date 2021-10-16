@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<section>
  <b-button v-b-modal.modal-1>New</b-button>
  <table class="table">
    <caption>InterWiki Rules</caption>
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Name</th>
        <th scope="col">Value</th>
        <th scope="col">Type</th>
        <th scope="col">Encode</th>
        <th scope="col">Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($entries as $entry)
      <tr>
        <th scope="row">{{ $entry->id }}</th>
        <td>{{ $entry->name }}</td>
        <td>{{ $entry->value }}</td>
        <td>{{ $entry->type }}</td>
        <td>{{ $entry->encode }}</td>
        <td>
          <b-button v-b-modal.edit value="{{ $entry->id }}">Edit</b-button>
          <b-button v-b-modal.delete value="{{ $entry->id }}">Delete</b-button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
<div>
  <b-modal id="modal-1" title="New">
    <p class="my-4">Hello from modal!</p>
  </b-modal>
</div>
@endsection
