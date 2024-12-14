<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostSeeder extends Seeder
{
    public function run()
    {
        $petitionsData = [
            [
                'post_type' => 'petition',
                'title' => 'Petition for Better Road Infrastructure',
                'context' => 'We need better roads in our community to ensure safety and accessibility.',
                'media' => json_encode(['https://i.imgur.com/6OiQwEJ.jpeg', 'https://i.imgur.com/cl7CDK6.jpeg']),
                'creator_id' => 2,
                'target_representatives' => [10, 12],
            ],
            [
                'post_type' => 'petition',
                'title' => 'Petition for Improved Healthcare Services',
                'context' => 'Our healthcare system needs urgent reforms to better serve the community.',
                'media' => json_encode(['https://i.imgur.com/DI5HhNd.jpeg', 'https://i.imgur.com/SMBmLqX.jpeg']),
                'creator_id' => 3,
                'target_representatives' => [11, 13],
            ],
            [
                'post_type' => 'petition',
                'title' => 'Petition for Environmental Protection',
                'context' => 'We urge the government to take action against pollution in our area.',
                'media' => json_encode(['https://i.imgur.com/vSltOz5.jpeg', 'https://i.imgur.com/fu2EWSt.png']),
                'creator_id' => 4,
                'target_representatives' => [12, 14],
            ],
        ];

        $eyewitnessReportsData = [
            [
                'post_type' => 'eyewitness',
                'title' => 'Eyewitness Report: Accident on Main Street',
                'context' => 'I witnessed a serious accident involving two cars at the intersection.',
                'media' => json_encode(['https://i.imgur.com/bNSRtUa.jpeg', 'https://i.imgur.com/ZGBLL5r.jpeg']),
                'creator_id' => 5,
                'category' => 'accident',
            ],
            [
                'post_type' => 'eyewitness',
                'title' => 'Eyewitness Report: Theft at Local Market',
                'context' => 'I saw a theft incident at the local market yesterday afternoon.',
                'media' => json_encode(['https://i.imgur.com/cbxyz99.jpeg', 'https://i.imgur.com/U43c4kd.jpeg']),
                'creator_id' => 6,
                'category' => 'crime',
            ],
            [
                'post_type' => 'eyewitness',
                'title' => 'Eyewitness Report: Fire Incident at Warehouse',
                'context' => 'A fire broke out at the warehouse causing significant damage.',
                'media' => json_encode(['https://i.imgur.com/68BVZ0K.jpeg', 'https://i.imgur.com/8OFejgA.jpeg']),
                'creator_id' => 7,
                'category' => 'other',
            ],
        ];

        foreach ($petitionsData as $petition) {
            try {
                $postId = DB::table('posts')->insertGetId([
                    'post_type' => $petition['post_type'],
                    'title' => $petition['title'],
                    'context' => $petition['context'],
                    'media' => $petition['media'],
                    'creator_id' => $petition['creator_id'],
                ]);

                $petitionId = DB::table('petitions')->insertGetId([
                    'post_id' => $postId,
                ]);

                foreach ($petition['target_representatives'] as $repId) {
                    DB::table('petition_representatives')->insert([
                        'petition_id' => $petitionId,
                        'representative_id' => $repId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to insert petition: ' . $e->getMessage(), $petition);
            }
        }

        foreach ($eyewitnessReportsData as $report) {
            try {
                $postId = DB::table('posts')->insertGetId([
                    'post_type' => $report['post_type'],
                    'title' => $report['title'],
                    'context' => $report['context'],
                    'media' => $report['media'],
                    'creator_id' => $report['creator_id'],
                ]);

                DB::table('eye_witness_reports')->insert([
                    'post_id' => $postId,
                    'category' => $report['category'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to insert eyewitness report: ' . $e->getMessage(), $report);
            }
        }
    }
}
