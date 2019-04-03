@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<section>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Value</th>
                <th>Type</th>
                <th>Encode</th>
                <th>Action</th>
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
                <td></td>
             </tr>
         @endforeach
        </tbody>
    </table>
</section>
<div>
    <b-button v-b-modal.modal-1>New</b-button>
    <b-modal id="modal-1" title="New">
        <p class="my-4">Hello from modal!</p>
    </b-modal>
</div>
@endsection