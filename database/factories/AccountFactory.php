<?php

namespace Database\Factories;

use App\Models\Account;
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

    public function indexAccount($accountId)
    {
        try {
            $fetchedAccount = $this->getAccount($accountId);
            $accountData = json_decode($fetchedAccount->account_data, true);

            $dataToIndex = [
                'id' => $fetchedAccount->id,
                'account_type' => $fetchedAccount->account_type,
                'photo_url' => $fetchedAccount->photo_url,
                'name' => $fetchedAccount->name,
                'state' => $fetchedAccount->state,
                'local_government' => $fetchedAccount->local_government,
                'position' => $accountData['position'] ?? null,
                'constituency' => $accountData['constituency'] ?? null,
                'party' => $accountData['party'] ?? null,
                'district' => $accountData['district'] ?? null,
                'created_at' => $fetchedAccount->created_at,

            ];

            $sortableAttributes = ['created_at', 'name', 'account_type'];
            $filterableAttributes = [
                'account_type', 'position', 'constituency', 'party',
                'district', 'state', 'local_government'
            ];

            $total = app('meilisearch')->indexData(
                'accounts',
                [$dataToIndex],
                $sortableAttributes,
                $filterableAttributes
            );
            Log::info($total . ' Account Indexed');

        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function getAccount($identifier)
    {
        $query = "
        SELECT
            a.id, a.name, at.name AS account_type, a.photo_url, a.location, a.password,
            s.name AS state, lg.name AS local_government, a.created_at,
            CASE
                WHEN a.account_type = 2 THEN JSON_OBJECT(
                    'position', p.title,
                    'constituency', c.name,
                    'party', pa.name,
                    'district', d.name,
                    'bio', r.bio
                )
                ELSE NULL
            END AS account_data
        FROM accounts a
        LEFT JOIN representatives r ON a.id = r.account_id AND a.account_type = 2
        LEFT JOIN states s ON a.state_id = s.id
        LEFT JOIN local_governments lg ON a.local_government_id = lg.id
        LEFT JOIN positions p ON r.position_id = p.id
        LEFT JOIN constituencies c ON r.constituency_id = c.id
        LEFT JOIN parties pa ON r.party_id = pa.id
        LEFT JOIN districts d ON r.district_id = d.id
        LEFT JOIN account_types at ON a.account_type = at.id
        WHERE a.id = ? OR a.email = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$identifier, $identifier]);

        return $stmt->fetchObject();
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

}
