<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticsExport implements WithMultipleSheets
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $sheets[] = new OverallStatisticsSheet($this->statistics);
        $sheets[] = new ModuleStatisticsSheet($this->statistics);
        $sheets[] = new GroupStatisticsSheet($this->statistics);
        $sheets[] = new TopAbsentStudentsSheet($this->statistics);
        $sheets[] = new DailyTrendSheet($this->statistics);
        $sheets[] = new WeeklyTrendSheet($this->statistics);
        
        return $sheets;
    }
}

class OverallStatisticsSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        return [
            ['Période', $this->statistics['period']['from'] . ' - ' . $this->statistics['period']['to']],
            [],
            ['Séances totales', $this->statistics['overall']['sessions']],
            ['Absences totales', $this->statistics['overall']['absences']],
            ['Étudiants total', $this->statistics['overall']['students']],
            ['Taux de présence global', $this->statistics['overall']['attendance_rate'] . '%'],
        ];
    }

    public function headings(): array
    {
        return ['Détail', 'Valeur'];
    }

    public function title(): string
    {
        return 'Résumé Global';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            'A3:A7' => ['font' => ['bold' => true]],
        ];
    }
}

class ModuleStatisticsSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        $data = [];
        
        $data[] = ['Module', 'Code', 'Séances', 'Absences', 'Étudiants', 'Taux de présence'];
        
        foreach ($this->statistics['module_stats'] as $moduleStat) {
            $data[] = [
                $moduleStat['module']->name,
                $moduleStat['module']->code,
                $moduleStat['sessions'],
                $moduleStat['absences'],
                $moduleStat['students'],
                $moduleStat['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Module', 'Code', 'Séances', 'Absences', 'Étudiants', 'Taux de présence'];
    }

    public function title(): string
    {
        return 'Par Module';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
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
        
        $data[] = ['Groupe', 'Séances', 'Absences', 'Étudiants', 'Taux de présence'];
        
        foreach ($this->statistics['group_stats'] as $groupStat) {
            $data[] = [
                $groupStat['group']->name,
                $groupStat['sessions'],
                $groupStat['absences'],
                $groupStat['students'],
                $groupStat['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Groupe', 'Séances', 'Absences', 'Étudiants', 'Taux de présence'];
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
        
        $data[] = ['Étudiant', 'Numéro étudiant', 'Groupe', 'Absences'];
        
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
        return ['Étudiant', 'Numéro étudiant', 'Groupe', 'Absences'];
    }

    public function title(): string
    {
        return 'Top Élèves Absents';
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
        
        $data[] = ['Date', 'Séances', 'Absences', 'Taux de présence'];
        
        foreach ($this->statistics['daily_trend'] as $day) {
            $data[] = [
                $day['date'],
                $day['sessions'],
                $day['absences'],
                $day['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Date', 'Séances', 'Absences', 'Taux de présence'];
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

class WeeklyTrendSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    public function array(): array
    {
        $data = [];
        
        $data[] = ['Semaine', 'Séances', 'Absences', 'Taux de présence'];
        
        foreach ($this->statistics['weekly_trend'] as $week) {
            $data[] = [
                'Semaine ' . $week['week'],
                $week['sessions'],
                $week['absences'],
                $week['attendance_rate'] . '%',
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return ['Semaine', 'Séances', 'Absences', 'Taux de présence'];
    }

    public function title(): string
    {
        return 'Tendance Hebdomadaire';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}