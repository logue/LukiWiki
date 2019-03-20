@extends('layout.default')

@section('title', sprintf(__('Histories of %s'), $page))

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
        <td><a href="{{ url($page.':history/'.$i) }}">{{ $backup->updated_at }}</a></td>
        <td><a href="https://www.robtex.com/ip-lookup/?{{ $backup->ip_address }}" target="_blank">{{ $backup->ip_address }}</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
@endsection