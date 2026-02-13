<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nip', 'name', 'password', 'role', 'join_date', 'position', 'unit', 'phone', 
        'supervisor_id', 'signature', 'front_title', 'back_title', 'pob', 'dob', 
        'gender', 'rank', 'address', 'education', 'nik', 'photo', 'is_supervisor', 
        'is_head_of_agency', 'leave_balance_n', 'leave_balance_n1', 'leave_balance_n2'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    public function getDetailedRemainingLeave($userId, $year = null)
    {
        $db = \Config\Database::connect();
        if ($year === null) {
            $year = date('Y');
        }
        
        $user = $this->find($userId);
        if (!$user) return ['n' => 0, 'n1' => 0, 'n2' => 0, 'total' => 0];

        // 1. Get Initial Balances (Option 2)
        $initialN  = (int)($user['leave_balance_n'] ?? 12);
        $initialN1 = (int)($user['leave_balance_n1'] ?? 0);
        $initialN2 = (int)($user['leave_balance_n2'] ?? 0);

        // 2. ASN Rule Enforcement for Initial Balances (Constraints)
        $initialN1 = min($initialN1, 6); // N-1 max 6 days carry over
        $initialN2 = min($initialN2, 6); // N-2 from previous carry over also max 6
        
        // Total initial at start of year N
        // Theoretically max is 24 (12 + 6 + 6)

        // 3. Get total annual leave taken in year N (only in this system)
        $builder = $db->table('leave_requests');
        $builder->select('SUM(duration) as used_days', false);
        $builder->join('leave_types', 'leave_types.id = leave_requests.leave_type_id');
        $builder->where('user_id', $userId);
        $builder->where('leave_types.name', 'Cuti Tahunan');
        $builder->where('status', 'approved');
        $builder->where('YEAR(start_date)', $year);
        $query = $builder->get();
        $result = $query->getRow();
        $usedInN = (int)($result->used_days ?? 0);

        // 4. FIFO Deduction Logic
        // We have $usedInN to deduct from $initialN2, then $initialN1, then $initialN
        
        // Deduct from N-2 first
        $remN2 = max(0, $initialN2 - $usedInN);
        $leftoverAfterN2 = max(0, $usedInN - $initialN2);

        // Deduct from N-1
        $remN1 = max(0, $initialN1 - $leftoverAfterN2);
        $leftoverAfterN1 = max(0, $leftoverAfterN2 - $initialN1);

        // Deduct from N
        $remN = max(0, $initialN - $leftoverAfterN1);

        return [
            'n'     => $remN,
            'n1'    => $remN1,
            'n2'    => $remN2,
            'total' => $remN + $remN1 + $remN2
        ];
    }

    public function getRemainingLeave($userId, $year = null)
    {
        $details = $this->getDetailedRemainingLeave($userId, $year);
        return $details['total'];
    }
}
