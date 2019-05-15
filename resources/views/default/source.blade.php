@extends('layout.default')

@if(isset($age))
@section('title', sprintf(__('Backup of %s (%d)'), $page, $age))
<p><a href="{{ url($page.':history') }}" class="btn btn-secondary">Back to History list</a></p>
@else
@section('title', sprintf(__('Source of %s'), $page))
@endif

@section('content')
<pre class="pre CodeMirror" v-lw-sh data-lang="lukiwiki">{{ $source }}</pre>
@endsection