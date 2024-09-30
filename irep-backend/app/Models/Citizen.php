<?php

namespace App\Models;

class Citizen
{
    protected $accountId;
    protected $occupation;
    protected $location;

    public function __construct($accountId, $data)
    {
        $this->accountId = $accountId;
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    /**
     * Insert a new citizen into the database
     *
     * @param \PDO $db
     * @return void
     */
    public function insert($db)
    {
        $query = "
        INSERT INTO citizens (account_id, occupation, location)
        VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$this->accountId, $this->occupation, $this->location]);

        return $this->accountId;
    }
}
