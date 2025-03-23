<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'otp_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = ['email', 'otp', 'user_id', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getOtp($email)
    {
        return $this->where('email', $email)->orderBy('created_at', 'DESC')->first();
    }

    public function getOtpByEmailAndOtp($email, $otp)
    {
        return $this->where('email', $email)->where('otp', $otp)->first();
    }

    public function createOtp($email, $user_id, $otp)
    {
        return $this->save([
            'email' => $email,
            'user_id' => $user_id,
            'otp' => $otp
        ]);
    }

    public function deleteOtp($email)
    {
        return $this->where('email', $email)->delete();
    }

}