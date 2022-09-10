<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $page = new Page();
        $page->name ='MainPage';
        $page->source = Storage::get('data/4D61696E50616765.txt');
        $page->created_at = '2021-01-01 0:0:0';
        $page->save();

        $page = new Page();
        $page->name ='SandBox';
        $page->source = Storage::get('data/53616E64426F78.txt');
        $page->created_at = '2021-01-01 0:0:0';
        $page->save();

        Page::clearCache();
    }
}
