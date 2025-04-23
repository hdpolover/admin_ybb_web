<?php

namespace App\Models;

use CodeIgniter\Model;

class FaqModel extends Model
{
    protected $table      = 'program_faqs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object';

    protected $allowedFields = ['program_id', 'question', 'answer', 'faq_category', 'order_number',  'is_active', 'is_deleted'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get all active FAQs by program ID
     *
     * @param int $programId The program ID
     * @return array The active FAQs
     */    public function getActiveFaqsByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->orderBy('order_number', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get all FAQs by program ID
     *
     * @param int $programId The program ID
     * @return array The FAQs
     */    public function getAllFaqsByProgramId($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->where('is_active', 1)
            ->orderBy('order_number', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get all FAQs by program ID and category
     *
     * @param int $programId The program ID
     * @param string $category The FAQ category
     * @return array The FAQs
     */    public function getFaqsByProgramIdAndCategory($programId, $category)
    {
        return $this->where('program_id', $programId)
            ->where('faq_category', $category)
            ->where('is_deleted', 0)
            ->orderBy('order_number', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get FAQ by ID
     *
     * @param int $id The FAQ ID
     * @return object|null The FAQ object or null if not found
     */
    public function getFaqById($id)
    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->first();
    }

    /**
     * Get all FAQs
     *
     * @return array All FAQs
     */
    public function getAllFaqs()
    {
        return $this->where('is_deleted', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Soft delete FAQ by ID
     *
     * @param int $id The FAQ ID
     * @return bool True on success, false on failure
     */
    public function softDeleteFaq($id)
    {
        return $this->update($id, ['is_deleted' => 1]);
    }
}
