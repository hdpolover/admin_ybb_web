<?php

namespace App\Controllers\Reviewers;

use App\Controllers\BaseController;
use App\Models\ReviewerModel;

class MyInfo extends BaseController
{
    protected $reviewerModel;

    public function __construct()
    {
        $this->reviewerModel = new ReviewerModel();
    }    public function index()
    {
        $reviewerId = session()->get('reviewerId');
        $reviewer = $this->reviewerModel->find($reviewerId);
        
        if (!$reviewer) {
            return redirect()->to('/reviewers/dashboard')->with('error', 'Reviewer profile not found.');
        }
        
        // Get reviewer statistics
        $stats = $this->reviewerModel->getReviewerStatistics($reviewerId);
        
        $data = [
            'pageTitle' => 'My Profile',
            'title' => 'My Profile',
            'pagetitle' => 'Reviewer Dashboard',
            'reviewer' => $reviewer,
            'stats' => $stats,
            'currentUser' => (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ]
        ];

        return view('reviewers/my-info/index', $data);
    }

    public function update()
    {
        $reviewerId = session()->get('reviewerId');
          // Get form data
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'institution' => $this->request->getPost('institution')
        ];
        
        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return redirect()->back()->with('error', 'Name and email are required.');
        }
        
        // Check if email is already taken by another reviewer
        $existingReviewer = $this->reviewerModel->where('email', $data['email'])
                                               ->where('id !=', $reviewerId)
                                               ->where('is_deleted', 0)
                                               ->first();
        
        if ($existingReviewer) {
            return redirect()->back()->with('error', 'Email address is already taken by another reviewer.');
        }
        
        // Update profile
        if ($this->reviewerModel->updateProfile($reviewerId, $data)) {
            // Update session data
            session()->set('reviewerName', $data['name']);
            session()->set('reviewerEmail', $data['email']);
            
            return redirect()->back()->with('success', 'Profile updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function changePassword()
    {
        $reviewerId = session()->get('reviewerId');
        
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'All password fields are required.');
        }
        
        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'New password and confirmation do not match.');
        }
        
        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'New password must be at least 6 characters long.');
        }
        
        // Verify current password
        $reviewer = $this->reviewerModel->find($reviewerId);
        if (!password_verify($currentPassword, $reviewer->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }
        
        // Change password
        if ($this->reviewerModel->changePassword($reviewerId, $newPassword)) {
            return redirect()->back()->with('success', 'Password changed successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to change password. Please try again.');        }
    }
}
