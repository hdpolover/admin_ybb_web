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
    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'full_name',
        'email',
        'password',
        'is_verified',
        'program_category_id',
        'is_active',
        'is_deleted',
        'verification_token'
    ];

    protected $hidden = [
        'password'
    ];

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
        if (md5($password) !== $user->password) {
            return false;
        }
        
        // Check if user's email is verified
        if (!$user->is_verified) {
            // Set a custom property to indicate email not verified
            $user->email_not_verified = true;
            return $user;
        }

        return $user;
    }

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getUserByParams($params)
    {
        $builder = $this->builder();
        $builder->select('users.*')
            ->where($params);
        return $builder->get()->getFirstRow('object');
    }

    public function getUserByEmailAndWebUrl($email, $web_url)
    {
        $builder = $this->builder();
        $builder->select('users.*')
            ->where('users.email ', $email)
            ->join('program_categories', 'program_categories.id = users.program_category_id')
            ->where('program_categories.web_url', $web_url);
        return $builder->get()->getRow();
    }

    public function updatePassword($user_id, $newPassword)
    {
        return $this->where('id', $user_id)
            ->set(['password' => md5($newPassword)])
            ->update();
    }

    public function createUser($data)
    {
        // Validate input
        if (empty($data['email']) || empty($data['password']) || empty($data['program_category_id'])) {
            return false;
        }

        // Hash password
        $data['password'] = md5($data['password']);
        
        // Set is_verified to 0 (false) by default
        $data['is_verified'] = 0;
        
        // Generate verification token
        $data['verification_token'] = bin2hex(random_bytes(16));

        // save user into database
        $this->save($data);
        // get user id
        $userId = $this->insertID();

        return $this->find($userId);
    }
    
    /**
     * Verify a user's email with the provided token
     *
     * @param string $email User email
     * @param string $token Verification token
     * @return bool True if verified successfully, false otherwise
     */
    public function verifyEmail($email, $token)
    {
        // Find the user by email and token
        $user = $this->where('email', $email)
                     ->where('verification_token', $token)
                     ->first();
                     
        if (!$user) {
            return false;
        }
        
        // Update user as verified
        return $this->update($user->id, [
            'is_verified' => 1,
            'verification_token' => null // Clear the token after verification
        ]);
    }
    
    /**
     * Get user by verification token
     * 
     * @param string $token Verification token
     * @return object|null User object or null if not found
     */
    public function getUserByVerificationToken($token)
    {
        return $this->where('verification_token', $token)->first();
    }
    
    /**
     * Resend verification email token
     *
     * @param string $email User email
     * @param string $web_url Web URL of the program
     * @return object|false User object with new token or false on failure
     */
    public function regenerateVerificationToken($email, $web_url)
    {
        // Get program category by web URL
        $programCategoryModel = new ProgramCategoryModel();
        $programCategory = $programCategoryModel->getProgramCategoryIdByWebUrl($web_url);
        
        // Find the user
        $user = $this->where('email', $email)
                     ->where('program_category_id', $programCategory['id'])
                     ->first();
        
        if (!$user) {
            return false;
        }
        
        // Don't regenerate if already verified
        if ($user->is_verified) {
            return $user;
        }
        
        // Generate new token
        $token = bin2hex(random_bytes(16));
        
        // Update user with new token
        $updated = $this->update($user->id, [
            'verification_token' => $token
        ]);
        
        if (!$updated) {
            return false;
        }
        
        // Return updated user
        return $this->find($user->id);
    }
}
