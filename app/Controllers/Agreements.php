<?php

namespace App\Controllers;

use App\Models\FaqModel;

class Agreements extends BaseController
{
    

    public function __construct()
    {
        $this->ProgramDoc = new \App\Models\ParticipantProgramDocumentModel();
        $this->DocModel = new \App\Models\ProgramDocumentModel();
    }

    /**
     * Display list of FAQs
     */
    public function index()
    {
        //get current program ID from session
        $programId = session('current_program');
        $documentName = 'Agreement Letter'; // Ganti sesuai kebutuhan
        // Get all FAQs
        
        $document = $this->DocModel->getDocumentIdByName($programId, $documentName);
        if ($document) {
            $programDocumentId = $document->id;
        } else {
            // Handle jika tidak ditemukan
            $programDocumentId = null;
        }
        // echo '<pre>';
        // var_dump($document);
        // echo '</pre>';
        // exit;
        $docs = $this->ProgramDoc->getAllDocsByProgramId($programDocumentId);

        if (empty($docs)) {
            $docs = []; // Initialize as an empty array if no FAQs found
        }

        $data = [
            'title' => 'Frequently Asked Questions',
            'docs' => $docs
        ];

        return view('documents/agreements/index', $data);
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

    public function updateStatus()
    {
        $id        = $this->request->getPost('id_doc');
        $status    = $this->request->getPost('status_doc');
        $notes     = $this->request->getPost('notes');
        
        $model = new \App\Models\ParticipantProgramDocumentModel();

        $existing = $model->where('id', $id)
                        ->first();

        if (!$existing) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $updated = $model->where('id', $id)->set([
            'status' => $status,
            'notes' => $notes,
        ])->update();

        if ($updated) {
            return redirect()->back()->with('success', 'Status Update.');
        } else {
            return redirect()->back()->with('error', 'Failed Update Status.');
        }
    }

    /**
     * Delete a Docs (soft delete)
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */    
    public function delete()
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
        if ($this->faqModel->update($id, ['is_deleted' => 1, 'is_active' => 0])) {
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