@extends('layout.default')

@section('content')
@php($i=0)
<section id="histories">
  <table class="table table-borderd table-sm mx-0">
    <thead>
      <tr>
        <th>#</th>
        <th>Date</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      @foreach($entries as $backup)
      <tr>
        <td>{{ $i++ }}</td>
        <td><a href="{{ url($page.':history/'.$backup->id) }}">{{ $backup->updated_at }}</a></td>
        <td>{{$backup->ip_address}}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
@endsection