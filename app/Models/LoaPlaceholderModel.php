<!-- 

namespace App\Models;

use CodeIgniter\Model;

class LoaPlaceholderModel extends Model
{
    protected $table = 'loa_placeholders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'letter_type',
        'placeholder',
        'label',
        'description',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'is_deleted';

    protected $validationRules = [
        'letter_type' => 'required|in_list[regular,journal]',
        'placeholder' => 'required',
        'label' => 'required'
    ];

    protected $validationMessages = [
        'letter_type' => [
            'required' => 'Letter type is required',
            'in_list' => 'Letter type must be either regular or journal'
        ],
        'placeholder' => [
            'required' => 'Placeholder is required'
        ],
        'label' => [
            'required' => 'Label is required'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // get LOA placeholders by letter type
    public function getPlaceholdersByLetterType($letterType)
    {
        return $this->where('letter_type', $letterType)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
    }

    // get LOA placeholder by id
    public function getPlaceholderById($id)
    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->first();
    }
} -->
