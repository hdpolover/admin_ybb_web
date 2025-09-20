<?php

namespace App\Controllers;

class Agreements extends AdminBaseController
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
        $documentName = 'Agreement Letter';
        $type = 'agreement';

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
                'status' => $statusDoc,
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->ProgramDoc->update($idDoc, $data);

            if ($updated) {
                // Ambil data dokumen yang sudah diupdate
                $doc = $this->ProgramDoc->find($idDoc);

                // Debug 1: cek isi $doc
                var_dump($doc);
                exit;

                if ($doc) {
                    // Ambil participant
                    $participantModel = new \App\Models\ParticipantModel();
                    $participant = $participantModel->getById($doc->participant_id);

                    // Debug 2: cek isi $participant
                    var_dump($participant);
                    exit;

                    if ($participant && $participant->user_id) {
                        // Ambil user
                        $userModel = new \App\Models\UserModel();
                        $user = $userModel->find($participant->user_id);

                        // Debug 3: cek isi $user
                        var_dump($user);
                        exit;

                        if ($user && !empty($user->email)) {
                            // Debug 4: cek email aja
                            var_dump("Email tujuan: " . $user->email);
                            exit;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating agreement status: ' . $e->getMessage());
            session()->setFlashdata('error', 'An error occurred while updating the status');
        }

        return redirect()->to(base_url('submissions/agreements'));
    }
}
