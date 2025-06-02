<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupDB extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Backup database menggunakan mysqldump ke writable/backup/.';

    public function run(array $params)
    {
        // Ganti dengan info koneksi database kamu
        $dbName   = 'u1437096_ybb_master_app_db';
        $dbUser   = 'u1437096_ybb_master_app_admin_user';
        $dbPass   = '7J8*^dFEa&lN'; // Kosong jika tidak pakai password
        $host     = '194.163.42.101'; // atau 'localhost'

        $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
        $backupDir = WRITEPATH . 'backup/';
        $fullPath = $backupDir . $filename;

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Pastikan argumen di-escape
        $cmd = sprintf(
            'mysqldump --column-statistics=0 -h%s -u%s %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($dbUser),
            $dbPass ? '-p' . escapeshellarg($dbPass) : '',
            escapeshellarg($dbName),
            escapeshellarg($fullPath)
        );

        CLI::write("⏳ Menjalankan backup...", 'yellow');
        exec($cmd, $output, $resultCode);

        if ($resultCode === 0) {
            CLI::write("✅ Backup berhasil disimpan di: $fullPath", 'green');
        } else {
            CLI::error("❌ Backup gagal. Kode: $resultCode. Periksa koneksi, nama DB, atau akses ke mysqldump.");
        }
    }
}