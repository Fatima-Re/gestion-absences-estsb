<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$failed = DB::table('failed_jobs')->latest()->first();

if ($failed) {
    echo "Failed Job ID: " . $failed->id . "\n";
    echo "UUID: " . $failed->uuid . "\n";
    echo "Exception:\n";
    $exception = json_decode($failed->exception, true);
    if ($exception) {
        echo json_encode($exception, JSON_PRETTY_PRINT);
    } else {
        echo $failed->exception;
    }
} else {
    echo "No failed jobs found\n";
}
