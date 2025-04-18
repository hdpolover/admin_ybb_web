<?php

namespace App\Controllers;

use App\Models\FaqModel;

class Faqs extends BaseController
{
    protected $faqModel;
    protected $faqCategories = [
        'event_details' => 'Event Details',
        'registration' => 'Registration',
        'payments' => 'Payments'
    ];
    
    public function __construct()
    {
        $this->faqModel = new FaqModel();
    }

    /**
     * Display list of FAQs
     */
    public function index()
    {
        //get current program ID from session
        $programId = session('current_program');
        // Get all FAQs
        $faqs = $this->faqModel->getAllFaqsByProgramId($programId);
        

        if (empty($faqs)) {
            return redirect()->to('/master-data/faqs')->with('error', 'No FAQs found for the current program.');
        }
        
        $data = [
            'title' => 'Frequently Asked Questions',
            'faqs' => $faqs,
            'faqCategories' => $this->faqCategories,
        ];
        
        return view('master-data/faqs/index', $data);
    }
      /**
     * Get FAQ by ID (AJAX)
     * 
     * @param int $id FAQ ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function get($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'FAQ ID is required']);
        }
        
        // Find the FAQ
        $faq = $this->faqModel->find($id);
        
        // Check if FAQ exists
        if (!$faq) {
            return $this->response->setJSON(['success' => false, 'message' => 'FAQ not found']);
        }
        
        // Add category name to the FAQ object
        if (!empty($faq->faq_category)) {
            $faq->category_name = $this->faqCategories[$faq->faq_category] ?? $faq->faq_category;
        } else {
            $faq->category_name = 'General';
        }
        
        return $this->response->setJSON(['success' => true, 'data' => $faq]);
    }
    
    /**
     * Create a new FAQ
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */    public function create()
    {
        // Validate form data
        $rules = [
            'question' => 'required|max_length[255]',
            'answer' => 'required',
            'faq_category' => 'permit_empty|in_list[event_details,registration,payments]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'program_id' => 'required|numeric'
        ];
        
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();
        
        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create FAQ: ' . implode(', ', $this->validator->getErrors())
                ]);
            } else {
                return redirect()->to('/master-data/faqs')
                    ->with('error', 'Failed to create FAQ: ' . implode(', ', $this->validator->getErrors()));
            }
        }
        
        // Prepare data
        $data = [
            'program_id' => $this->request->getPost('program_id'),
            'question' => $this->request->getPost('question'),
            'answer' => $this->request->getPost('answer'),
            'faq_category' => $this->request->getPost('faq_category'),
            'is_active' => $this->request->getPost('is_active') ?: 1,
            'is_deleted' => 0
        ];
        
        // Create the FAQ
        if ($this->faqModel->insert($data)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'FAQ created successfully',
                    'data' => $data
                ]);
            } else {
                return redirect()->to('/master-data/faqs')
                    ->with('success', 'FAQ created successfully');
            }
        } else {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create FAQ'
                ]);
            } else {
                return redirect()->to('/master-data/faqs')
                    ->with('error', 'Failed to create FAQ');
            }
        }
    }
      /**
     * Update an existing FAQ
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */    public function update()
    {
        $id = $this->request->getPost('id');
        $isAjax = $this->request->isAJAX();
        
        if (!$id) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'FAQ ID is required']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'FAQ ID is required');
            }
        }
        
        // Find the FAQ
        $faq = $this->faqModel->find($id);
        
        // Check if FAQ exists
        if (!$faq) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'FAQ not found']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'FAQ not found');
            }
        }
          // Validate form data
        $rules = [
            'question' => 'required|max_length[255]',
            'answer' => 'required',
            'faq_category' => 'permit_empty|in_list[event_details,registration,payments]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'program_id' => 'required|numeric'
        ];
        
        if (!$this->validate($rules)) {
            $errorMsg = 'Failed to update FAQ: ' . implode(', ', $this->validator->getErrors());
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', $errorMsg);
            }
        }
        
        // Prepare data
        $data = [
            'program_id' => $this->request->getPost('program_id'),
            'question' => $this->request->getPost('question'),
            'answer' => $this->request->getPost('answer'),
            'faq_category' => $this->request->getPost('faq_category'),
            'is_active' => $this->request->getPost('is_active') ?: 0
        ];
        
        // Update the FAQ
        if ($this->faqModel->update($id, $data)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'FAQ updated successfully',
                    'data' => $data
                ]);
            } else {
                return redirect()->to('/master-data/faqs')->with('success', 'FAQ updated successfully');
            }
        } else {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update FAQ']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'Failed to update FAQ');
            }
        }
    }
    
    /**
     * Delete a FAQ (soft delete)
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */    public function delete()
    {
        $id = $this->request->getPost('id');
        $isAjax = $this->request->isAJAX();
        
        if (!$id) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'FAQ ID is required']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'FAQ ID is required');
            }
        }
        
        // Find the FAQ
        $faq = $this->faqModel->find($id);
        
        // Check if FAQ exists
        if (!$faq) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'FAQ not found']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'FAQ not found');
            }
        }
        
        // Soft delete the FAQ
        if ($this->faqModel->update($id, ['is_deleted' => 1])) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'message' => 'FAQ deleted successfully']);
            } else {
                return redirect()->to('/master-data/faqs')->with('success', 'FAQ deleted successfully');
            }
        } else {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete FAQ']);
            } else {
                return redirect()->to('/master-data/faqs')->with('error', 'Failed to delete FAQ');
            }
        }
    }
}
