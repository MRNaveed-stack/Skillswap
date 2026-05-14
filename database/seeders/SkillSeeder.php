<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Programming & Tech',
            'Design & Creative',
            'Business & Finance',
            'Music & Audio',
            'Languages',
        ];

        foreach ($categories as $cat) {
            $catId = DB::table('skill_categories')->insertGetId([
                'name' => $cat,
                'slug' => Str::slug($cat),
                'created_at' => now(),
            ]);

            // Add some skills for each
            $skills = [];
            if ($cat == 'Programming & Tech') {
                $skills = ['Python Programming', 'React Native', 'Laravel API Development'];
            } elseif ($cat == 'Design & Creative') {
                $skills = ['UI/UX Design', 'Logo Animation', 'Photoshop Mastery'];
            } elseif ($cat == 'Business & Finance') {
                $skills = ['Stock Trading Basics', 'Startup Accounting'];
            } elseif ($cat == 'Music & Audio') {
                $skills = ['Acoustic Guitar', 'Music Production (Ableton)'];
            } elseif ($cat == 'Languages') {
                $skills = ['Conversational Spanish', 'Japanese for Beginners'];
            }

            foreach ($skills as $skill) {
                DB::table('skills')->insert([
                    'category_id' => $catId,
                    'title' => $skill,
                    'slug' => Str::slug($skill),
                    'description' => 'Learn the ins and outs of ' . $skill . ' from an experienced mentor.',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
