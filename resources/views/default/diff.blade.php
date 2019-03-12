@extends('layout.default')

@section('title', sprintf(__('Difference of %s'), $page) )

@section('content')
<pre class="CodeMirror" v-lw-sh data-lang="diff">{{ $diff }}</pre>
@endsection