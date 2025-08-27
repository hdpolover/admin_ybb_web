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
        $admin = $this->adminModel->signIn($email, $password);

        if ($admin) {
            // Get admin programs
            $adminProgramModel = new \App\Models\AdminProgramModel();
            $adminPrograms = $adminProgramModel->getAdminPrograms($admin->id);
            
            // Update last login
            $this->adminModel->updateLastLogin($admin->id, session_id());
            
            // Set session data
            $this->session->set('isLoggedIn', true);
            $this->session->set('adminId', $admin->id);
            $this->session->set('userType', 'admin');
            $this->session->set('userRole', $admin->role);
            
            // Set comprehensive session data for topbar and sidebar
            $sessionData = [
                'currentUser' => $admin,
                'userType' => 'admin',
                'userRole' => $admin->role,
                'userId' => $admin->id,
                'adminPrograms' => $adminPrograms,
                'isJournalType' => false // Will be updated based on program selection
            ];
            $this->session->set('topbar_data', $sessionData);

            // Role-based redirection
            if ($admin->role == 'super_admin') {
                return redirect()->to('/welcome');
            } else {
                // For non-super admins, set first assigned program if available
                if (!empty($adminPrograms)) {
                    $firstProgram = $adminPrograms[0];
                    $this->session->set('current_program_id', $firstProgram->program_id);
                }
                return redirect()->to('/dashboard');
            }
        } else {
            return redirect()->back()->with('error', 'Invalid email or password.');
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
