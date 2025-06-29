<?php

namespace App\Controllers;

use App\Models\ProgramCertificateContentBlockModel;

class ProgramCertificateContentBlocks extends BaseController
{
    protected $contentBlockModel;

    public function __construct()
    {
        $this->contentBlockModel = new ProgramCertificateContentBlockModel();
    }

    /**
     * Get all content blocks
     */
    public function index()
    {
        try {
            $certificateId = $this->request->getGet('certificate_id');
            $type = $this->request->getGet('type');
            
            if ($certificateId && $type) {
                $blocks = $this->contentBlockModel->getContentBlocksByType($certificateId, $type);
            } elseif ($certificateId) {
                $blocks = $this->contentBlockModel->getContentBlocksByCertificate($certificateId);
            } else {
                $blocks = $this->contentBlockModel->where('is_active', 1)
                                                  ->where('is_deleted', 0)
                                                  ->orderBy('y', 'ASC')
                                                  ->orderBy('x', 'ASC')
                                                  ->findAll();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $blocks,
                'message' => 'Content blocks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch content blocks: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve content blocks: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get a specific content block
     */
    public function show($id)
    {
        try {
            $block = $this->contentBlockModel->where('is_deleted', 0)->find($id);

            if (!$block) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Content block not found'
                                     ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $block,
                'message' => 'Content block retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch content block: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve content block: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Create a new content block
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            // Set default values
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_deleted'] = 0;
            $data['font_size'] = $data['font_size'] ?? 12;
            $data['font_family'] = $data['font_family'] ?? 'Arial';
            $data['font_weight'] = $data['font_weight'] ?? 'normal';
            $data['text_align'] = $data['text_align'] ?? 'left';
            $data['color'] = $data['color'] ?? '#000000';

            if ($this->contentBlockModel->save($data)) {
                $insertId = $this->contentBlockModel->getInsertID();
                $block = $this->contentBlockModel->find($insertId);

                return $this->response->setStatusCode(201)
                                     ->setJSON([
                                         'success' => true,
                                         'data' => $block,
                                         'message' => 'Content block created successfully'
                                     ]);
            } else {
                $errors = $this->contentBlockModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create content block: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to create content block: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Update a content block
     */
    public function update($id)
    {
        try {
            $block = $this->contentBlockModel->where('is_deleted', 0)->find($id);

            if (!$block) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Content block not found'
                                     ]);
            }

            $data = $this->request->getJSON(true) ?: $this->request->getPost();

            if ($this->contentBlockModel->update($id, $data)) {
                $updatedBlock = $this->contentBlockModel->find($id);

                return $this->response->setJSON([
                    'success' => true,
                    'data' => $updatedBlock,
                    'message' => 'Content block updated successfully'
                ]);
            } else {
                $errors = $this->contentBlockModel->errors();
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Validation failed',
                                         'errors' => $errors
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update content block: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to update content block: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Delete a content block (soft delete)
     */
    public function delete($id)
    {
        try {
            $block = $this->contentBlockModel->where('is_deleted', 0)->find($id);

            if (!$block) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Content block not found'
                                     ]);
            }

            if ($this->contentBlockModel->softDelete($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Content block deleted successfully'
                ]);
            } else {
                return $this->response->setStatusCode(500)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Failed to delete content block'
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete content block: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to delete content block: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Get content blocks by certificate
     */
    public function byCertificate($certificateId)
    {
        try {
            $blocks = $this->contentBlockModel->getContentBlocksByCertificate($certificateId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $blocks,
                'message' => 'Certificate content blocks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch content blocks by certificate: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to retrieve certificate content blocks: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Update content block position
     */
    public function updatePosition($id)
    {
        try {
            $block = $this->contentBlockModel->where('is_deleted', 0)->find($id);

            if (!$block) {
                return $this->response->setStatusCode(404)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Content block not found'
                                     ]);
            }

            $data = $this->request->getJSON(true) ?: $this->request->getPost();
            
            if (!isset($data['x']) || !isset($data['y'])) {
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'X and Y coordinates are required'
                                     ]);
            }

            if ($this->contentBlockModel->updatePosition($id, $data['x'], $data['y'])) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Content block position updated successfully'
                ]);
            } else {
                return $this->response->setStatusCode(500)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Failed to update content block position'
                                     ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update content block position: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to update position: ' . $e->getMessage()
                                 ]);
        }
    }

    /**
     * Bulk create content blocks
     */
    public function bulkCreate()
    {
        try {
            $blocks = $this->request->getJSON(true) ?: $this->request->getPost('blocks');

            if (!is_array($blocks) || empty($blocks)) {
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Blocks array is required'
                                     ]);
            }

            $createdBlocks = [];
            $errors = [];

            foreach ($blocks as $index => $blockData) {
                // Set default values
                $blockData['is_active'] = $blockData['is_active'] ?? 1;
                $blockData['is_deleted'] = 0;
                $blockData['font_size'] = $blockData['font_size'] ?? 12;
                $blockData['font_family'] = $blockData['font_family'] ?? 'Arial';
                $blockData['font_weight'] = $blockData['font_weight'] ?? 'normal';
                $blockData['text_align'] = $blockData['text_align'] ?? 'left';
                $blockData['color'] = $blockData['color'] ?? '#000000';

                if ($this->contentBlockModel->save($blockData)) {
                    $insertId = $this->contentBlockModel->getInsertID();
                    $createdBlocks[] = $this->contentBlockModel->find($insertId);
                } else {
                    $errors["block_$index"] = $this->contentBlockModel->errors();
                }
            }

            if (!empty($errors)) {
                return $this->response->setStatusCode(400)
                                     ->setJSON([
                                         'success' => false,
                                         'message' => 'Some blocks failed validation',
                                         'errors' => $errors,
                                         'created' => $createdBlocks
                                     ]);
            }

            return $this->response->setStatusCode(201)
                                 ->setJSON([
                                     'success' => true,
                                     'data' => $createdBlocks,
                                     'message' => 'Content blocks created successfully'
                                 ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to bulk create content blocks: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                 ->setJSON([
                                     'success' => false,
                                     'message' => 'Failed to create content blocks: ' . $e->getMessage()
                                 ]);
        }
    }
}
