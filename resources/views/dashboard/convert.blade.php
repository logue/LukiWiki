@extends('layout.dashboard')

@section('content')
<p>PukiWiki文法のデーターをLukiWiki文法に変換し、データベースにインポートします。</p>
<p>実際の処理は、コマンドラインで<code>php artisan queue:work</code>で実行します。</p>
<h2>注意</h2>
<ul>
  <li>複数回実行しないでください。</li>
  <li>最初にWikiデーターの移行を行ってください。その他のデータは、移行したページに紐づく形で実行されるため、処理が完了していない段階で他の処理を実行すると、不完全な移行になります。</li>
  <li>一部を除き、プラグインは移行しないか、一定の書式に統合されます。（添付など）</li>
  <li>バックアップの書式は変換しません。</li>
  <li>存在しない（削除済み）ページ、の添付ファイルや、バックアップの移行はしません。</li>
</ul>
<form action="{{ url(':dashboard/convert') }}" method="post">
  @csrf
  <div class="form-group">
    <label for="path">PukiWikiのデーターの置かれている場所へのパス</label>
    <input type="text" class="form-control" name="path" id="path" aria-describedby="pathHelp" placeholder="pukiwiki">
    <small id="pathHelp"
      class="form-text text-muted">LukiWikiの<code>storage/app</code>内にPukiWikiのディレクトリを入れてください。</small>
  </div>
  <div class="custom-control custom-radio">
    <input type="radio" id="typeWiki" name="type" value="wiki" class="custom-control-input">
    <label class="custom-control-label" for="typeWiki">Wikiデーター（必ず最初に実行してください。）</label>
  </div>
  <div class="custom-control custom-radio">
    <input type="radio" id="typeAttach" name="type" value="attach" class="custom-control-input">
    <label class="custom-control-label" for="typeAttach">添付ファイル</label>
  </div>
  <div class="custom-control custom-radio">
    <input type="radio" id="typeBackup" name="type" value="backup" class="custom-control-input">
    <label class="custom-control-label" for="typeBackup">バックアップ</label>
  </div>
  <div class="custom-control custom-radio">
    <input type="radio" id="typeCounter" name="type" value="counter" class="custom-control-input">
    <label class="custom-control-label" for="typeCounter">カウンタ</label>
  </div>
  <div class="custom-control custom-radio">
    <input type="radio" id="typeInterWiki" name="type" value="interwiki" class="custom-control-input">
    <label class="custom-control-label" for="typeInterWiki">InterWikiName、AutoAlias、Glossary</label>
  </div>
  <button type="submit" class="btn btn-primary">実行</button>
</form>
@endsection