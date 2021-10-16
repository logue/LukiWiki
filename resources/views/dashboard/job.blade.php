@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<section>
  <table class="table">
    <caption>現在キューにあるジョブ</caption>
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">キューの種類</th>
        <th scope="col">実行内容</th>
        <th scope="col">施行日</th>
        <th scope="col">有効日</th>
        <th scope="col">作成日</th>
        <th scope="col">作業</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($jobs as $job)
      {{--@php(dd(unserialize($job->payload['data']['command'])))--}}
      <tr>
        <th scope="row">{{ $job->id }}</th>
        <td>{{ $job->queue }}</td>
        <td class="text-truncate">{{ $job->payload['data']['command'] }}</td>
        <td>{{ $job->attempts }}</td>
        <td>{{ $job->reserved_at }}</td>
        <td>{{ $job->available_at }}</td>
        <td>{{ $job->created_at }}</td>
        <td></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $jobs->links() }}
</section>

<section>
  <h2>失敗したジョブ</h2>
</section>
@endsection
