<?php

namespace App\Controllers;

class Dashboard extends AdminBaseController
{
    protected $dashboardModel;
    protected $programModel;
    protected $programCategoryModel;
    protected $adminModel;

    public function __construct()
    {
        $this->dashboardModel = new \App\Models\DashboardModel();
        $this->programModel = new \App\Models\ProgramModel();
        $this->programCategoryModel = new \App\Models\ProgramCategoryModel();
        $this->adminModel = new \App\Models\AdminModel();
    }    public function index()
    {
        $programId = session('current_program');
        
        // If no program is selected, redirect to welcome page
        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }
        
        $program = $this->programModel->find($programId);
        
        // If program not found, redirect to welcome (allow both active and inactive programs)
        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }
        
        // Prepare topbar data for navigation (now handled by AdminBaseController)
        $topbarData = session('topbar_data') ?? [];
        
        // Ensure the cookie that indicates a program is selected persists
        // This ensures proper JS detection across all pages
        setcookie('has_program_selected', 'true', time() + 86400, '/');
        
        // Get all statistics data
        $data = [
            'program' => $program,
            'summary' => $this->dashboardModel->getProgramSummary($programId),
            'registrationStats' => $this->dashboardModel->getParticipantRegistrationStats($programId, 'day', 30),
            'genderStats' => $this->dashboardModel->getGenderDistribution($programId),
            'nationalityStats' => $this->dashboardModel->getNationalityDistribution($programId, 10),
            'ageStats' => $this->dashboardModel->getAgeDistribution($programId),
            'ambassadorStats' => $this->dashboardModel->getAmbassadorReferrals($programId, 5),
            'topbarData' => $topbarData,
        ];
        
        // Prepare chart data in JSON format for JavaScript
        $data['registrationChartData'] = json_encode([
            'labels' => array_map(function($item) { return $item->label; }, $data['registrationStats']),
            'values' => array_map(function($item) { return (int)$item->total; }, $data['registrationStats']),
        ]);
        
        // Format gender data — normalise labels, exclude 'Not Specified' from chart
        $genderLabels = [];
        $genderValues = [];
        $genderMap    = ['male' => 'Male', 'female' => 'Female'];

        foreach ($data['genderStats'] as $item) {
            $raw   = empty($item->gender) ? '' : strtolower($item->gender);
            $label = $genderMap[$raw] ?? 'Not Specified';
            if ($label === 'Not Specified') {
                continue; // keep noise out of the chart; modal table stays full
            }
            $genderLabels[] = $label;
            $genderValues[] = (int)$item->total;
        }

        if (empty($genderLabels)) {
            $genderLabels = ['No Data'];
            $genderValues = [0];
        }

        $data['genderChartData'] = json_encode([
            'labels' => $genderLabels,
            'values' => $genderValues,
        ]);

        // Format nationality data — exclude 'Not Specified' from chart
        $nationalityLabels = [];
        $nationalityValues = [];

        foreach ($data['nationalityStats'] as $item) {
            $label = empty($item->nationality) ? 'Not Specified' : $item->nationality;
            if ($label === 'Not Specified') {
                continue;
            }
            $nationalityLabels[] = $label;
            $nationalityValues[] = (int)$item->total;
        }

        if (empty($nationalityLabels)) {
            $nationalityLabels = ['No Data'];
            $nationalityValues = [0];
        }

        $data['nationalityChartData'] = json_encode([
            'labels' => $nationalityLabels,
            'values' => $nationalityValues,
        ]);

        // Format age data — exclude 'Unknown' from chart; keep it in modal table
        $ageLabels = [];
        $ageValues = [];

        foreach ($data['ageStats'] as $item) {
            if ($item->age_group === 'Unknown') {
                continue;
            }
            $ageLabels[] = $item->age_group;
            $ageValues[] = (int)$item->total;
        }

        if (empty($ageLabels)) {
            $ageLabels = ['No Data'];
            $ageValues = [0];
        }

        $data['ageChartData'] = json_encode([
            'labels' => $ageLabels,
            'values' => $ageValues,
        ]);

        // Format knowledge source data
        $data['knowledgeSourceStats'] = $this->dashboardModel->getKnowledgeSourceDistribution($programId, 10);

        $ksLabels = [];
        $ksValues = [];

        // Exclude "Not Specified" from the chart — at ~90% it drowns out the actual
        // channel mix. It stays in the modal table / matrix for the full picture.
        foreach ($data['knowledgeSourceStats'] as $item) {
            $source = empty($item->source) ? 'Not Specified' : $item->source;
            if ($source === 'Not Specified') {
                continue;
            }
            $ksLabels[] = $source;
            $ksValues[] = (int)$item->total;
        }

        if (empty($ksLabels)) {
            $ksLabels = ['No Data'];
            $ksValues = [0];
        }

        $data['knowledgeSourceChartData'] = json_encode([
            'labels' => $ksLabels,
            'values' => $ksValues,
        ]);

        return view('dashboard/index', $data);
    }
    
    /**
     * Prepare topbar data for navigation
     */
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
    
    public function ajaxGenderStats()
    {
        $programId = session('current_program');
        $stats = $this->dashboardModel->getGenderDistribution($programId);
        
        // Format gender data - handle empty values and nulls
        $genderLabels = [];
        $genderValues = [];
        
        foreach ($stats as $item) {
            $genderLabels[] = empty($item->gender) ? 'Not Specified' : $item->gender;
            $genderValues[] = (int)$item->total;
        }
        
        // If no gender data, provide default
        if (empty($genderLabels)) {
            $genderLabels = ['No Data'];
            $genderValues = [0];
        }
        
        return $this->response->setJSON([
            'labels' => $genderLabels,
            'values' => $genderValues,
        ]);
    }
    
    public function ajaxAgeStats()
    {
        $programId = session('current_program');
        $stats = $this->dashboardModel->getAgeDistribution($programId);
        
        // Format age data
        $ageLabels = [];
        $ageValues = [];
        
        foreach ($stats as $item) {
            $ageLabels[] = $item->age_group;
            $ageValues[] = (int)$item->total;
        }
        
        // If no age data, provide default
        if (empty($ageLabels)) {
            $ageLabels = ['No Data'];
            $ageValues = [0];
        }
        
        return $this->response->setJSON([
            'labels' => $ageLabels,
            'values' => $ageValues,
        ]);
    }
    public function ajaxNationalityStats()
    {
        $programId = session('current_program');
        $fullData = $this->request->getGet('fullData') === '1';
        $limit = $fullData ? 1000 : (int)($this->request->getGet('limit') ?? 10);
        $stats = $this->dashboardModel->getNationalityDistribution($programId, $limit);
        
        // Format nationality data - handle empty values and nulls
        $nationalityLabels = [];
        $nationalityValues = [];
        
        foreach ($stats as $item) {
            $nationalityLabels[] = empty($item->nationality) ? 'Not Specified' : $item->nationality;
            $nationalityValues[] = (int)$item->total;
        }
        
        // If no nationality data, provide default
        if (empty($nationalityLabels)) {
            $nationalityLabels = ['No Data'];
            $nationalityValues = [0];
        }
        
        return $this->response->setJSON([
            'labels' => $nationalityLabels,
            'values' => $nationalityValues,
        ]);
    }

    public function ajaxKnowledgeSourceStats()
    {
        $programId = session('current_program');
        $fullData = $this->request->getGet('fullData') === '1';
        $limit = $fullData ? null : (int)($this->request->getGet('limit') ?? 10);
        $stats = $this->dashboardModel->getKnowledgeSourceDistribution($programId, $limit);

        // Format knowledge source data - handle empty values and nulls
        $ksLabels = [];
        $ksValues = [];

        foreach ($stats as $item) {
            $ksLabels[] = empty($item->source) ? 'Not Specified' : $item->source;
            $ksValues[] = (int)$item->total;
        }

        // If no data, provide default
        if (empty($ksLabels)) {
            $ksLabels = ['No Data'];
            $ksValues = [0];
        }

        return $this->response->setJSON([
            'labels' => $ksLabels,
            'values' => $ksValues,
        ]);
    }

    // -------------------------------------------------------------------------
    // Nationality analytics
    // -------------------------------------------------------------------------

    public function nationalityAnalytics()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        $topbarData = session('topbar_data') ?? [];

        // Full distributions + cross-tabs
        $nationalityStats = $this->dashboardModel->getNationalityDistribution($programId, 1000);
        $bySource         = $this->dashboardModel->getNationalityBySource($programId);
        $byGender         = $this->dashboardModel->getNationalityByGender($programId);

        // Chart labels/values — exclude 'Not Specified'
        $chartLabels = [];
        $chartValues = [];

        foreach ($nationalityStats as $item) {
            $label = empty($item->nationality) ? 'Not Specified' : $item->nationality;
            if ($label === 'Not Specified') {
                continue;
            }
            $chartLabels[] = $label;
            $chartValues[] = (int)$item->total;
        }

        if (empty($chartLabels)) {
            $chartLabels = ['No Data'];
            $chartValues = [0];
        }

        $nationalityChartData = json_encode([
            'labels' => $chartLabels,
            'values' => $chartValues,
        ]);

        // Build Source matrix
        [$sourceCols, $sourceMatrixRows, $sourceCountries] = $this->buildMatrix(
            $bySource, 'nationality', 'source'
        );

        // Build Gender matrix
        [$genderCols, $genderMatrixRows, $genderCountries] = $this->buildMatrix(
            $byGender, 'nationality', 'gender'
        );

        // Summary stats
        $grandTotal          = 0;
        $notSpecifiedCount   = 0;
        $topCountry          = 'N/A';
        $topCountryCount     = 0;
        $distinctCountries   = 0;

        foreach ($nationalityStats as $item) {
            $label = empty($item->nationality) ? 'Not Specified' : $item->nationality;
            $grandTotal += (int)$item->total;
            if ($label === 'Not Specified') {
                $notSpecifiedCount = (int)$item->total;
                continue;
            }
            $distinctCountries++;
            if ((int)$item->total > $topCountryCount) {
                $topCountryCount = (int)$item->total;
                $topCountry      = $label;
            }
        }

        $topCountryPct     = $grandTotal > 0 && $topCountry !== 'N/A'
            ? round(($topCountryCount / $grandTotal) * 100, 1) : 0;
        $notSpecifiedPct   = $grandTotal > 0
            ? round(($notSpecifiedCount / $grandTotal) * 100, 1) : 0;

        $data = [
            'program'              => $program,
            'summary'              => $this->dashboardModel->getProgramSummary($programId),
            'topbarData'           => $topbarData,
            'nationalityStats'     => $nationalityStats,
            'nationalityChartData' => $nationalityChartData,
            'bySource'             => $bySource,
            'byGender'             => $byGender,
            'sourceCols'           => $sourceCols,
            'sourceMatrixRows'     => $sourceMatrixRows,
            'sourceCountries'      => $sourceCountries,
            'genderCols'           => $genderCols,
            'genderMatrixRows'     => $genderMatrixRows,
            'genderCountries'      => $genderCountries,
            'grandTotal'           => $grandTotal,
            'distinctCountries'    => $distinctCountries,
            'topCountry'           => $topCountry,
            'topCountryPct'        => $topCountryPct,
            'notSpecifiedPct'      => $notSpecifiedPct,
        ];

        return view('dashboard/nationality_analytics', $data);
    }

    // -------------------------------------------------------------------------
    // Gender analytics
    // -------------------------------------------------------------------------

    public function genderAnalytics()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        $topbarData = session('topbar_data') ?? [];

        $genderStats   = $this->dashboardModel->getGenderDistributionNormalized($programId);
        $byNationality = $this->dashboardModel->getGenderByNationality($programId);
        $byAge         = $this->dashboardModel->getGenderByAge($programId);

        // Chart labels/values — exclude 'Not Specified'
        $chartLabels = [];
        $chartValues = [];

        foreach ($genderStats as $item) {
            if ($item->gender === 'Not Specified') {
                continue;
            }
            $chartLabels[] = $item->gender;
            $chartValues[] = (int)$item->total;
        }

        if (empty($chartLabels)) {
            $chartLabels = ['No Data'];
            $chartValues = [0];
        }

        $genderChartData = json_encode([
            'labels' => $chartLabels,
            'values' => $chartValues,
        ]);

        // Build cross-tab matrices
        [$nationalityCols, $nationalityMatrixRows, $nationalityGenders] = $this->buildMatrix(
            $byNationality, 'gender', 'nationality'
        );

        // For Age matrix, use canonical age-band order for column sorting
        $ageBandOrder = ['Under 18', '18-24', '25-34', '35-44', '45-54', '55+', 'Unknown'];
        [$ageCols, $ageMatrixRows, $ageGenders] = $this->buildMatrix(
            $byAge, 'gender', 'age_group', $ageBandOrder
        );

        // Summary stats
        $grandTotal        = 0;
        $malePct           = 0;
        $femalePct         = 0;
        $notSpecifiedPct   = 0;
        $maleCount         = 0;
        $femaleCount       = 0;
        $notSpecifiedCount = 0;

        foreach ($genderStats as $item) {
            $grandTotal += (int)$item->total;
            if ($item->gender === 'Male')          $maleCount         = (int)$item->total;
            elseif ($item->gender === 'Female')    $femaleCount       = (int)$item->total;
            elseif ($item->gender === 'Not Specified') $notSpecifiedCount = (int)$item->total;
        }

        if ($grandTotal > 0) {
            $malePct         = round(($maleCount         / $grandTotal) * 100, 1);
            $femalePct       = round(($femaleCount       / $grandTotal) * 100, 1);
            $notSpecifiedPct = round(($notSpecifiedCount / $grandTotal) * 100, 1);
        }

        $data = [
            'program'              => $program,
            'summary'              => $this->dashboardModel->getProgramSummary($programId),
            'topbarData'           => $topbarData,
            'genderStats'          => $genderStats,
            'genderChartData'      => $genderChartData,
            'byNationality'        => $byNationality,
            'byAge'                => $byAge,
            'nationalityCols'      => $nationalityCols,
            'nationalityMatrixRows'=> $nationalityMatrixRows,
            'ageCols'              => $ageCols,
            'ageMatrixRows'        => $ageMatrixRows,
            'grandTotal'           => $grandTotal,
            'malePct'              => $malePct,
            'femalePct'            => $femalePct,
            'notSpecifiedPct'      => $notSpecifiedPct,
        ];

        return view('dashboard/gender_analytics', $data);
    }

    // -------------------------------------------------------------------------
    // Age analytics
    // -------------------------------------------------------------------------

    public function ageAnalytics()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        $topbarData = session('topbar_data') ?? [];

        // Use the full distribution (no birthdate IS NOT NULL filter) so that
        // grandTotal = program total and Unknown % reflects missing-birthdate reality.
        $ageStats       = $this->dashboardModel->getAgeDistributionFull($programId);
        $byGender       = $this->dashboardModel->getAgeByGender($programId);
        $byNationality  = $this->dashboardModel->getAgeByNationality($programId);

        // Chart labels/values — exclude 'Unknown'
        $chartLabels = [];
        $chartValues = [];

        foreach ($ageStats as $item) {
            if ($item->age_group === 'Unknown') {
                continue;
            }
            $chartLabels[] = $item->age_group;
            $chartValues[] = (int)$item->total;
        }

        if (empty($chartLabels)) {
            $chartLabels = ['No Data'];
            $chartValues = [0];
        }

        $ageChartData = json_encode([
            'labels' => $chartLabels,
            'values' => $chartValues,
        ]);

        // Cross-tab matrices
        [$genderCols, $genderMatrixRows, $genderBands] = $this->buildMatrix(
            $byGender, 'age_group', 'gender'
        );

        [$nationalityCols, $nationalityMatrixRows, $nationalityBands] = $this->buildMatrix(
            $byNationality, 'age_group', 'nationality'
        );

        // Summary stats
        $grandTotal      = 0;
        $unknownCount    = 0;
        $dominantBand    = 'N/A';
        $dominantCount   = 0;

        foreach ($ageStats as $item) {
            $grandTotal += (int)$item->total;
            if ($item->age_group === 'Unknown') {
                $unknownCount = (int)$item->total;
                continue;
            }
            if ((int)$item->total > $dominantCount) {
                $dominantCount = (int)$item->total;
                $dominantBand  = $item->age_group;
            }
        }

        $unknownPct  = $grandTotal > 0 ? round(($unknownCount  / $grandTotal) * 100, 1) : 0;
        $dominantPct = $grandTotal > 0 && $dominantBand !== 'N/A'
            ? round(($dominantCount / $grandTotal) * 100, 1) : 0;

        $data = [
            'program'               => $program,
            'summary'               => $this->dashboardModel->getProgramSummary($programId),
            'topbarData'            => $topbarData,
            'ageStats'              => $ageStats,
            'ageChartData'          => $ageChartData,
            'byGender'              => $byGender,
            'byNationality'         => $byNationality,
            'genderCols'            => $genderCols,
            'genderMatrixRows'      => $genderMatrixRows,
            'nationalityCols'       => $nationalityCols,
            'nationalityMatrixRows' => $nationalityMatrixRows,
            'grandTotal'            => $grandTotal,
            'dominantBand'          => $dominantBand,
            'dominantPct'           => $dominantPct,
            'unknownPct'            => $unknownPct,
        ];

        return view('dashboard/age_analytics', $data);
    }

    // -------------------------------------------------------------------------
    // Shared matrix-building helper
    // -------------------------------------------------------------------------

    /**
     * Pivot a flat cross-tab result set into column headers + matrix rows.
     *
     * @param array  $rows      Query result rows (objects)
     * @param string $rowField  Property name for the row dimension
     * @param string $colField  Property name for the column dimension
     * @param array  $colOrder  Optional explicit column sort order
     * @return array [colHeaders[], matrixRows[], rowLabels[]]
     */
    private function buildMatrix(array $rows, string $rowField, string $colField, array $colOrder = []): array
    {
        $colHeaders = [];
        $grouped    = [];

        foreach ($rows as $row) {
            $rKey = $row->$rowField;
            $cKey = $row->$colField;
            if (!in_array($cKey, $colHeaders)) {
                $colHeaders[] = $cKey;
            }
            $grouped[$rKey][$cKey] = (int)$row->total;
        }

        if (!empty($colOrder)) {
            // Sort columns by the provided explicit order
            usort($colHeaders, function ($a, $b) use ($colOrder) {
                $ia = array_search($a, $colOrder);
                $ib = array_search($b, $colOrder);
                $ia = $ia === false ? PHP_INT_MAX : $ia;
                $ib = $ib === false ? PHP_INT_MAX : $ib;
                return $ia - $ib;
            });
        } else {
            // Sort columns by grand total descending
            usort($colHeaders, function ($a, $b) use ($grouped) {
                $ta = array_sum(array_map(fn($r) => $r[$a] ?? 0, $grouped));
                $tb = array_sum(array_map(fn($r) => $r[$b] ?? 0, $grouped));
                return $tb - $ta;
            });
        }

        $matrixRows = [];
        foreach ($grouped as $rKey => $colCounts) {
            $matrixRows[] = [
                'label'    => $rKey,
                'counts'   => $colCounts,
                'rowTotal' => array_sum($colCounts),
            ];
        }

        usort($matrixRows, fn($a, $b) => $b['rowTotal'] - $a['rowTotal']);

        $rowLabels = array_column($matrixRows, 'label');

        return [$colHeaders, $matrixRows, $rowLabels];
    }

    // -------------------------------------------------------------------------
    // Registrations analytics
    // -------------------------------------------------------------------------

    public function registrationsAnalytics()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        $topbarData = session('topbar_data') ?? [];

        $summary          = $this->dashboardModel->getProgramSummary($programId);
        $regSummary       = $this->dashboardModel->getRegistrationSummary($programId);
        $dailyStats       = $this->dashboardModel->getParticipantRegistrationStats($programId, 'day', 90);
        $monthlyStats     = $this->dashboardModel->getParticipantRegistrationStats($programId, 'month', 24);
        $nationalityStats = $this->dashboardModel->getNationalityDistribution($programId, 20);
        $knowledgeStats   = $this->dashboardModel->getKnowledgeSourceDistribution($programId);

        // Daily line/area chart data
        $dailyChartData = json_encode([
            'labels' => array_map(fn($i) => $i->label, $dailyStats),
            'values' => array_map(fn($i) => (int)$i->total, $dailyStats),
        ]);

        // Monthly bar chart data
        $monthlyChartData = json_encode([
            'labels' => array_map(fn($i) => $i->label, $monthlyStats),
            'values' => array_map(fn($i) => (int)$i->total, $monthlyStats),
        ]);

        $data = [
            'program'          => $program,
            'summary'          => $summary,
            'regSummary'       => $regSummary,
            'topbarData'       => $topbarData,
            'dailyStats'       => $dailyStats,
            'monthlyStats'     => $monthlyStats,
            'nationalityStats' => $nationalityStats,
            'knowledgeStats'   => $knowledgeStats,
            'dailyChartData'   => $dailyChartData,
            'monthlyChartData' => $monthlyChartData,
        ];

        return view('dashboard/registrations_analytics', $data);
    }

    // -------------------------------------------------------------------------
    // Ambassadors analytics
    // -------------------------------------------------------------------------

    public function ambassadorsAnalytics()
    {
        $programId = session('current_program');

        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        $topbarData = session('topbar_data') ?? [];

        $summary      = $this->dashboardModel->getProgramSummary($programId);
        $ambassadors  = $this->dashboardModel->getAmbassadorReferralsFull($programId);

        // Compute summary stats from the full list
        $totalAmbassadors  = count($ambassadors);
        $totalReferred     = 0;
        $topAmbassadorName = 'N/A';
        $topReferrals      = 0;

        foreach ($ambassadors as $i => $amb) {
            $totalReferred += (int)$amb->total_referrals;
            if ($i === 0) {
                $topAmbassadorName = $amb->ambassador_name;
                $topReferrals      = (int)$amb->total_referrals;
            }
        }

        $avgReferrals = $totalAmbassadors > 0
            ? round($totalReferred / $totalAmbassadors, 1) : 0;

        // Top 15 for the chart
        $top15 = array_slice($ambassadors, 0, 15);

        $chartLabels = array_map(fn($a) => $a->ambassador_name, $top15);
        $chartValues = array_map(fn($a) => (int)$a->total_referrals, $top15);

        // Reverse so smallest bar is at top (ApexCharts horizontal bar renders bottom-to-top)
        $chartLabels = array_reverse($chartLabels);
        $chartValues = array_reverse($chartValues);

        $ambassadorChartData = json_encode([
            'labels' => $chartLabels,
            'values' => $chartValues,
        ]);

        $data = [
            'program'              => $program,
            'summary'              => $summary,
            'topbarData'           => $topbarData,
            'ambassadors'          => $ambassadors,
            'ambassadorChartData'  => $ambassadorChartData,
            'totalAmbassadors'     => $totalAmbassadors,
            'totalReferred'        => $totalReferred,
            'avgReferrals'         => $avgReferrals,
            'topAmbassadorName'    => $topAmbassadorName,
            'topReferrals'         => $topReferrals,
        ];

        return view('dashboard/ambassadors_analytics', $data);
    }

    // -------------------------------------------------------------------------

    public function knowledgeSource()
    {
        $programId = session('current_program');

        // If no program is selected, redirect to welcome page
        if (!$programId) {
            return redirect()->to('welcome')->with('error', 'Please select a program to continue.');
        }

        $program = $this->programModel->find($programId);

        // If program not found, redirect to welcome
        if (!$program) {
            session()->remove('current_program');
            return redirect()->to('welcome')->with('error', 'Selected program is not available. Please select another program.');
        }

        // Prepare topbar data for navigation
        $topbarData = session('topbar_data') ?? [];

        // Get full distribution (no limit) and cross-tab data
        $knowledgeSourceStats = $this->dashboardModel->getKnowledgeSourceDistribution($programId);
        $byCountry = $this->dashboardModel->getKnowledgeSourceByCountry($programId);

        // Build chart data
        $ksLabels = [];
        $ksValues = [];

        // Exclude "Not Specified" from charts — keeps the channel mix readable.
        foreach ($knowledgeSourceStats as $item) {
            $source = empty($item->source) ? 'Not Specified' : $item->source;
            if ($source === 'Not Specified') {
                continue;
            }
            $ksLabels[] = $source;
            $ksValues[] = (int)$item->total;
        }

        if (empty($ksLabels)) {
            $ksLabels = ['No Data'];
            $ksValues = [0];
        }

        $knowledgeSourceChartData = json_encode([
            'labels' => $ksLabels,
            'values' => $ksValues,
        ]);

        // Pivot byCountry into a cross-tab matrix
        $sourceCols = [];
        $grouped = [];

        foreach ($byCountry as $row) {
            if (!in_array($row->source, $sourceCols)) {
                $sourceCols[] = $row->source;
            }
            $grouped[$row->nationality][$row->source] = (int)$row->total;
        }

        // Sort source columns by total (descending) to match distribution order
        usort($sourceCols, function ($a, $b) use ($grouped) {
            $totalA = array_sum(array_column(array_map(fn($r) => [$r[$a] ?? 0], array_values($grouped)), 0));
            $totalB = array_sum(array_column(array_map(fn($r) => [$r[$b] ?? 0], array_values($grouped)), 0));
            return $totalB - $totalA;
        });

        $matrixRows = [];
        foreach ($grouped as $nationality => $sourceCounts) {
            $rowTotal = array_sum($sourceCounts);
            $matrixRows[] = [
                'nationality' => $nationality,
                'counts'      => $sourceCounts,
                'rowTotal'    => $rowTotal,
            ];
        }

        // Sort matrix rows by rowTotal descending
        usort($matrixRows, fn($a, $b) => $b['rowTotal'] - $a['rowTotal']);

        // Summary stats
        $totalWithSource = 0;
        $topSource = 'N/A';
        $topSourceCount = 0;
        $notSpecifiedCount = 0;
        $grandTotal = 0;

        // "Top Source" surfaces the top NAMED channel for marketing — skip the
        // non-actionable buckets ("Not Specified", "Others"). "With Known Source"
        // still counts every non-null source, including Others.
        foreach ($knowledgeSourceStats as $item) {
            $grandTotal += (int)$item->total;
            if ($item->source === 'Not Specified') {
                $notSpecifiedCount = (int)$item->total;
                continue;
            }
            $totalWithSource += (int)$item->total;
            if ($item->source !== 'Others' && (int)$item->total > $topSourceCount) {
                $topSourceCount = (int)$item->total;
                $topSource = $item->source;
            }
        }

        $notSpecifiedPct = $grandTotal > 0 ? round(($notSpecifiedCount / $grandTotal) * 100, 1) : 0;

        $data = [
            'program'                  => $program,
            'summary'                  => $this->dashboardModel->getProgramSummary($programId),
            'topbarData'               => $topbarData,
            'knowledgeSourceStats'     => $knowledgeSourceStats,
            'knowledgeSourceChartData' => $knowledgeSourceChartData,
            'byCountry'                => $byCountry,
            'sourceCols'               => $sourceCols,
            'matrixRows'               => $matrixRows,
            'countries'                => array_column($matrixRows, 'nationality'),
            'totalWithSource'          => $totalWithSource,
            'topSource'                => $topSource,
            'notSpecifiedPct'          => $notSpecifiedPct,
            'grandTotal'               => $grandTotal,
        ];

        return view('dashboard/knowledge_source', $data);
    }
}