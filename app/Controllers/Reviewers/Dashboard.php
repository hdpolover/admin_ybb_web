<?php

namespace App\Controllers\Reviewers;

use App\Controllers\BaseController;
use App\Models\ReviewerModel;
use App\Models\AbstractFeedbackModel;
use App\Models\AbstractReviewerSubthemeModel;

class Dashboard extends BaseController
{
    protected $reviewerModel;
    protected $abstractFeedbackModel;
    protected $reviewerSubthemeModel;

    public function __construct()
    {
        $this->reviewerModel = new ReviewerModel();
        $this->abstractFeedbackModel = new AbstractFeedbackModel();
        $this->reviewerSubthemeModel = new AbstractReviewerSubthemeModel();
    }    public function index()
    {
        $reviewerId = session()->get('reviewerId');
        
        // Debug session information
        log_message('info', '=== Dashboard Session Debug ===');
        log_message('info', 'Reviewer ID from session: ' . ($reviewerId ?: 'NULL'));
        log_message('info', 'All session data: ' . json_encode(session()->get()));
        
        // Validation - only check for reviewer ID since program is tied to reviewer
        if (!$reviewerId) {
            log_message('error', 'No reviewer ID in session');
            return redirect()->to('/reviewers/login')->with('error', 'Please log in to access the dashboard.');
        }
          // Get basic reviewer stats
        $stats = $this->abstractFeedbackModel->getReviewerStats($reviewerId);
        
        // TEMPORARY DEBUG: Try simplified method
        $debugAbstracts = $this->abstractFeedbackModel->getAbstractsForReviewerSimple($reviewerId);
        log_message('info', 'DEBUG: Simple method found ' . count($debugAbstracts) . ' abstracts');
        
        // Get enhanced stats including subtheme breakdown
        $enhancedStats = $this->getEnhancedReviewerStats($reviewerId);
          // Get recent reviews with more details
        // TEMPORARY: Use fixed method
        $recentReviews = $this->abstractFeedbackModel->getFeedbacksByReviewerFixed($reviewerId, []);
        
        // Sort by feedback date (most recent first) and limit to 5
        usort($recentReviews, function($a, $b) {
            $dateA = $a->feedback_updated_at ?? $a->feedback_created_at ?? $a->submission_date;
            $dateB = $b->feedback_updated_at ?? $b->feedback_created_at ?? $b->submission_date;
            return strtotime($dateB) - strtotime($dateA);
        });
        $recentReviews = array_slice($recentReviews, 0, 5);        // Get pending reviews count
        // TEMPORARY: Use fixed method
        $pendingReviews = $this->abstractFeedbackModel->getFeedbacksByReviewerFixed($reviewerId, ['status' => 'pending']);
          // Get reviewer's assigned subthemes with statistics
        $subthemeStats = $this->getSubthemeStatistics($reviewerId);
          // Debug: Add some logging for troubleshooting
        log_message('info', 'Dashboard - Reviewer ID: ' . $reviewerId);
        log_message('info', 'Dashboard - Basic stats: ' . json_encode($stats));
        log_message('info', 'Dashboard - Enhanced stats: ' . json_encode($enhancedStats));
        log_message('info', 'Dashboard - Subtheme stats count: ' . count($subthemeStats));
        log_message('info', 'Dashboard - Recent reviews count: ' . count($recentReviews));
        
        $data = [
            'pageTitle' => 'Reviewer Dashboard',
            'title' => 'Reviewer Dashboard',
            'pagetitle' => 'Reviewer Panel',
            'stats' => $stats,
            'enhancedStats' => $enhancedStats,
            'recentReviews' => $recentReviews,
            'pendingCount' => count($pendingReviews),
            'subthemeStats' => $subthemeStats,
            'currentUser' => (object)[
                'id' => session()->get('reviewerId'),
                'name' => session()->get('reviewerName'),
                'email' => session()->get('reviewerEmail')
            ]
        ];

        return view('reviewers/dashboard/index', $data);
    }
    public function ajaxAbstractStats()
    {
        $reviewerId = session()->get('reviewerId');
        $stats = $this->abstractFeedbackModel->getReviewerStats($reviewerId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function ajaxReviewStats()
    {
        $reviewerId = session()->get('reviewerId');

        // Get monthly review completion data for chart
        $builder = $this->abstractFeedbackModel->builder();
        $monthlyStats = $builder
            ->select("DATE_FORMAT(updated_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('abstract_reviewer_id', $reviewerId)
            ->where('feedback IS NOT NULL')
            ->where('feedback !=', '')
            ->where('updated_at >=', date('Y-m-d', strtotime('-12 months')))
            ->groupBy("DATE_FORMAT(updated_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON([
            'success' => true,
            'data' => $monthlyStats
        ]);
    }

    /**
     * Get reviewer assigned subthemes
     */
    public function getReviewerSubthemes()
    {
        $reviewerId = session()->get('reviewerId');
        $subthemes = $this->reviewerSubthemeModel->getReviewerSubthemes($reviewerId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $subthemes
        ]);
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
            $data['pagetitle'] = 'Reviewer Panel';
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
     * Get enhanced reviewer statistics with additional metrics
     */
    private function getEnhancedReviewerStats($reviewerId)
    {
        // Get all abstracts for this reviewer
        $allAbstracts = $this->abstractFeedbackModel->getFeedbacksByReviewer($reviewerId, []);
        $completedAbstracts = array_filter($allAbstracts, function ($abstract) {
            return !empty($abstract->feedback);
        });
        
        // Calculate additional metrics
        $totalAssigned = count($allAbstracts);
        $totalCompleted = count($completedAbstracts);
        
        // Calculate average review time (if feedback dates are available)
        $reviewTimes = [];
        foreach ($completedAbstracts as $abstract) {
            if (!empty($abstract->feedback_created_at) && !empty($abstract->submission_date)) {
                $submitTime = strtotime($abstract->submission_date);
                $reviewTime = strtotime($abstract->feedback_created_at);
                $reviewTimes[] = ($reviewTime - $submitTime) / (24 * 3600); // days
            }
        }
        
        $avgReviewTime = !empty($reviewTimes) ? round(array_sum($reviewTimes) / count($reviewTimes), 1) : 0;
          // Get recent activity (reviews in last 7 days)
        $recentActivity = 0;
        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        foreach ($completedAbstracts as $abstract) {
            // Check if feedback was created or updated in the last 7 days
            $feedbackDate = null;
            if (!empty($abstract->feedback_updated_at)) {
                $feedbackDate = $abstract->feedback_updated_at;
            } elseif (!empty($abstract->feedback_created_at)) {
                $feedbackDate = $abstract->feedback_created_at;
            }
            
            if ($feedbackDate && $feedbackDate >= $weekAgo) {
                $recentActivity++;
            }
        }
        
        return [
            'total_abstracts_in_subthemes' => $totalAssigned,
            'reviews_this_week' => $recentActivity,
            'avg_review_time_days' => $avgReviewTime,
            'productivity_score' => $totalAssigned > 0 ? round(($totalCompleted * 100) / $totalAssigned, 1) : 0
        ];
    }
    
    /**
     * Get statistics broken down by subtheme
     */
    private function getSubthemeStatistics($reviewerId)
    {
        // Get reviewer's assigned subthemes
        $assignedSubthemes = $this->reviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);
        
        $subthemeStats = [];
        
        foreach ($assignedSubthemes as $subtheme) {            // Get abstracts for this specific subtheme
            // TEMPORARY: Use fixed method
            $subthemeAbstracts = $this->abstractFeedbackModel->getFeedbacksByReviewerFixed($reviewerId, [
                'subtheme_id' => $subtheme->program_subtheme_id
            ]);
            
            $totalInSubtheme = count($subthemeAbstracts);
            $completedInSubtheme = count(array_filter($subthemeAbstracts, function ($abstract) {
                return !empty($abstract->feedback);
            }));
            $pendingInSubtheme = $totalInSubtheme - $completedInSubtheme;
            
            $subthemeStats[] = [
                'id' => $subtheme->program_subtheme_id,
                'name' => $subtheme->subtheme_name,
                'description' => $subtheme->subtheme_description ?? '',
                'total_abstracts' => $totalInSubtheme,
                'completed_reviews' => $completedInSubtheme,
                'pending_reviews' => $pendingInSubtheme,
                'completion_percentage' => $totalInSubtheme > 0 ? round(($completedInSubtheme / $totalInSubtheme) * 100, 1) : 0
            ];
        }
        
        return $subthemeStats;
    }

    /**
     * Debug method to investigate reviewer data issues
     */
    public function debugReviewerData()
    {
        $reviewerId = session()->get('reviewerId');
        $reviewerProgramId = session()->get('reviewerProgramId');
        
        echo "<h3>Debug Information for Reviewer ID: {$reviewerId}</h3>";
        
        // 1. Check reviewer exists and is active
        $reviewer = $this->reviewerModel->find($reviewerId);
        echo "<h4>1. Reviewer Details:</h4>";
        if ($reviewer) {
            echo "<pre>" . print_r($reviewer, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>Reviewer not found!</p>";
        }
        
        // 2. Check reviewer's subtheme assignments
        echo "<h4>2. Reviewer's Subtheme Assignments:</h4>";
        $assignedSubthemes = $this->reviewerSubthemeModel->getSubthemesByReviewerId($reviewerId);
        if (!empty($assignedSubthemes)) {
            echo "<table border='1'>";
            echo "<tr><th>Subtheme ID</th><th>Subtheme Name</th><th>Is Active</th><th>Is Deleted</th></tr>";
            foreach ($assignedSubthemes as $subtheme) {
                echo "<tr><td>{$subtheme->program_subtheme_id}</td><td>{$subtheme->subtheme_name}</td><td>Active</td><td>Not Deleted</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>No subthemes assigned to this reviewer!</p>";
        }
        
        // 3. Check abstracts in the program
        echo "<h4>3. Abstracts in Program {$reviewerProgramId}:</h4>";
        $builder = $this->abstractFeedbackModel->db->table('abstracts a');
        $abstracts = $builder->select('a.id, a.status, av.title, p.full_name as participant_name')
                           ->join('abstract_versions av', 'av.id = a.active_version_id')
                           ->join('participants p', 'p.id = a.primary_participant_id')
                           ->where('a.program_id', $reviewerProgramId)
                           ->where('a.is_deleted', 0)
                           ->get()->getResult();
        
        if (!empty($abstracts)) {
            echo "<table border='1'>";
            echo "<tr><th>Abstract ID</th><th>Title</th><th>Participant</th><th>Status</th></tr>";
            foreach ($abstracts as $abstract) {
                echo "<tr><td>{$abstract->id}</td><td>{$abstract->title}</td><td>{$abstract->participant_name}</td><td>{$abstract->status}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>No abstracts found in this program!</p>";
        }
        
        // 4. Check participant subtheme assignments
        echo "<h4>4. Participant Subtheme Assignments:</h4>";
        $builder = $this->abstractFeedbackModel->db->table('participant_subthemes ps');
        $participantSubthemes = $builder->select('ps.participant_id, ps.program_subtheme_id, psub.name as subtheme_name, p.full_name as participant_name, ps.is_active, ps.is_deleted')
                                      ->join('program_subthemes psub', 'psub.id = ps.program_subtheme_id')
                                      ->join('participants p', 'p.id = ps.participant_id')
                                      ->where('psub.program_id', $reviewerProgramId)
                                      ->get()->getResult();
        
        if (!empty($participantSubthemes)) {
            echo "<table border='1'>";
            echo "<tr><th>Participant</th><th>Subtheme</th><th>Is Active</th><th>Is Deleted</th></tr>";
            foreach ($participantSubthemes as $ps) {
                $active = $ps->is_active ? 'Yes' : 'No';
                $deleted = $ps->is_deleted ? 'Yes' : 'No';
                echo "<tr><td>{$ps->participant_name}</td><td>{$ps->subtheme_name}</td><td>{$active}</td><td>{$deleted}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>No participant subtheme assignments found!</p>";
        }
        
        // 5. Check existing feedback
        echo "<h4>5. Existing Feedback by this Reviewer:</h4>";
        $builder = $this->abstractFeedbackModel->db->table('abstract_feedbacks af');
        $feedbacks = $builder->select('af.id, af.abstract_version_id, av.title, af.feedback, af.created_at')
                           ->join('abstract_versions av', 'av.id = af.abstract_version_id')
                           ->where('af.abstract_reviewer_id', $reviewerId)
                           ->get()->getResult();
        
        if (!empty($feedbacks)) {
            echo "<table border='1'>";
            echo "<tr><th>Feedback ID</th><th>Abstract Title</th><th>Feedback</th><th>Created</th></tr>";
            foreach ($feedbacks as $feedback) {
                $feedbackText = substr($feedback->feedback, 0, 100) . '...';
                echo "<tr><td>{$feedback->id}</td><td>{$feedback->title}</td><td>{$feedbackText}</td><td>{$feedback->created_at}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No feedback found by this reviewer.</p>";
        }
        
        // 6. Test the actual query used by getFeedbacksByReviewer
        echo "<h4>6. Testing getFeedbacksByReviewer Query:</h4>";
        try {
            $results = $this->abstractFeedbackModel->getFeedbacksByReviewer($reviewerId, []);
            echo "<p>Found " . count($results) . " abstracts using getFeedbacksByReviewer method.</p>";
            if (!empty($results)) {
                echo "<table border='1'>";
                echo "<tr><th>Abstract ID</th><th>Title</th><th>Participant</th><th>Subtheme</th><th>Has Feedback</th></tr>";
                foreach ($results as $result) {
                    $hasFeedback = !empty($result->feedback) ? 'Yes' : 'No';
                    echo "<tr><td>{$result->abstract_id}</td><td>{$result->abstract_title}</td><td>{$result->participant_name}</td><td>{$result->subtheme_name}</td><td>{$hasFeedback}</td></tr>";
                }
                echo "</table>";
            }
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Error in getFeedbacksByReviewer: " . $e->getMessage() . "</p>";
        }
        
        // Direct database check for debugging
        $db = \Config\Database::connect();
        
        // Check if reviewer exists in database
        $reviewerExists = $db->table('abstract_reviewers')
                            ->where('id', $reviewerId)
                            ->where('is_active', 1)
                            ->where('is_deleted', 0)
                            ->countAllResults();
        
        log_message('info', 'Reviewer exists in DB: ' . ($reviewerExists ? 'YES' : 'NO'));
        
        // Check reviewer's subtheme assignments
        $subthemeAssignments = $db->table('abstract_reviewer_subthemes ars')
                                 ->select('ars.program_subtheme_id, ps.name')
                                 ->join('program_subthemes ps', 'ps.id = ars.program_subtheme_id')
                                 ->where('ars.abstract_reviewer_id', $reviewerId)
                                 ->where('ars.is_active', 1)
                                 ->where('ars.is_deleted', 0)
                                 ->get()->getResult();
        
        log_message('info', 'Direct DB subtheme check: ' . count($subthemeAssignments) . ' assignments found');
        
        // Check if there are any abstracts in the program
        $abstractsInProgram = $db->table('abstracts')
                                ->where('program_id', $reviewerProgramId)
                                ->where('status', 'submitted')
                                ->where('is_deleted', 0)
                                ->countAllResults();
        
        log_message('info', 'Total submitted abstracts in program: ' . $abstractsInProgram);
        
        // TEMPORARY: Check for reviewer with specific email for debugging
        if (session()->get('reviewerEmail') === 'suhendra@gmail.com') {
            log_message('info', '=== SPECIFIC DEBUG for suhendra@gmail.com ===');
            
            // Get reviewer details from email
            $reviewerByEmail = $db->table('abstract_reviewers ar')
                                 ->select('ar.*, u.email')
                                 ->join('users u', 'u.id = ar.user_id')
                                 ->where('u.email', 'suhendra@gmail.com')
                                 ->get()->getRow();
            
            if ($reviewerByEmail) {
                log_message('info', 'Found reviewer by email: ' . json_encode($reviewerByEmail));
                
                // Check if this matches session reviewer ID
                if ($reviewerByEmail->id != $reviewerId) {
                    log_message('error', 'SESSION MISMATCH: Session has reviewer ID ' . $reviewerId . ' but email belongs to reviewer ID ' . $reviewerByEmail->id);
                }
                
                // Force correct reviewer ID for debugging
                $reviewerId = $reviewerByEmail->id;
                $reviewerProgramId = $reviewerByEmail->program_id;
                log_message('info', 'Using corrected reviewer ID: ' . $reviewerId . ', program: ' . $reviewerProgramId);
            } else {
                log_message('error', 'No reviewer found with email suhendra@gmail.com');
            }
        }
        
        exit; // Stop execution to show debug info
    }
    
    /**
     * Simple test to check data retrieval
     */
    public function testDataRetrieval()
    {
        $reviewerId = session()->get('reviewerId');
        
        echo "<h2>Data Retrieval Test</h2>";
        echo "<p>Reviewer ID from session: " . ($reviewerId ?: 'NULL') . "</p>";
        
        if (!$reviewerId) {
            echo "<p style='color: red;'>No reviewer ID in session!</p>";
            return;
        }
        
        try {
            // Test original method
            echo "<h3>Original Method:</h3>";
            $originalResults = $this->abstractFeedbackModel->getFeedbacksByReviewer($reviewerId, []);
            echo "<p>Found " . count($originalResults) . " abstracts</p>";
            
            // Test fixed method
            echo "<h3>Fixed Method:</h3>";
            $fixedResults = $this->abstractFeedbackModel->getFeedbacksByReviewerFixed($reviewerId, []);
            echo "<p>Found " . count($fixedResults) . " abstracts</p>";
            
            // Test simple method
            echo "<h3>Simple Method:</h3>";
            $simpleResults = $this->abstractFeedbackModel->getAbstractsForReviewerSimple($reviewerId);
            echo "<p>Found " . count($simpleResults) . " abstracts</p>";
            
            if (!empty($fixedResults)) {
                echo "<h3>Sample Data:</h3>";
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Title</th><th>Participant</th><th>Subtheme</th><th>Has Feedback</th></tr>";
                foreach (array_slice($fixedResults, 0, 5) as $abstract) {
                    $hasFeedback = !empty($abstract->feedback) ? 'YES' : 'NO';
                    echo "<tr><td>{$abstract->abstract_id}</td><td>{$abstract->abstract_title}</td><td>{$abstract->participant_name}</td><td>{$abstract->subtheme_name}</td><td>{$hasFeedback}</td></tr>";
                }
                echo "</table>";
            }
            
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
        
        exit;
    }
}
