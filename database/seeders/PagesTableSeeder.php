<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Page::insert([
            'name'    => 'MainPage',
            'source'  => '# Welcome to LukiWiki

インストール成功おめでとうございます。この画面が正常に表示されているということは、インストールに成功したということです。
まずは、[SandBox]で行きLukiWikiの機能を試してみましょう。

実働環境に置く前に、.envの書き換えを行ってください。

# カスタマイズ

- [MainPage] - このページです。
- [SideBar] - サイドメニューを定義します

# サポート

- [プロジェクトサイト](https://github.com/logue/LukiWiki)
 - [問題報告](https://github.com/logue/LukiWiki/issues)
- [Twitter](https://twitter.com/pukiwiki_adv)',
            'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'created_at' => '2021-01-01 0:0:0',
        ]);
    }
}
