@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('partials.alerts')
    
    @include('partials.page-header', [
        'title' => 'Étudiants du groupe: ' . $group->name,
        'actions' => '<a href="' . route('admin.groups.show', $group) . '" class="btn btn-secondary">Retour</a>'
    ])

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des étudiants ({{ $students->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Numéro étudiant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->user->name }}</td>
                                    <td>{{ $student->user->email }}</td>
                                    <td>{{ $student->student_number }}</td>
                                    <td>
                                        <form action="{{ route('admin.groups.remove-student', [$group, $student]) }}" method="POST" class="d-inline" onsubmit="return confirm('Retirer cet étudiant du groupe?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Retirer</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucun étudiant dans ce groupe.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
