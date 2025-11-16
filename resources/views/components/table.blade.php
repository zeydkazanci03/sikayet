{{-- Reusable Table Komponenti --}}
@props(['headers' => [], 'rows' => [], 'striped' => true, 'hover' => true, 'bordered' => false])

@php
$tableClasses = 'table';
if($striped) $tableClasses .= ' table-striped';
if($hover) $tableClasses .= ' table-hover';
if($bordered) $tableClasses .= ' table-bordered';
@endphp

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if(!empty($headers))
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
