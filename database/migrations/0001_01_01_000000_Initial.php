<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create the account_types table
        DB::statement("
            CREATE TABLE account_types (
                id INT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL
            )
        ");

        // Insert default account types
        DB::table('account_types')->insert([
            ['id' => 1, 'name' => 'citizen'],
            ['id' => 2, 'name' => 'representative'],
            ['id' => 3, 'name' => 'admin'],
        ]);
        DB::table('account_types')->updateOrInsert(
            ['id' => 4],
            ['name' => 'super_admin']
        );

        // Create the states table
        DB::statement("
            CREATE TABLE states (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL
            )
        ");

        // Create the local_governments table
        DB::statement("
            CREATE TABLE local_governments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                state_id INT NOT NULL,
				FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
				UNIQUE (name, state_id)
            )
        ");

        // Create the constituencies table
        DB::statement("
            CREATE TABLE constituencies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
				state_id INT NOT NULL,
                FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
				UNIQUE (name, state_id)
            )
			");

        // Create the districts table
        DB::statement("
			CREATE TABLE districts (
				id INT AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				state_id INT NOT NULL,
				FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
				UNIQUE (name, state_id)
			)
		");

        // Create the positions table
        DB::statement("
            CREATE TABLE positions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) UNIQUE NOT NULL
            )
        ");

        // Create the parties table
        DB::statement("
            CREATE TABLE parties (
                id INT AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) UNIQUE NOT NULL,
				code VARCHAR(255) UNIQUE NOT NULL
            )
        ");

        // Create the accounts table
        DB::statement("
            CREATE TABLE accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
				photo_url VARCHAR(255) DEFAULT
				'https://res.cloudinary.com/dsueaitln/image/upload/v1733239113/istockphoto-522855255-612x612_eyv1vf.jpg',
                cover_photo_url VARCHAR(255),
                name VARCHAR(255),
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255),
                phone_number VARCHAR(20) UNIQUE,
                gender ENUM('male', 'female', 'other'),
				dob DATE,
				location VARCHAR(255),
                state_id INT,
                local_government_id INT,
                account_type INT NOT NULL DEFAULT 1,
                polling_unit VARCHAR(255),
                kyc JSON DEFAULT NULL,
                email_verified BOOLEAN DEFAULT FALSE,
                kyced BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (account_type) REFERENCES account_types(id) ON DELETE CASCADE,
                FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL,
                FOREIGN KEY (local_government_id) REFERENCES local_governments(id) ON DELETE SET NULL
            )
        ");

        // Create the representatives table
        DB::statement("
            CREATE TABLE representatives (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sworn_in_date DATE,
                position_id INT,
				constituency_id INT,
				district_id INT,
                party_id INT,
                social_handles JSON,
				bio TEXT,
				proof_of_office JSON DEFAULT NULL,
                account_id INT NOT NULL UNIQUE,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
                FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
                FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE CASCADE,
                FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
            )
        ");

        // Create the verification_tokens table
        DB::statement("
            CREATE TABLE verification_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(255) UNIQUE NOT NULL,
                account_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
            )
        ");

        // Create the sessions table
        DB::statement("
            CREATE TABLE sessions (
                id VARCHAR(255) PRIMARY KEY,
                account_id INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                payload LONGTEXT,
                last_activity INT,
                FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop tables in the correct order
        DB::statement("DROP TABLE IF EXISTS sessions");
        DB::statement("DROP TABLE IF EXISTS verification_tokens");
        DB::statement("DROP TABLE IF EXISTS representatives");
        DB::statement("DROP TABLE IF EXISTS citizens");
        DB::statement("DROP TABLE IF EXISTS accounts");
        DB::statement("DROP TABLE IF EXISTS parties");
        DB::statement("DROP TABLE IF EXISTS positions");
        DB::statement("DROP TABLE IF EXISTS constituencies");
        DB::statement("DROP TABLE IF EXISTS local_governments");
        DB::statement("DROP TABLE IF EXISTS states");
        DB::statement("DROP TABLE IF EXISTS account_types");
    }
};
