<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Kgb extends BaseController
{
    /**
     * KGB status constants
     */
    const STATUS_OVERDUE = 'overdue';   // already past due date
    const STATUS_WARNING = 'warning';  // within 2 months
    const STATUS_OK = 'ok';       // safe
    const STATUS_EXPIRED = 'contract_expired'; // contract ended
    const STATUS_OUTSIDE = 'outside_contract'; // next kgb after contract ends

    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $userModel = new \App\Models\UserModel();

        // Only PNS and PPPK are eligible for KGB (PPPK Paruh Waktu excluded for now)
        $employees = $userModel
            ->whereIn('user_type', ['PNS', 'PPPK'])
            ->orderBy('name', 'ASC')
            ->findAll();

        $today = new \DateTime();
        $kgbList = [];

        foreach ($employees as $emp) {
            $kgbInfo = $this->calculateKgb($emp, $today);
            $kgbList[] = array_merge($emp, $kgbInfo);
        }

        // Sort: overdue first, then warning, then outside/expired, then ok
        usort($kgbList, function ($a, $b) {
            $order = [
                self::STATUS_OVERDUE => 0,
                self::STATUS_WARNING => 1,
                self::STATUS_OUTSIDE => 2,
                self::STATUS_EXPIRED => 3,
                self::STATUS_OK => 4
            ];
            $cmp = ($order[$a['kgb_status']] ?? 5) - ($order[$b['kgb_status']] ?? 5);
            if ($cmp !== 0)
                return $cmp;
            return strcmp($a['kgb_next_date'], $b['kgb_next_date']);
        });

        $dueCount = array_reduce($kgbList, function ($carry, $e) {
            // Count overdue and warning for badges
            return $carry + (in_array($e['kgb_status'], [self::STATUS_OVERDUE, self::STATUS_WARNING]) ? 1 : 0);
        }, 0);

        return view('kgb/index', [
            'title' => 'Manajemen KGB',
            'kgbList' => $kgbList,
            'dueCount' => $dueCount,
        ]);
    }

    public function update($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $processedDate = $this->request->getPost('processed_date') ?: date('Y-m-d');

        $userModel = new \App\Models\UserModel();
        $userModel->update($id, ['last_kgb_date' => $processedDate]);

        return redirect()->to('/admin/kgb')->with('success', 'Data KGB berhasil diperbarui. KGB berikutnya dihitung dari ' . date('d M Y', strtotime($processedDate)) . '.');
    }

    /**
     * Calculate KGB info for an employee.
     * Rules: 
     * - PNS: 2 years cycle.
     * - PPPK: 2 years cycle, but restricted by contract.
     *   - If contract expired: status STOP.
     *   - If contract length < 2 years from last basis: status OUTSIDE.
     */
    public static function calculateKgb(array $emp, \DateTime $today): array
    {
        $basisDateStr = !empty($emp['last_kgb_date']) ? $emp['last_kgb_date'] : ($emp['join_date'] ?? null);

        if (empty($basisDateStr)) {
            return [
                'kgb_basis_date' => null,
                'kgb_next_date' => null,
                'kgb_days_left' => null,
                'kgb_status' => 'unknown',
                'kgb_basis_label' => 'TMT Belum Ada',
            ];
        }

        $isPns = ($emp['user_type'] === 'PNS');
        $contractEndStr = $emp['contract_end_date'] ?? null;
        $contractEnd = $contractEndStr ? new \DateTime($contractEndStr) : null;

        $basisDate = new \DateTime($basisDateStr);
        $nextKgb = clone $basisDate;
        $nextKgb->modify('+2 years');

        // Automatic Catch-up: 
        // If the calculated nextKgb is so far in the past that adding another 2 years 
        // still results in a date <= today, it means we are in a much later cycle.
        // We jump to the current cycle to help admins with old TMT dates.
        while ((clone $nextKgb)->modify('+2 years') <= $today) {
            $nextKgb->modify('+2 years');
        }

        $status = self::STATUS_OK;

        // 1. Check if contract expired (For PPPK)
        if (!$isPns && $contractEnd && $contractEnd < $today) {
            $status = self::STATUS_EXPIRED;
        } else {
            // 2. Standard 2-year logic
            $daysLeft = (int) $today->diff($nextKgb)->days;
            if ($nextKgb < $today) {
                $daysLeft = -(int) $today->diff($nextKgb)->days;
            }

            if ($daysLeft < 0) {
                $status = self::STATUS_OVERDUE;
            } elseif ($daysLeft <= 60) {
                $status = self::STATUS_WARNING;
            }

            // 3. For PPPK, check if next KGB falls outside contract
            if (!$isPns && $contractEnd && $nextKgb > $contractEnd) {
                // If not already overdue or warning, mark as outside
                if (!in_array($status, [self::STATUS_OVERDUE, self::STATUS_WARNING])) {
                    $status = self::STATUS_OUTSIDE;
                }
            }
        }

        return [
            'kgb_basis_date' => $basisDateStr,
            'kgb_basis_label' => !empty($emp['last_kgb_date']) ? 'KGB Terakhir' : 'TMT Masuk',
            'kgb_next_date' => $nextKgb->format('Y-m-d'),
            'kgb_days_left' => isset($daysLeft) ? $daysLeft : null,
            'kgb_status' => $status,
            'is_pns' => $isPns,
            'contract_end' => $contractEndStr
        ];
    }
}
