<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE petitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL UNIQUE,
                description TEXT NOT NULL,
                creator_id INT NOT NULL,
                target_representative_id INT NOT NULL,
                signature_count INT DEFAULT 0,
                submitted_to_authorities BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (creator_id) REFERENCES accounts(id) ON DELETE CASCADE,
                FOREIGN KEY (target_representative_id) REFERENCES representatives(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE petition_signatures (
                id INT AUTO_INCREMENT PRIMARY KEY,
                petition_id INT NOT NULL,
                account_id INT NOT NULL,
                signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (petition_id) REFERENCES petitions(id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                UNIQUE (petition_id, account_id)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE petition_signatures");
        DB::statement("DROP TABLE petitions");
    }
};
