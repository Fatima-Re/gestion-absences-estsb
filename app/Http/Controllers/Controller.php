<?php

namespace App\Http\Controllers;

// For Excel exports
use Maatwebsite\Excel\Facades\Excel;

// For PDF exports
use Barryvdh\DomPDF\Facade\Pdf;

// Your export classes
use App\Exports\AbsencesExport;
use App\Exports\AttendanceExport;
use App\Exports\StatisticsExport;
use App\Exports\StudentsExport;
use App\Exports\ReportExport;
abstract class Controller
{
    //
}
