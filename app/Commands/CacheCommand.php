<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\RedisCacheService;

/**
 * Cache Management CLI Command
 * 
 * Usage:
 * php spark cache:clear
 * php spark cache:stats
 * php spark cache:warmup
 * php spark cache:test
 */
class CacheCommand extends BaseCommand
{
    protected $group       = 'Cache';
    protected $name        = 'cache:manage';
    protected $description = 'Manage Redis cache for API endpoints';
    
    protected $usage = 'cache:manage [action]';
    protected $arguments = [
        'action' => 'Action to perform: clear, stats, warmup, test'
    ];

    private RedisCacheService $cacheService;

    public function run(array $params)
    {
        $this->cacheService = new RedisCacheService();
        
        $action = $params[0] ?? null;
        
        switch ($action) {
            case 'clear':
                $this->clearCache();
                break;
            case 'stats':
                $this->showStats();
                break;
            case 'warmup':
                $this->warmupCache();
                break;
            case 'test':
                $this->testCache();
                break;
            default:
                $this->showUsage();
                break;
        }
    }

    private function clearCache(): void
    {
        CLI::write('Clearing Redis cache...', 'yellow');
        
        try {
            $cache = \Config\Services::cache();
            $result = $cache->clean();
            
            if ($result) {
                CLI::write('✓ Cache cleared successfully!', 'green');
            } else {
                CLI::write('✗ Failed to clear cache', 'red');
            }
        } catch (\Exception $e) {
            CLI::write('✗ Error clearing cache: ' . $e->getMessage(), 'red');
        }
    }

    private function showStats(): void
    {
        CLI::write('Redis Cache Statistics:', 'yellow');
        CLI::newLine();
        
        try {
            $stats = $this->cacheService->getStats();
            
            foreach ($stats as $key => $value) {
                CLI::write("  {$key}: " . (is_array($value) ? json_encode($value) : $value), 'white');
            }
            
            CLI::newLine();
            CLI::write('✓ Stats retrieved successfully!', 'green');
        } catch (\Exception $e) {
            CLI::write('✗ Error getting stats: ' . $e->getMessage(), 'red');
        }
    }

    private function warmupCache(): void
    {
        CLI::write('Warming up cache for critical endpoints...', 'yellow');
        
        try {
            // Define critical endpoints for warmup
            $endpoints = [
                'web-settings',
                'programs',
                'landing/home',
                'landing/programs'
            ];
            
            $result = $this->cacheService->warmUp($endpoints);
            
            if ($result) {
                CLI::write('✓ Cache warmup initiated!', 'green');
            } else {
                CLI::write('✗ Failed to warmup cache', 'red');
            }
        } catch (\Exception $e) {
            CLI::write('✗ Error warming up cache: ' . $e->getMessage(), 'red');
        }
    }

    private function testCache(): void
    {
        CLI::write('Testing Redis cache functionality...', 'yellow');
        CLI::newLine();
        
        try {
            $testKey = 'test_cache_' . time();
            $testData = ['test' => 'data', 'timestamp' => time()];
            
            // Test SET
            CLI::write('Testing cache SET...', 'white');
            $setResult = $this->cacheService->set($testKey, $testData, 60);
            CLI::write($setResult ? '  ✓ SET successful' : '  ✗ SET failed', $setResult ? 'green' : 'red');
            
            // Test GET
            CLI::write('Testing cache GET...', 'white');
            $getData = $this->cacheService->get($testKey);
            $getResult = $getData !== null && $getData['test'] === 'data';
            CLI::write($getResult ? '  ✓ GET successful' : '  ✗ GET failed', $getResult ? 'green' : 'red');
            
            // Test DELETE
            CLI::write('Testing cache DELETE...', 'white');
            $deleteResult = $this->cacheService->delete($testKey);
            CLI::write($deleteResult ? '  ✓ DELETE successful' : '  ✗ DELETE failed', $deleteResult ? 'green' : 'red');
            
            // Test GET after DELETE
            CLI::write('Testing GET after DELETE...', 'white');
            $getAfterDelete = $this->cacheService->get($testKey);
            $deleteConfirm = $getAfterDelete === null;
            CLI::write($deleteConfirm ? '  ✓ Data properly deleted' : '  ✗ Data still exists', $deleteConfirm ? 'green' : 'red');
            
            CLI::newLine();
            
            if ($setResult && $getResult && $deleteResult && $deleteConfirm) {
                CLI::write('✓ All cache tests passed!', 'green');
            } else {
                CLI::write('✗ Some cache tests failed', 'red');
            }
            
        } catch (\Exception $e) {
            CLI::write('✗ Error testing cache: ' . $e->getMessage(), 'red');
        }
    }

    private function showUsage(): void
    {
        CLI::write('Redis Cache Management Commands:', 'yellow');
        CLI::newLine();
        CLI::write('php spark cache:manage clear   - Clear all cached data', 'white');
        CLI::write('php spark cache:manage stats   - Show cache statistics', 'white');
        CLI::write('php spark cache:manage warmup  - Warm up critical endpoints', 'white');
        CLI::write('php spark cache:manage test    - Test cache functionality', 'white');
        CLI::newLine();
        CLI::write('Example: php spark cache:manage test', 'light_gray');
    }
}
