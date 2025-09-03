<?php

namespace App\Controllers\Api\Abstracts;

use App\Controllers\Api\ApiBaseController;
use App\Models\AbstractAuthorModel;

class AbstractAuthorsApiController extends ApiBaseController
{
    protected $abstractAuthorModel;

    public function __construct()
    {
        parent::__construct();
        $this->abstractAuthorModel = new AbstractAuthorModel();
    }

    /**
     * Get all abstract authors
     */
    public function index()
    {
        try {
            $authors = $this->abstractAuthorModel->findAll();
            return $this->respondSuccess($authors, self::HTTP_OK, 'Abstract authors retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Failed to get abstract authors: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve abstract authors', self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get abstract authors by abstract ID
     */
    public function getByAbstractId($abstractId)
    {
        try {
            $authors = $this->abstractAuthorModel->where('abstract_id', $abstractId)->findAll();
            return $this->respondSuccess($authors, self::HTTP_OK, 'Abstract authors retrieved successfully');
        } catch (\Exception $e) {
            log_message('error', 'Failed to get abstract authors by abstract ID: ' . $e->getMessage());
            return $this->respondError('Failed to retrieve abstract authors', self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Create new abstract author
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);
            
            $rules = [
                'abstract_id' => 'required|integer',
                'name' => 'required|max_length[255]',
                'email' => 'permit_empty|valid_email',
                'affiliation' => 'permit_empty|max_length[255]',
                'is_corresponding' => 'permit_empty|in_list[0,1]'
            ];

            if (!$this->validate($data, $rules)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], self::HTTP_BAD_REQUEST);
            }

            $authorId = $this->abstractAuthorModel->insert($data);
            
            if ($authorId) {
                $author = $this->abstractAuthorModel->find($authorId);
                return $this->respondSuccess($author, self::HTTP_CREATED, 'Abstract author created successfully');
            } else {
                return $this->respondError('Failed to create abstract author', self::HTTP_INTERNAL_ERROR);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to create abstract author: ' . $e->getMessage());
            return $this->respondError('Failed to create abstract author', self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Update abstract author
     */
    public function updateAuthor($id)
    {
        try {
            $author = $this->abstractAuthorModel->find($id);
            if (!$author) {
                return $this->respondError('Abstract author not found', self::HTTP_NOT_FOUND);
            }

            $data = $this->request->getJSON(true);
            
            $rules = [
                'name' => 'permit_empty|max_length[255]',
                'email' => 'permit_empty|valid_email',
                'affiliation' => 'permit_empty|max_length[255]',
                'is_corresponding' => 'permit_empty|in_list[0,1]'
            ];

            if (!$this->validate($data, $rules)) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], self::HTTP_BAD_REQUEST);
            }

            if ($this->abstractAuthorModel->update($id, $data)) {
                $updatedAuthor = $this->abstractAuthorModel->find($id);
                return $this->respondSuccess($updatedAuthor, self::HTTP_OK, 'Abstract author updated successfully');
            } else {
                return $this->respondError('Failed to update abstract author', self::HTTP_INTERNAL_ERROR);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to update abstract author: ' . $e->getMessage());
            return $this->respondError('Failed to update abstract author', self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Delete abstract author
     */
    public function deleteAuthor($id)
    {
        try {
            $author = $this->abstractAuthorModel->find($id);
            if (!$author) {
                return $this->respondError('Abstract author not found', self::HTTP_NOT_FOUND);
            }

            if ($this->abstractAuthorModel->delete($id)) {
                return $this->respondSuccess(null, self::HTTP_OK, 'Abstract author deleted successfully');
            } else {
                return $this->respondError('Failed to delete abstract author', self::HTTP_INTERNAL_ERROR);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to delete abstract author: ' . $e->getMessage());
            return $this->respondError('Failed to delete abstract author', self::HTTP_INTERNAL_ERROR);
        }
    }
}
