<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create admins table
        DB::statement("
			CREATE TABLE admins (
			id INT AUTO_INCREMENT PRIMARY KEY,
			username VARCHAR(255) NOT NULL UNIQUE,
			password VARCHAR(255) NOT NULL,
			photo_url VARCHAR(255) DEFAULT
			'https://res.cloudinary.com/dsueaitln/image/upload/v1733239113/istockphoto-522855255-612x612_eyv1vf.jpg',
			email VARCHAR(255) UNIQUE,
			account_type INT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			)
			");

        // Create permissions table
        DB::statement('
			CREATE TABLE permissions (
			id INT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL UNIQUE,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			)
			');

        // Create admin_permissions table
        DB::statement('
			CREATE TABLE admin_permissions (
			id INT AUTO_INCREMENT PRIMARY KEY,
			admin_id INT NOT NULL,
			permission_id INT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
			FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
			UNIQUE (admin_id, permission_id)
			)
			');

        // Create deleted_admins table to track deleted admins
        DB::statement('
			CREATE TABLE deleted_entities (
				id INT AUTO_INCREMENT PRIMARY KEY,
				entity_id INT NOT NULL,
				entity_type ENUM("admin", "account") NOT NULL,
				deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		');

        // Create admin_activities table to track admin activities
        DB::statement('
			CREATE TABLE admin_activities (
			id INT AUTO_INCREMENT PRIMARY KEY,
			admin_id INT NOT NULL,
			activity_type VARCHAR(255) NOT NULL,
			description TEXT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
			)
			');

        DB::statement("
			CREATE TABLE reports (
				id INT AUTO_INCREMENT PRIMARY KEY,
				entity_id INT NOT NULL,
				entity_type ENUM('post', 'comment', 'account') NOT NULL,
				reporter_id INT NOT NULL,
				reason ENUM('spam', 'harassment', 'hate speech', 'violence', 'fake news', 'other') NOT NULL,
				description TEXT,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				FOREIGN KEY (reporter_id) REFERENCES accounts(id) ON DELETE CASCADE
			)
		");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS reports');
        DB::statement('DROP TABLE IF EXISTS admin_activities');
        DB::statement('DROP TABLE IF EXISTS deleted_admins');
        DB::statement('DROP TABLE IF EXISTS admin_permissions');
        DB::statement('DROP TABLE IF EXISTS permissions');
        DB::statement('DROP TABLE IF EXISTS admins');
    }
};
