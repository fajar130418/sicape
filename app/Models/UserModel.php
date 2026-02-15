<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'nip',
        'name',
        'password',
        'role',
        'join_date',
        'position',
        'unit',
        'phone',
        'supervisor_id',
        'signature',
        'front_title',
        'back_title',
        'pob',
        'dob',
        'gender',
        'rank',
        'address',
        'education',
        'nik',
        'user_type',
        'contract_end_date',
        'phone',
        'photo',
        'signature',
        'is_supervisor',
        'is_head_of_agency',
        'supervisor_id',
        'email',
        'leave_balance_n',
        'leave_balance_n1',
        'leave_balance_n2',
        'mkg_additional_years',
        'mkg_additional_months',
        'mkg_adjustment_years',
        'mkg_adjustment_months'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    public function getDetailedRemainingLeave($userId, $year = null)
    {
        $db = \Config\Database::connect();
        if ($year === null) {
            $year = date('Y');
        }

        $user = $this->find($userId);
        if (!$user)
            return ['n' => 0, 'n1' => 0, 'n2' => 0, 'total' => 0];

        // 1. Get Initial Balances (Option 2)
        $initialN = (int) ($user['leave_balance_n'] ?? 12);
        $initialN1 = (int) ($user['leave_balance_n1'] ?? 0);
        $initialN2 = (int) ($user['leave_balance_n2'] ?? 0);

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
        $usedInN = (int) ($result->used_days ?? 0);

        // 4. FIFO Deduction Logic (with Strict ASN Carryover Rule)
        // Rule: If N-1 is used, the remainder is forfeited (becomes 0).

        // Deduct from N-2 first
        $remN2 = max(0, $initialN2 - $usedInN);
        $leftoverAfterN2 = max(0, $usedInN - $initialN2);

        // Deduct from N-1
        if ($leftoverAfterN2 > 0) {
            // N1 is being used. Any use of N1 makes the entire balance 0.
            $remN1 = 0;
            $leftoverAfterN1 = max(0, $leftoverAfterN2 - $initialN1);
        } else {
            // N1 was not touched
            $remN1 = $initialN1;
            $leftoverAfterN1 = 0;
        }

        // Deduct from N
        $remN = max(0, $initialN - $leftoverAfterN1);

        return [
            'n' => $remN,
            'n1' => $remN1,
            'n2' => $remN2,
            'total' => $remN + $remN1 + $remN2
        ];
    }

    public function getRemainingLeave($userId, $year = null)
    {
        $details = $this->getDetailedRemainingLeave($userId, $year);
        return $details['total'];
    }

    /**
     * Menghitung Masa Kerja Golongan (MKG) dengan mempertimbangkan PMK dan Penyesuaian Gelar.
     */
    public function calculateSeniority($userId, $atDate = null)
    {
        $user = $this->find($userId);
        if (!$user) {
            return ['years' => 0, 'months' => 0];
        }

        $atDate = $atDate ? new \DateTime($atDate) : new \DateTime();
        $joinDate = new \DateTime($user['join_date']);

        // 1. Hitung masa kerja dasar dari TMT (join_date)
        $diff = $joinDate->diff($atDate);
        $baseYears = $diff->y;
        $baseMonths = $diff->m;

        // 2. Tambahkan Masa Kerja Tambahan (PMK)
        $additionalYears = (int) ($user['mkg_additional_years'] ?? 0);
        $additionalMonths = (int) ($user['mkg_additional_months'] ?? 0);

        // 3. Tambahkan/Kurangi Penyesuaian (Gelar/Ijazah)
        $adjustmentYears = (int) ($user['mkg_adjustment_years'] ?? 0);
        $adjustmentMonths = (int) ($user['mkg_adjustment_months'] ?? 0);

        // Total
        $totalMonths = ($baseYears * 12 + $baseMonths) +
            ($additionalYears * 12 + $additionalMonths) +
            ($adjustmentYears * 12 + $adjustmentMonths);

        if ($totalMonths < 0) {
            $totalMonths = 0;
        }

        $finalYears = floor($totalMonths / 12);
        $finalMonths = $totalMonths % 12;

        return [
            'years' => (int) $finalYears,
            'months' => (int) $finalMonths,
            'total_months' => $totalMonths
        ];
    }
}
