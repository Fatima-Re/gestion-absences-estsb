<?php


namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

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

abstract class Controller extends BaseController{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}