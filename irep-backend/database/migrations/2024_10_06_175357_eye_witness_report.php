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
            CREATE TABLE eye_witness_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL UNIQUE,
                description TEXT NOT NULL,
                creator_id INT NOT NULL,
                approvals INT DEFAULT 0,
                category ENUM('crime', 'accident', 'other') DEFAULT 'other',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (creator_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE eye_witness_reports_approvals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                report_id INT NOT NULL,
                account_id INT NOT NULL,
                approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (report_id) REFERENCES eye_witness_reports(id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                UNIQUE (report_id, account_id)
            )
        ");

        DB::statement("
            CREATE TABLE eye_witness_reports_comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                report_id INT NOT NULL,
                account_id INT NOT NULL,
                comment TEXT NOT NULL,
                commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (report_id) REFERENCES eye_witness_reports(id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE eye_witness_reports_comments");
        DB::statement("DROP TABLE eye_witness_reports_approvals");
        DB::statement("DROP TABLE eye_witness_reports");
    }
};
