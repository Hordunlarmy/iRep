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

    public function __construct($accountId, $data)
    {
        $this->accountId = $accountId;
        $this->position = $data['position'];
        $this->constituency = $data['constituency'];
        $this->party = $data['party'];
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
        INSERT INTO representatives (account_id, position, constituency, party)
        VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$this->accountId, $this->position, $this->constituency, $this->party]);

        return $this->accountId;
    }
}
