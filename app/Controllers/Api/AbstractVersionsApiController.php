<?php

namespace App\Controllers\Api;

use App\Models\AbstractModel;
use App\Models\AbstractVersionModel;
use App\Models\AbstractAuthorModel;
use App\Models\ProgramModel;
use App\Models\ParticipantModel;
use App\Models\AbstractTopicModel;


use App\Controllers\Api\ApiBaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;

class AbstractVersionsApiController extends ApiBaseController
{
    protected $abstractModel;
    protected $abstractVersionModel;
    protected $abstractAuthorModel;
    protected $programModel;
    protected $participantModel;
    protected $abstractTopicModel;

    /**
     * Initialize controller, set models
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);

        // Initialize models
        $this->abstractModel = new AbstractModel();
        $this->abstractVersionModel = new AbstractVersionModel();
        $this->abstractAuthorModel = new AbstractAuthorModel();
        $this->programModel = new ProgramModel();
        $this->participantModel = new ParticipantModel();
        $this->abstractTopicModel = new AbstractTopicModel();
    }

    /**
     * Get topics by program ID
     *
     * @param int $programId The program ID
     * @return ResponseInterface
     */
    public function getAbstractTopicsByProgramId($programId = null)
    {
        // Validate program ID
        if (empty($programId) || !is_numeric($programId)) {
            return $this->respondValidationErrors('Invalid program ID');
        }

        // Check if program exists
        if (!$this->programModel->find($programId)) {
            return $this->respondNotFound('Program not found');
        }

        try {
            // Get topics for the program
            $topics = $this->abstractTopicModel->where('program_id', $programId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            if (empty($topics)) {
                return $this->respondNotFound('No topics found');
            }

            return $this->respondSuccess(
                $topics,
                SELF::HTTP_OK,
                "Topics retrieved successfully",
            );
        } catch (\Exception $e) {
            log_message('error', 'Error getting topics: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve topics');
        }
    }
    
    /**
     * Get abstract version by ID
     *
     * @param int $id The abstract version ID
     * @return ResponseInterface
     */
    public function getAbstractVersionById($id = null)
    {
        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            return $this->respondValidationErrors('Invalid abstract version ID');
        }

        // Check if abstract version exists
        $abstractVersion = $this->abstractVersionModel->find($id);
        if (!$abstractVersion) {
            return $this->respondNotFound('Abstract version not found');
        }

        try {
            // Get abstract version details
            $abstractVersionDetails = $this->abstractVersionModel->getAbstractVersionById($id);
            
            // Get abstract details
            $abstract = $this->abstractModel->find($abstractVersionDetails->abstract_id);

             // Get authors if any
            $authors = $this->abstractAuthorModel
                ->where('abstract_id', $abstract->id)
                ->findAll();
                
            // Add authors to response
            $abstract->authors = $authors;
            
            return $this->respondSuccess(
                [
                    'abstract_version' => $abstractVersionDetails,
                    'abstract' => $abstract
                ],
                SELF::HTTP_OK,
                "Abstract version retrieved successfully"
            );
        } catch (\Exception $e) {
            log_message('error', 'Error getting abstract version: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve abstract version');
        }
    }

    /**
     * Compare two abstract versions
     *
     * @param int $version1Id The first abstract version ID
     * @param int $version2Id The second abstract version ID
     * @return ResponseInterface
     */
    public function compareVersions($version1Id = null, $version2Id = null)
    {
        // Validate version IDs
        if (empty($version1Id) || !is_numeric($version1Id)) {
            return $this->respondValidationErrors('Invalid first version ID');
        }
        
        if (empty($version2Id) || !is_numeric($version2Id)) {
            return $this->respondValidationErrors('Invalid second version ID');
        }

        // Check if both abstract versions exist
        $version1 = $this->abstractVersionModel->find($version1Id);
        if (!$version1) {
            return $this->respondNotFound('First abstract version not found');
        }

        $version2 = $this->abstractVersionModel->find($version2Id);
        if (!$version2) {
            return $this->respondNotFound('Second abstract version not found');
        }

        // Ensure both versions belong to the same abstract
        if ($version1->abstract_id !== $version2->abstract_id) {
            return $this->respondValidationErrors('Both versions must belong to the same abstract');
        }

        try {
            // Get detailed information for both versions
            $version1Details = $this->abstractVersionModel->getAbstractVersionById($version1Id);
            $version2Details = $this->abstractVersionModel->getAbstractVersionById($version2Id);
            
            // Get abstract details
            $abstract = $this->abstractModel->find($version1->abstract_id);
            
            // Get authors
            $authors = $this->abstractAuthorModel
                ->where('abstract_id', $abstract->id)
                ->findAll();

            // Compare the versions
            $comparison = $this->performVersionComparison($version1Details, $version2Details);
            
            return $this->respondSuccess(
                [
                    'abstract' => $abstract,
                    'authors' => $authors,
                    'version1' => $version1Details,
                    'version2' => $version2Details,
                    'comparison' => $comparison
                ],
                SELF::HTTP_OK,
                "Abstract versions compared successfully"
            );
        } catch (\Exception $e) {
            log_message('error', 'Error comparing abstract versions: ' . $e->getMessage());
            return $this->respondError('Failed to compare abstract versions');
        }
    }

    /**
     * Perform detailed comparison between two abstract versions
     *
     * @param object $version1 First version details
     * @param object $version2 Second version details
     * @return array Comparison results
     */
    private function performVersionComparison($version1, $version2)
    {
        $comparison = [
            'summary' => [
                'has_changes' => false,
                'total_changes' => 0,
                'changed_fields' => []
            ],
            'fields' => []
        ];

        // Fields to compare
        $fieldsToCompare = [
            'title' => 'Title',
            'content' => 'Content',
            'keywords' => 'Keywords',
            'refs' => 'References',
            'status' => 'Status',
            'version_number' => 'Version Number'
        ];

        foreach ($fieldsToCompare as $field => $label) {
            $value1 = $version1->$field ?? '';
            $value2 = $version2->$field ?? '';
            
            $hasChange = $value1 !== $value2;
            
            if ($hasChange) {
                $comparison['summary']['has_changes'] = true;
                $comparison['summary']['total_changes']++;
                $comparison['summary']['changed_fields'][] = $field;
            }

            $fieldComparison = [
                'field' => $field,
                'label' => $label,
                'has_change' => $hasChange,
                'version1_value' => $value1,
                'version2_value' => $value2
            ];

            // For text fields, calculate word count difference
            if (in_array($field, ['title', 'content', 'keywords', 'refs'])) {
                $wordCount1 = str_word_count(strip_tags($value1));
                $wordCount2 = str_word_count(strip_tags($value2));
                
                $fieldComparison['version1_word_count'] = $wordCount1;
                $fieldComparison['version2_word_count'] = $wordCount2;
                $fieldComparison['word_count_difference'] = $wordCount2 - $wordCount1;
                
                // For content, also calculate character count
                if ($field === 'content') {
                    $charCount1 = strlen(strip_tags($value1));
                    $charCount2 = strlen(strip_tags($value2));
                    
                    $fieldComparison['version1_char_count'] = $charCount1;
                    $fieldComparison['version2_char_count'] = $charCount2;
                    $fieldComparison['char_count_difference'] = $charCount2 - $charCount1;
                }
            }

            $comparison['fields'][] = $fieldComparison;
        }

        // Add metadata comparison
        $comparison['metadata'] = [
            'version1_created_at' => $version1->created_at,
            'version2_created_at' => $version2->created_at,
            'version1_updated_at' => $version1->updated_at,
            'version2_updated_at' => $version2->updated_at,
            'time_difference' => strtotime($version2->created_at) - strtotime($version1->created_at)
        ];

        return $comparison;
    }
}
