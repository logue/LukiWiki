@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<section>
    <b-button v-b-modal.modal-1>New</b-button>
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