<?php

namespace App\Exports;

use App\Models\Absence;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsencesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $absences;

    public function __construct($absences)
    {
        $this->absences = $absences;
    }

    public function collection()
    {
        return $this->absences;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Étudiant',
            'Numéro étudiant',
            'Module',
            'Date de la séance',
            'Heure',
            'Salle',
            'Statut',
            'Justifié',
            'Date de l\'absence',
            'Commentaires'
        ];
    }

    public function map($absence): array
    {
        return [
            $absence->id,
            $absence->student->user->name ?? 'N/A',
            $absence->student->student_number ?? 'N/A',
            $absence->session->module->name ?? 'N/A',
            $absence->session->date->format('d/m/Y') ?? 'N/A',
            $absence->session->start_time->format('H:i') . ' - ' . $absence->session->end_time->format('H:i'),
            $absence->session->room ?? 'N/A',
            $this->getStatusText($absence->status),
            $absence->justification ? 'Oui' : 'Non',
            $absence->created_at->format('d/m/Y H:i'),
            $absence->notes ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style the header row
            'A1:K1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFE0E0E0']
                ]
            ],
        ];
    }

    private function getStatusText($status)
    {
        $statuses = [
            'unjustified' => 'Non justifié',
            'justified' => 'Justifié',
            'pending' => 'En attente'
        ];
        
        return $statuses[$status] ?? $status;
    }
}