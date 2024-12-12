<?php

namespace App\Admin\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActionFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function insertState(string $state): int
    {
        try {
            $query = "
			INSERT INTO states (name)
			VALUES (:state)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':state', $state);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert State Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertLocalGovernment(string $localGovernment, int $stateId): int
    {
        try {
            $query = "
			INSERT INTO local_governments (name, state_id)
			VALUES (:local_government, :state_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':local_government', $localGovernment);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert Local Government Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertConstituency(string $constituency, int $stateId): int
    {
        try {
            $query = "
			INSERT INTO constituencies (name, state_id)
			VALUES (:constituency, :state_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':constituency', $constituency);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert Constituency Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertDistrict(string $district, int $stateId): int
    {
        try {
            $query = "
			INSERT INTO districts (name, state_id)
			VALUES (:district, :state_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':district', $district);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert District Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertPosition(string $position): int
    {
        try {
            $query = "
			INSERT INTO positions (title)
			VALUES (:position)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position', $position);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert Position Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertParty(string $name, string $code): int
    {
        try {
            $query = "
			INSERT INTO parties (name, code)
			VALUES (:name, :code)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            Log::error('Insert Party Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateState(int $stateId, string $state): int
    {
        try {
            $query = "
			UPDATE states
			SET name = :state
			WHERE id = :state_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update State Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateLocalGovernment(int $localGovernmentId, string $localGovernment, int $stateId): int
    {
        try {
            $query = "
			UPDATE local_governments
			SET name = :local_government, state_id = :state_id
			WHERE id = :local_government_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':local_government', $localGovernment);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->bindParam(':local_government_id', $localGovernmentId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update Local Government Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateConstituency(int $constituencyId, string $constituency, int $stateId): int
    {
        try {
            $query = "
			UPDATE constituencies
			SET name = :constituency, state_id = :state_id
			WHERE id = :constituency_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':constituency', $constituency);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->bindParam(':constituency_id', $constituencyId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update Constituency Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateDistrict(int $districtId, string $district, int $stateId): int
    {
        try {
            $query = "
			UPDATE districts
			SET name = :district, state_id = :state_id
			WHERE id = :district_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':district', $district);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->bindParam(':district_id', $districtId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update District Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updatePosition(int $positionId, string $position): int
    {
        try {
            $query = "
			UPDATE positions
			SET title = :position
			WHERE id = :position_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':position_id', $positionId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update Position Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateParty(int $partyId, string $name, string $code): int
    {
        try {
            $query = "
			UPDATE parties
			SET name = :name, code = :code
			WHERE id = :party_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':party_id', $partyId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Update Party Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteState(int $stateId): int
    {
        try {
            $query = "
			DELETE FROM states
			WHERE id = :state_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':state_id', $stateId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete State Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteLocalGovernment(int $localGovernmentId): int
    {
        try {
            $query = "
			DELETE FROM local_governments
			WHERE id = :local_government_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':local_government_id', $localGovernmentId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete Local Government Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteConstituency(int $constituencyId): int
    {
        try {
            $query = "
			DELETE FROM constituencies
			WHERE id = :constituency_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':constituency_id', $constituencyId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete Constituency Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteDistrict(int $districtId): int
    {
        try {
            $query = "
			DELETE FROM districts
			WHERE id = :district_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':district_id', $districtId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete District Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deletePosition(int $positionId): int
    {
        try {
            $query = "
			DELETE FROM positions
			WHERE id = :position_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position_id', $positionId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete Position Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteParty(int $partyId): int
    {
        try {
            $query = "
			DELETE FROM parties
			WHERE id = :party_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':party_id', $partyId);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\Exception $e) {
            Log::error('Delete Party Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
