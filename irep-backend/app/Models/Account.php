<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class Account represents the Account model in the database
 */
class Account extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $db;
    public $id;
    public $name;
    public $email;
    public $phone_number;
    public $dob;
    public $password;
    public $account_type;
    public $state;
    public $local_government;

    public function __construct($db, $data)
    {
        $this->db = $db;

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Insert a new account into the database
     *
     * @param array $data
     * @return int
     */
    public function insertAccount(): int
    {
        $query = "
        INSERT INTO accounts (name, email, phone_number, dob, password, account_type, state,
        local_government)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->name,
            $this->email,
            $this->phone_number,
            $this->dob,
            Hash::make($this->password),
            $this->account_type,
            $this->state,
            $this->local_government,
        ]);

        return $this->db->lastInsertId();
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->id;
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'account_type' => $this->account_type,
        ];
    }

    public static function getAccountByEmail($db, string $email)
    {
        $query = "SELECT * FROM accounts WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            // Return an instance of the Account class
            return new self($db, $result);
        }

        return null;
    }
}
