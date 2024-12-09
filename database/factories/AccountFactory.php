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

    public function updateDeviceToken($accountId, $deviceToken, $deviceType)
    {
        $query = 'INSERT INTO device_tokens (account_id, device_token, device_type)
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE
              device_token = VALUES(device_token),
              device_type = VALUES(device_type)';

        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId, $deviceToken, $deviceType]);
    }

    public function fetchNotifications($accountId)
    {
        $query = 'SELECT * FROM account_notifications
			WHERE account_id = ? ORDER BY created_at DESC'
        ;
        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

            $total = app('search')->indexData(
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
		a.*, at.name AS account_type,
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

            // dummy kyc data
            $data['kyced'] = true;

            $accountId = $this->updateAccount($data['id'], $data);

            $this->db->commit();

            return new Account($this->db, ['id' => $accountId]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getOnboardingDetails($accountId)
    {
        try {
            Log::info('Fetching onboarding details for account: ' . $accountId);

            $query = "
            SELECT name, gender, dob, state_id, local_government_id
            FROM accounts
            WHERE id = :id
        ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$accountId]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            Log::error('Error fetching onboarding details: ' . $e->getMessage());
            throw $e;
        }
    }

    public function insertRepresentativeDetails($data)
    {
        try {
            $this->db->beginTransaction();

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
        $accountFields = [];
        $accountValues = [];
        $representativeFields = [];
        $representativeValues = [];

        $representativeFieldNames = [
            'position_id', 'district_id', 'constituency_id',
            'party_id', 'social_handles', 'proof_of_office',
            'sworn_in_date', 'bio'
        ];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                if (($key === 'kyc' ||
                    $key === 'proof_of_office' ||
                    $key === 'social_handles') &&
                    is_array($value)) {
                    $value = json_encode($value);
                }

                if (in_array($key, $representativeFieldNames)) {
                    $representativeFields[] = "$key = ?";
                    $representativeValues[] = $value;
                } else {
                    $accountFields[] = "$key = ?";
                    $accountValues[] = $value;
                }
            }
        }

        if (!empty($accountFields)) {
            $accountFields[] = 'updated_at = CURRENT_TIMESTAMP';
            $accountValues[] = $accountId;
            $accountQuery = 'UPDATE accounts SET ' .
                implode(', ', $accountFields) .
                ' WHERE id = ?';
            $this->db->prepare($accountQuery)
                ->execute($accountValues);
        }

        if (!empty($representativeFields)) {
            $representativeQuery = 'UPDATE representatives SET ' .
                implode(', ', $representativeFields) .
                ' WHERE account_id = ?';
            $this->db->prepare($representativeQuery)
                ->execute(array_merge(
                    $representativeValues,
                    [$accountId]
                ));
        }

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

    public function fetchStatus($accountId)
    {
        $query = 'SELECT id, email_verified, kyced FROM accounts WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
