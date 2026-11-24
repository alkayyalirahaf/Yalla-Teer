@props(['rows', 'selected' => null, 'name' => 'seat', 'seatIds' => [], 'caption' => 'Select an available seat'])

@php
    $letters = collect($rows)->flatMap(fn ($seats) => array_keys($seats))->unique()->sort(SORT_NATURAL)->values()->all();
    $columns = $letters;
    if (count($columns) > 1) {
        array_splice($columns, (int) ceil(count($columns) / 2), 0, ['']);
    }
@endphp

<div class="table-wrap booking-cabin">
    <div class="booking-cabin-shell">
    <div class="booking-cabin-end booking-cabin-front">{{ __('Front of aircraft') }}</div>
    <table>
        <caption>{{ $caption }}</caption>
        <thead>
            <tr>
                <th scope="col"><span class="sr-only">{{ __('Row') }}</span></th>
                @foreach($columns as $column)
                    <th scope="col">@if($column){{ $column }}@else<span class="sr-only">{{ __('Aisle') }}</span>@endif</th>
                @endforeach
                <td aria-hidden="true"></td>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row => $seats)
                <tr>
                    <th class="booking-cabin-edge" scope="row">{{ $row }}</th>
                    @foreach($columns as $column)
                        <td @class(['booking-cabin-aisle' => !$column])>
                            @if(isset($seats[$column]))
                                @php($seat = $row.$column)
                                @php($value = $seatIds[$seat] ?? $seat)
                                <label class="booking-seat" title="Seat {{ $seat }}">
                                    <input class="sr-only" type="radio" name="{{ $name }}" value="{{ $value }}" data-seat-label="{{ $seat }}"
                                        @checked((string) $selected === (string) $value) @disabled(!$seats[$column]) @required($seats[$column])
                                        aria-label="{{ 'Seat '.$seat.($seats[$column] ? '' : ', unavailable') }}">
                                    <span aria-hidden="true">{{ $seats[$column] ? $seat : "×" }}</span>
                                </label>
                            @endif
                        </td>
                    @endforeach
                    <td class="booking-cabin-edge" aria-hidden="true">{{ $row }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="booking-cabin-end booking-cabin-rear">{{ __('Rear of aircraft') }}</div>
    </div>
    <div class="booking-cabin-legend">
        <span>{{ __('Available') }}</span><span>{{ __('Selected') }}</span><span>{{ __('Unavailable') }}</span>
    </div>
</div>
