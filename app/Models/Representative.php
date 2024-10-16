<?php

namespace App\Models;

/**
 * Class Representative represents the Representative model in the database
 */
class Representative
{
    protected $accountId;
    protected $position;
    protected $constituency;
    protected $party;
    protected $socialMedia;


    public function __construct($accountId, $data)
    {
        $this->accountId = $accountId;
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (is_array($data['social_handles'])) {
            $this->socialMedia = json_encode($data['social_handles']);
        }

    }
    /**
     * Insert a new representative into the database
     *
     * @param \PDO $db
     * @return void
     */
    public function insert($db)
    {
        $query = "
        INSERT INTO representatives (account_id, position, constituency, party, social_handles)
        VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $this->accountId,
            $this->position,
            $this->constituency,
            $this->party,
            $this->socialMedia
        ]);

        return $this->accountId;
    }
}
