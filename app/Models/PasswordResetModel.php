<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = ['email', 'token', 'user_id', 'created_at', 'expires_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get the most recent reset token for an email
     *
     * @param string $email User email
     * @return object|null Token object or null if not found
     */
    public function getToken($email)
    {
        return $this->where('email', $email)->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get reset data by token
     *
     * @param string $token Reset token
     * @return object|null Token object or null if not found
     */
    public function getResetByToken($token)
    {
        return $this->where('token', $token)->first();
    }

    /**
     * Create a new password reset token
     *
     * @param string $email User email
     * @param int $user_id User ID
     * @param string $token Reset token
     * @return bool Success or failure
     */
    public function createToken($email, $user_id, $token)
    {
        // Delete any existing tokens for this email
        $this->where('email', $email)->delete();
        
        // Create token with 24 hour expiry
        $expires = date('Y-m-d H:i:s', time() + 86400);
        
        return $this->save([
            'email' => $email,
            'user_id' => $user_id,
            'token' => $token,
            'expires_at' => $expires
        ]);
    }

    /**
     * Delete reset token(s) for a user
     *
     * @param string $email User email
     * @return bool Success or failure
     */
    public function deleteToken($email)
    {
        return $this->where('email', $email)->delete();
    }

    /**
     * Check if a token is valid and not expired
     *
     * @param string $token Reset token
     * @return bool True if valid, false if invalid or expired
     */
    public function isValidToken($token)
    {
        $reset = $this->getResetByToken($token);
        
        if (!$reset) {
            return false;
        }
        
        // Check if token has expired
        return strtotime($reset->expires_at) > time();
    }
}