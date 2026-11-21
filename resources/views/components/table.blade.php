@props(['headers' => [], 'rows' => [], 'sortable' => false, 'striped' => false, 'hover' => true, 'class' => '', 'emptyMessage' => 'No data available', 'attributes' => []])

<div class="table-container scrollbar-thin {{ $class }}" {{ $attributes }}>
    @if(empty($rows))
        <div class="empty-state">
            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="empty-state-title">No data</p>
            <p class="empty-state-description">{{ $emptyMessage }}</p>
        </div>
    @else
        <table class="table {{ $striped ? 'table-striped' : '' }}" {{ $sortable ? 'data-sortable' : '' }}>
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th
                            {{ $sortable && isset($header['sort']) ? 'data-sort="'.$header['sort'].'" style="cursor: pointer;"' : '' }}
                            {{ isset($header['width']) ? 'style="width: '.$header['width'].';"' : '' }}
                            class="{{ isset($header['align']) ? 'text-'.$header['align'] : '' }}"
                        >
                            {{ $header['label'] ?? $header }}
                            @if($sortable && isset($header['sort']))
                                <svg class="w-4 h-4 inline-block ml-1 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h18M19 9H5m14 6H5"/></svg>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($headers as $header)
                            <td class="{{ isset($header['align']) ? 'text-'.$header['align'] : '' }}">
                                @if(isset($header['render']))
                                    {{ $header['render']($row) }}
                                @elseif(isset($header['key']))
                                    {{ $row[$header['key']] ?? '' }}
                                @else
                                    {{ $row[$loop->index] ?? '' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>