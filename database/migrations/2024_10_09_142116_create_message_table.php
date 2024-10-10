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
        DB::statement(
            "
			CREATE TABLE messages (
				id INT AUTO_INCREMENT PRIMARY KEY,
				sender_id INT,
				receiver_id INT,
				message TEXT,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				FOREIGN KEY (sender_id) REFERENCES accounts(id) ON DELETE CASCADE,
				FOREIGN KEY (receiver_id) REFERENCES accounts(id) ON DELETE CASCADE
			)"
        );

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE messages");
    }
};
