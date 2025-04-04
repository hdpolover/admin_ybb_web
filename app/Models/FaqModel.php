<?php

namespace App\Models;

use CodeIgniter\Model;

class FaqModel extends Model
{
    protected $table          = 'program_faqs';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'object'; // Set to return objects
    protected $useSoftDeletes = false; // Using is_deleted field manually
    protected $protectFields  = true;
    protected $allowedFields  = [
        'program_id',
        'question',
        'answer',
        'faq_category',
        'is_active',
        'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = ''; // Not using soft deletes

    // Validation
    protected $validationRules      = [
        'program_id'    => 'required|numeric',
        'question'      => 'required|min_length[5]',
        'answer'        => 'required|min_length[5]',
        'faq_category'  => 'permit_empty',
        'is_active'     => 'permit_empty|in_list[0,1]',
        'is_deleted'    => 'permit_empty|in_list[0,1]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get active FAQs for a program
     *
     * @param int $programId
     * @return object[]
     */
    public function getActiveFaqs($programId)
    {
        return $this->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }

    /**
     * Get active FAQs for a program by category
     *
     * @param int $programId
     * @param string $category
     * @return object[]
     */
    public function getActiveFaqsByCategory($programId, $category)
    {
        return $this->where('program_id', $programId)
                    ->where('faq_category', $category)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->findAll();
    }
    
    /**
     * Get active FAQs for a program category (using program_category relationship)
     *
     * @param int $programCategoryId
     * @return object[]
     */
    public function getActiveFaqsByProgramCategory($programCategoryId)
    {
        $builder = $this->builder('program_faqs f');
        $builder->select('f.*')
                ->join('programs p', 'p.id = f.program_id')
                ->where('p.program_category_id', $programCategoryId)
                ->where('f.is_active', 1)
                ->where('f.is_deleted', 0);
                
        return $builder->get()->getResult();  // Changed to getResult() for objects
    }
    
    /**
     * Get FAQs grouped by category for a program category
     *
     * @param int $programCategoryId
     * @return array
     */
    public function getGroupedFaqsByProgramCategory($programCategoryId)
    {
        $faqs = $this->getActiveFaqsByProgramCategory($programCategoryId);
        
        // Group FAQs by category
        $grouped_faqs = [];
        foreach ($faqs as $faq) {
            $category = $faq->faq_category ?: 'General';  // Changed from array access to object property
            if (!isset($grouped_faqs[$category])) {
                $grouped_faqs[$category] = [];
            }
            $grouped_faqs[$category][] = $faq;
        }
        
        return $grouped_faqs;
    }
}