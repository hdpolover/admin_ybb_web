<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Storage extends BaseConfig
{
    /**
     * External storage domain URL
     * The domain where files will be uploaded to and served from
     */
    public $storageUrl = 'https://storage.ybbfoundation.com';

    /**
     * Storage API key (if required by your storage system)
     */
    public $apiKey = 'ybb_storage_api_2024';
    
    /**
     * File upload path on the storage server
     */
    public $basePath = '/uploads';
    
    /**
     * Specific upload paths for different file types
     */
    public $profilePictures = '/profile-pictures';
    public $documents = '/documents';
    public $programFiles = '/program-files';
    
    /**
     * Whether to use FTP for file transfers instead of HTTP POST
     */
    public $useFtp = true;
    
    /**
     * FTP connection settings
     */
    public $ftpHost = 'ftp.ybbfoundation.com';
    public $ftpUsername = 'storage_user@ybbfoundation.com';
    public $ftpPassword = 'Ns@%L(y_iSU9';
    public $ftpPort = 21;
    public $ftpPassive = true;
    public $ftpRootPath = '/';
    
    /**
     * Allowed max file size in MB
     */
    public $maxFileSize = 5;
    
    /**
     * Allowed file types for profile pictures
     */
    public $allowedProfilePictureTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      /**
     * Generate full URL for a file path
     * 
     * @param string $path File path relative to storage root
     * @return string Full URL to the file
     */
    public function getFileUrl(string $path): string
    {
        // Ensure path starts with slash if it's not empty
        if ($path && substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        return $this->storageUrl . $path;
    }
}