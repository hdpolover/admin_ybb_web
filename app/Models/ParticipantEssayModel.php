<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantEssayModel extends Model
{
    protected $table = 'participant_essays';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    // `id`, `participant_id`, `program_essay_id`, `answer`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    protected $allowedFields = [
        'participant_id',
        'program_essay_id',
        'answer',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    function getParticipantEssaysByParticipantIds($participant_ids)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->whereIn('participant_id', $participant_ids)->get()->getResultArray();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            // get essay questions
            $programEssayModel = new ProgramEssayModel();

            foreach ($result as $key => $row) {
                $programEssayId = $row['program_essay_id'];
                $programEssay = $programEssayModel->find($programEssayId);

                $result[$key]['question'] = $programEssay->questions;
            }

            return $result;
        }
    }

    // get participant essay by participant id
    public function getParticipantEssayByParticipantId($participant_id)
    {
        $builder = $this->builder();

        // get data
        $builder->select('*');

        // Execute the query and get the result as an array of objects
        $result  = $builder->where('participant_id', $participant_id)->get()->getResultArray();

        // check if result is empty
        if (empty($result)) {
            return null;
        } else {
            // get essay questions
            $programEssayModel = new ProgramEssayModel();

            foreach ($result as $key => $row) {
                $programEssayId = $row['program_essay_id'];
                $programEssay = $programEssayModel->find($programEssayId);

                $result[$key]['question'] = $programEssay->questions;
            }

            return $result;
        }
    }
}
