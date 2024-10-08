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
			CREATE TABLE posts (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_type ENUM('petition', 'eyewitness') NOT NULL,
			title VARCHAR(255) NOT NULL UNIQUE,
			context TEXT NOT NULL,
			media JSON DEFAULT NULL,
			creator_id INT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY (creator_id) REFERENCES accounts(id) ON DELETE CASCADE
			)
			");

        DB::statement("
			CREATE TABLE petitions (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			signatures INT DEFAULT 0,
			target_representative_id INT NOT NULL,
			status ENUM('open', 'submitted', 'approved') DEFAULT 'open',
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (target_representative_id) REFERENCES representatives(id) ON DELETE CASCADE
			)
			");

        DB::statement("
			CREATE TABLE eye_witness_reports (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			approvals INT DEFAULT 0,
			category ENUM('crime', 'accident', 'other') DEFAULT 'other',
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
			)
			");

        DB::statement("
			CREATE TABLE petition_signatures (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
			UNIQUE (post_id, account_id)
			)
			");

        DB::statement("
			CREATE TABLE eye_witness_reports_approvals (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
			UNIQUE (post_id, account_id)
			)
			");

        DB::statement("
			CREATE TABLE comments (
			id INT AUTO_INCREMENT PRIMARY KEY,
			parent_id INT,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			comment TEXT NOT NULL,
			commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
			)
			");

        DB::statement("
			CREATE TABLE likes (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
			UNIQUE (post_id, account_id)
			)
			");

        DB::statement("
			CREATE TABLE reposts (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			reposted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
			UNIQUE (post_id, account_id)
			)
			");

        DB::statement("
			CREATE TABLE bookmarks (
			id INT AUTO_INCREMENT PRIMARY KEY,
			post_id INT NOT NULL,
			account_id INT NOT NULL,
			bookmarked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
			FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
			UNIQUE (post_id, account_id)
			)
			");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE bookmarks");
        DB::statement("DROP TABLE reposts");
        DB::statement("DROP TABLE likes");
        DB::statement("DROP TABLE comments");
        DB::statement("DROP TABLE eye_witness_reports_approvals");
        DB::statement("DROP TABLE petition_signatures");
        DB::statement("DROP TABLE eye_witness_reports");
        DB::statement("DROP TABLE petitions");
        DB::statement("DROP TABLE posts");
    }
};
