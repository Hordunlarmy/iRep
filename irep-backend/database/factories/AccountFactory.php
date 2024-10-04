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

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    protected function sendVerificationEmail($accountId, $email, $name)
    {
        $emailService = app('emailService');

        $otp = Str::random(6);
        $this->saveVerificationToken($accountId, $otp);

        $emailService = app('emailService');
        $templateVariables = [
            'otp' => $otp,
        ];
        $emailService->sendNewUserVerification($email, $name, $templateVariables);
    }

    /**
     * Create an account
     *
     * @param array $data
     * @return int
     */
    public function createAccount($data)
    {
        try {
            $this->db->beginTransaction();

            log::info('Transaction started.');

            $account = new Account($this->db, $data);
            $accountId = $account->insertAccount();

            log::info('Initial account created.', ['account_id' => $accountId]);

            if ($data['account_type'] === 'citizen') {
                (new Citizen($accountId, $data))->insert($this->db);
            } elseif ($data['account_type'] === 'representative') {
                (new Representative($accountId, $data))->insert($this->db);
            } elseif ($data['account_type'] === 'social') {
                // Do nothing
            } else {
                throw new \Exception('Invalid account type.');
            }

            if ($data['account_type'] !== 'social') {
                $this->sendVerificationEmail($accountId, $data['email'], $data['name']);
            }

            log::info('Transaction completed.');
            $this->db->commit();
            log::info('Transaction committed.');

            return new Account($this->db, ['id' => $accountId, 'account_type' => $data['account_type']]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function resendActivation($email)
    {
        $account = Account::getAccount($this->db, $email);

        if ($account->email_verified == 1) {
            throw new HttpException(400, 'Email already verified.');
        }
        $this->sendVerificationEmail($account->id, $account->email, $account->name);
    }


    /**
     * Save OTP to the verification_token table
     *
     * @param int $accountId
     * @param string $otp
     * @return void
     */
    protected function saveVerificationToken($accountId, $otp)
    {
        $query = 'INSERT INTO verification_tokens (account_id, token) VALUES (?, ?)';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$accountId, $otp]);
    }

    /**
     * Activate account using OTP
     *
     * @param int $accountId
     * @param string $otp
     * @return bool
     */
    public function activateAccount($email, $otp)
    {
        try {
            $query = 'SELECT vt.account_id, a.account_type FROM verification_tokens vt
                  JOIN accounts a ON vt.account_id = a.id
                  WHERE a.email = ? AND vt.token = ?';
            $stmt = $this->db->prepare($query);

            // Execute the query and log the result
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

    /**
     * Set email_verified to true for the given account
     *
     * @param int $accountId
     * @return void
     */
    public function setEmailVerified($accountId)
    {
        $query = 'UPDATE accounts SET email_verified = ? WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([true, $accountId]);
    }
}
