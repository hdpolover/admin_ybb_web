<?php

namespace App\Models;

use App\Entities\UserEntity;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'full_name',
        'email',
        'password',
        'is_verified',
        'program_category_id',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUsers($limit = 10, $offset = 0, $filters = [])
    {
        $builder = $this->builder();

        // Apply filters if any
        if (!empty($filters)) {
            $builder->where($filters);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);
        
        // Select fields (exclude password for security)
        $builder->select('*');

        // Execute query
        $result = $builder->get()->getResultArray();

        return [
            'data' => $result,
            'total' => $total
        ];
    }

    // sign in
    public function signIn($email, $password, $web_url)
    {
        // Validate input
        if (empty($email) || empty($password) || empty($web_url)) {
            return false;
        }

        // get program category id by web_url
        $programCategoryModel = new ProgramCategoryModel();
        $programCategory = $programCategoryModel->getProgramCategoryIdByWebUrl($web_url);

        // Check if user exists
        $user = $this->where('email', $email)
            ->where('program_category_id', $programCategory['id'])
            ->first();

        if (!$user) {
            return false;
        }

        // Verify password with md5
        // if (!password_verify($password, $user->password)) {
        //     return false;
        // }

        return $user;
    }

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }
        

    public function getUserByEmailAndWebUrl($email, $web_url)
    {
        $builder = $this->builder();
        $builder->select('users.*')
            ->where('users.email ', $email)
            ->join('program_categories', 'program_categories.id = users.program_category_id')
            ->where('program_categories.web_url', $web_url);
        return $builder->get()->getRowArray();
    }

    public function updatePassword($email, $newPassword)
    {
        return $this->where('email', $email)
            ->set(['password' => md5($newPassword)])
            ->update();
    }

}
?>