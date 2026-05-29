<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefBankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'Maybank (Malayan Banking Berhad)', 'swift_code' => 'MBBEMYKL'],
            ['name' => 'CIMB Bank Berhad', 'swift_code' => 'CIBBMYKL'],
            ['name' => 'Public Bank Berhad', 'swift_code' => 'PBBEMYKL'],
            ['name' => 'RHB Bank Berhad', 'swift_code' => 'RHBBMYKL'],
            ['name' => 'Hong Leong Bank Berhad', 'swift_code' => 'HLBBMYKL'],
            ['name' => 'AmBank (M) Berhad', 'swift_code' => 'ARBKMYKL'],
            ['name' => 'UOB Malaysia (United Overseas Bank)', 'swift_code' => 'UABORYKL'],
            ['name' => 'Bank Islam Malaysia Berhad', 'swift_code' => 'BIMBMYKL'],
            ['name' => 'Bank Muamalat Malaysia Berhad', 'swift_code' => 'BMMBMYKL'],
            ['name' => 'OCBC Bank (Malaysia) Berhad', 'swift_code' => 'OCBCMYKL'],
            ['name' => 'HSBC Bank Malaysia Berhad', 'swift_code' => 'HBMBMYKL'],
            ['name' => 'Standard Chartered Bank Malaysia', 'swift_code' => 'SCBLMYKX'],
            ['name' => 'Affin Bank Berhad', 'swift_code' => 'PHBMMYKL'],
            ['name' => 'Alliance Bank Malaysia Berhad', 'swift_code' => 'MFBBMYKL'],
            ['name' => 'Bank Rakyat (Bank Kerjasama Rakyat)', 'swift_code' => 'BKRMMYKL'],
            ['name' => 'Bank Simpanan Nasional (BSN)', 'swift_code' => 'BSNAMYK1'],
            ['name' => 'Agrobank (Bank Pertanian Malaysia)', 'swift_code' => 'BPMBMYKL'],
            ['name' => 'Al Rajhi Banking & Investment Corporation', 'swift_code' => 'RJHIMYKL'],
            ['name' => 'Citibank Berhad', 'swift_code' => 'CITIMYKL'],
            ['name' => 'Deutsche Bank (Malaysia) Berhad', 'swift_code' => 'DEUTMYKL'],
            ['name' => 'GX Bank Berhad', 'swift_code' => null],
            ['name' => 'Boost Bank Berhad', 'swift_code' => null],
            ['name' => 'AEON Bank Berhad', 'swift_code' => null],
            ['name' => 'KAF Investment Bank Berhad', 'swift_code' => 'KAFBMYKL'],
        ];

        foreach ($banks as $bank) {
            DB::table('tbl_ref_bank')->updateOrInsert(
                ['name' => $bank['name']],
                array_merge($bank, ['status' => 'active'])
            );
        }
    }
}
