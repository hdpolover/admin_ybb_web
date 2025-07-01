<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SessionTest extends Controller
{
    public function setProgram()
    {
        // Set program 8 which we know has awards
        session()->set('current_program', 8);
        session()->set('user_id', 1);
        
        echo "<h2>Session Set Successfully!</h2>";
        echo "<p>Program ID: 8</p>";
        echo "<p>User ID: 1</p>";
        echo "<br>";
        echo "<a href='/documents/certificates' style='background:#007bff; color:white; padding:10px; text-decoration:none;'>Go to Certificates Page</a><br><br>";
        echo "<a href='/debug/certificates-simple' style='background:#28a745; color:white; padding:10px; text-decoration:none;'>Test Debug Page</a>";
    }
    
    public function selectProgram($programId)
    {
        session()->set('current_program', $programId);
        session()->set('user_id', 1);
        
        echo "<h2>Session Updated!</h2>";
        echo "<p>Program ID set to: " . $programId . "</p>";
        echo "<p>User ID set to: 1</p>";
        echo "<a href='/documents/certificates'>Go to Certificates Page</a><br>";
        echo "<a href='/debug/certificates-simple'>Test Certificates Debug</a>";
    }
}
