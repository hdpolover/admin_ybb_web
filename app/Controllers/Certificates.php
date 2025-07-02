<?php

namespace App\Controllers;

use App\Models\ProgramAwardModel;
use App\Models\ProgramCertificateModel;
use App\Models\ParticipantAwardModel;
use App\Models\ParticipantCertificateModel;
use App\Models\ParticipantModel;
use App\Models\ProgramModel;

class Certificates extends BaseController
{
    protected $programAwardModel;
    protected $programCertificateModel;
    protected $participantAwardModel;
    protected $participantCertificateModel;
    protected $participantModel;

    public function __construct()
    {
        $this->programAwardModel = new ProgramAwardModel();
        $this->programCertificateModel = new ProgramCertificateModel();
        $this->participantAwardModel = new ParticipantAwardModel();
        $this->participantCertificateModel = new ParticipantCertificateModel();
        $this->participantModel = new ParticipantModel();
    }

    /**
     * Display the certificate management page
     */
    public function index()
    {
        $programId = session('current_program');
        
        if (!$programId) {
            return redirect()->to('/welcome')->with('error', 'Please select a program first.');
        }

        // Get awards data for the view
        $awards = $this->getAwardsData($programId);
        
        // Add debug logging
        log_message('info', 'Certificates::index - Program ID: ' . $programId);
        log_message('info', 'Certificates::index - Awards count: ' . count($awards));
        
        $data = [
            'title' => 'Certificate Management',
            'pagetitle' => 'Documents',
            'programId' => $programId,
            'awards' => $awards
        ];

        return view('documents/certificates/index', $data);
    }

    /**
     * Display award details view page
     */
    public function view($awardId)
    {
        log_message('info', "=== Certificates::view START ===");
        log_message('info', "Award ID: $awardId");
        log_message('info', "Session data: " . json_encode(session()->get()));
        
        $programId = session('current_program');
        log_message('info', "Program ID from session: $programId");
        
        if (!$programId) {
            log_message('error', "No program selected - redirecting to welcome");
            return redirect()->to('/welcome')->with('error', 'Please select a program first.');
        }

        try {
            log_message('info', 'Loading award details...');
            log_message('info', 'Certificates::view - Award ID: ' . $awardId . ', Program ID: ' . $programId);
            
            // Get award details - convert to object if needed
            $awardData = $this->programAwardModel->find($awardId);
            log_message('info', 'Award found: ' . ($awardData ? 'Yes' : 'No'));
            log_message('info', 'Award data: ' . json_encode($awardData));
            
            // Convert array to object if necessary
            if (is_array($awardData)) {
                $award = (object) $awardData;
            } else {
                $award = $awardData;
            }
            
            if (!$award || !isset($award->program_id) || $award->program_id != $programId) {
                log_message('error', 'Award validation failed - Award exists: ' . ($award ? 'Yes' : 'No') . 
                           ', Award program_id: ' . (isset($award->program_id) ? $award->program_id : 'N/A') . 
                           ', Session program_id: ' . $programId);
                return redirect()->to('/documents/certificates')->with('error', 'Award not found or not accessible.');
            }

            log_message('info', 'Getting program details for program ID: ' . $programId);
            
            // Get program details - convert to object if needed
            $programModel = new ProgramModel();
            $programData = $programModel->find($programId);
            log_message('info', 'Program found: ' . ($programData ? 'Yes' : 'No'));
            
            // Convert array to object if necessary
            if (is_array($programData)) {
                $program = (object) $programData;
            } else {
                $program = $programData;
            }

            log_message('info', 'Getting award statistics...');
            
            // Get participant counts for statistics only
            $availableCount = $this->participantModel
                ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_active', 1)
                ->where('participants.is_deleted', 0)
                ->where('participant_awards.id IS NULL')
                ->countAllResults();
            
            $assignedCount = $this->participantAwardModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->countAllResults();

            log_message('info', 'Getting certificates...');
            
            // Get certificate templates for this award
            $certificates = $this->programCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->findAll();
            
            log_message('info', 'Certificates count: ' . count($certificates));

            // Get certificate issuance count
            $certificatesIssued = $this->participantCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->countAllResults();

            // Add statistics to award
            $award->participants_count = $assignedCount;
            $award->certificates_issued = $certificatesIssued;

            log_message('info', 'Preparing data for view...');

            log_message('info', 'Preparing data for view...');
            log_message('info', 'View data prepared - availableCount: ' . $availableCount . ', assignedCount: ' . $assignedCount);

            $data = [
                'title' => 'Award Details - ' . $award->title,
                'pagetitle' => 'Documents',
                'award' => $award,
                'program' => $program,
                'availableCount' => $availableCount,
                'assignedCount' => $assignedCount,
                'certificates' => $certificates
            ];

            log_message('info', 'Rendering view...');
            log_message('info', "=== Certificates::view END ===");
            
            return view('documents/certificates/view', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::view: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'Award ID: ' . $awardId . ', Program ID: ' . $programId);
            return redirect()->to('/documents/certificates')->with('error', 'Failed to load award details: ' . $e->getMessage());
        }
    }

    /**
     * Test method to check DataTable without authentication
     */
    public function testIndex()
    {
        // Force set session for testing
        $_SESSION['current_program'] = 8; // Use program 8 which we know has data
        $_SESSION['user_id'] = 1;
        
        $programId = 8;
        
        // Get awards data for the view
        $awards = $this->getAwardsData($programId);
        
        // Add debug logging
        log_message('info', 'Certificates::testIndex - Program ID: ' . $programId);
        log_message('info', 'Certificates::testIndex - Awards count: ' . count($awards));
        
        $data = [
            'title' => 'Certificate Management (TEST)',
            'pagetitle' => 'Documents',
            'programId' => $programId,
            'awards' => $awards
        ];

        return view('documents/certificates/index', $data);
    }

    /**
     * Debug method to check data retrieval
     */
    public function debugData()
    {
        // Force set session for testing
        $_SESSION['current_program'] = 8;
        $_SESSION['user_id'] = 1;
        
        $programId = 8;
        
        echo "<h2>Debug Certificates Data</h2>";
        echo "<p>Program ID: $programId</p>";
        
        try {
            // Test basic query
            $awards = $this->getAwardsData($programId);
            
            echo "<h3>Awards Data:</h3>";
            echo "<p>Count: " . count($awards) . "</p>";
            
            if (!empty($awards)) {
                echo "<table class='table table-striped'>";
                echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Participants</th><th>Certificates</th><th>Has Template</th></tr>";
                foreach ($awards as $award) {
                    echo "<tr>";
                    echo "<td>" . $award->id . "</td>";
                    echo "<td>" . $award->title . "</td>";
                    echo "<td>" . $award->award_type . "</td>";
                    echo "<td>" . $award->participants_count . "</td>";
                    echo "<td>" . $award->certificates_issued . "</td>";
                    echo "<td>" . ($award->has_certificate_template ? 'Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No awards found!</p>";
            }
            
            // Test direct database query
            echo "<h3>Direct Database Test:</h3>";
            $db = \Config\Database::connect();
            $directQuery = "SELECT * FROM program_awards WHERE program_id = ? AND is_active = 1 AND is_deleted = 0";
            $directResults = $db->query($directQuery, [$programId])->getResult();
            echo "<p>Direct query found: " . count($directResults) . " awards</p>";
            
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    /**
     * Get awards data formatted for display
     */
    private function getAwardsData($programId)
    {
        try {
            $db = \Config\Database::connect();
            
            $query = "
                SELECT 
                    pa.*,
                    COALESCE(participant_counts.participants_count, 0) as participants_count,
                    COALESCE(certificate_counts.certificates_issued, 0) as certificates_issued
                FROM program_awards pa
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as participants_count
                    FROM participant_awards 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) participant_counts ON participant_counts.award_id = pa.id
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as certificates_issued
                    FROM participant_certificates 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) certificate_counts ON certificate_counts.award_id = pa.id
                WHERE pa.program_id = ? 
                    AND pa.is_active = 1 
                    AND pa.is_deleted = 0
                ORDER BY pa.order_number ASC
            ";
            
            $awards = $db->query($query, [$programId])->getResult();
            
            // Get certificate templates for each award
            foreach ($awards as &$award) {
                $certificates = $this->programCertificateModel
                    ->where('award_id', $award->id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $award->certificate_templates = $certificates;
                $award->has_certificate_template = count($certificates) > 0;
            }

            return $awards;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getAwardsData: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get data for DataTables AJAX request
     */
    public function getData()
    {
        $programId = session('current_program');
        
        if (!$programId) {
            log_message('error', 'Certificates::getData - No program selected in session');
            return $this->response->setJSON(['error' => 'No program selected']);
        }

        log_message('info', 'Certificates::getData - Program ID: ' . $programId);

        try {
            // First, get all program awards for the current program
            $db = \Config\Database::connect();
            
            $query = "
                SELECT 
                    pa.*,
                    COALESCE(participant_counts.participants_count, 0) as participants_count,
                    COALESCE(certificate_counts.certificates_issued, 0) as certificates_issued
                FROM program_awards pa
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as participants_count
                    FROM participant_awards 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) participant_counts ON participant_counts.award_id = pa.id
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as certificates_issued
                    FROM participant_certificates 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) certificate_counts ON certificate_counts.award_id = pa.id
                WHERE pa.program_id = ? 
                    AND pa.is_active = 1 
                    AND pa.is_deleted = 0
                ORDER BY pa.order_number ASC
            ";
            
            $awards = $db->query($query, [$programId])->getResult();
            
            log_message('info', 'Certificates::getData - Found ' . count($awards) . ' awards for program ' . $programId);

            // Get certificate templates for each award
            foreach ($awards as &$award) {
                $certificates = $this->programCertificateModel
                    ->where('award_id', $award->id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $award->certificate_templates = $certificates;
                $award->has_certificate_template = count($certificates) > 0;
                
                log_message('info', 'Award: ' . $award->title . ' - Participants: ' . $award->participants_count . ' - Certificates: ' . $award->certificates_issued);
            }

            // Format data for DataTables
            $data = [];
            foreach ($awards as $award) {
                $certificateStatus = $award->has_certificate_template 
                    ? '<span class="badge bg-success">Available</span>' 
                    : '<span class="badge bg-warning">No Template</span>';

                $progressText = $award->participants_count > 0 
                    ? "{$award->certificates_issued} / {$award->participants_count}" 
                    : "0 / 0";

                $progressPercent = $award->participants_count > 0 
                    ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                    : 0;

                $actions = '<div class="btn-group" role="group">' .
                    '<button type="button" class="btn btn-primary btn-sm" onclick="viewAwardDetails(' . $award->id . ')" title="View Details">' .
                        '<i class="ri-eye-line"></i>' .
                    '</button>' .
                    '<button type="button" class="btn btn-success btn-sm" onclick="manageParticipants(' . $award->id . ')" title="Manage Participants">' .
                        '<i class="ri-group-line"></i>' .
                    '</button>';

                if ($award->has_certificate_template) {
                    $actions .= '<button type="button" class="btn btn-info btn-sm" onclick="issueCertificates(' . $award->id . ')" title="Issue Certificates">' .
                        '<i class="ri-award-line"></i>' .
                    '</button>';
                }

                $actions .= '</div>';

                $progressHtml = '<div class="progress" style="height: 20px;">' .
                    '<div class="progress-bar" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">' .
                        $progressText .
                    '</div>' .
                '</div>';

                $data[] = [
                    'id' => $award->id,
                    'title' => esc($award->title),
                    'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                    'description' => esc($award->description),
                    'participants_count' => $award->participants_count,
                    'certificates_issued' => $award->certificates_issued,
                    'progress' => $progressHtml,
                    'certificate_status' => $certificateStatus,
                    'actions' => $actions
                ];
            }

            log_message('info', 'Certificates::getData - Found ' . count($awards) . ' awards, returning ' . count($data) . ' data rows');

            // Add debug information to verify JSON encoding
            $jsonData = ['data' => $data];
            $jsonString = json_encode($jsonData);
            
            if ($jsonString === false) {
                $error = 'JSON encoding failed: ' . json_last_error_msg();
                log_message('error', 'Certificates::getData - ' . $error);
                return $this->response->setJSON(['error' => $error]);
            }
            
            log_message('info', 'Certificates::getData - JSON encoded successfully, size: ' . strlen($jsonString) . ' bytes');

            return $this->response->setJSON($jsonData);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON(['error' => 'Failed to load data: ' . $e->getMessage()]);
        }
    }

    /**
     * Get award details with participants
     */
    public function getAwardDetails($awardId)
    {
        try {
            // Get award details
            $award = $this->programAwardModel->find($awardId);
            if (!$award) {
                return $this->response->setJSON(['error' => 'Award not found']);
            }

            // Get participants assigned to this award
            $participants = $this->participantAwardModel
                ->select('participant_awards.*, participants.full_name, participants.account_id, users.email, 
                         participant_certificates.id as certificate_id, participant_certificates.generated_at')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_certificates', 'participant_certificates.participant_id = participant_awards.participant_id AND participant_certificates.award_id = participant_awards.award_id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('participant_awards.award_id', $awardId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0)
                ->orderBy('participant_awards.assigned_at', 'DESC')
                ->findAll();

            // Get certificate templates for this award
            $certificates = $this->programCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->findAll();

            return $this->response->setJSON([
                'award' => $award,
                'participants' => $participants,
                'certificates' => $certificates
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAwardDetails: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load award details']);
        }
    }

    /**
     * Get available participants data for DataTables AJAX
     */
    public function getAvailableParticipantsData($awardId)
    {
        log_message('info', "=== getAvailableParticipantsData START ===");
        log_message('info', "Award ID: $awardId");
        log_message('info', "Request method: " . $this->request->getMethod());
        log_message('info', "Session data: " . json_encode(session()->get()));
        
        $programId = session('current_program');
        log_message('info', "Program ID from session: $programId");
        
        if (!$programId) {
            log_message('error', "No program selected in session");
            return $this->response->setJSON(['error' => 'No program selected']);
        }

        try {
            // DataTables parameters - support both GET and POST
            $draw = $this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 1;
            $start = $this->request->getPost('start') ?? $this->request->getGet('start') ?? 0;
            $length = $this->request->getPost('length') ?? $this->request->getGet('length') ?? 25;
            $searchValue = '';
            $paymentFilter = $this->request->getPost('payment_filter') ?? $this->request->getGet('payment_filter') ?? 'any_payment';
            
            // Debug the payment filter
            log_message('info', "Payment filter from POST: " . $this->request->getPost('payment_filter'));
            log_message('info', "Payment filter from GET: " . $this->request->getGet('payment_filter'));
            log_message('info', "Final payment filter value: $paymentFilter");
            
            // Validate and ensure proper values
            $start = max(0, intval($start));
            $length = max(1, min(100, intval($length))); // Ensure length is between 1 and 100
            
            // Handle search parameter for both POST and GET
            if ($this->request->getMethod() === 'post') {
                $searchValue = $this->request->getPost('search')['value'] ?? '';
            } else {
                $searchValue = $this->request->getGet('search')['value'] ?? '';
            }
            
            log_message('info', "DataTable params: draw=$draw, start=$start, length=$length, search=$searchValue, payment_filter=$paymentFilter");
            
            // Get total count without pagination - only get participants with specific payment type
            $totalRecordsQuery = $this->participantModel
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_active', 1)
                ->where('participants.is_deleted', 0)
                ->where('participant_awards.id IS NULL');
            
            // Apply payment filtering based on selected filter
            $this->applyPaymentFilterToQuery($totalRecordsQuery, $paymentFilter, $programId);
            
            $totalRecords = $totalRecordsQuery->countAllResults();
            
            log_message('info', "Total available participants before search: $totalRecords");
            
            // Get filtered count if search is applied - start with participants who have payments
            $filteredRecords = $totalRecords;
            if (!empty($searchValue)) {
                $filteredQuery = $this->participantModel
                    ->join('users', 'users.id = participants.user_id', 'left')
                    ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                    ->where('participants.program_id', $programId)
                    ->where('participants.is_active', 1)
                    ->where('participants.is_deleted', 0)
                    ->where('participant_awards.id IS NULL');
                
                // Apply payment filtering
                $this->applyPaymentFilterToQuery($filteredQuery, $paymentFilter, $programId);
                
                // Apply search filter
                $filteredQuery->groupStart()
                    ->like('participants.full_name', $searchValue)
                    ->orLike('participants.account_id', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('participants.nationality', $searchValue)
                    ->groupEnd();
                
                $filteredRecords = $filteredQuery->countAllResults();
            }
            
            log_message('info', "Filtered participants count: $filteredRecords");
            
            // Get paginated data - use database builder for proper limit control
            $db = \Config\Database::connect();
            $builder = $db->table('participants');
            $builder->select('participants.id, participants.full_name, participants.account_id, participants.nationality as country, 
                         participants.category as funding_type, participants.created_at, users.email');
            $builder->join('users', 'users.id = participants.user_id', 'left');
            $builder->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left');
            $builder->where('participants.program_id', $programId);
            $builder->where('participants.is_active', 1);
            $builder->where('participants.is_deleted', 0);
            $builder->where('participant_awards.id IS NULL');
            
            // Apply payment filtering
            $this->applyPaymentFilterToQuery($builder, $paymentFilter, $programId);
            
            // Log the query we're about to execute
            $finalSql = $builder->getCompiledSelect(false);  // Get SQL without reset
            log_message('info', "Payment Filter SQL: $finalSql");
            
            // Log the query we're about to execute
            $finalSql = $builder->getCompiledSelect(false);  // Get SQL without reset
            log_message('info', "Final SQL query: $finalSql");
            
            // Apply search if provided
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('participants.full_name', $searchValue)
                    ->orLike('participants.account_id', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('participants.nationality', $searchValue)
                    ->groupEnd();
            }

            // Add ordering and pagination
            $builder->orderBy('participants.full_name', 'ASC');
            $builder->limit($length, $start);

            // Execute query
            $participants = $builder->get()->getResultArray();

            log_message('info', "Using limit: $length, offset: $start");
            log_message('info', "Found " . count($participants) . " participants in this page (limit: $length, start: $start)");

            // Format data for DataTables with proper UTF-8 encoding
            $data = [];
            foreach ($participants as $participant) {
                // Ensure all string values are properly UTF-8 encoded
                $fullName = mb_convert_encoding($participant['full_name'] ?? '', 'UTF-8', 'auto');
                $email = mb_convert_encoding($participant['email'] ?? '', 'UTF-8', 'auto');
                $country = mb_convert_encoding($participant['country'] ?? 'Unknown', 'UTF-8', 'auto');
                $accountId = mb_convert_encoding($participant['account_id'] ?? '', 'UTF-8', 'auto');
                
                $data[] = [
                    'checkbox' => '<div class="form-check"><input class="form-check-input participant-checkbox" type="checkbox" value="' . $participant['id'] . '" data-name="' . esc($fullName) . '"></div>',
                    'avatar' => '<div class="participant-avatar">' . strtoupper(substr($fullName, 0, 1)) . '</div>',
                    'name' => '<strong>' . esc($fullName) . '</strong>',
                    'account_id' => '<code>' . esc($accountId) . '</code>',
                    'email' => esc($email),
                    'country' => esc($country),
                    'funding' => isset($participant['funding_type']) ? 
                        '<span class="badge funding-type-badge bg-' . ($participant['funding_type'] === 'fully_funded' ? 'success' : 'info') . '">' . 
                        ucfirst(str_replace('_', ' ', $participant['funding_type'])) . '</span>' :
                        '<span class="badge funding-type-badge bg-secondary">Unknown</span>',
                    'registration_date' => date('M d, Y', strtotime($participant['created_at'])),
                    'actions' => '<button type="button" class="btn btn-sm btn-primary" onclick="assignSingleParticipant(' . $participant['id'] . ')" title="Assign to Award"><i class="ri-user-add-line"></i></button>'
                ];
            }

            $response = [
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ];
            
            // Don't log the entire response if it's too large
            if (count($data) <= 10) {
                log_message('info', "Sending response: " . json_encode($response));
            } else {
                log_message('info', "Sending response with " . count($data) . " records (too large to log)");
            }
            log_message('info', "=== getAvailableParticipantsData END ===");

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAvailableParticipantsData: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load participants data']);
        }
    }

    /**
     * Get assigned participants data for DataTables AJAX
     */
    public function getAssignedParticipantsData($awardId)
    {
        log_message('info', "=== getAssignedParticipantsData START ===");
        log_message('info', "Award ID: $awardId");
        log_message('info', "Request method: " . $this->request->getMethod());
        
        try {
            // DataTables parameters - support both GET and POST
            $draw = $this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 1;
            $start = $this->request->getPost('start') ?? $this->request->getGet('start') ?? 0;
            $length = $this->request->getPost('length') ?? $this->request->getGet('length') ?? 25;
            $searchValue = '';
            
            // Validate and ensure proper values
            $start = max(0, intval($start));
            $length = max(1, min(100, intval($length))); // Ensure length is between 1 and 100
            
            // Handle search parameter for both POST and GET
            if ($this->request->getMethod() === 'post') {
                $searchValue = $this->request->getPost('search')['value'] ?? '';
            } else {
                $searchValue = $this->request->getGet('search')['value'] ?? '';
            }
            
            log_message('info', "DataTable params: draw=$draw, start=$start, length=$length, search=$searchValue");
            
            // Get total count without pagination - use fresh instance
            $totalRecords = $this->participantAwardModel
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->where('participant_awards.award_id', $awardId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0)
                ->countAllResults();
            
            log_message('info', "Total assigned participants: $totalRecords");
            
            // Get filtered count if search is applied - use fresh instance
            $filteredRecords = $totalRecords;
            if (!empty($searchValue)) {
                $filteredRecords = $this->participantAwardModel
                    ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                    ->join('users', 'users.id = participants.user_id', 'left')
                    ->where('participant_awards.award_id', $awardId)
                    ->where('participant_awards.is_active', 1)
                    ->where('participant_awards.is_deleted', 0)
                    ->groupStart()
                    ->like('participants.full_name', $searchValue)
                    ->orLike('participants.account_id', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('participants.nationality', $searchValue)
                    ->groupEnd()
                    ->countAllResults();
            }
            
            // Get paginated data
            $assignedParticipants = $this->participantAwardModel
                ->select('participant_awards.id as participant_award_id, participant_awards.assigned_at, participant_awards.notes,
                         participants.id as participant_id, participants.full_name, participants.account_id,
                         users.email, participants.nationality as country,
                         participant_certificates.id as certificate_id, participant_certificates.generated_at')
                ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_certificates', 'participant_certificates.participant_id = participant_awards.participant_id AND participant_certificates.award_id = participant_awards.award_id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('participant_awards.award_id', $awardId)
                ->where('participant_awards.is_active', 1)
                ->where('participant_awards.is_deleted', 0);
            
            // Apply search if provided
            if (!empty($searchValue)) {
                $assignedParticipants->groupStart()
                    ->like('participants.full_name', $searchValue)
                    ->orLike('participants.account_id', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('participants.nationality', $searchValue)
                    ->groupEnd();
            }
            
            $assignedParticipants = $assignedParticipants
                ->orderBy('participant_awards.assigned_at', 'DESC')
                ->limit($length, $start)
                ->findAll();

            log_message('info', "Found " . count($assignedParticipants) . " assigned participants in this page");

            // Format data for DataTables
            $data = [];
            foreach ($assignedParticipants as $participant) {
                // Check if participant is an array or object and access properties accordingly
                $isArray = is_array($participant);
                
                // Log the data type for debugging
                log_message('info', "Participant data type: " . ($isArray ? 'Array' : 'Object'));
                
                // Access certificate_id properly based on data type
                $certificateId = $isArray ? $participant['certificate_id'] : $participant->certificate_id;
                $generatedAt = $isArray ? ($participant['generated_at'] ?? '') : ($participant->generated_at ?? '');
                $participantId = $isArray ? $participant['participant_id'] : $participant->participant_id;
                $participantAwardId = $isArray ? $participant['participant_award_id'] : $participant->participant_award_id;
                $fullName = $isArray ? $participant['full_name'] : $participant->full_name;
                $accountId = $isArray ? $participant['account_id'] : $participant->account_id;
                $email = $isArray ? $participant['email'] : $participant->email;
                $country = $isArray ? ($participant['country'] ?? 'Unknown') : ($participant->country ?? 'Unknown');
                $assignedAt = $isArray ? $participant['assigned_at'] : $participant->assigned_at;

                // Create certificate status HTML
                $certificateStatus = $certificateId ? 
                    '<span class="badge certificate-status-badge bg-success"><i class="ri-shield-check-line me-1"></i>Issued</span><br><small class="text-muted">' . date('M d, Y', strtotime($generatedAt)) . '</small>' :
                    '<span class="badge certificate-status-badge bg-warning"><i class="ri-time-line me-1"></i>Pending</span>';
                
                // Create actions HTML
                $actions = '';
                if (!$certificateId) {
                    $actions .= '<button type="button" class="btn btn-sm btn-success me-1" onclick="issueSingleCertificate(' . $participantId . ')" title="Issue Certificate"><i class="ri-file-text-line"></i></button>';
                } else {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning me-1" onclick="revokeCertificate(' . $certificateId . ')" title="Revoke Certificate"><i class="ri-close-circle-line"></i></button>';
                }
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="removeParticipantFromAward(' . $participantAwardId . ')" title="Remove from Award"><i class="ri-user-unfollow-line"></i></button>';
                
                $data[] = [
                    'checkbox' => '<div class="form-check"><input class="form-check-input assigned-checkbox" type="checkbox" value="' . $participantAwardId . '" data-name="' . esc($fullName) . '"></div>',
                    'avatar' => '<div class="participant-avatar">' . strtoupper(substr($fullName, 0, 1)) . '</div>',
                    'name' => '<strong>' . esc($fullName) . '</strong>',
                    'account_id' => '<code>' . esc($accountId) . '</code>',
                    'email' => esc($email),
                    'country' => esc($country),
                    'certificate_status' => $certificateStatus,
                    'assigned_date' => date('M d, Y', strtotime($assignedAt)),
                    'actions' => $actions
                ];
            }

            $response = [
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ];
            
            log_message('info', "Sending response: " . json_encode($response));
            log_message('info', "=== getAssignedParticipantsData END ===");

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAssignedParticipantsData: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load assigned participants data']);
        }
    }

    /**
     * Get available participants for assignment
     */
    public function getAvailableParticipants($awardId)
    {
        $programId = session('current_program');
        
        try {
            // Get participants who are not yet assigned to this award
            $participants = $this->participantModel
                ->select('participants.id, participants.full_name, participants.account_id, users.email')
                ->join('users', 'users.id = participants.user_id', 'left')
                ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->where('participants.program_id', $programId)
                ->where('participants.is_active', 1)
                ->where('participants.is_deleted', 0)
                ->where('participant_awards.id IS NULL')
                ->orderBy('participants.full_name', 'ASC')
                ->findAll();

            return $this->response->setJSON(['participants' => $participants]);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::getAvailableParticipants: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load participants']);
        }
    }

    /**
     * Assign participants to an award
     */
    public function assignParticipants()
    {
        try {
            log_message('info', 'assignParticipants START');
            $data = $this->request->getJSON(true);
            $awardId = $data['award_id'] ?? null;
            $participantIds = $data['participant_ids'] ?? [];
            $notes = $data['notes'] ?? '';
            
            log_message('info', 'Request data: ' . json_encode($data));
            log_message('info', 'Award ID: ' . $awardId);
            log_message('info', 'Participant IDs: ' . json_encode($participantIds));

            if (!$awardId || empty($participantIds)) {
                log_message('error', 'Missing required data: Award ID or participant IDs');
                return $this->response->setJSON(['error' => 'Award ID and participant IDs are required']);
            }

            // Get user ID from session - if not set, use a default admin ID
            $userId = session('user_id');
            if (empty($userId)) {
                $userId = 1; // Using default admin ID as fallback
                log_message('warning', 'User ID not found in session, using default admin ID: ' . $userId);
            }
            log_message('info', 'User ID for assignment: ' . $userId);
            
            $assignedCount = 0;
            $errors = [];

            foreach ($participantIds as $participantId) {
                // Check if already assigned
                log_message('info', 'Processing participant ID: ' . $participantId);
                if (!$this->participantAwardModel->hasParticipantAward($participantId, $awardId)) {
                    $assignData = [
                        'participant_id' => $participantId,
                        'award_id' => $awardId,
                        'assigned_by' => $userId, // Use admin ID if user_id is not in session
                        'assigned_at' => date('Y-m-d H:i:s'),
                        'notes' => $notes,
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];
                    
                    log_message('info', 'Inserting assignment data: ' . json_encode($assignData));
                    
                    $insertResult = $this->participantAwardModel->insert($assignData);
                    if ($insertResult) {
                        $assignedCount++;
                        log_message('info', 'Successfully assigned participant ID: ' . $participantId);
                    } else {
                        $errors[] = "Failed to assign participant ID: $participantId";
                        log_message('error', 'DB insertion failed for participant ID: ' . $participantId);
                        log_message('error', 'Validation errors: ' . json_encode($this->participantAwardModel->errors()));
                    }
                } else {
                    log_message('info', 'Participant ID: ' . $participantId . ' already assigned to award ID: ' . $awardId);
                }
            }

            log_message('info', 'Total participants assigned: ' . $assignedCount);
            
            if ($assignedCount > 0) {
                log_message('info', 'assignParticipants END - success');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "$assignedCount participant(s) assigned successfully",
                    'errors' => $errors
                ]);
            } else {
                log_message('info', 'assignParticipants END - no assignments');
                return $this->response->setJSON(['error' => 'No participants were assigned']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::assignParticipants: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON(['error' => 'Failed to assign participants: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove participant from award
     */
    public function removeParticipant()
    {
        try {
            $data = $this->request->getJSON(true);
            $participantAwardId = $data['participant_award_id'] ?? null;

            if (!$participantAwardId) {
                return $this->response->setJSON(['error' => 'Participant award ID is required']);
            }

            if ($this->participantAwardModel->softDelete($participantAwardId)) {
                // Also remove any issued certificates for this participant-award combination
                $participantAward = $this->participantAwardModel->find($participantAwardId);
                if ($participantAward) {
                    $this->participantCertificateModel
                        ->where('participant_id', $participantAward->participant_id)
                        ->where('award_id', $participantAward->award_id)
                        ->set(['is_deleted' => 1, 'is_active' => 0])
                        ->update();
                }

                return $this->response->setJSON(['success' => true, 'message' => 'Participant removed from award']);
            } else {
                return $this->response->setJSON(['error' => 'Failed to remove participant']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::removeParticipant: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to remove participant']);
        }
    }

    /**
     * Issue certificates to participants
     */
    public function issueCertificates()
    {
        try {
            $data = $this->request->getJSON(true);
            $awardId = $data['award_id'] ?? null;
            $participantIds = $data['participant_ids'] ?? [];

            if (!$awardId) {
                return $this->response->setJSON(['error' => 'Award ID is required']);
            }

            // Get certificate template for this award
            $certificate = $this->programCertificateModel
                ->where('award_id', $awardId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->first();

            if (!$certificate) {
                return $this->response->setJSON(['error' => 'No certificate template found for this award']);
            }

            $issuedCount = 0;
            $errors = [];

            // If no specific participants provided, issue to all assigned participants
            if (empty($participantIds)) {
                $assignedParticipants = $this->participantAwardModel
                    ->select('participant_id')
                    ->where('award_id', $awardId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $participantIds = array_column($assignedParticipants, 'participant_id');
            }

            foreach ($participantIds as $participantId) {
                // Check if certificate already issued
                if (!$this->participantCertificateModel->hasParticipantCertificate($participantId, $certificate->id)) {
                    $certificateData = [
                        'participant_id' => $participantId,
                        'award_id' => $awardId,
                        'certificate_id' => $certificate->id,
                        'generated_at' => date('Y-m-d H:i:s'),
                        'is_active' => 1,
                        'is_deleted' => 0
                    ];

                    if ($this->participantCertificateModel->insert($certificateData)) {
                        $issuedCount++;
                    } else {
                        $errors[] = "Failed to issue certificate to participant ID: $participantId";
                    }
                }
            }

            if ($issuedCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "$issuedCount certificate(s) issued successfully",
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON(['error' => 'No certificates were issued']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::issueCertificates: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to issue certificates']);
        }
    }

    /**
     * Revoke certificate from participant
     */
    public function revokeCertificate()
    {
        try {
            $data = $this->request->getJSON(true);
            $participantCertificateId = $data['participant_certificate_id'] ?? null;

            if (!$participantCertificateId) {
                return $this->response->setJSON(['error' => 'Participant certificate ID is required']);
            }

            if ($this->participantCertificateModel->softDelete($participantCertificateId)) {
                return $this->response->setJSON(['success' => true, 'message' => 'Certificate revoked successfully']);
            } else {
                return $this->response->setJSON(['error' => 'Failed to revoke certificate']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::revokeCertificate: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to revoke certificate']);
        }
    }

    /**
     * Test server-side participants endpoint
     */
    public function testParticipantsData($awardId)
    {
        // Force set session for testing
        $_SESSION['current_program'] = 8;
        $_SESSION['user_id'] = 1;
        
        echo "<h2>Test Participants Data for Award ID: $awardId</h2>";
        
        // Test available participants
        echo "<h3>Available Participants:</h3>";
        try {
            // Simulate POST request data
            $_POST = [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'search' => ['value' => '']
            ];
            
            $result = $this->getAvailableParticipantsData($awardId);
            $response = $result->getBody();
            echo "<pre>" . $response . "</pre>";
            
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        }
        
        // Test assigned participants
        echo "<h3>Assigned Participants:</h3>";
        try {
            $result = $this->getAssignedParticipantsData($awardId);
            $response = $result->getBody();
            echo "<pre>" . $response . "</pre>";
            
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        }
    }

    /**
     * Test method to debug data retrieval without authentication
     */
    public function testGetData()
    {
        // Force set program ID for testing
        $programId = 8; // Middle East Youth Summit 2025
        
        log_message('info', 'Certificates::testGetData - Program ID: ' . $programId);

        try {
            // Get all awards for the current program with participant counts
            $awards = $this->programAwardModel
                ->select('program_awards.*, 
                         COUNT(DISTINCT participant_awards.participant_id) as participants_count,
                         COUNT(DISTINCT participant_certificates.participant_id) as certificates_issued')
                ->join('participant_awards', 'participant_awards.award_id = program_awards.id AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                ->join('participant_certificates', 'participant_certificates.award_id = program_awards.id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                ->where('program_awards.program_id', $programId)
                ->where('program_awards.is_active', 1)
                ->where('program_awards.is_deleted', 0)
                ->groupBy('program_awards.id')
                ->orderBy('program_awards.order_number', 'ASC')
                ->findAll();

            log_message('info', 'Certificates::testGetData - Found ' . count($awards) . ' awards');

            // Get certificate templates for each award
            foreach ($awards as &$award) {
                // Convert array to object if needed
                if (is_array($award)) {
                    $award = (object) $award;
                }
                
                $certificates = $this->programCertificateModel
                    ->where('award_id', $award->id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                $award->certificate_templates = $certificates;
                $award->has_certificate_template = count($certificates) > 0;
                
                log_message('info', 'Award: ' . $award->title . ' - Participants: ' . $award->participants_count . ' - Certificates: ' . $award->certificates_issued);
            }

            // Format data for DataTables
            $data = [];
            foreach ($awards as $award) {
                $certificateStatus = $award->has_certificate_template 
                    ? '<span class="badge bg-success">Available</span>' 
                    : '<span class="badge bg-warning">No Template</span>';

                $progressText = $award->participants_count > 0 
                    ? "{$award->certificates_issued} / {$award->participants_count}" 
                    : "0 / 0";

                $progressPercent = $award->participants_count > 0 
                    ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                    : 0;

                $actions = '<div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="viewAwardDetails(' . $award->id . ')" title="View Details">
                        <i class="ri-eye-line"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="manageParticipants(' . $award->id . ')" title="Manage Participants">
                        <i class="ri-group-line"></i>
                    </button>';

                if ($award->has_certificate_template) {
                    $actions .= '<button type="button" class="btn btn-info btn-sm" onclick="issueCertificates(' . $award->id . ')" title="Issue Certificates">
                        <i class="ri-award-line"></i>
                    </button>';
                }

                $actions .= '</div>';

                $data[] = [
                    'id' => $award->id,
                    'title' => esc($award->title),
                    'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                    'description' => esc($award->description),
                    'participants_count' => $award->participants_count,
                    'certificates_issued' => $award->certificates_issued,
                    'progress' => '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $progressPercent . '%;" aria-valuenow="' . $progressPercent . '" aria-valuemin="0" aria-valuemax="100">
                            ' . $progressText . '
                        </div>
                    </div>',
                    'certificate_status' => $certificateStatus,
                    'actions' => $actions
                ];
            }

            log_message('info', 'Certificates::testGetData - Returning ' . count($data) . ' data rows');

            // Return both JSON and HTML for debugging
            $response = [
                'success' => true,
                'program_id' => $programId,
                'awards_found' => count($awards),
                'data_rows' => count($data),
                'data' => $data,
                'debug_info' => [
                    'session_program' => session('current_program'),
                    'forced_program' => $programId,
                    'awards_raw' => $awards
                ]
            ];

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error in Certificates::testGetData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Test method without any authentication - for debugging only
     */
    public function testDataDirect()
    {
        // Force program ID to 8 (which we know has data)
        $programId = 8;
        
        try {
            $db = \Config\Database::connect();
            
            $query = "
                SELECT 
                    pa.*,
                    COALESCE(participant_counts.participants_count, 0) as participants_count,
                    COALESCE(certificate_counts.certificates_issued, 0) as certificates_issued
                FROM program_awards pa
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as participants_count
                    FROM participant_awards 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) participant_counts ON participant_counts.award_id = pa.id
                LEFT JOIN (
                    SELECT 
                        award_id,
                        COUNT(DISTINCT participant_id) as certificates_issued
                    FROM participant_certificates 
                    WHERE is_active = 1 AND is_deleted = 0
                    GROUP BY award_id
                ) certificate_counts ON certificate_counts.award_id = pa.id
                WHERE pa.program_id = ? 
                    AND pa.is_active = 1 
                    AND pa.is_deleted = 0
                ORDER BY pa.order_number ASC
            ";
            
            $awards = $db->query($query, [$programId])->getResult();
            
            // Format data for DataTables
            $data = [];
            foreach ($awards as $award) {
                $data[] = [
                    'id' => $award->id,
                    'title' => $award->title,
                    'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                    'description' => $award->description,
                    'participants_count' => $award->participants_count,
                    'certificates_issued' => $award->certificates_issued,
                    'progress' => '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;">
                            0 / 0
                        </div>
                    </div>',
                    'certificate_status' => '<span class="badge bg-warning">No Template</span>',
                    'actions' => '<button class="btn btn-primary btn-sm">View</button>'
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'program_id' => $programId,
                'total_awards' => count($awards),
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to load data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Debug method to test view functionality
     */
    public function debugView($awardId)
    {
        // Force set session for testing
        $_SESSION['current_program'] = 8; // Set to a known program ID
        $_SESSION['user_id'] = 1; // Set to a known user ID
        
        echo "<h2>Debug Award View</h2>";
        echo "<p>Award ID: $awardId</p>";
        echo "<p>Program ID: " . session('current_program') . "</p>";
        
        try {
            // Test basic award lookup
            $award = $this->programAwardModel->find($awardId);
            echo "<h3>Award Details:</h3>";
            if ($award) {
                echo "<pre>" . print_r($award, true) . "</pre>";
            } else {
                echo "<p>Award not found!</p>";
                return;
            }
            
            // Test program lookup
            $programModel = new ProgramModel();
            $program = $programModel->find(session('current_program'));
            echo "<h3>Program Details:</h3>";
            if ($program) {
                echo "<pre>" . print_r($program, true) . "</pre>";
            } else {
                echo "<p>Program not found!</p>";
            }
            
            // Test available participants
            echo "<h3>Available Participants Query:</h3>";
            try {
                $availableParticipants = $this->participantModel
                    ->select('participants.*, users.email, participants.country, participants.funding_type, participants.is_ambassador')
                    ->join('users', 'users.id = participants.user_id', 'left')
                    ->join('participant_awards', 'participant_awards.participant_id = participants.id AND participant_awards.award_id = ' . $awardId . ' AND participant_awards.is_active = 1 AND participant_awards.is_deleted = 0', 'left')
                    ->where('participants.program_id', session('current_program'))
                    ->where('participants.is_active', 1)
                    ->where('participants.is_deleted', 0)
                    ->where('participant_awards.id IS NULL')
                    ->orderBy('participants.full_name', 'ASC')
                    ->findAll();
                
                echo "<p>Available participants count: " . count($availableParticipants) . "</p>";
                if (count($availableParticipants) > 0 && count($availableParticipants) < 5) {
                    echo "<pre>" . print_r($availableParticipants, true) . "</pre>";
                }
            } catch (\Exception $e) {
                echo "<p style='color:red;'>Error getting available participants: " . $e->getMessage() . "</p>";
            }
            
            // Test assigned participants
            echo "<h3>Assigned Participants Query:</h3>";
            try {
                $assignedParticipants = $this->participantAwardModel
                    ->select('participant_awards.id as participant_award_id, participant_awards.assigned_at, participant_awards.notes,
                             participants.id as participant_id, participants.full_name, participants.account_id, participants.is_ambassador,
                             users.email, participants.country,
                             participant_certificates.id as certificate_id, participant_certificates.generated_at')
                    ->join('participants', 'participants.id = participant_awards.participant_id', 'left')
                    ->join('users', 'users.id = participants.user_id', 'left')
                    ->join('participant_certificates', 'participant_certificates.participant_id = participant_awards.participant_id AND participant_certificates.award_id = participant_awards.award_id AND participant_certificates.is_active = 1 AND participant_certificates.is_deleted = 0', 'left')
                    ->where('participant_awards.award_id', $awardId)
                    ->where('participant_awards.is_active', 1)
                    ->where('participant_awards.is_deleted', 0)
                    ->orderBy('participant_awards.assigned_at', 'DESC')
                    ->findAll();
                
                echo "<p>Assigned participants count: " . count($assignedParticipants) . "</p>";
                if (count($assignedParticipants) > 0 && count($assignedParticipants) < 5) {
                    echo "<pre>" . print_r($assignedParticipants, true) . "</pre>";
                }
            } catch (\Exception $e) {
                echo "<p style='color:red;'>Error getting assigned participants: " . $e->getMessage() . "</p>";
            }
            
            // Test certificates
            echo "<h3>Certificates Query:</h3>";
            try {
                $certificates = $this->programCertificateModel
                    ->where('award_id', $awardId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
                
                echo "<p>Certificates count: " . count($certificates) . "</p>";
                if (count($certificates) > 0) {
                    echo "<pre>" . print_r($certificates, true) . "</pre>";
                }
            } catch (\Exception $e) {
                echo "<p style='color:red;'>Error getting certificates: " . $e->getMessage() . "</p>";
            }
            
            echo "<h3>Now testing actual view method:</h3>";
            echo "<a href='/documents/certificates/view/$awardId'>Test View Method</a>";
            
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
            echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    /**
     * Simple test endpoint
     */
    public function simpleTest()
    {
        $programId = session('current_program');
        
        echo "<h2>Certificate Management Debug Test</h2>";
        echo "<p>Session Program ID: " . $programId . "</p>";
        echo "<p>User ID: " . (session('user_id') ?? 'Not Set') . "</p>";
        
        if (!$programId) {
            echo "<p style='color:red;'>No program selected in session!</p>";
            return;
        }
        
        // Test direct SQL query
        $db = \Config\Database::connect();
        
        echo "<h3>Direct Program Awards Query:</h3>";
        $directQuery = "SELECT * FROM program_awards WHERE program_id = ? AND is_active = 1 AND is_deleted = 0";
        $directResults = $db->query($directQuery, [$programId])->getResult();
        echo "<p>Direct query found: " . count($directResults) . " awards</p>";
        
        foreach ($directResults as $award) {
            echo "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>";
            echo "<strong>Award ID:</strong> " . $award->id . "<br>";
            echo "<strong>Title:</strong> " . $award->title . "<br>";
            echo "<strong>Order:</strong> " . $award->order_number . "<br>";
            echo "</div>";
        }
        
        echo "<h3>Complex Query with Joins:</h3>";
        $complexQuery = "
            SELECT 
                pa.*,
                COALESCE(participant_counts.participants_count, 0) as participants_count,
                COALESCE(certificate_counts.certificates_issued, 0) as certificates_issued
            FROM program_awards pa
            LEFT JOIN (
                SELECT 
                    award_id,
                    COUNT(DISTINCT participant_id) as participants_count
                FROM participant_awards 
                WHERE is_active = 1 AND is_deleted = 0
                GROUP BY award_id
            ) participant_counts ON participant_counts.award_id = pa.id
            LEFT JOIN (
                SELECT 
                    award_id,
                    COUNT(DISTINCT participant_id) as certificates_issued
                FROM participant_certificates 
                WHERE is_active = 1 AND is_deleted = 0
                GROUP BY award_id
            ) certificate_counts ON certificate_counts.award_id = pa.id
            WHERE pa.program_id = ? 
                AND pa.is_active = 1 
                AND pa.is_deleted = 0
            ORDER BY pa.order_number ASC
        ";
        
        $complexResults = $db->query($complexQuery, [$programId])->getResult();
        echo "<p>Complex query found: " . count($complexResults) . " awards</p>";
        
        foreach ($complexResults as $award) {
            echo "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>";
            echo "<strong>Award ID:</strong> " . $award->id . "<br>";
            echo "<strong>Title:</strong> " . $award->title . "<br>";
            echo "<strong>Participants:</strong> " . $award->participants_count . "<br>";
            echo "<strong>Certificates:</strong> " . $award->certificates_issued . "<br>";
            echo "</div>";
        }
        
        echo "<h3>Test getData() Method Response:</h3>";
        try {
            $testResult = $this->testGetData();
            echo "<p>getData() test completed - check console for JSON response</p>";
        } catch (\Exception $e) {
            echo "<p style='color:red;'>Error in getData(): " . $e->getMessage() . "</p>";
        }
    }
    
    /**
     * Add payment filter to a Model query
     */
    private function addPaymentFilter($query, $paymentFilter, $programId)
    {
        log_message('info', "Adding payment filter: $paymentFilter for program: $programId");
        
        switch ($paymentFilter) {
            case 'any_payment':
                // Get participants with any successful payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'registration':
                // Show participants with successful registration fee payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'registration')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_1':
                // Show participants with successful program fee 1 payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_1')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_2':
                // Show participants with successful program fee 2 payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_2')
                                   ->where('pay.status', 2);
                });
                break;
                
            default:
                log_message('warning', "Unknown payment filter type: '$paymentFilter', defaulting to ANY PAYMENT filter");
                // Default to any_payment if invalid filter provided
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
        }
        
        return $query;
    }
    
    /**
     * Add payment filter to a Database Query Builder
     */
    private function addPaymentFilterToBuilder($builder, $paymentFilter, $programId)
    {
        log_message('info', "Adding payment filter to builder: $paymentFilter for program: $programId");
        
        switch ($paymentFilter) {
            case 'any_payment':
                // Get participants with any successful payment
                $builder->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'registration':
                // Show participants with successful registration fee payment
                $builder->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'registration')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_1':
                // Show participants with successful program fee 1 payment
                $builder->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_1')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_2':
                // Show participants with successful program fee 2 payment
                $builder->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_2')
                                   ->where('pay.status', 2);
                });
                break;
                
            default:
                log_message('warning', "Unknown payment filter type: '$paymentFilter', defaulting to ANY PAYMENT filter");
                // Default to any_payment if invalid filter provided
                $builder->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
        }
        
        return $builder;
    }
    
    /**
     * Apply payment filter directly to query, optimized to only get participants with specific payment type
     */
    private function applyPaymentFilterToQuery($query, $paymentFilter, $programId)
    {
        log_message('info', "Applying payment filter: '$paymentFilter' for program: $programId");
        
        switch ($paymentFilter) {
            case 'any_payment':
                log_message('info', "Applying ANY PAYMENT filter");
                // Get participants with any successful payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'registration':
                log_message('info', "Applying REGISTRATION filter");
                // Show participants with successful registration fee payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'registration')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_1':
                log_message('info', "Applying PROGRAM FEE 1 filter");
                // Show participants with successful program fee 1 payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_1')
                                   ->where('pay.status', 2);
                });
                break;
                
            case 'program_fee_2':
                log_message('info', "Applying PROGRAM FEE 2 filter");
                // Show participants with successful program fee 2 payment
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pp.category', 'program_fee_2')
                                   ->where('pay.status', 2);
                });
                break;
                
            default:
                log_message('warning', "Unknown payment filter type: '$paymentFilter', defaulting to ANY PAYMENT filter");
                // Default to any_payment if invalid filter provided
                $query->whereIn('participants.id', function($subquery) use ($programId) {
                    return $subquery->select('pay.participant_id')
                                   ->distinct()
                                   ->from('payments pay')
                                   ->join('program_payments pp', 'pay.program_payment_id = pp.id')
                                   ->where('pp.program_id', $programId)
                                   ->where('pay.status', 2);
                });
        }
        
        return $query;
    }
}
