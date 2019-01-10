@extends('layout.default')

@section('content')
<pre class="CodeMirror" v-lw-sh data-lang="lukiwiki">{{ $source }}</pre>
@endsection