@props(['interactive' => false])
<div class="table-wrap">
    <table class="seats">
        <caption>{{ __('Sample seat map') }}</caption>
        <thead><tr><th scope="col">{{ __('Row') }}</th>@foreach(['A', 'B', 'C', 'D'] as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach(range(1, 4) as $row)
                <tr><th scope="row">{{ $row }}</th>
                    @foreach(['A', 'B', 'C', 'D'] as $column)
                        @php
                            $taken = in_array($row.$column, ['1A', '2D', '3C'], true);
                        @endphp
                        <td>
                            @if($interactive)
                                <label><input type="radio" name="seat" value="{{ $row.$column }}" @disabled($taken) aria-label="{{ 'Seat '.$row.$column.($taken ? ', occupied' : '') }}"> {{ $row.$column }}</label>
                            @else
                                <span class="badge">{{ $row.$column }} · {{ $taken ? 'Occupied' : 'Available' }}</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
