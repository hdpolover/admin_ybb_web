<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProgramSpeakerModel;

class ProgramSpeakersApiController extends ApiBaseController
{
    protected $model;

    /**
     * Initialize controller, set model
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        // Call parent initializer
        parent::initController($request, $response, $logger);
        
        // Initialize model
        $this->model = new ProgramSpeakerModel(); 
    }

    /**
     * Get All Program Speakers
     * GET /api/program-speakers
     */
    public function index()
    {
        $programSpeakers = $this->model->where('is_deleted', 0)->findAll();
        return $this->respondSuccess($programSpeakers, self::HTTP_OK, 'Program speakers retrieved successfully');
    }

    /**
     * Get Single Program Speaker
     * GET /api/program-speakers/{id}
     */
    public function show($id = null)
    {
        $speaker = $this->model->find($id);
        
        if (!$speaker) {
            return $this->respondNotFound('Speaker not found');
        }
        
        if ($speaker->is_deleted == 1) {
            return $this->respondNotFound('Speaker not found');
        }
        
        return $this->respondSuccess($speaker, self::HTTP_OK, 'Speaker retrieved successfully');
    }

    /**
     * Get speakers by program ID
     * GET /api/program-speakers/program/{programId}
     */
    public function getByProgramId($programId = null)
    {
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        $speakers = $this->model->getByProgramId($programId, true);
        
        return $this->respondSuccess($speakers, self::HTTP_OK, 'Program speakers retrieved successfully');
    }

    /**
     * Get keynote speakers for a program
     * GET /api/program-speakers/program/{programId}/keynote
     */
    public function getKeynoteSpeakers($programId = null)
    {
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        $speakers = $this->model->getKeynoteSpeakers($programId);
        
        return $this->respondSuccess($speakers, self::HTTP_OK, 'Keynote speakers retrieved successfully');
    }

    /**
     * Get regular speakers for a program
     * GET /api/program-speakers/program/{programId}/regular
     */
    public function getRegularSpeakers($programId = null)
    {
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        $speakers = $this->model->getRegularSpeakers($programId);
        
        return $this->respondSuccess($speakers, self::HTTP_OK, 'Regular speakers retrieved successfully');
    }

    /**
     * Get speaker statistics for a program
     * GET /api/program-speakers/program/{programId}/stats
     */
    public function getSpeakerStats($programId = null)
    {
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        $stats = $this->model->getSpeakerStats($programId);
        
        return $this->respondSuccess($stats, self::HTTP_OK, 'Speaker statistics retrieved successfully');
    }

    /**
     * Create Program Speaker
     * POST /api/program-speakers
     */
    public function create()
    {
        $rules = [
            'program_id' => 'required|integer',
            'name' => 'required|max_length[255]',
            'title' => 'permit_empty|max_length[255]',
            'bio' => 'permit_empty',
            'email' => 'permit_empty|valid_email',
            'organization' => 'permit_empty|max_length[255]',
            'linkedin_url' => 'permit_empty|valid_url',
            'twitter_url' => 'permit_empty|valid_url',
            'expertise_areas' => 'permit_empty',
            'session_title' => 'permit_empty|max_length[500]',
            'session_description' => 'permit_empty',
            'session_time' => 'permit_empty|valid_date',
            'is_keynote' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Set default values
        $data['is_keynote'] = $data['is_keynote'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['is_deleted'] = 0;
        
        // Set order number
        $data['order_number'] = $this->model->getNextOrderNumber($data['program_id'], $data['is_keynote']);

        try {
            $id = $this->model->insert($data);
            
            if ($id) {
                $speaker = $this->model->find($id);
                return $this->respondCreated($speaker, 'Speaker created successfully');
            } else {
                return $this->respondError('Failed to create speaker', self::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->respondError('Failed to create speaker: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Update Program Speaker
     * PUT /api/program-speakers/{id}
     */
    public function update($id = null)
    {
        $speaker = $this->model->find($id);
        
        if (!$speaker || $speaker->is_deleted == 1) {
            return $this->respondNotFound('Speaker not found');
        }

        $rules = [
            'name' => 'required|max_length[255]',
            'title' => 'permit_empty|max_length[255]',
            'bio' => 'permit_empty',
            'email' => 'permit_empty|valid_email',
            'organization' => 'permit_empty|max_length[255]',
            'linkedin_url' => 'permit_empty|valid_url',
            'twitter_url' => 'permit_empty|valid_url',
            'expertise_areas' => 'permit_empty',
            'session_title' => 'permit_empty|max_length[500]',
            'session_description' => 'permit_empty',
            'session_time' => 'permit_empty|valid_date',
            'is_keynote' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Remove fields that shouldn't be updated via API
        unset($data['id'], $data['program_id'], $data['created_at'], $data['updated_at'], $data['is_deleted']);

        try {
            $updated = $this->model->update($id, $data);
            
            if ($updated) {
                $speaker = $this->model->find($id);
                return $this->respondSuccess($speaker, self::HTTP_OK, 'Speaker updated successfully');
            } else {
                return $this->respondError('Failed to update speaker', self::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->respondError('Failed to update speaker: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Delete Program Speaker (Soft Delete)
     * DELETE /api/program-speakers/{id}
     */
    public function delete($id = null)
    {
        $speaker = $this->model->find($id);
        
        if (!$speaker || $speaker->is_deleted == 1) {
            return $this->respondNotFound('Speaker not found');
        }

        try {
            $deleted = $this->model->softDelete($id);
            
            if ($deleted) {
                return $this->respondSuccess(null, self::HTTP_OK, 'Speaker deleted successfully');
            } else {
                return $this->respondError('Failed to delete speaker', self::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->respondError('Failed to delete speaker: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Update speaker order
     * POST /api/program-speakers/reorder
     */
    public function reorder()
    {
        $orderData = $this->request->getJSON(true);
        
        if (empty($orderData) || !is_array($orderData)) {
            return $this->respondError('Invalid order data', self::HTTP_BAD_REQUEST);
        }
        
        try {
            $success = $this->model->updateSpeakerOrder($orderData);
            
            if ($success) {
                return $this->respondSuccess(null, self::HTTP_OK, 'Speaker order updated successfully');
            } else {
                return $this->respondError('Failed to update speaker order', self::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->respondError('Failed to update speaker order: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Search speakers
     * POST /api/program-speakers/search
     */
    public function search()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $programId = $data['program_id'] ?? null;
        $search = $data['search'] ?? '';
        $filters = $data['filters'] ?? [];
        
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        try {
            $speakers = $this->model->searchSpeakers($programId, $search, $filters);
            
            return $this->respondSuccess($speakers, self::HTTP_OK, 'Speaker search completed successfully');
        } catch (\Exception $e) {
            return $this->respondError('Failed to search speakers: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Export speakers data
     * GET /api/program-speakers/program/{programId}/export
     */
    public function exportSpeakers($programId = null)
    {
        if (!$programId) {
            return $this->respondError('Program ID is required', self::HTTP_BAD_REQUEST);
        }
        
        try {
            $speakers = $this->model->getSpeakersForExport($programId);
            
            // Transform data for export
            $exportData = [];
            foreach ($speakers as $speaker) {
                $exportData[] = [
                    'ID' => $speaker->id,
                    'Name' => $speaker->name,
                    'Title' => $speaker->title ?? '',
                    'Organization' => $speaker->organization ?? '',
                    'Email' => $speaker->email ?? '',
                    'Type' => $speaker->speaker_type,
                    'Session_Title' => $speaker->session_title ?? '',
                    'Session_Time' => $speaker->session_time ? date('Y-m-d H:i:s', strtotime($speaker->session_time)) : '',
                    'LinkedIn' => $speaker->linkedin_url ?? '',
                    'Twitter' => $speaker->twitter_url ?? '',
                    'Expertise_Areas' => $speaker->expertise_areas ?? '',
                    'Bio' => $speaker->bio ?? '',
                    'Order_Number' => $speaker->order_number,
                    'Status' => $speaker->is_active ? 'Active' : 'Inactive',
                    'Created_At' => $speaker->created_at,
                    'Updated_At' => $speaker->updated_at
                ];
            }
            
            return $this->respondSuccess($exportData, self::HTTP_OK, 'Speakers export data retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondError('Failed to export speakers: ' . $e->getMessage(), self::HTTP_INTERNAL_ERROR);
        }
    }
}