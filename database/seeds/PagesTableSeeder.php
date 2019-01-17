<?php

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert([
            'title'  => 'MainPage',
            'source' => '# Welcome to LukiWiki
インストール成功おめでとうございます。この画面が正常に表示されているということは、インストールに成功したということです。
まずは、[[SandBox]]で行きLukiWikiの機能を試してみましょう。

実働環境に置く前に、.envの書き換えを行ってください。

# カスタマイズ
-[[MainPage]] - このページです。
-[[SideBar]] - サイドメニューを定義します
-[[InterWikiName]] - 外部のWikiと連携させる場合はここで設定します。詳細は、[[Help/InterWiki]]をご覧になって下さい。

# サポート
-[[ヘルプ>Help]]
-[[PukiWiki Adv.公式サイト>https://lukiwiki.logue.be/]]
--[[問題報告>https://github.com/logue/LukiWiki/issues]]
-[[Twitter>https://twitter.com/pukiwiki_adv]]
-[[プロジェクトサイト>https://github.com/logue/LukiWiki]]',
        ]);
    }
}
