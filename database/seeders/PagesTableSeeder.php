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
            'source' => Storage::get('data/4D61696E50616765.txt'),
            'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'created_at' => '2021-01-01 0:0:0',
        ]);
        Page::insert([
            'name' => 'SandBox',
            'source' => Storage::get('data/53616E64426F78.txt'),
            'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'created_at' => '2021-01-01 0:0:0',
        ]);
        Page::clearCache();
    }
}
