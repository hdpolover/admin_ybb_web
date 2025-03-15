<?php

namespace App\Controllers;

use App\Models\AmbassadorModel;

class Ambassadors extends BaseController
{
    protected $ambassadorModel;

    public function __construct()
    {
        
        $this->ambassadorModel = new AmbassadorModel();
    }
    public function index()
    {
        // $programId = $this->request->getGet('program_id');

        // $data = [
        //     'title' => 'Ambassadors',
        //     'programId' => $programId,
        //     'ambassadors' => $this->ambassadorModel->getAmbassadors(['program_id' => $programId]),
        // ];

        // echo $data['ambassadors'];

        echo "tet";
    }

}