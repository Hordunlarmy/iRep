<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Representative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class SearchFactory 
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }
    
        /**
     * Search accounts by given criteria.
     *
     * @param array $criteria
     * @return array
     */

        public function searchAccounts(array $criteria): array
        {
            $query = 'SELECT * FROM accounts WHERE 1=1';
    
            // Prepare parameters array to bind values dynamically
            $params = [];
    
            // Add filters dynamically based on criteria
            if (isset($criteria['name'])) {
                $query .= ' AND name LIKE ?';
                $params[] = '%' . $criteria['name'] . '%'; // Partial match
            }
    
            if (isset($criteria['email'])) {
                $query .= ' AND email = ?';
                $params[] = $criteria['email'];
            }
    
            if (isset($criteria['phone_number'])) {
                $query .= ' AND phone_number = ?';
                $params[] = $criteria['phone_number'];
            }
    
            if (isset($criteria['account_type'])) {
                $query .= ' AND account_type = ?';
                $params[] = $criteria['account_type'];
            }
    
            if (isset($criteria['state'])) {
                $query .= ' AND state = ?';
                $params[] = $criteria['state'];
            }
    
            if (isset($criteria['local_government'])) {
                $query .= ' AND local_government = ?';
                $params[] = $criteria['local_government'];
            }
    
            try {
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
    
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                Log::error('Error searching accounts: ' . $e->getMessage());
                return [];
            }
        }
    }











