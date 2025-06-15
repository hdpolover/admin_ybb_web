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
        return view('auth/sign-in');
    }    public function signIn()
    {

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $type = $this->request->getPost('type');

        // Check if this is a reviewer login attempt
        if ($type === 'reviewer') {
            return redirect()->to('/reviewer-sign-in')->withInput();
        }

        // Pass the parameters directly to signIn method
        $admin = $this->adminModel->signIn($email, $password, $type);

        if ($admin) {
            // Set session data
            $this->session->set('isLoggedIn', true);
            $this->session->set('adminId', $admin->id);
            $this->session->set('userType', 'admin');

            // if admin role is super, return welcome page. if not, return dashboard page
            if ($admin->role == 'super') {
                return redirect()->to('/welcome');
            } else {
                // set current program id in session
                $this->session->set('current_program_id', $admin->program_id);
                return redirect()->to('/dashboard');
            }
        } else {
            return redirect()->back()->with('error', $admin['message'] ?? 'Invalid email or password.');
        }
    }
    
    public function signOut()
    {
        $session = session();
        $session->remove('isLoggedIn');
        
        // Clear the program selection cookie as well
        $this->response->deleteCookie('has_program_selected');
        
        $session->remove('current_program');
        
        return redirect()->to('/');
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
