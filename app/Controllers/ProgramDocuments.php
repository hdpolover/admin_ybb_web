<?php

namespace App\Controllers;

use App\Models\ProgramDocumentModel;
use App\Models\ProgramModel;


class ProgramDocuments extends BaseController
{
    protected $programDocumentModel;
    protected $programModel;

    public function __construct()
    {
        // Load the required models
        $this->programDocumentModel = new ProgramDocumentModel();
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        $programId = session('current_program');
        $program = $this->programModel->find($programId);

        if (!$program) {
            return redirect()->to('/welcome')->with('error', 'Program not found');
        }

        // program documents
        $programDocuments = $this->programDocumentModel->getProgramDocumentsByProgramId($programId);

        $data = [
            'title' => 'Program Documents',
            'program' => $program,
            'programDocuments' => $programDocuments,
        ];

        return view('documents/program-documents/index', $data);
    }

    /**
     * View a single document
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function view($id = null)
    {
        if ($id === null) {
            return redirect()->to('/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        // If document is LOA type, check if template exists
        $hasTemplate = false;
        if ($document->type === 'loa') {
            $loaTemplateModel = new \App\Models\LoaTemplateModel();
            $template = $loaTemplateModel->getLoaTemplateByProgramDocumentId($id);
            
            
            $hasTemplate = ($template !== null);
        }

        $data = [
            'title' => 'View Document',
            'document' => $document,
            'hasLoaTemplate' => $hasTemplate
        ];


        return view('documents/program-documents/view', $data);
    }

    /**
     * Process document creation
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function create()
    {
        $programId = session('current_program');

        // Get form data
        $data = [
            'program_id' => $programId,
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'desc' => $this->request->getPost('desc'),
            'is_upload' => $this->request->getPost('is_upload') ?? 0,
            'is_generated' => $this->request->getPost('is_generated') ?? 0,
            'visibility' => $this->request->getPost('visibility') ?? 1,
            'is_active' => $this->request->getPost('is_active') ?? 1,
            'is_deleted' => 0
        ];

        // Handle file upload if it exists
        $file = $this->request->getFile('document_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Generate new file name
            $newName = $file->getRandomName();

            // Move file to uploads directory
            $file->move(ROOTPATH . 'writable/uploads/documents', $newName);

            // Set file URL
            $data['file_url'] = base_url('uploads/documents/' . $newName);
            $data['is_upload'] = 1;
        } else if ($this->request->getPost('drive_url')) {
            // Set Google Drive URL if provided instead
            $data['drive_url'] = $this->request->getPost('drive_url');
            $data['is_upload'] = 0;
        }

        // Insert the data
        if ($this->programDocumentModel->insert($data)) {
            return redirect()->to('/program-documents')
                ->with('success', 'Document has been added successfully');
        } else {
            return redirect()->to('/program-documents')
                ->with('error', 'Failed to add document: ' . implode(', ', $this->programDocumentModel->errors()));
        }
    }

    /**
     * Show edit document form
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        $data = [
            'title' => 'Edit Document',
            'document' => $document,
        ];

        return view('documents/program-documents/edit', $data);
    }

    /**
     * Process document update
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update($id = null)
    {
        if ($id === null) {
            return redirect()->to('/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID to check if it exists
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        // Get form data
        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'desc' => $this->request->getPost('desc'),
            'visibility' => $this->request->getPost('visibility') ?? 1,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        // Handle file upload if it exists
        $file = $this->request->getFile('document_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Generate new file name
            $newName = $file->getRandomName();

            // Move file to uploads directory
            $file->move(ROOTPATH . 'writable/uploads/documents', $newName);

            // Set file URL
            $data['file_url'] = base_url('uploads/documents/' . $newName);
            $data['is_upload'] = 1;
            $data['drive_url'] = null;
        } else if ($this->request->getPost('drive_url') && $this->request->getPost('url_type') === 'drive') {
            // Set Google Drive URL if provided instead
            $data['drive_url'] = $this->request->getPost('drive_url');
            $data['is_upload'] = 0;

            // Only clear file_url if we're switching to drive link
            if ($document->is_upload == 1) {
                $data['file_url'] = null;
            }
        }

        // Update the document
        if ($this->programDocumentModel->update($id, $data)) {
            return redirect()->to('/program-documents/view/' . $id)
                ->with('success', 'Document has been updated successfully');
        } else {
            return redirect()->to('/program-documents/edit/' . $id)
                ->with('error', 'Failed to update document: ' . implode(', ', $this->programDocumentModel->errors()));
        }
    }

    /**
     * Delete a document
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID to check if it exists
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        // Soft delete by setting is_deleted = 1
        if ($this->programDocumentModel->update($id, ['is_deleted' => 1])) {
            return redirect()->to('/program-documents')
                ->with('success', 'Document has been deleted successfully');
        } else {
            return redirect()->to('/program-documents')
                ->with('error', 'Failed to delete document');
        }
    }

    /**
     * AJAX endpoint to get document data
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\Response
     */
    public function getDocument($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Document ID is required']);
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return $this->response->setJSON(['success' => false, 'message' => 'Document not found']);
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You do not have access to this document']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $document]);
    }

    /**
     * Show LOA template settings page
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function loaSettings($id = null)
    {
        if ($id === null) {
            return redirect()->to('/documents/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/documents/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/documents/program-documents')->with('error', 'You do not have access to this document');
        }

        // Check if document is of type 'loa'
        if ($document->type !== 'loa') {
            return redirect()->to('/documents/program-documents/view/' . $id)->with('error', 'This feature is only available for LOA documents');
        }

        // Get program details
        $program = $this->programModel->find($programId);

        // Get saved LOA template if exists
        $loaTemplateModel = new \App\Models\LoaTemplateModel();
        $template = $loaTemplateModel->where('program_document_id', $id)->first();

        // Get LOA placeholders
        $placeholders = $this->getPlaceholders();

        $data = [
            'title' => 'LOA Template Settings',
            'document' => $document,
            'program' => $program,
            'placeholders' => $placeholders,
            'loaTemplate' => $template ? $template->body : $this->getDefaultLoaTemplate()
        ];

        return view('documents/program-documents/loa_template_setting', $data);
    }

    /**
     * Get all LOA placeholders for use in templates
     * @return \CodeIgniter\HTTP\Response
     */
    public function getPlaceholders()
    {
        // Load the LOA placeholder model
        $placeholders = [
            'today_date' => "Today's Date",
            'participant_name' => 'Participant Name',
            'program_name' => 'Program Name',
            'program_location' => 'Program Location',
            'program_tagline' => 'Program Tagline',
            'institution' => 'Institution Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
        ];

        // Format placeholders for display
        $formattedPlaceholders = array_map(function ($key, $value) {
            return [
                'key' => $key,
                'value' => $value
            ];
        }, array_keys($placeholders), $placeholders);

        // Return
        return $formattedPlaceholders;
    }

    /**
     * Save LOA template
     * @param int $id Document ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function saveLoaTemplate($id = null)
    {
        if ($id === null) {
            return redirect()->to('/documents/program-documents')->with('error', 'Document ID is required');
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/documents/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        // Check if document is of type 'loa'
        if ($document->type !== 'loa') {
            return redirect()->to('/program-documents/view/' . $id)->with('error', 'This feature is only available for LOA documents');
        }

        // Get template content from the form
        $templateContent = $this->request->getPost('loa_template');

        if (!$templateContent) {
            return redirect()->to('/program-documents/loa-settings/' . $id)->with('error', 'Template content is required');
        }

        // Save or update the LOA template
        $loaTemplateModel = new \App\Models\LoaTemplateModel();

        // Check if template already exists
        $existingTemplate = $loaTemplateModel->where('program_document_id', $id)->first();

        if ($existingTemplate) {
            // Update existing template
            $loaTemplateModel->update($existingTemplate->id, [
                'body' => $templateContent,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new template
            $loaTemplateModel->insert([
                'program_document_id' => $id,
                'letter_type' => 'regular',
                'body' => $templateContent,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->to('/documents/program-documents/loa-settings/' . $id)
            ->with('success', 'LOA template has been saved successfully');
    }

    /**
     * Generate LOA document for a participant
     * @param int $id Document ID
     * @param int $participantId Participant ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function generateLoa($id = null, $participantId = null)
    {
        if ($id === null || $participantId === null) {
            return redirect()->to('/program-documents')->with('error', 'Document ID and Participant ID are required');
        }

        // Get document by ID
        $document = $this->programDocumentModel->getProgramDocumentById($id);

        if (!$document) {
            return redirect()->to('/program-documents')->with('error', 'Document not found');
        }

        $programId = session('current_program');

        // Verify that the document belongs to the current program
        if ($document->program_id != $programId) {
            return redirect()->to('/program-documents')->with('error', 'You do not have access to this document');
        }

        // Check if document is of type 'loa'
        if ($document->type !== 'loa') {
            return redirect()->to('/program-documents/view/' . $id)->with('error', 'This feature is only available for LOA documents');
        }

        // Get participant details
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($participantId);

        if (!$participant) {
            return redirect()->to('/program-documents/view/' . $id)->with('error', 'Participant not found');
        }

        // Get program details
        $program = $this->programModel->find($programId);

        // Get LOA template
        $loaTemplateModel = new \App\Models\LoaTemplateModel();
        $template = $loaTemplateModel->where('document_id', $id)->first();

        if (!$template) {
            return redirect()->to('/program-documents/loa-settings/' . $id)
                ->with('error', 'LOA template not found. Please create a template first.');
        }

        // Generate LOA content with participant data
        $loaContent = $this->generateLoaContent($template->template_content, $participant, $program, $document);

        $data = [
            'title' => 'Generated LOA',
            'document' => $document,
            'program' => $program,
            'participant' => $participant,
            'loaContent' => $loaContent
        ];

        return view('documents/program-documents/loa_generated', $data);
    }

    /**
     * Get default LOA template
     * @return string
     */
    private function getDefaultLoaTemplate()
    {
        return '<h3 style="text-align: center;">LETTER OF AGREEMENT</h3>
<p>Reference Number: {{reference_number}}</p>
<p>Date: {{today_date}}</p>
<p><br></p>
<p>Dear {{participant_name}},</p>
<p><br></p>
<p>We are pleased to inform you that you have been selected to participate in the {{program_name}} organized by {{institution}}.</p>
<p><br></p>
<p>Program Details:</p>
<ul>
    <li>Start Date: {{start_date}}</li>
    <li>End Date: {{end_date}}</li>
    <li>Location: Youth Break the Boundaries Center</li>
</ul>
<p><br></p>
<p>Please confirm your participation by signing this letter of agreement.</p>
<p><br></p>
<p>By signing this agreement, you agree to:</p>
<ol>
    <li>Attend all scheduled sessions of the program</li>
    <li>Follow the rules and guidelines set by the organizers</li>
    <li>Participate actively in all activities</li>
    <li>Represent yourself and your institution with the highest level of integrity</li>
</ol>
<p><br></p>
<p>We look forward to your active participation in this program.</p>
<p><br></p>
<p>Sincerely,</p>
<p><br></p>
<div class="preview-signature-section">
    {{participant_signature}}
    {{admin_signature}}
</div>';
    }

    /**
     * Generate LOA content with participant data
     * @param string $template Template content
     * @param object $participant Participant object
     * @param object $program Program object
     * @param object $document Document object
     * @return string
     */
    private function generateLoaContent($template, $participant, $program, $document)
    {
        // Replace variables with actual data
        $content = $template;
        $content = str_replace('{{participant_name}}', $participant->full_name, $content);
        $content = str_replace('{{program_name}}', $program->name, $content);
        $content = str_replace('{{institution}}', $participant->institution ?? 'Youth Break the Boundaries', $content);
        $content = str_replace('{{today_date}}', date('F d, Y'), $content);
        $content = str_replace('{{start_date}}', date('F d, Y', strtotime($program->start_date)), $content);
        $content = str_replace('{{end_date}}', date('F d, Y', strtotime($program->end_date)), $content);
        $content = str_replace('{{participant_address}}', $participant->current_address ?? $participant->origin_address ?? '-', $content);
        $content = str_replace('{{reference_number}}', 'LOA-' . date('Y') . '-' . $document->id . '-' . $participant->id, $content);

        // Replace signature placeholders
        $participantSignature = '<div class="signature-box"><p><strong>Participant:</strong></p><p>' . $participant->full_name . '</p></div>';
        $adminSignature = '<div class="signature-box"><p><strong>For ' . $program->name . ':</strong></p><p>' . session('name') . '<br>Administrator</p></div>';

        $content = str_replace('{{participant_signature}}', $participantSignature, $content);
        $content = str_replace('{{admin_signature}}', $adminSignature, $content);

        return $content;
    }
}
