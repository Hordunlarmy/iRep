<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        DB::statement('
			CREATE TABLE permissions (
			id INT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL UNIQUE,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			)
		');

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS admin_permissions');
        DB::statement('DROP TABLE IF EXISTS permissions');
        DB::statement('DROP TABLE IF EXISTS admins');
    }
};
