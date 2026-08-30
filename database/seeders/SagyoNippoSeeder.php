<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobCode;
use App\Models\PartCode;

class SagyoNippoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobCodes = [
            // CAD / DESIGN
            ['type' => 'A. CAD / DESIGN', 'item' => 'DESIGN CONCEPT', 'code' => 'A-1'],
            ['type' => 'A. CAD / DESIGN', 'item' => '3D MODELING', 'code' => 'A-2'],
            ['type' => 'A. CAD / DESIGN', 'item' => '2D DRAWING', 'code' => 'A-3'],
            ['type' => 'A. CAD / DESIGN', 'item' => 'PART LIST', 'code' => 'A-4'],
            ['type' => 'A. CAD / DESIGN', 'item' => 'MOLD CALCULATION', 'code' => 'A-5'],
            ['type' => 'A. CAD / DESIGN', 'item' => 'PART D/R', 'code' => 'A-6'],
            ['type' => 'A. CAD / DESIGN', 'item' => 'MOLD D/R', 'code' => 'A-7'],

            // CAM / PROGRAM
            ['type' => 'B. CAM / PROGRAM', 'item' => 'NC DATA / PROGRAM', 'code' => 'B-1'],
            ['type' => 'B. CAM / PROGRAM', 'item' => 'ELECTRODE DESIGN', 'code' => 'B-2'],
            ['type' => 'B. CAM / PROGRAM', 'item' => 'NC SIMULATION', 'code' => 'B-3'],

            // PRODUCTION / MACHINING
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'MACHINING CENTER (CNC)', 'code' => 'C-1'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'ELECTRIC DISCHARGE (EDM)', 'code' => 'C-2'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'WIRECUT', 'code' => 'C-3'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'SUPER DRILL', 'code' => 'C-4'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'LATHE / TURNING', 'code' => 'C-5'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'GRINDING', 'code' => 'C-6'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'DRILLING / BORING', 'code' => 'C-7'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'RAW CUTTING (BANDSAW)', 'code' => 'C-8'],
            ['type' => 'C. PRODUCTION/MACHINING', 'item' => 'INSPECTION (QC)', 'code' => 'C-9'],

            // ASSEMBLY
            ['type' => 'D. ASSEMBLY', 'item' => 'POLISHING', 'code' => 'D-1'],
            ['type' => 'D. ASSEMBLY', 'item' => 'MATCHING', 'code' => 'D-2'],
            ['type' => 'D. ASSEMBLY', 'item' => 'FITTING', 'code' => 'D-3'],
            ['type' => 'D. ASSEMBLY', 'item' => 'ASSY & DISS ASSY COOLING', 'code' => 'D-4'],
            ['type' => 'D. ASSEMBLY', 'item' => 'ASSY & DISS ASSY ELECTRICAL', 'code' => 'D-5'],
            ['type' => 'D. ASSEMBLY', 'item' => 'ASSY & DISS ASSY HOT RUNNER', 'code' => 'D-6'],
            ['type' => 'D. ASSEMBLY', 'item' => 'ASSY & DISS ASSY MOLD UNIT', 'code' => 'D-7'],
            ['type' => 'D. ASSEMBLY', 'item' => 'WELDING', 'code' => 'D-8'],
            ['type' => 'D. ASSEMBLY', 'item' => 'DESPOTTING', 'code' => 'D-9'],
            ['type' => 'D. ASSEMBLY', 'item' => 'MECHANISM CHECK', 'code' => 'D-10'],
            ['type' => 'D. ASSEMBLY', 'item' => 'MOLD PAINTING', 'code' => 'D-11'],
            ['type' => 'D. ASSEMBLY', 'item' => 'FINAL MOLD INSPECTION', 'code' => 'D-12'],
            ['type' => 'D. ASSEMBLY', 'item' => 'BONKAI TENKEN / OVER HAUL', 'code' => 'D-13'],
            ['type' => 'D. ASSEMBLY', 'item' => 'TUNNING / REPAIR', 'code' => 'D-14'],
        ];

        foreach ($jobCodes as $job) {
            JobCode::firstOrCreate(['code' => $job['code']], $job);
        }

        $partCodes = [
            ['code' => '01-P', 'item' => 'ANGULAR BLOCK'],
            ['code' => '02-P', 'item' => 'ANGULAR PIN'],
            ['code' => '03-P', 'item' => 'BACK UP PLATE'],
            ['code' => '04-P', 'item' => 'BASE SLIDER'],
            ['code' => '05-P', 'item' => 'CAVITY BASE'],
            ['code' => '06-P', 'item' => 'CAVITY PLATE'],
            ['code' => '07-P', 'item' => 'CAVITY SPACER'],
            ['code' => '08-P', 'item' => 'COOLING'],
            ['code' => '09-P', 'item' => 'CORE BASE'],
            ['code' => '10-P', 'item' => 'CORE PLATE'],
            ['code' => '11-P', 'item' => 'CORE SPACER'],
            ['code' => '12-P', 'item' => 'COVER EJECTORE PIN'],
            ['code' => '13-P', 'item' => 'EJECTOR BLOCK'],
            ['code' => '14-P', 'item' => 'EJECTOR PIN'],
            ['code' => '15-P', 'item' => 'EJECTOR PLATE'],
            ['code' => '16-P', 'item' => 'EJECTOR RETAINER PLATE'],
            ['code' => '17-P', 'item' => 'ELECTRIC COMP.'],
            ['code' => '18-P', 'item' => 'ELECTRODE'],
            ['code' => '19-P', 'item' => 'GAS SPRING BLOCK'],
            ['code' => '20-P', 'item' => 'GUIDE BUSH BLOCK'],
            ['code' => '21-P', 'item' => 'GUIDE PIN + BUSHING'],
            ['code' => '22-P', 'item' => 'GUIDE RAIL'],
            ['code' => '23-P', 'item' => 'HOT RUNNER'],
            ['code' => '24-P', 'item' => 'INSERT AIR ASIS'],
            ['code' => '25-P', 'item' => 'INSERT CAVITY'],
            ['code' => '26-P', 'item' => 'INSERT CORE'],
            ['code' => '27-P', 'item' => 'KEISHA CORE'],
            ['code' => '28-P', 'item' => 'LOCATING RING'],
            ['code' => '29-P', 'item' => 'LOCK KEY'],
            ['code' => '30-P', 'item' => 'LOWER PLATE'],
            ['code' => '31-P', 'item' => 'MAKURI'],
            ['code' => '32-P', 'item' => 'MANIFOLD'],
            ['code' => '33-P', 'item' => 'PLUG COOLING'],
            ['code' => '34-P', 'item' => 'PRESSURE PLATE'],
            ['code' => '35-P', 'item' => 'RETURN PIN BLOCK'],
            ['code' => '36-P', 'item' => 'SLIDER HANEBASI'],
            ['code' => '37-P', 'item' => 'STOPPER EJECTOR'],
            ['code' => '38-P', 'item' => 'STOPPER SLIDER'],
            ['code' => '39-P', 'item' => 'SUPPORT PIN'],
            ['code' => '40-P', 'item' => 'UPPER PLATE'],
            ['code' => '41-P', 'item' => 'VALVE GATE COMP'],
        ];

        foreach ($partCodes as $part) {
            PartCode::firstOrCreate(['code' => $part['code']], $part);
        }
    }
}
