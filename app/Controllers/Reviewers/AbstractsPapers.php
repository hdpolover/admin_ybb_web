<?php

namespace App\Controllers\Reviewers;

use App\Controllers\BaseController;
use App\Models\AbstractFeedbackModel;

class AbstractsPapers extends BaseController
{
    protected $abstractFeedbackModel;

    public function __construct()
    {
        $this->abstractFeedbackModel = new AbstractFeedbackModel();
    }

    /**
     * Get reviewer's program ID from database
     * 
     * @param int $reviewerId
     * @return int|null
     */
    private function getReviewerProgramId($reviewerId)
    {
        $reviewerModel = new \App\Models\ReviewerModel();
        $reviewer = $reviewerModel->find($reviewerId);
        return $reviewer ? $reviewer->program_id : null;
    }

    public function index()
    {
        $reviewerId = session()->get('reviewerId');

        if (!$reviewerId) {
            return redirect()->to('/reviewers/login')
                ->with('error', 'Please log in as a reviewer to access this page.');
        } // Get reviewer's assigned subthemes for debugging purposes
        $abstractReviewerSubthemeModel = new \App\Models\AbstractReviewerSubthemeModel();
        $assignedSubthemes = $abstractReviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);        // Debug logging
        log_message('info', 'Reviewer accessing abstracts: ID=' . $reviewerId);
        log_message('info', 'Assigned subthemes count: ' . count($assignedSubthemes));
        if (!empty($assignedSubthemes)) {
            foreach ($assignedSubthemes as $subtheme) {
                log_message('info', 'Subtheme: ' . ($subtheme->subtheme_name ?? 'NO NAME') . ' (ID: ' . $subtheme->program_subtheme_id . ')');
            }
        } else {
            log_message('warning', 'No subthemes found for reviewer ID: ' . $reviewerId);
        }

        $data = [
            'pageTitle' => 'Abstracts & Papers',
            'title' => 'Abstracts & Papers',
            'pagetitle' => 'Reviewer Dashboard',
            'currentUser' => (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ],
            'assignedSubthemes' => $assignedSubthemes
        ];

        return view('reviewers/abstracts-papers/index', $data);
    }
    public function getData()
    {
        $reviewerId = session()->get('reviewerId');

        // Check if this is an AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'This endpoint only accepts AJAX requests'
            ]);
        }

        if (!$reviewerId) {
            return $this->response->setJSON([
                'draw' => $this->request->getPost('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Reviewer session not found'
            ]);
        }
        try {
            // Get DataTables parameters
            $draw = $this->request->getPost('draw');
            $start = $this->request->getPost('start') ?? 0;
            $length = $this->request->getPost('length') ?? 10;
            $searchValue = $this->request->getPost('search')['value'] ?? '';

            log_message('info', '=== DataTables getData START ===');
            log_message('info', 'DataTables parameters - Draw: ' . $draw . ', Start: ' . $start . ', Length: ' . $length);
            log_message('info', 'Search value: ' . $searchValue);              // Get filters
            $subthemeFilter = $this->request->getPost('subtheme_filter');
            $statusFilter = $this->request->getPost('status_filter');

            $filters = [];
            if (!empty($subthemeFilter) && $subthemeFilter !== 'all') {
                $filters['subtheme_id'] = $subthemeFilter;
            }
            if (!empty($statusFilter) && $statusFilter !== 'all') {
                // Map frontend status values to appropriate filters
                switch ($statusFilter) {
                    case 'submitted':
                    case 'under_review':
                    case 'accepted':
                    case 'rejected':
                        $filters['abstract_status'] = $statusFilter;
                        break;
                }
            } // Get abstracts available for review based on reviewer's assigned subthemes
            // TEMPORARY: Use fixed method for debugging
            $abstracts = $this->abstractFeedbackModel->getFeedbacksByReviewerFixed($reviewerId, $filters);

            // Debug logging for troubleshooting
            log_message('info', 'DataTables request - Reviewer ID: ' . $reviewerId);
            log_message('info', 'Total abstracts found for reviewer: ' . count($abstracts));
            log_message('info', 'Applied filters: ' . json_encode($filters));            // Apply search filter
            if (!empty($searchValue)) {
                $abstracts = array_filter($abstracts, function ($abstract) use ($searchValue) {
                    return stripos($abstract->abstract_title, $searchValue) !== false ||
                        stripos($abstract->authors_list, $searchValue) !== false ||
                        stripos($abstract->program_name, $searchValue) !== false ||
                        stripos($abstract->subtheme_name ?? '', $searchValue) !== false;
                });
                log_message('info', 'After search filter, abstracts count: ' . count($abstracts));
            }

            $totalRecords = count($abstracts);
            log_message('info', 'Total records before pagination: ' . $totalRecords);

            // Apply pagination
            $abstracts = array_slice($abstracts, $start, $length);
            log_message('info', 'After pagination, abstracts count: ' . count($abstracts));

            // Format data for DataTables
            $data = [];
            foreach ($abstracts as $index => $abstract) {
                log_message('info', 'Processing abstract ' . $index . ': ID=' . $abstract->abstract_id . ', Title=' . $abstract->abstract_title);
                try {
                    $statusBadge = $this->getStatusBadge($abstract);
                    $actionButtons = $this->getActionButtons($abstract);

                    // Get authors from the query result or fallback
                    $authors = !empty($abstract->authors_list) ? $abstract->authors_list : 'No authors listed';

                    // Get feedbacks count for this abstract
                    $feedbacksCount = $this->getFeedbacksCount($abstract->abstract_id);

                    $data[] = [
                        'id' => $abstract->abstract_id,
                        'abstract_title' => $abstract->abstract_title .
                            (!empty($abstract->subtheme_name) ? '<br><small class="text-muted">Subtheme: ' . $abstract->subtheme_name . '</small>' : '<br><small class="text-danger">No subtheme assigned</small>'),
                        'authors' => $authors,
                        'program_name' => $abstract->program_name,
                        'submission_date' => date('M d, Y H:i', strtotime($abstract->submission_date)),
                        'abstract_status' => $statusBadge,
                        'feedbacks_count' => $feedbacksCount,
                        'actions' => $actionButtons
                    ];

                    log_message('info', 'Successfully processed abstract ' . $index);
                } catch (\Exception $e) {
                    log_message('error', 'Error processing abstract ' . $index . ': ' . $e->getMessage());
                }
            }
            $response = [
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ];

            log_message('info', 'DataTables response: ' . count($data) . ' records returned');
            log_message('info', 'Response structure: draw=' . $response['draw'] . ', recordsTotal=' . $response['recordsTotal'] . ', recordsFiltered=' . $response['recordsFiltered']);
            log_message('info', 'Sample data item: ' . (isset($data[0]) ? json_encode($data[0]) : 'NO DATA'));
            log_message('info', '=== DataTables getData END ===');

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Error in getData: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $this->request->getPost('draw') ?? 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data: ' . $e->getMessage()
            ]);
        }
    }
    public function view($abstractId)
    {
        $reviewerId = session()->get('reviewerId');

        $abstract = $this->abstractFeedbackModel->getComprehensiveAbstractDetails($abstractId, $reviewerId);

        if (!$abstract) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Abstract not found, access denied, or you are not assigned to this subtheme.');
        }

        // Additional security check: ensure the abstract belongs to reviewer's program
        $reviewerProgramId = $this->getReviewerProgramId($reviewerId);
        if ($abstract->program_id != $reviewerProgramId) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Access denied: This abstract belongs to a different program.');
        }
        $data = [
            'pageTitle' => 'View Abstract - ' . $abstract->abstract_title,
            'title' => 'View Abstract - ' . $abstract->abstract_title,
            'pagetitle' => 'Abstracts & Papers',
            'abstract' => $abstract,
            'currentUser' => (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ]
        ];

        return view('reviewers/abstracts-papers/view', $data);
    }
    public function review($abstractId)
    {
        $reviewerId = session()->get('reviewerId');

        $abstract = $this->abstractFeedbackModel->getComprehensiveAbstractDetails($abstractId, $reviewerId);

        if (!$abstract) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Abstract not found, access denied, or you are not assigned to this subtheme.');
        }

        // Additional security check: ensure the abstract belongs to reviewer's program
        $reviewerProgramId = $this->getReviewerProgramId($reviewerId);
        if ($abstract->program_id != $reviewerProgramId) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Access denied: This abstract belongs to a different program.');
        }
        $data = [
            'pageTitle' => 'Review Abstract - ' . $abstract->abstract_title,
            'title' => 'Review Abstract - ' . $abstract->abstract_title,
            'pagetitle' => 'Abstracts & Papers',
            'abstract' => $abstract,
            'currentUser' => (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ]
        ];

        return view('reviewers/abstracts-papers/review', $data);
    }
    public function submitReview($abstractId)
    {
        $reviewerId = session()->get('reviewerId');

        // Verify the abstract belongs to this reviewer and they have access
        $abstract = $this->abstractFeedbackModel->getFeedbackDetails($abstractId, $reviewerId);
        if (!$abstract) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Abstract not found, access denied, or you are not assigned to this subtheme.');
        }

        // Additional security check: ensure the abstract belongs to reviewer's program
        $reviewerProgramId = $this->getReviewerProgramId($reviewerId);
        if ($abstract->program_id != $reviewerProgramId) {
            return redirect()->to('/reviewers/abstracts-papers')
                ->with('error', 'Access denied: This abstract belongs to a different program.');
        }        // Get form data
        $feedback = $this->request->getPost('feedback');

        // Validate required fields
        if (empty($feedback)) {
            return redirect()->back()->with('error', 'Feedback is required.');
        }

        // Submit the feedback
        if ($this->abstractFeedbackModel->submitFeedback($abstractId, $reviewerId, $feedback)) {
            return redirect()->to('/reviewers/abstracts-papers')->with('success', 'Feedback submitted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to submit feedback. Please try again.');
        }
    }

    /**
     * Accept an abstract - mark it as accepted by the reviewer
     */
    public function accept($abstractId)
    {
        $reviewerId = session()->get('reviewerId');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'This endpoint only accepts AJAX requests'
            ]);
        }

        if (!$reviewerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer session not found'
            ]);
        }

        try {
            // First, verify that the reviewer has access to this abstract
            $abstract = $this->abstractFeedbackModel->getFeedbackDetails($abstractId, $reviewerId);

            if (!$abstract) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Abstract not found or access denied'
                ]);
            }

            // Check if reviewer has already provided feedback
            if (empty($abstract->feedback)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You must provide feedback before accepting an abstract'
                ]);
            }

            // Check if abstract is already accepted
            if ($abstract->abstract_status === 'accepted') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This abstract has already been accepted'
                ]);
            }

            // Update abstract status to accepted
            $abstractModel = new \App\Models\AbstractModel();
            $updateResult = $abstractModel->update($abstractId, [
                'status' => 'accepted',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updateResult) {
                log_message('info', "Abstract {$abstractId} accepted by reviewer {$reviewerId}");

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Abstract has been successfully accepted'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update abstract status'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error accepting abstract: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while accepting the abstract'
            ]);
        }
    }
    private function getStatusBadge($abstract)
    {
        // Determine status based on abstract status and feedback
        $abstractStatus = $abstract->abstract_status ?? 'submitted';
        $hasFeedback = !empty($abstract->feedback);

        switch ($abstractStatus) {
            case 'accepted':
                return '<span class="badge bg-success">
                            <i class="ri-check-double-line me-1"></i>Accepted
                        </span>';

            case 'under_review':
                if ($hasFeedback) {
                    return '<span class="badge bg-info">
                                <i class="ri-eye-line me-1"></i>Under Review
                            </span>';
                } else {
                    return '<span class="badge bg-warning">
                                <i class="ri-time-line me-1"></i>Pending Review
                            </span>';
                }

            case 'rejected':
                return '<span class="badge bg-danger">
                            <i class="ri-close-circle-line me-1"></i>Rejected
                        </span>';

            default: // submitted, draft, etc.
                if ($hasFeedback) {
                    return '<span class="badge bg-success">
                                <i class="ri-check-line me-1"></i>Review Completed
                            </span>';
                } else {
                    return '<span class="badge bg-secondary">
                                <i class="ri-file-text-line me-1"></i>Submitted
                            </span>';
                }
        }
    }
    private function getActionButtons($abstract)
    {
        $buttons = '<div class="btn-group" role="group">';

        // View button
        $buttons .= '<a href="/reviewers/abstracts-papers/view/' . $abstract->abstract_id . '" class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="ri-eye-line"></i>
                    </a>';

        // Review/Edit button - only show if abstract is not accepted
        $abstractStatus = $abstract->abstract_status ?? 'submitted';
        if ($abstractStatus !== 'accepted') {
            if (empty($abstract->feedback)) {
                $buttons .= '<a href="/reviewers/abstracts-papers/review/' . $abstract->abstract_id . '" class="btn btn-sm btn-outline-primary" title="Add Review">
                                <i class="ri-edit-line"></i>
                            </a>';
            } else {
                $buttons .= '<a href="/reviewers/abstracts-papers/review/' . $abstract->abstract_id . '" class="btn btn-sm btn-outline-warning" title="Edit Feedback">
                                <i class="ri-edit-2-line"></i>
                            </a>';
            }
        } else {
            // For accepted abstracts, show a disabled indicator
            $buttons .= '<span class="btn btn-sm btn-outline-success disabled" title="Abstract Accepted - No Further Action Required">
                            <i class="ri-check-double-line"></i>
                        </span>';
        }

        $buttons .= '</div>';

        return $buttons;
    }

    private function getAbstractStatusColor($status)
    {
        $colors = [
            'submitted' => 'primary',
            'under_review' => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger'
        ];

        return $colors[$status] ?? 'secondary';
    }
    /**
     * Helper method to render view with standard data
     */
    private function renderView($viewName, $data = [])
    {
        // Ensure title is set if pageTitle exists
        if (isset($data['pageTitle']) && !isset($data['title'])) {
            $data['title'] = $data['pageTitle'];
        }

        // Ensure pagetitle is set for breadcrumb
        if (!isset($data['pagetitle'])) {
            $data['pagetitle'] = 'Reviewer Dashboard';
        }

        // Add current user if not already set
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ];
        }

        return view($viewName, $data);
    }

    /**
     * Get reviewer dashboard statistics
     */    public function getStats()
    {
        $reviewerId = session()->get('reviewerId');

        if (!$reviewerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid reviewer session'
            ]);
        }

        // Get overall statistics
        $stats = $this->abstractFeedbackModel->getReviewerStats($reviewerId);

        // Get assigned subthemes information
        $abstractReviewerSubthemeModel = new \App\Models\AbstractReviewerSubthemeModel();
        $assignedSubthemes = $abstractReviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);

        $data = [
            'total_assigned' => $stats['total_assigned'],
            'total_completed' => $stats['total_completed'],
            'total_pending' => $stats['total_pending'],
            'completion_rate' => $stats['completion_rate'],
            'assigned_subthemes_count' => count($assignedSubthemes),
            'assigned_subthemes' => $assignedSubthemes
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Debug method to check reviewer access and assignments
     */    public function debugReviewerAccess()
    {
        $reviewerId = session()->get('reviewerId');

        if (!$reviewerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid reviewer session',
                'session_data' => [
                    'reviewerId' => $reviewerId,
                    'reviewerProgramId' => $this->getReviewerProgramId($reviewerId)
                ]
            ]);
        }        // Get reviewer info
        $reviewerModel = new \App\Models\ReviewerModel();
        $reviewer = $reviewerModel->find($reviewerId);
        $reviewerProgramId = $reviewer ? $reviewer->program_id : null;

        // Get reviewer's assigned subthemes
        $subthemeModel = new \App\Models\AbstractReviewerSubthemeModel();
        $assignedSubthemes = $subthemeModel->getSubthemesByReviewerId($reviewerId);

        // Get all submitted abstracts in reviewer's program
        $abstractModel = new \App\Models\AbstractModel();
        $allSubmittedAbstracts = $abstractModel->where('program_id', $reviewerProgramId)
            ->where('status', 'submitted')
            ->where('is_deleted', 0)
            ->findAll();

        // Get abstracts available to this reviewer
        $availableAbstracts = $this->abstractFeedbackModel->getFeedbacksByReviewer($reviewerId);

        return $this->response->setJSON([
            'success' => true,
            'debug_info' => [
                'reviewer_id' => $reviewerId,
                'reviewer_program_id' => $reviewerProgramId,
                'reviewer_details' => $reviewer,
                'assigned_subthemes' => $assignedSubthemes,
                'assigned_subthemes_count' => count($assignedSubthemes),
                'total_submitted_abstracts_in_program' => count($allSubmittedAbstracts),
                'available_abstracts_for_review' => count($availableAbstracts),
                'available_abstracts' => $availableAbstracts
            ]
        ]);
    }

    /**
     * Test method to check subtheme assignments
     */
    public function testSubthemes()
    {
        $reviewerId = session()->get('reviewerId');

        if (!$reviewerId) {
            echo "<h3>No reviewer ID in session</h3>";
            echo "<pre>Session data: " . print_r(session()->get(), true) . "</pre>";
            return;
        }

        echo "<h3>Reviewer ID: " . $reviewerId . "</h3>";

        // Test direct database query
        $db = \Config\Database::connect();
        $builder = $db->table('abstract_reviewer_subthemes ars');
        $builder->select('ars.*, ps.name as subtheme_name, ps.desc as subtheme_description, ar.name as reviewer_name');
        $builder->join('program_subthemes ps', 'ps.id = ars.program_subtheme_id', 'left');
        $builder->join('abstract_reviewers ar', 'ar.id = ars.abstract_reviewer_id', 'left');
        $builder->where('ars.abstract_reviewer_id', $reviewerId);
        $builder->where('ars.is_active', 1);
        $builder->where('ars.is_deleted', 0);

        $result = $builder->get()->getResult();

        echo "<h4>Direct Database Query Results:</h4>";
        echo "<pre>" . print_r($result, true) . "</pre>";

        // Test model method
        $abstractReviewerSubthemeModel = new \App\Models\AbstractReviewerSubthemeModel();
        $modelResult = $abstractReviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);

        echo "<h4>Model Method Results:</h4>";
        echo "<pre>" . print_r($modelResult, true) . "</pre>";

        // Check if reviewer exists
        $reviewerModel = new \App\Models\AbstractReviewerModel();
        $reviewer = $reviewerModel->find($reviewerId);

        echo "<h4>Reviewer Details:</h4>";
        echo "<pre>" . print_r($reviewer, true) . "</pre>";
    }

    /**
     * Simple test endpoint to debug DataTables response
     */
    public function testData()
    {
        $reviewerId = session()->get('reviewerId');

        log_message('info', '=== TEST DATA ENDPOINT ===');
        log_message('info', 'Reviewer ID: ' . $reviewerId);

        // Get abstracts without any filters first
        $abstracts = $this->abstractFeedbackModel->getFeedbacksByReviewer($reviewerId, []);
        log_message('info', 'Abstracts found (no filters): ' . count($abstracts));

        // Test simple DataTables response format
        $data = [];
        foreach ($abstracts as $abstract) {
            $data[] = [
                'id' => $abstract->abstract_id,
                'abstract_title' => $abstract->abstract_title,
                'participant_name' => $abstract->participant_name,
                'program_name' => $abstract->program_name,
                'submission_date' => date('M d, Y', strtotime($abstract->submission_date)),
                'status' => 'Test Status',
                'abstract_status' => 'submitted',
                'actions' => 'Test Actions'
            ];
        }

        $response = [
            'draw' => 1,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ];

        log_message('info', 'Test response: ' . json_encode($response));

        return $this->response->setJSON($response);
    }
    /**
     * Get specific abstract version details via AJAX
     */
    public function version($versionId)
    {
        $reviewerId = session()->get('reviewerId');

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'This endpoint only accepts AJAX requests'
            ]);
        }

        if (!$reviewerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reviewer session not found'
            ]);
        }

        try {
            // Simplified approach: Get version details and verify reviewer has access to the abstract
            $builder = $this->abstractFeedbackModel->db->table('abstract_versions av');
            $builder->select('av.id, av.title, av.content, av.keywords, av.refs, 
                             av.version_number, av.is_active, av.created_at, av.updated_at,
                             av.status, a.id as abstract_id, a.program_id, a.program_subtheme_id');
            $builder->join('abstracts a', 'a.id = av.abstract_id');
            $builder->where('av.id', $versionId);
            $builder->where('av.is_deleted', 0);
            $builder->where('av.status', 'submitted'); // Only allow access to submitted versions

            $version = $builder->get()->getRow();

            if (!$version) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Version not found or not submitted'
                ]);
            }

            // Check if reviewer has access to this abstract through their subtheme assignments
            $reviewerAccess = $this->abstractFeedbackModel->db->table('abstract_reviewers ar')
                ->join('abstract_reviewer_subthemes ars', 'ars.abstract_reviewer_id = ar.id')
                ->where('ar.id', $reviewerId)
                ->where('ar.program_id', $version->program_id)
                ->where('ars.program_subtheme_id', $version->program_subtheme_id)
                ->where('ar.is_active', 1)
                ->where('ar.is_deleted', 0)
                ->where('ars.is_active', 1)
                ->where('ars.is_deleted', 0)
                ->countAllResults();

            if ($reviewerAccess == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied - not assigned to this subtheme'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'version' => $version
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching version details: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while fetching version details'
            ]);
        }
    }

    /**
     * Get authors for an abstract
     */
    private function getAuthorsForAbstract($abstractId)
    {
        $authors = $this->abstractFeedbackModel->getAbstractAuthors($abstractId);

        if (empty($authors)) {
            return 'No authors listed';
        }

        // Return first 2 authors with "and X more" if there are more
        $authorNames = array_map(function ($author) {
            return $author->full_name;
        }, $authors);

        if (count($authorNames) <= 2) {
            return implode(', ', $authorNames);
        } else {
            $displayAuthors = array_slice($authorNames, 0, 2);
            $remaining = count($authorNames) - 2;
            return implode(', ', $displayAuthors) . ' <small class="text-muted">and ' . $remaining . ' more</small>';
        }
    }

    /**
     * Get feedbacks count for an abstract
     */
    private function getFeedbacksCount($abstractId)
    {
        try {
            $feedbacks = $this->abstractFeedbackModel->getAllFeedbacksForAbstract($abstractId);
            return count($feedbacks);
        } catch (\Exception $e) {
            log_message('error', 'Error getting feedbacks count for abstract ' . $abstractId . ': ' . $e->getMessage());
            return 0;
        }
    }
}
