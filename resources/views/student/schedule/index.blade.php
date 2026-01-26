@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')

    @include('partials.page-header', [
        'title' => 'Mon emploi du temps'
    ])

    <!-- Current Week Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Semaine du {{ $startOfWeek->format('d/m/Y') }} au {{ $endOfWeek->format('d/m/Y') }}</h5>
                        <div>
                            <a href="{{ route('student.schedule.index', ['week' => $currentWeek - 1]) }}" class="btn btn-outline-secondary btn-sm">← Semaine précédente</a>
                            <a href="{{ route('student.schedule.index', ['week' => $currentWeek + 1]) }}" class="btn btn-outline-secondary btn-sm">Semaine suivante →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="15%">Heure</th>
                                    <th width="17%">Lundi<br><small>{{ $startOfWeek->format('d/m') }}</small></th>
                                    <th width="17%">Mardi<br><small>{{ $startOfWeek->copy()->addDays(1)->format('d/m') }}</small></th>
                                    <th width="17%">Mercredi<br><small>{{ $startOfWeek->copy()->addDays(2)->format('d/m') }}</small></th>
                                    <th width="17%">Jeudi<br><small>{{ $startOfWeek->copy()->addDays(3)->format('d/m') }}</small></th>
                                    <th width="17%">Vendredi<br><small>{{ $startOfWeek->copy()->addDays(4)->format('d/m') }}</small></th>
                                    <th width="17%">Samedi<br><small>{{ $startOfWeek->copy()->addDays(5)->format('d/m') }}</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $timeSlots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
                                @endphp

                                @foreach($timeSlots as $time)
                                <tr>
                                    <td><strong>{{ $time }}</strong></td>
                                    @for($day = 0; $day < 6; $day++)
                                        @php
                                            $currentDate = $startOfWeek->copy()->addDays($day);
                                            $sessions = $schedule->where('date', $currentDate->format('Y-m-d'))
                                                                ->where('start_time', '>=', $time)
                                                                ->where('start_time', '<', date('H:i', strtotime($time) + 3600));
                                        @endphp
                                        <td>
                                            @if($sessions->count() > 0)
                                                @foreach($sessions as $session)
                                                    <div class="p-2 mb-1 border rounded" style="background-color: var(--primary-color-2); color: white; font-size: 0.8em;">
                                                        <strong>{{ $session->module->name }}</strong><br>
                                                        {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}<br>
                                                        <small>{{ $session->room ?? 'Salle N/A' }}</small>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-muted small">-</div>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
