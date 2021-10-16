@extends('layout.dashboard')

@section('content')
<table class="table">
  <caption>User List</caption>
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Name</th>
    </tr>
  </thead>
  <tbody>
    @foreach($users as $user)
    <tr>
      <th scope="row">{{ $user['id'] }}</th>
      <td>{{ $user['name'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
