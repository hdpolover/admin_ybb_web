<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramScheduleModel extends Model
{

    //`id`, `program_id`, `name`, `description`, `start_date`, `end_date`, `order_number`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $table = 'program_schedules';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'program_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'order_number',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get program schedules by program ID
     *
     * @param int $programId
     * @return array
     */
    public function getProgramSchedules($programId)
    {
        return $this->where('program_id', $programId)
            ->where('is_deleted', 0)
            ->findAll();
    }

    /**
     * Get program schedule by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getProgramScheduleById($id)
    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->first();
    }
}
