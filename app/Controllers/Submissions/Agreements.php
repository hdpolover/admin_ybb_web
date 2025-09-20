<?php

namespace App\Controllers\Submissions;

use App\Controllers\BaseController;
use App\Models\FaqModel;

class Agreements extends BaseController
{
    protected $ProgramDoc;
    protected $DocModel;

    public function __construct()
    {
        $this->ProgramDoc = new \App\Models\ParticipantProgramDocumentModel();
        $this->DocModel = new \App\Models\ProgramDocumentModel();
    }

    /**
     * Display list of Agreement Letters
     */
    public function index()
    {
        //get current program ID from session
        $programId = session('current_program');
        $documentName = 'Agreement Letter'; // Ganti sesuai kebutuhan
        $type = 'agreement'; // Ganti sesuai kebutuhan

        // $document = $this->DocModel->getDocumentIdByName($programId, $documentName);
        $document = $this->DocModel->getDocumentIdByType($programId, $type);
        if ($document) {
            $programDocumentId = $document->id;
        } else {
            // Handle jika tidak ditemukan
            $programDocumentId = null;
        }

        $docs = $this->ProgramDoc->getAllDocsByProgramId($programDocumentId);

        if (empty($docs)) {
            $docs = []; // Initialize as an empty array if no agreements found
        }

        $data = [
            'title' => 'Agreement Letters',
            'docs' => $docs
        ];

        return view('submissions/agreements/index', $data);
    }

    /**
     * Update status of agreement letter
     * 
     * @return \CodeIgniter\HTTP\Response
     */



    public function updateStatus()
    {
        $idDoc = $this->request->getPost('id_doc');
        $statusDoc = $this->request->getPost('status_doc');
        $notes = $this->request->getPost('notes');

        if (!$idDoc) {
            session()->setFlashdata('error', 'Document ID is required');
            return redirect()->back();
        }

        try {
            // Update the document status
            $data = [
                'status'     => $statusDoc,
                'notes'      => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->ProgramDoc->update($idDoc, $data);

            if ($updated) {
                $doc = $this->ProgramDoc->find($idDoc);

                if ($doc) {
                    $participantModel = new \App\Models\ParticipantModel();
                    $participant = $participantModel->getById($doc->participant_id);

                    if ($participant && $participant->user_id) {
                        $userModel = new \App\Models\UserModel();
                        $user = $userModel->find($participant->user_id);

                        if ($user && !empty($user->email)) {

                            $emailService = new \App\Services\EmailService();

                            $emailSent = $emailService->sendStatus(
                                $user->email,
                                $user->full_name ?? 'User',
                                $statusDoc
                            );

                            if ($emailSent) {
                                session()->setFlashdata(
                                    'success',
                                    'Agreement letter updated & notification email sent to: ' . $user->email
                                );
                            } else {
                                session()->setFlashdata(
                                    'warning',
                                    'Status updated, but failed to send email to: ' . $user->email
                                );
                            }
                        } else {
                            session()->setFlashdata('warning', 'Status updated, but user email not found.');
                        }
                    }
                }
            } else {
                session()->setFlashdata('error', 'Failed to update agreement letter status');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating agreement status: ' . $e->getMessage());
            session()->setFlashdata('error', 'An error occurred while updating the status');
        }

        return redirect()->to(base_url('submissions/agreements'));
    }
}
