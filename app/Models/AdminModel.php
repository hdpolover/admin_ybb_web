<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'program_id',
        'profile_url',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password'
    ];

    public function getAdminByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // login
    public function login($email, $password)
    {
        $admin = $this->getAdminByEmail($email);
        if ($admin && md5($password) === $admin->password) {
            return $admin;
        }

        return false;
    }

}
?>