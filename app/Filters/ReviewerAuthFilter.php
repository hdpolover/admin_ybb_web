<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ReviewerAuthFilter implements FilterInterface
{    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in and is a reviewer
        if (!$session->get('isLoggedIn') || $session->get('userType') !== 'reviewer') {
            return redirect()->to('/')->with('error', 'Please sign in as a reviewer to access this area.');
        }
        
        // Optional: Check if reviewer is active
        if ($session->get('reviewerStatus') !== 'active') {
            return redirect()->to('/')->with('error', 'Your reviewer account is not active.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
