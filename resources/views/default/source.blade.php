@extends('layout.default')

@section('title', sprintf(__('Source of %s'), $page))

@section('content')
<pre class="CodeMirror" v-lw-sh data-lang="lukiwiki">{{ $source }}</pre>
@endsection