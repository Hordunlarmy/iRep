<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deletedAdmins = [
            [
                'entity_id' => 6,
                'entity_type' => 'admin',
            ],
            [
                'entity_id' => 7,
                'entity_type' => 'admin',
            ]
        ];

        DB::table('deleted_entities')->insert($deletedAdmins);

        $adminActivities = [
            [
                'admin_id' => 1,
                'activity_type' => 'login',
                'description' => 'Admin logged in',
            ],
            [
                'admin_id' => 1,
                'activity_type' => 'update',
                'description' => 'Updated user profile',
            ],
            [
                'admin_id' => 2,
                'activity_type' => 'delete',
                'description' => 'Deleted user account with ID 10',
            ]
        ];

        DB::table('admin_activities')->insert($adminActivities);

        $reportData = [
        [
            'entity_id' => 1,
            'entity_type' => 'post',
            'reporter_id' => 1,
            'reason' => 'spam',
            'description' => 'Post with ID 1 has been reported for containing spam content',
            'created_at' => now(),
        ],
        [
            'entity_id' => 2,
            'entity_type' => 'comment',
            'reporter_id' => 2,
            'reason' => 'harassment',
            'description' => 'Comment with ID 2 has been reported for harassment',
            'created_at' => now(),
        ],
        [
            'entity_id' => 3,
            'entity_type' => 'account',
            'reporter_id' => 3,
            'reason' => 'hate speech',
            'description' => 'Account with ID 3 has been reported for hate speech',
            'created_at' => now(),
        ],
        [
            'entity_id' => 4,
            'entity_type' => 'post',
            'reporter_id' => 4,
            'reason' => 'fake news',
            'description' => 'Post with ID 4 has been reported for spreading fake news',
            'created_at' => now(),
        ],
        [
            'entity_id' => 5,
            'entity_type' => 'comment',
            'reporter_id' => 5,
            'reason' => 'violence',
            'description' => 'Comment with ID 5 has been reported for promoting violence',
            'created_at' => now(),
        ]
    ];

        DB::table('reports')->insert($reportData);
    }
}
