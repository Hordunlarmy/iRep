<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Citizen;
use App\Models\Representative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * class for creating account
 */
class AccountFactory
{
    protected $db;
    protected $accountColumns = null;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function createAccount($data)
    {
        try {
            $this->db->beginTransaction();

            $account = new Account($this->db, $data);
            $accountId = $account->insertAccount();

            if ($data['account_type']) {
                $this->sendVerificationEmail($accountId, $data['email']);
            }

            $this->db->commit();

            return new Account($this->db, ['id' => $accountId, 'account_type' => $data['account_type']]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function insertAccountDetails($data)
    {
        try {
            $this->db->beginTransaction();

            Log::info('Initiating onboarding for account: ' . $data['id']);

            if (!empty($data['kyc'])) {

                $data['kyc'] = app('uploadMediaService')->handleMediaFiles($data['kyc']);
            }

            $accountId = $this->updateAccount($data['id'], $data);

            $this->db->commit();

            return new Account($this->db, ['id' => $accountId]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateAccount($accountId, $data)
    {
        $columns = $this->getAccountColumns();

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $columns) && $key !== 'id') {
                // Handle JSON fields
                if ($key === 'kyc' && is_array($value)) {
                    $value = json_encode($value); // Encode array as JSON
                }
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        $values[] = $accountId;

        $query = 'UPDATE accounts SET ' . implode(', ', $fields) . ' WHERE id = ?';

        $this->db->prepare($query)->execute($values);

        return $accountId;
    }

    public function getAccount($identifier)
    {
        $query = "
		SELECT
		a.*,
		CASE
		WHEN a.account_type = 2 THEN JSON_OBJECT(
		'position', r.position,
		'constituency', r.constituency,
		'party', r.party,
		'bio', r.bio
		)
		ELSE NULL
		END AS account_data
		FROM accounts a
		LEFT JOIN representatives r ON a.id = r.account_id AND a.account_type = 2
		WHERE a.id = ? OR a.email = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$identifier, $identifier]);

        return $stmt->fetchObject();
    }

    public function uploadPhoto($field, $accountId, $file)
    {

        $photo = app('uploadMediaService')->handleMediaFiles([$file]);

        $this->updateAccount($accountId, [$field => $photo[0]]);

        return $photo[0];
    }

    protected function sendVerificationEmail($accountId, $email, $name = '')
    {
        $otp = strtoupper(Str::random(4));
        $this->saveVerificationToken($accountId, $otp);

        $templateVariables = [
            'otp' => $otp,
        ];
        app('emailService')->sendNewUserVerification($email, $name, $templateVariables);
    }

    protected function getAccountType($accountType)
    {
        if (is_int($accountType)) {
            $query = "SELECT * FROM account_types WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$accountType]);
        } else {
            $query = "SELECT * FROM account_types WHERE name = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$accountType]);
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getAccountColumns()
    {
        if ($this->accountColumns === null) {
            $query = "SHOW COLUMNS FROM accounts";
            $result = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN);
            $this->accountColumns = $result;
        }

        return $this->accountColumns;
    }

    public function resendActivation($email)
    {
        $account = $this->getAccount($email);

        if ($account->email_verified == 1) {
            throw new HttpException(400, 'Email already verified.');
        }
        $this->sendVerificationEmail($account->id, $account->email, $account->name);
    }


    protected function saveVerificationToken($accountId, $otp)
    {
        $query = 'INSERT INTO verification_tokens (account_id, token) VALUES (?, ?)';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId, $otp]);
    }

    public function activateAccount($email, $otp)
    {
        try {
            $query = 'SELECT vt.account_id, a.account_type FROM verification_tokens vt
			JOIN accounts a ON vt.account_id = a.id
			WHERE a.email = ? AND vt.token = ?';
            $stmt = $this->db->prepare($query);

            $stmt->execute([trim($email), trim($otp)]);

            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $accountId = $result['account_id'];
                $accountType = $result['account_type'];

                $deleteQuery = 'DELETE FROM verification_tokens WHERE account_id = ?';
                $deleteStmt = $this->db->prepare($deleteQuery);
                $deleteStmt->execute([$accountId]);

                $this->setEmailVerified($accountId);

                Log::info('Account activated for email: ' . $email);

                return new Account($this->db, [
                    'id' => $accountId,
                    'account_type' => $accountType,
                ]);
            }

            Log::info('Invalid OTP for email: ' . $email);

            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function setEmailVerified($accountId)
    {
        $query = 'UPDATE accounts SET email_verified = ? WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([true, $accountId]);
    }

    public function getRepresentatives($criteria)
    {
        $query = 'SELECT * FROM accounts WHERE account_type = ?';
        $params = [2];

        $search = $criteria['search'] ?? null;
        $stateFilter = $criteria['state'] ?? null;
        $positionFilter = $criteria['position'] ?? null;
        $localGovtFilter = $criteria['local_government'] ?? null;
        $sortBy = $criteria['sort_by'] ?? 'created_at';
        $sortOrder = $criteria['sort_order'] ?? 'desc';

        if ($search) {
            $query .= ' AND (name LIKE ? OR email LIKE ? OR phone_number LIKE ?)';
            $params = array_merge($params, array_fill(0, 3, '%' . $search . '%'));
        }

        if ($stateFilter) {
            $query .= ' AND state = ?';
            $params[] = $stateFilter;
        }

        if ($positionFilter) {
            $query .= ' AND position = ?';
            $params[] = $positionFilter;
        }

        if ($localGovtFilter) {
            $query .= ' AND local_government = ?';
            $params[] = $localGovtFilter;
        }
        $allowedSortColumns = ['created_at', 'name', 'constituency', 'state'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query .= " ORDER BY {$sortBy} {$sortOrder}";
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            Log::error('Error fetching representatives: ' . $e->getMessage());
            return [];
        }
    }
}
