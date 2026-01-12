<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Student::with(['user', 'group'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Email',
            'Numéro étudiant',
            'Groupe',
            'Année académique',
            'Date de naissance',
            'Téléphone',
            'Adresse',
            'Statut'
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->user->name,
            $student->user->email,
            $student->student_number,
            $student->group->name ?? 'N/A',
            $student->academic_year,
            $student->date_of_birth?->format('d/m/Y') ?? 'N/A',
            $student->user->phone ?? 'N/A',
            $student->address ?? 'N/A',
            $student->user->is_active ? 'Actif' : 'Inactif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:J1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFE0E0E0']
                ]
            ],
        ];
    }
}