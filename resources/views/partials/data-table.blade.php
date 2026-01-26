@props(['data', 'columns', 'actions', 'emptyMessage' => 'Aucune donnée trouvée.'])

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['label'] ?? $column }}</th>
                @endforeach
                @if(isset($actions))
                    <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    @foreach($columns as $key => $column)
                        <td>
                            @if(is_array($column))
                                @if(isset($column['format']) && $column['format'] === 'badge')
                                    @include('partials.status-badges', ['status' => data_get($item, $column['field'] ?? $key)])
                                @elseif(isset($column['format']) && $column['format'] === 'date')
                                    {{ data_get($item, $column['field'] ?? $key) ? \Carbon\Carbon::parse(data_get($item, $column['field'] ?? $key))->format('d/m/Y') : 'N/A' }}
                                @elseif(isset($column['format']) && $column['format'] === 'datetime')
                                    {{ data_get($item, $column['field'] ?? $key) ? \Carbon\Carbon::parse(data_get($item, $column['field'] ?? $key))->format('d/m/Y H:i') : 'N/A' }}
                                @else
                                    {{ data_get($item, $column['field'] ?? $key) }}
                                @endif
                            @else
                                {{ data_get($item, $column) }}
                            @endif
                        </td>
                    @endforeach
                    @if(isset($actions))
                        <td>
                            {{ $actions($item) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + (isset($actions) ? 1 : 0) }}" class="text-center text-muted">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($data, 'links'))
    {{ $data->links() }}
@endif
