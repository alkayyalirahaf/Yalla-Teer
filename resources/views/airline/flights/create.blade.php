@extends('airline-admin.flights.layout')

@section('title', __('Create Flight'))

@section('flight-content')
<h1>{{ __('Create Flight') }}</h1>
<form method="POST" action="{{ route('airline.store') }}" class="stack">
    @csrf

    <section class="panel stack">
        <h2>{{ __('Flight details') }}</h2>
        <div class="form-grid">
            <label class="field">
                <span>{{ __('Flight number') }}</span>
                <input type="text" name="flight_number" value="{{ old('flight_number') }}" maxlength="10" required>
                @error('flight_number')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Origin airport') }}</span>
                <select name="origin_airport_id" required>
                    <option value="">{{ __('Select an airport') }}</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" @selected((string) old('origin_airport_id') === (string) $airport->id)>
                            {{ $airport->code }} — {{ $airport->name }}{{ $airport->city ? ', '.$airport->city->name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('origin_airport_id')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Destination airport') }}</span>
                <select name="destination_airport_id" required>
                    <option value="">{{ __('Select an airport') }}</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" @selected((string) old('destination_airport_id') === (string) $airport->id)>
                            {{ $airport->code }} — {{ $airport->name }}{{ $airport->city ? ', '.$airport->city->name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('destination_airport_id')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Departure') }}</span>
                <input type="datetime-local" name="departure_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('departure_at') }}" required>
                @error('departure_at')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Arrival') }}</span>
                <input type="datetime-local" name="arrival_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('arrival_at') }}" required>
                @error('arrival_at')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Stops') }}</span>
                <input type="number" name="stops" value="{{ old('stops', 0) }}" min="0" max="10" required>
                @error('stops')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>{{ __('Status') }}</span>
                <select name="status" required>
                    @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'scheduled') === $status)>{{ __(ucfirst($status)) }}</option>
                    @endforeach
                </select>
                @error('status')<small class="error">{{ $message }}</small>@enderror
            </label>
        </div>
    </section>

    @php
        $classRows = old('classes', [[
            'class' => 'economy',
            'price' => '',
            'seat_rows' => 10,
            'seats_per_row' => 6,
            'cabin_baggage_kg' => 7,
            'checked_baggage_kg' => 23,
        ]]);
    @endphp
    <section class="panel stack" data-cabins>
        <div class="flight-card">
            <div><h2>{{ __('Cabin classes') }}</h2><p>{{ __('Set fares, baggage allowance, and capacity for each cabin.') }}</p></div>
            <button type="button" class="quiet" data-add-cabin>{{ __('Add cabin class') }}</button>
        </div>
        @error('classes')<p class="notice error" role="alert">{{ $message }}</p>@enderror

        <div class="stack" data-cabin-list>
            @foreach($classRows as $index => $classData)
                <article class="panel stack" data-cabin-row data-index="{{ $index }}">
                    <div class="flight-card">
                        <h3>{{ __('Cabin class') }}</h3>
                        <button type="button" class="button danger" data-remove-cabin>{{ __('Remove') }}</button>
                    </div>
                    <div class="form-grid">
                        <label class="field">
                            <span>{{ __('Class') }}</span>
                            <select name="classes[{{ $index }}][class]" required>
                                @foreach(['economy', 'business', 'first'] as $className)
                                    <option value="{{ $className }}" @selected(($classData['class'] ?? '') === $className)>{{ __(ucfirst($className)) }}</option>
                                @endforeach
                            </select>
                            @error("classes.$index.class")<small class="error">{{ $message }}</small>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('Price') }}</span>
                            <input type="number" name="classes[{{ $index }}][price]" value="{{ $classData['price'] ?? '' }}" min="1" step="0.01" required>
                            @error("classes.$index.price")<small class="error">{{ $message }}</small>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('Seat rows') }}</span>
                            <input type="number" name="classes[{{ $index }}][seat_rows]" value="{{ $classData['seat_rows'] ?? 10 }}" min="1" max="100" required data-seat-rows>
                            @error("classes.$index.seat_rows")<small class="error">{{ $message }}</small>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('Seats per row') }}</span>
                            <input type="number" name="classes[{{ $index }}][seats_per_row]" value="{{ $classData['seats_per_row'] ?? 6 }}" min="1" max="10" required data-seats-per-row>
                            @error("classes.$index.seats_per_row")<small class="error">{{ $message }}</small>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('Cabin baggage (kg)') }}</span>
                            <input type="number" name="classes[{{ $index }}][cabin_baggage_kg]" value="{{ $classData['cabin_baggage_kg'] ?? 7 }}" min="0" max="100" required>
                            @error("classes.$index.cabin_baggage_kg")<small class="error">{{ $message }}</small>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('Checked baggage (kg)') }}</span>
                            <input type="number" name="classes[{{ $index }}][checked_baggage_kg]" value="{{ $classData['checked_baggage_kg'] ?? 23 }}" min="0" max="100" required>
                            @error("classes.$index.checked_baggage_kg")<small class="error">{{ $message }}</small>@enderror
                        </label>
                    </div>
                    <p>{{ __('Total seats in this class:') }} <strong data-row-total>0</strong></p>
                    <div class="stack"><h3>{{ __('Seat map preview') }}</h3><div class="table-wrap" data-seat-map aria-live="polite"></div></div>
                </article>
            @endforeach
        </div>
        <p class="notice">{{ __('Total seats across cabin classes:') }} <strong data-total-seats aria-live="polite">0</strong></p>
    </section>

    <template data-cabin-template>
        <article class="panel stack" data-cabin-row data-index="__INDEX__">
            <div class="flight-card">
                <h3>{{ __('Cabin class') }}</h3>
                <button type="button" class="button danger" data-remove-cabin>{{ __('Remove') }}</button>
            </div>
            <div class="form-grid">
                <label class="field"><span>{{ __('Class') }}</span><select name="classes[__INDEX__][class]" required><option value="economy">{{ __('Economy') }}</option><option value="business">{{ __('Business') }}</option><option value="first">{{ __('First') }}</option></select></label>
                <label class="field"><span>{{ __('Price') }}</span><input type="number" name="classes[__INDEX__][price]" min="1" step="0.01" required></label>
                <label class="field"><span>{{ __('Seat rows') }}</span><input type="number" name="classes[__INDEX__][seat_rows]" value="10" min="1" max="100" required data-seat-rows></label>
                <label class="field"><span>{{ __('Seats per row') }}</span><input type="number" name="classes[__INDEX__][seats_per_row]" value="6" min="1" max="10" required data-seats-per-row></label>
                <label class="field"><span>{{ __('Cabin baggage (kg)') }}</span><input type="number" name="classes[__INDEX__][cabin_baggage_kg]" value="7" min="0" max="100" required></label>
                <label class="field"><span>{{ __('Checked baggage (kg)') }}</span><input type="number" name="classes[__INDEX__][checked_baggage_kg]" value="23" min="0" max="100" required></label>
            </div>
            <p>{{ __('Total seats in this class:') }} <strong data-row-total>0</strong></p>
            <div class="stack"><h3>{{ __('Seat map preview') }}</h3><div class="table-wrap" data-seat-map aria-live="polite"></div></div>
        </article>
    </template>

    <div class="actions">
        <button type="submit" class="button">{{ __('Create Flight') }}</button>
        <a class="button secondary" href="{{ route('airline.flights') }}">{{ __('Cancel') }}</a>
    </div>
</form>

<script>
(() => {
    const root = document.querySelector('[data-cabins]');
    if (!root) return;
    const list = root.querySelector('[data-cabin-list]');
    const template = document.querySelector('[data-cabin-template]');
    const total = root.querySelector('[data-total-seats]');
    let nextIndex = Math.max(-1, ...Array.from(list.querySelectorAll('[data-index]'), row => Number(row.dataset.index))) + 1;

    const update = () => {
        let totalSeats = 0;
        list.querySelectorAll('[data-cabin-row]').forEach(row => {
            const rows = Math.min(100, Math.max(0, Number(row.querySelector('[data-seat-rows]').value) || 0));
            const seatsPerRow = Math.min(10, Math.max(0, Number(row.querySelector('[data-seats-per-row]').value) || 0));
            const count = rows * seatsPerRow;
            totalSeats += count;
            row.querySelector('[data-row-total]').textContent = count;
            const map = row.querySelector('[data-seat-map]');
            map.replaceChildren();
            if (!rows || !seatsPerRow) return;

            const grid = document.createElement('div');
            grid.style.display = 'grid';
            grid.style.gridTemplateColumns = `repeat(${seatsPerRow + 1}, max-content)`;
            grid.style.gap = '.35rem';
            grid.setAttribute('role', 'grid');
            const rowLabel = document.createElement('span');
            rowLabel.className = 'badge';
            rowLabel.textContent = 'Row';
            grid.append(rowLabel);
            for (let column = 0; column < seatsPerRow; column++) {
                const heading = document.createElement('span');
                heading.className = 'badge';
                heading.textContent = String.fromCharCode(65 + column);
                heading.setAttribute('role', 'columnheader');
                grid.append(heading);
            }
            for (let rowNumber = 1; rowNumber <= rows; rowNumber++) {
                const rowHeading = document.createElement('span');
                rowHeading.className = 'badge';
                rowHeading.textContent = rowNumber;
                rowHeading.setAttribute('role', 'rowheader');
                grid.append(rowHeading);
                for (let column = 0; column < seatsPerRow; column++) {
                    const seat = document.createElement('span');
                    seat.className = 'badge';
                    seat.textContent = `${rowNumber}${String.fromCharCode(65 + column)}`;
                    seat.setAttribute('role', 'gridcell');
                    grid.append(seat);
                }
            }
            map.append(grid);
        });
        total.textContent = totalSeats;
    };

    root.addEventListener('input', event => {
        if (event.target.matches('[data-seat-rows], [data-seats-per-row]')) update();
    });
    root.addEventListener('change', event => {
        if (event.target.matches('[data-seat-rows], [data-seats-per-row]')) update();
    });
    root.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-cabin]');
        if (remove) {
            remove.closest('[data-cabin-row]').remove();
            update();
        }
    });
    root.querySelector('[data-add-cabin]').addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        update();
    });
    update();
})();
</script>
@endsection
