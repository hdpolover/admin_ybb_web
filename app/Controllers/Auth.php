<?php

namespace App\Controllers;

use App\Models\AdminModel;

class Auth extends BaseController
{
    protected $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        // $session = session();
        // if ($session->get('isLoggedIn')) {
        //     return redirect()->to('/dashboard');
        // }
        return view('auth/login');
    }

    public function login()
    {
        $email = trim($this->request->getPost('email', FILTER_SANITIZE_EMAIL));
        $password = trim($this->request->getPost('password'));

        // Add rate limiting/brute force protection
        // if ($this->isRateLimited()) {
        //     return redirect()->back()->with('error', 'Too many login attempts. Please try again later.');
        // }

        $admin = $this->adminModel->login($email, $password);


        if ($admin) {
            $session = session();
            $session->set('isLoggedIn', true);
            $session->set('adminId', $admin->id);
            return redirect()->to('/welcome');
        } else {
            return redirect()->back()->with('error', 'Invalid email or password.');
        }
    }

    public function logout()
    {
        $session = session();
        $session->remove('isLoggedIn');
        return redirect()->to('/login');
    }
    
    protected function isRateLimited()
    {
        // Implement rate limiting logic here
        // Example: Check login attempts count in session/database
        $session = session();
        $attempts = $session->get('login_attempts') ?? 0;
        $lastAttempt = $session->get('last_login_attempt') ?? 0;
        
        // Reset attempts if last attempt was more than 1 hour ago
        if (time() - $lastAttempt > 3600) {
            $session->set('login_attempts', 1);
            $session->set('last_login_attempt', time());
            return false;
        }
        
        // Set attempts
        $session->set('login_attempts', $attempts + 1);
        $session->set('last_login_attempt', time());
        
        // Limit to 5 attempts per hour
        return $attempts >= 5;
    }
    
    protected function getStoredHash($username)
    {
        // In a real application, fetch this from a database
        // This is just an example for demonstration
        $users = [
            'admin@ybb.com' => password_hash('admin123', PASSWORD_DEFAULT),
            'user@example.com' => password_hash('user123', PASSWORD_DEFAULT),
        ];
        
        return isset($users[$username]) ? $users[$username] : '';
    }

}
