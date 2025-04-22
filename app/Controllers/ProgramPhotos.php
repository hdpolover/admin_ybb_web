<?php

namespace App\Controllers;
use App\Models\ProgramPhotoModel;

class ProgramPhotos extends BaseController
{
    protected $programPhotoModel;

    public function __construct()
    {
        $this->programPhotoModel = new ProgramPhotoModel();
    }
        
    public function index()
    {
        return view('index');
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
