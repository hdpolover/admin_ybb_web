<?php

namespace App\Controllers;
use App\Models\ProgramTestimonyModel;

class ProgramTestimonies extends BaseController
{
    protected $programTestimonyModel;

    public function __construct()
    {
        $this->programTestimonyModel = new ProgramTestimonyModel();
    }

    public function index()
    {
        $data = [
            'testimonies' => $this->programTestimonyModel->findAll()
        ];

        return view('master-data/program-testimonies/index', $data);
    }

    public function root($path = '')
    {
        if ($path !== '') {
            if(@file_exists(APPPATH.'Views/'.$path.'.php')) {
                return view($path);
            } else {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        } else {
            echo 'Page Not Found.';
        }
    }
}
