<?php

namespace App\Controllers\Reviewers;

use App\Controllers\BaseController;
use App\Models\ReviewerModel;

class Auth extends BaseController
{
    protected $reviewerModel;

    public function __construct()
    {
        $this->reviewerModel = new ReviewerModel();
    }    public function signIn()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Email and password are required.');
        }

        // Get reviewer by email from abstract_reviewers table
        $reviewer = $this->reviewerModel->getReviewerByEmail($email);        if ($reviewer && password_verify($password, $reviewer->password) && $reviewer->is_active) {
            // Set session data for reviewer
            $this->session->set([
                'isLoggedIn' => true,
                'userType' => 'reviewer',
                'reviewerId' => $reviewer->id,
                'reviewerName' => $reviewer->name,
                'reviewerEmail' => $reviewer->email,
                'reviewerProgramId' => $reviewer->program_id,
                'reviewerInstitution' => $reviewer->institution,
                'reviewerStatus' => 'active'
            ]);

            // Set cookie to indicate program is "selected" for reviewers
            // This prevents the JavaScript program check from blocking navigation
            $cookie = [
                'name' => 'has_program_selected',
                'value' => 'true',
                'expire' => time() + (24 * 60 * 60), // 24 hours
                'path' => '/',
                'secure' => false,
                'httponly' => false
            ];
            $this->response->setCookie($cookie);

            return redirect()->to('/reviewers/dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid email or password, or account is inactive.');
        }
    }    public function signOut()
    {
        $session = session();
        $session->remove([
            'isLoggedIn',
            'userType', 
            'reviewerId',
            'reviewerName',
            'reviewerEmail',
            'reviewerStatus'
        ]);
        
        // Remove the program selection cookie
        $cookie = [
            'name' => 'has_program_selected',
            'value' => '',
            'expire' => time() - 3600, // Expire in the past
            'path' => '/',
        ];
        $this->response->setCookie($cookie);
        
        return redirect()->to('/')->with('success', 'You have been signed out successfully.');
    }
}
