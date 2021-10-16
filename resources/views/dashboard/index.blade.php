@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<section>
  <h2>アクセス情報</h2>
  <div class="row">
    <div class="col-md-4 col-lg">
      <div class="card bg-primary text-white p-3">
        <div class="card-body">
          本日のアクセス：{{ $counter->today()->sum('today') }}
        </div>
      </div>
    </div>
    <div class="col-md-4 col-lg">
      <div class="card bg-info text-white p-3">
        <div class="card-body">
          本日の更新
        </div>
      </div>
    </div>
    <div class="col-md-4 col-lg">
      <div class="card bg-warning text-white p-3">
        <div class="card-body">
          本日のユーザ
        </div>
      </div>
    </div>
  </div>
</section>

<section>
  <h2>キャッシュ削除</h2>
  <form action="{{ url(':dashboard/clear-cache') }}" method="post">
    @csrf
    <div class="custom-control custom-checkbox">
      <input type="checkbox" id="cache_view" name="cache" value="view" class="custom-control-input">
      <label class="custom-control-label" for="cache_view">ビュー</label>
    </div>
    <div class="custom-control custom-checkbox">
      <input type="checkbox" id="cache_debug" name="cache" value="debug" class="custom-control-input">
      <label class="custom-control-label" for="cache_debug">デバッグ</label>
    </div>
    <div class="custom-control custom-checkbox">
      <input type="checkbox" id="cache_system" name="cache" value="system" class="custom-control-input">
      <label class="custom-control-label" for="cache_system">システム</label>
    </div>
    <button type="submit" class="btn btn-primary">実行</button>
  </form>
</section>
@endsection
