<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreModel extends Model
{
    protected $table = 'scores';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'participant_id',
        'score_weight_id',
        'score_input',
        'score_calculated',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    public function getScoreWeightsByParticipantId($participant_id)
    {
        $builder = $this->builder();
        $builder->select('*');
        $result = $builder->where('participant_id', $participant_id)->get()->getResult(); // <= pakai object

        if (!$result) {
            return null;
        }

        $scoreWeightModel = new ScoreWeightModel();
        foreach ($result as $row) {
            $scoreWeight    = $scoreWeightModel->find($row->score_weight_id);   // pakai ->
            $row->description = $scoreWeight ? $scoreWeight->description : null;
        }

        return $result;
    }

    public function getScoresByParticipantIds($participant_ids)
    {
        $builder = $this->builder();
        $builder->select('*');
        $result = $builder->whereIn('participant_id', $participant_ids)->get()->getResult(); // <= pakai object

        if (!$result) {
            return null;
        }

        $scoreWeightModel = new ScoreWeightModel();
        foreach ($result as $row) {
            $scoreWeight      = $scoreWeightModel->find($row->score_weight_id);
            $row->description = $scoreWeight ? $scoreWeight->description : null;
        }
        return $result;
    }

    public function getScoreByParticipantId($participant_id)
    {
        $builder = $this->builder();
        $builder->select('*');
        $result  = $builder->where('participant_id', $participant_id)->get()->getResult(); // <= pakai object

        if (!$result) {
            return null;
        }

        $scoreWeightModel = new ScoreWeightModel();
        foreach ($result as $row) {
            $scoreWeight      = $scoreWeightModel->find($row->score_weight_id);
            $row->description = $scoreWeight ? $scoreWeight->description : null;
        }
        return $result;
    }

}