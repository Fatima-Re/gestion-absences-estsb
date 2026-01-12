<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements WithMultipleSheets
{
    protected $statistics;
    protected $params;

    public function __construct($statistics, $params)
    {
        $this->statistics = $statistics;
        $this->params = $params;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Sheet 1: Summary
        $sheets[] = new AttendanceSummarySheet($this->statistics);
        
        // Sheet 2: Group Statistics
        $sheets[] = new GroupStatisticsSheet($this->statistics);
        
        // Sheet 3: Top Absent Students
        $sheets[] = new TopAbsentStudentsSheet($this->statistics);
        
        // Sheet 4: Daily Trend
        $sheets[] = new DailyTrendSheet($this->statistics);
        
        return $sheets;
    }
}

// Separate sheets for better organization

class AttendanceSummarySheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        return [
            ['Module', $this->statistics['module']->name],
            ['Enseignant', $this->statistics['teacher']->user->name],
            ['Période', $this->statistics['period']['from'] . ' au ' . $this->statistics['period']['to']],
            [],
            ['Séances totales', $this->statistics['total_sessions']],
            ['Étudiants total', $this->statistics['total_students']],
            ['Présences totales', $this->statistics['total_present']],
            ['Absences totales', $this->statistics['total_absent']],
            ['Retards totaux', $this->statistics['total_late']],
            ['Taux de présence global', $this->statistics['overall_attendance_rate'] . '%'],
        ];
    }

    public function headings(): array
    {
        return ['Détail', 'Valeur'];
    }

    public function title(): string
    {
        return 'Résumé';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            'A5:A10' => ['font' => ['bold' => true]],
        ];
    }
}

class GroupStatisticsSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        $data = [];
        
        // Headers
        $data[] = ['Groupe', 'Séances', 'Étudiants', 'Présences', 'Absences', 'Retards', 'Taux de présence'];
        
        // Data rows
        foreach ($this->statistics['group_statistics'] as $groupStat) {
            $data[] = [
                $groupStat['group']->name,
                $groupStat['sessions'],
                $groupStat['students'],
                $groupStat['present'],
                $groupStat['absent'],
                $groupStat['late'],
                $groupStat['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Groupe',
            'Séances',
            'Étudiants',
            'Présences',
            'Absences',
            'Retards',
            'Taux de présence'
        ];
    }

    public function title(): string
    {
        return 'Par Groupe';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class TopAbsentStudentsSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        $data = [];
        
        // Headers
        $data[] = ['Étudiant', 'Numéro étudiant', 'Groupe', 'Absences totales'];
        
        // Data rows
        foreach ($this->statistics['top_absent_students'] as $student) {
            $data[] = [
                $student->user->name,
                $student->student_number,
                $student->group->name,
                $student->absences_count,
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Étudiant', 'Numéro étudiant', 'Groupe', 'Absences totales'];
    }

    public function title(): string
    {
        return 'Top Absences';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class DailyTrendSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        $data = [];
        
        // Headers
        $data[] = ['Date', 'Séances', 'Présences', 'Absences', 'Taux de présence'];
        
        // Data rows
        foreach ($this->statistics['daily_trend'] as $day) {
            $data[] = [
                $day['date'],
                $day['sessions'],
                $day['present'],
                $day['absent'],
                $day['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Date', 'Séances', 'Présences', 'Absences', 'Taux de présence'];
    }

    public function title(): string
    {
        return 'Tendance Journalière';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}