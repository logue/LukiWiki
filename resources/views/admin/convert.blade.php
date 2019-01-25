@extends('layout.admin')

@section('content')
<p>PukiWiki文法のデーターをLukiWiki文法に変換し、インポートします。</p>
<h2>注意</h2>
<ul>
    <li>最初にWikiデーターの移行から行ってください。</li>
    <li>プラグインは移行できません。</li>
    <li>バックアップの書式は変換しません。</li>
    <li>存在しない（削除済み）ページの添付ファイルや、バックアップの移行はしません。</li>
</ul>
<form action="{{ url(':admin/convert') }}" method="post">
    @csrf
    <div class="form-group">
        <label for="path">PukiWikiのデーターの置かれている場所へのパス</label>
        <input type="text" class="form-control" name="path" id="path"  aria-describedby="pathHelp" placeholder="pukiwiki">
        <small id="pathHelp" class="form-text text-muted">LukiWikiの<code>storage/app</code>内にPukiWikiのディレクトリを入れてください。</small>
    </div>
    <div class="custom-control custom-radio">
      <input type="radio" id="typeWiki" name="type" value="wiki" class="custom-control-input">
      <label class="custom-control-label" for="typeWiki">Wikiデーター（必ず最初に実行してください。）</label>
    </div>
    <div class="custom-control custom-radio">
      <input type="radio" id="typeAttach" name="type" value="attach" class="custom-control-input">
      <label class="custom-control-label" for="typeAttach">添付ファイル</label>
    </div>
    <button type="submit" class="btn btn-primary">実行</button>
</form>
@endsection