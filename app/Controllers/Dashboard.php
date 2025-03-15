<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    protected $dashboardModel;
    protected $programModel;

    public function __construct()
    {
        $this->dashboardModel = new \App\Models\DashboardModel();
        $this->programModel = new \App\Models\ProgramModel();
    }

    public function index()
    {
        $programId = session('current_program');
        $program = $this->programModel->find($programId);
        
        // Get all statistics data
        $data = [
            'program' => $program,
            'summary' => $this->dashboardModel->getProgramSummary($programId),
            'registrationStats' => $this->dashboardModel->getParticipantRegistrationStats($programId, 'day', 30),
            'genderStats' => $this->dashboardModel->getGenderDistribution($programId),
            'nationalityStats' => $this->dashboardModel->getNationalityDistribution($programId, 10),
            'ageStats' => $this->dashboardModel->getAgeDistribution($programId),
            'ambassadorStats' => $this->dashboardModel->getAmbassadorReferrals($programId, 5),
        ];
        
        // Prepare chart data in JSON format for JavaScript
        $data['registrationChartData'] = json_encode([
            'labels' => array_map(function($item) { return $item->label; }, $data['registrationStats']),
            'values' => array_map(function($item) { return (int)$item->total; }, $data['registrationStats']),
        ]);
        
        // Format gender data - handle empty values and nulls
        $genderLabels = [];
        $genderValues = [];
        
        foreach ($data['genderStats'] as $item) {
            $genderLabels[] = empty($item->gender) ? 'Not Specified' : $item->gender;
            $genderValues[] = (int)$item->total;
        }
        
        // If no gender data, provide default
        if (empty($genderLabels)) {
            $genderLabels = ['No Data'];
            $genderValues = [0];
        }
        
        $data['genderChartData'] = json_encode([
            'labels' => $genderLabels,
            'values' => $genderValues,
        ]);
        
        // Format nationality data - handle empty values and nulls
        $nationalityLabels = [];
        $nationalityValues = [];
        
        foreach ($data['nationalityStats'] as $item) {
            $nationalityLabels[] = empty($item->nationality) ? 'Not Specified' : $item->nationality;
            $nationalityValues[] = (int)$item->total;
        }
        
        // If no nationality data, provide default
        if (empty($nationalityLabels)) {
            $nationalityLabels = ['No Data'];
            $nationalityValues = [0];
        }
        
        $data['nationalityChartData'] = json_encode([
            'labels' => $nationalityLabels,
            'values' => $nationalityValues,
        ]);
        
        // Format age data
        $ageLabels = [];
        $ageValues = [];
        
        foreach ($data['ageStats'] as $item) {
            $ageLabels[] = $item->age_group;
            $ageValues[] = (int)$item->total;
        }
        
        // If no age data, provide default
        if (empty($ageLabels)) {
            $ageLabels = ['No Data'];
            $ageValues = [0];
        }
        
        $data['ageChartData'] = json_encode([
            'labels' => $ageLabels,
            'values' => $ageValues,
        ]);

        return view('dashboard/index', $data);
    }
    
    public function ajaxRegistrationStats()
    {
        $programId = session('current_program');
        $period = $this->request->getGet('period') ?? 'day';
        $limit = (int)($this->request->getGet('limit') ?? 30);
        
        $stats = $this->dashboardModel->getParticipantRegistrationStats($programId, $period, $limit);
        
        return $this->response->setJSON([
            'labels' => array_map(function($item) { return $item->label; }, $stats),
            'values' => array_map(function($item) { return (int)$item->total; }, $stats),
        ]);
    }
}