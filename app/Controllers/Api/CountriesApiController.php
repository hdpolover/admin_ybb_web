<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;

/**
 * Countries API Controller
 * 
 * Provides API endpoints for country data
 */
class CountriesApiController extends ApiBaseController
{
    /**
     * Get all countries
     * GET /api/countries
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        try {
            $countries = $this->getCountriesFromJson();
            
            if (empty($countries)) {
                return $this->respondError('No countries data available', self::HTTP_NOT_FOUND);
            }
            
            return $this->respondSuccess($countries, self::HTTP_OK, 'Countries retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error fetching countries: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve countries', self::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Search countries by name
     * GET /api/countries/search?q=Albania
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function search()
    {
        try {
            $query = $this->request->getGet('q');
            
            if (empty($query)) {
                return $this->respondValidationErrors('Search query parameter "q" is required');
            }
            
            $countries = $this->getCountriesFromJson();
            
            if (empty($countries)) {
                return $this->respondError('No countries data available', self::HTTP_NOT_FOUND);
            }
            
            // Filter countries by search query (case-insensitive)
            $searchQuery = strtolower(trim($query));
            $filteredCountries = array_filter($countries, function($country) use ($searchQuery) {
                return stripos($country['countryName'], $searchQuery) !== false;
            });
            
            // Re-index array to maintain proper JSON structure
            $filteredCountries = array_values($filteredCountries);
            
            return $this->respondSuccess([
                'query' => $query,
                'count' => count($filteredCountries),
                'countries' => $filteredCountries
            ], self::HTTP_OK, 'Search completed successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error searching countries: ' . $e->getMessage());
            return $this->respondError('Failed to search countries', self::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Get country by ID
     * GET /api/countries/{id}
     * 
     * @param int $id Country ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->respondValidationErrors('Country ID is required');
            }
            
            $countries = $this->getCountriesFromJson();
            
            if (empty($countries)) {
                return $this->respondError('No countries data available', self::HTTP_NOT_FOUND);
            }
            
            // Find country by ID
            $country = null;
            foreach ($countries as $item) {
                if ($item['id'] == $id) {
                    $country = $item;
                    break;
                }
            }
            
            if (!$country) {
                return $this->respondNotFound('Country not found with ID: ' . $id);
            }
            
            return $this->respondSuccess($country, self::HTTP_OK, 'Country retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error fetching country by ID: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve country', self::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Get country by name
     * GET /api/countries/by-name/{name}
     * 
     * @param string $name Country name
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getByName($name = null)
    {
        try {
            if (!$name) {
                return $this->respondValidationErrors('Country name is required');
            }
            
            $countries = $this->getCountriesFromJson();
            
            if (empty($countries)) {
                return $this->respondError('No countries data available', self::HTTP_NOT_FOUND);
            }
            
            // Find country by exact name match (case-insensitive)
            $country = null;
            foreach ($countries as $item) {
                if (strcasecmp($item['countryName'], $name) === 0) {
                    $country = $item;
                    break;
                }
            }
            
            if (!$country) {
                return $this->respondNotFound('Country not found: ' . $name);
            }
            
            return $this->respondSuccess($country, self::HTTP_OK, 'Country retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error fetching country by name: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve country', self::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Get all country codes
     * GET /api/countries/codes
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function codes()
    {
        try {
            $countries = $this->getCountriesFromJson();
            
            if (empty($countries)) {
                return $this->respondError('No countries data available', self::HTTP_NOT_FOUND);
            }
            
            // Extract only country codes
            $codes = array_map(function($country) {
                return [
                    'id' => $country['id'],
                    'countryName' => $country['countryName'],
                    'countryCode' => $country['countryCode']
                ];
            }, $countries);
            
            return $this->respondSuccess($codes, self::HTTP_OK, 'Country codes retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error fetching country codes: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve country codes', self::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Load countries data from JSON file
     * 
     * @return array
     */
    private function getCountriesFromJson()
    {
        $jsonPath = FCPATH . 'assets/json/country-list.json';
        
        if (!file_exists($jsonPath)) {
            log_message('error', 'Country list JSON file not found: ' . $jsonPath);
            return [];
        }
        
        $jsonContent = file_get_contents($jsonPath);
        $countries = json_decode($jsonContent, true);
        
        if (!$countries || !is_array($countries)) {
            log_message('error', 'Failed to decode country list JSON or invalid format');
            return [];
        }
        
        // Convert relative flag paths to full URLs
        $baseUrl = base_url();
        foreach ($countries as &$country) {
            if (isset($country['flagImg'])) {
                // Convert relative path to full URL
                $country['flagImg'] = $baseUrl . $country['flagImg'];
            }
        }
        
        return $countries;
    }
}
