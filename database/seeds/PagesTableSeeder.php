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
�C���X�g�[���������߂łƂ��������܂��B���̉�ʂ�����ɕ\������Ă���Ƃ������Ƃ́A�C���X�g�[���ɐ��������Ƃ������Ƃł��B
�܂��́A[[SandBox]]�ōs��LukiWiki�̋@�\�������Ă݂܂��傤�B

�������ɒu���O�ɁA.env�̏����������s���Ă��������B

# �J�X�^�}�C�Y
-[[MainPage]] - ���̃y�[�W�ł��B
-[[SideBar]] - �T�C�h���j���[���`���܂�
-[[InterWikiName]] - �O����Wiki�ƘA�g������ꍇ�͂����Őݒ肵�܂��B�ڍׂ́A[[Help/InterWiki]]�������ɂȂ��ĉ������B

# �T�|�[�g
-[[�w���v>Help]]
-[[PukiWiki Adv.�����T�C�g>https://lukiwiki.logue.be/]]
--[[����>https://github.com/logue/LukiWiki/issues]]
-[[Twitter>https://twitter.com/pukiwiki_adv]]
-[[�v���W�F�N�g�T�C�g>https://github.com/logue/LukiWiki]]',
        ]);
    }
}
