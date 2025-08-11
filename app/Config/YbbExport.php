<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * YBB Export API Configuration
 */
class YbbExport extends BaseConfig
{
    /**
     * YBB Export API base URL
     */
    public string $apiUrl = '';

    /**
     * Request timeout in seconds
     */
    public int $timeout = 300;

    /**
     * Maximum records per export
     */
    public int $maxRecords = 50000;

    /**
     * Chunk threshold for large exports
     */
    public int $chunkThreshold = 5000;

    /**
     * Retry attempts for failed requests
     */
    public int $retryAttempts = 3;

    /**
     * Retry delay in seconds
     */
    public int $retryDelay = 2;

    /**
     * Download timeout for large files (seconds)
     */
    public int $downloadTimeout = 600;

    /**
     * Temporary storage path for downloaded files
     */
    public string $tempStoragePath = WRITEPATH . 'uploads/exports/';

    /**
     * Cleanup expired files after hours
     */
    public int $cleanupAfterHours = 24;

    /**
     * Enable debug logging
     */
    public bool $enableDebugLogging = true;

    // NEW: Certificate endpoints
    public string $certificateGenerateEndpoint = '/api/ybb/certificates/generate';
    public string $certificateHealthEndpoint = '/api/ybb/certificates/health';
    public string $certificateValidateTemplateEndpoint = '/api/ybb/certificates/templates/validate';
    public string $certificateValidateBlocksEndpoint = '/api/ybb/certificates/content-blocks/validate';
    public string $certificatePlaceholdersEndpoint = '/api/ybb/certificates/placeholders';

    // Certificate settings
    public int $certificateTimeout = 60; // seconds
    public int $maxCertificateSize = 10 * 1024 * 1024; // 10MB
    public array $allowedTemplateFormats = ['pdf', 'jpg', 'jpeg', 'png'];

    public function __construct()
    {
        parent::__construct();

        // Load from environment variables with production defaults
        $this->apiUrl = getenv('YBB_EXPORT_API_URL') ?: 'https://ybb-data-management-service-production.up.railway.app';
        $this->timeout = (int) (getenv('YBB_EXPORT_API_TIMEOUT') ?: 300);
        $this->maxRecords = (int) (getenv('YBB_EXPORT_MAX_RECORDS') ?: 50000);
        $this->enableDebugLogging = ENVIRONMENT === 'development';
    }
}
