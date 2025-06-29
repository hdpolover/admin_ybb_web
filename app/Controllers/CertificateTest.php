<?php

namespace App\Controllers;

class CertificateTest extends BaseController
{
    public function testData()
    {
        // Force set program ID for testing
        session()->set('current_program', 8);
        
        // Direct database test
        $db = \Config\Database::connect();
        
        $query = "
            SELECT program_awards.*, 
                   COUNT(DISTINCT participant_awards.participant_id) as participants_count,
                   COUNT(DISTINCT participant_certificates.participant_id) as certificates_issued
            FROM program_awards
            LEFT JOIN participant_awards ON participant_awards.award_id = program_awards.id 
                AND participant_awards.is_active = 1 
                AND participant_awards.is_deleted = 0
            LEFT JOIN participant_certificates ON participant_certificates.award_id = program_awards.id 
                AND participant_certificates.is_active = 1 
                AND participant_certificates.is_deleted = 0
            WHERE program_awards.program_id = 8
                AND program_awards.is_active = 1 
                AND program_awards.is_deleted = 0
            GROUP BY program_awards.id
            ORDER BY program_awards.order_number ASC
        ";
        
        $awards = $db->query($query)->getResult();
        
        $data = [];
        foreach ($awards as $award) {
            $progressText = $award->participants_count > 0 
                ? "{$award->certificates_issued} / {$award->participants_count}" 
                : "0 / 0";

            $progressPercent = $award->participants_count > 0 
                ? round(($award->certificates_issued / $award->participants_count) * 100, 1) 
                : 0;

            $actions = '<div class="btn-group" role="group">
                <button type="button" class="btn btn-primary btn-sm" onclick="alert(\'View Award: ' . $award->title . '\')" title="View Details">
                    👁️ View
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="alert(\'Manage Participants for: ' . $award->title . '\')" title="Manage Participants">
                    👥 Assign
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="alert(\'Issue Certificates for: ' . $award->title . '\')" title="Issue Certificates">
                    🏆 Issue
                </button>
            </div>';

            $data[] = [
                'id' => $award->id,
                'title' => esc($award->title),
                'award_type' => ucfirst(str_replace('_', ' ', $award->award_type)),
                'description' => esc($award->description),
                'participants_count' => $award->participants_count,
                'certificates_issued' => $award->certificates_issued,
                'progress' => '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: ' . $progressPercent . '%;">
                        ' . $progressText . '
                    </div>
                </div>',
                'certificate_status' => '<span class="badge bg-warning">No Template</span>',
                'actions' => $actions
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
    
    public function testPage()
    {
        return view('test_certificate_page');
    }
}
