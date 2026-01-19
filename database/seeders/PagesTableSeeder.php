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
        Page::insert([
            'name' => 'MainPage',
            'source' => Storage::disk('local')->get('data/MainPage.txt'),
            'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'created_at' => '2021-01-01 0:0:0',
        ]);
        Page::insert([
            'name' => 'SandBox',
            'source' => Storage::disk('local')->get('data/SandBox.txt'),
            'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'created_at' => '2021-01-01 0:0:0',
        ]);
        Page::clearCache();
    }
}
