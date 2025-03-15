<?php

namespace App\Controllers;

class Excel extends BaseController
{
    public function index()
    {
        $data = [
            ['ID', 'Name', 'Score'],
            [1, 'Hendra', 95],
            [2, 'Alex', 100]
        ];

        exportToExcel($data, 'report.xlsx');
    }

}
