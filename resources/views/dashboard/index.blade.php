@extends('layout.dashboard')

@section('title', 'index')

@section('content')
<sectioon>
    <div class="row">
        <div class="col-md-4 col-lg">
            <div class="card bg-primary text-white p-3">
                <div class="card-body pb-0">
                    本日のアクセス件数
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card bg-info text-white p-3">
                <div class="card-body pb-0">
                    本日の更新
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="card bg-warning text-white p-3">
                <div class="card-body pb-0">
                    本日のユーザ
                </div>
            </div>
        </div>
    </div>
</sectioon>


@endsection