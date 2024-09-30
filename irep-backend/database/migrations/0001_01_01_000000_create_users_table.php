<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE account_types (
                id INT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL
            )
        ");

        // Seed the account_types table
        DB::table('account_types')->insert([
            ['id' => 0, 'name' => 'social'],
            ['id' => 1, 'name' => 'citizen'],
            ['id' => 2, 'name' => 'representative'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        DB::statement("
            CREATE TABLE accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                photo_url VARCHAR(255),
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone_number VARCHAR(20) UNIQUE,
                dob DATE,
                state VARCHAR(255),
                local_government VARCHAR(255),
                polling_unit VARCHAR(255),
                password VARCHAR(255) NOT NULL,
                email_verified BOOLEAN DEFAULT FALSE,
                remember_token VARCHAR(100),
                account_type INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (account_type) REFERENCES account_types(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE citizens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                occupation VARCHAR(255),
                location VARCHAR(255),
                account_id INT NOT NULL,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE representatives (
                id INT AUTO_INCREMENT PRIMARY KEY,
                position VARCHAR(255),
                constituency VARCHAR(255),
                party VARCHAR(255),
                bio TEXT,
                account_id INT,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE verification_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(255) UNIQUE NOT NULL,
                account_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE TABLE sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                payload LONGTEXT,
                last_activity INT,
                FOREIGN KEY (user_id) REFERENCES citizens(id) ON DELETE SET NULL
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS sessions");
        DB::statement("DROP TABLE IF EXISTS verification_tokens");
        DB::statement("DROP TABLE IF EXISTS representatives");
        DB::statement("DROP TABLE IF EXISTS citizens");
        DB::statement("DROP TABLE IF EXISTS accounts");
        DB::statement("DROP TABLE IF EXISTS account_types");
    }
};
