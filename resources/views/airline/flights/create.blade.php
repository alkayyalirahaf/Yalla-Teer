@extends('airline-admin.flights.layout')

@section('title', 'Create Flight')

@section('flight-content')
@php
    $classRows = old('classes', [[
        'class' => 'economy',
        'price' => '',
        'seat_rows' => 10,
        'seats_per_row' => 6,
        'cabin_baggage_kg' => 7,
        'checked_baggage_kg' => 23,
    ]]);
    $realWorldPresets = [
        ['label' => 'Airbus A320 (Economy) — 30 rows × 6 seats', 'class' => 'economy', 'seat_rows' => 30, 'seats_per_row' => 6, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
        ['label' => 'Airbus A320 (Business) — 5 rows × 4 seats', 'class' => 'business', 'seat_rows' => 5, 'seats_per_row' => 4, 'cabin_baggage_kg' => 10, 'checked_baggage_kg' => 32],
        ['label' => 'Boeing 737-800 (Economy) — 27 rows × 6 seats', 'class' => 'economy', 'seat_rows' => 27, 'seats_per_row' => 6, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 20],
        ['label' => 'Boeing 737-800 (Business) — 4 rows × 4 seats', 'class' => 'business', 'seat_rows' => 4, 'seats_per_row' => 4, 'cabin_baggage_kg' => 10, 'checked_baggage_kg' => 30],
        ['label' => 'Embraer E190 (Economy) — 25 rows × 4 seats', 'class' => 'economy', 'seat_rows' => 25, 'seats_per_row' => 4, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
        ['label' => 'Boeing 777-300ER (Economy) — 42 rows × 9 seats', 'class' => 'economy', 'seat_rows' => 42, 'seats_per_row' => 9, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
        ['label' => 'Boeing 777-300ER (Business) — 7 rows × 6 seats', 'class' => 'business', 'seat_rows' => 7, 'seats_per_row' => 6, 'cabin_baggage_kg' => 12, 'checked_baggage_kg' => 32],
        ['label' => 'Boeing 777-300ER (First) — 2 rows × 4 seats', 'class' => 'first', 'seat_rows' => 2, 'seats_per_row' => 4, 'cabin_baggage_kg' => 15, 'checked_baggage_kg' => 40],
        ['label' => 'Airbus A330-300 (Economy) — 32 rows × 8 seats', 'class' => 'economy', 'seat_rows' => 32, 'seats_per_row' => 8, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 23],
        ['label' => 'ATR 72-600 (Economy) — 18 rows × 4 seats', 'class' => 'economy', 'seat_rows' => 18, 'seats_per_row' => 4, 'cabin_baggage_kg' => 7, 'checked_baggage_kg' => 20],
    ];
    $dbPresetLabels = [];
    $dbPresets = [];
    foreach (($layoutPresets ?? collect()) as $preset) {
        $key = implode('|', [$preset->class, $preset->seat_rows, $preset->seats_per_row, $preset->cabin_baggage_kg, $preset->checked_baggage_kg]);
        if (isset($dbPresetLabels[$key])) continue;
        $dbPresetLabels[$key] = true;
        $dbPresets[] = [
            'label' => 'Saved layout ('.ucfirst($preset->class).') — '.$preset->seat_rows.' rows × '.$preset->seats_per_row.' seats, '.$preset->cabin_baggage_kg.'/'.$preset->checked_baggage_kg.' kg',
            'class' => $preset->class,
            'seat_rows' => $preset->seat_rows,
            'seats_per_row' => $preset->seats_per_row,
            'cabin_baggage_kg' => $preset->cabin_baggage_kg,
            'checked_baggage_kg' => $preset->checked_baggage_kg,
        ];
    }
    $allPresets = array_merge($realWorldPresets, $dbPresets);
@endphp

<h1>Create Flight</h1>
<form method="POST" action="{{ route('airline.store') }}" class="stack" data-flight-form>
    @csrf

    <section class="panel stack">
        <h2>Route</h2>
        <div class="form-grid">
            <div class="field">
                <span>Airline</span>
                <input type="text" value="{{ $airline->name ?? 'Your airline' }}{{ isset($airline->code) ? ' ('.$airline->code.')' : '' }}" readonly aria-readonly="true">
                <small class="muted">Locked to your airline.</small>
            </div>
            <label class="field">
                <span>Flight number</span>
                <input type="text" name="flight_number" value="{{ old('flight_number') }}" maxlength="10" required>
                @error('flight_number')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>Origin airport</span>
                <select name="origin_airport_id" required>
                    <option value="">Select an airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" @selected((string) old('origin_airport_id') === (string) $airport->id)>
                            {{ $airport->code }} - {{ $airport->city?->name ?? $airport->name }}
                        </option>
                    @endforeach
                </select>
                @error('origin_airport_id')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>Destination airport</span>
                <select name="destination_airport_id" required>
                    <option value="">Select an airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" @selected((string) old('destination_airport_id') === (string) $airport->id)>
                            {{ $airport->code }} - {{ $airport->city?->name ?? $airport->name }}
                        </option>
                    @endforeach
                </select>
                @error('destination_airport_id')<small class="error">{{ $message }}</small>@enderror
            </label>
        </div>
    </section>

    <section class="panel stack">
        <h2>Schedule</h2>
        <div class="form-grid">
            <label class="field">
                <span>Departure</span>
                <input type="datetime-local" name="departure_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('departure_at') }}" required>
                @error('departure_at')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>Arrival</span>
                <input type="datetime-local" name="arrival_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('arrival_at') }}" required>
                @error('arrival_at')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>Stops</span>
                <input type="number" name="stops" value="{{ old('stops', 0) }}" min="0" max="10" required>
                @error('stops')<small class="error">{{ $message }}</small>@enderror
            </label>
            <label class="field">
                <span>Status</span>
                <select name="status" required>
                    @foreach(['scheduled', 'delayed', 'cancelled', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'scheduled') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @error('status')<small class="error">{{ $message }}</small>@enderror
            </label>
        </div>
    </section>

    <section class="panel stack" data-cabins>
        <div class="flight-card">
            <div>
                <h2>Classes and prices</h2>
                <p>Choose cabin type and set fares. Add or remove cabin classes as needed.</p>
            </div>
            <button type="button" class="button secondary" data-add-cabin>Add cabin class</button>
        </div>
        @error('classes')<p class="notice error" role="alert">{{ $message }}</p>@enderror

        <div class="stack" data-cabin-list>
            @foreach($classRows as $index => $classData)
                <article class="panel stack" data-cabin-row data-index="{{ $index }}">
                    <div class="flight-card">
                        <h3>Cabin {{ $index + 1 }}</h3>
                        <button type="button" class="quiet" data-remove-cabin>Remove</button>
                    </div>
                    <div class="stack">
                        <h4>Class and price</h4>
                        <div class="form-grid">
                            <label class="field">
                                <span>Class</span>
                                <select name="classes[{{ $index }}][class]" required>
                                    @foreach(['economy', 'business', 'first'] as $className)
                                        <option value="{{ $className }}" @selected(($classData['class'] ?? '') === $className)>{{ ucfirst($className) }}</option>
                                    @endforeach
                                </select>
                                @error("classes.$index.class")<small class="error">{{ $message }}</small>@enderror
                            </label>
                            <label class="field">
                                <span>Price</span>
                                <input type="number" name="classes[{{ $index }}][price]" value="{{ $classData['price'] ?? '' }}" min="1" step="0.01" required>
                                @error("classes.$index.price")<small class="error">{{ $message }}</small>@enderror
                            </label>
                        </div>
                    </div>
                    <div class="stack">
                        <h4>Seats and baggage</h4>
                        <div class="form-grid">
                            <label class="field">
                                <span>Choose a layout</span>
                                <select data-layout-preset>
                                    <option value="">Custom layout</option>
                                    @foreach($allPresets as $preset)
                                        <option value='@json($preset)'>{{ $preset['label'] }}</option>
                                    @endforeach
                                </select>
                                <small class="muted">Select a real-world or saved layout to fill fields; you can still edit the numbers.</small>
                            </label>
                        </div>
                        <div class="form-grid">
                            <label class="field">
                                <span>Seat rows</span>
                                <input type="number" name="classes[{{ $index }}][seat_rows]" value="{{ $classData['seat_rows'] ?? 10 }}" min="1" max="100" required data-seat-rows>
                                @error("classes.$index.seat_rows")<small class="error">{{ $message }}</small>@enderror
                            </label>
                            <label class="field">
                                <span>Seats per row</span>
                                <input type="number" name="classes[{{ $index }}][seats_per_row]" value="{{ $classData['seats_per_row'] ?? 6 }}" min="1" max="10" required data-seats-per-row>
                                @error("classes.$index.seats_per_row")<small class="error">{{ $message }}</small>@enderror
                            </label>
                            <label class="field">
                                <span>Cabin baggage (kg)</span>
                                <input type="number" name="classes[{{ $index }}][cabin_baggage_kg]" value="{{ $classData['cabin_baggage_kg'] ?? 7 }}" min="0" max="100" required data-cabin-baggage>
                                @error("classes.$index.cabin_baggage_kg")<small class="error">{{ $message }}</small>@enderror
                            </label>
                            <label class="field">
                                <span>Checked baggage (kg)</span>
                                <input type="number" name="classes[{{ $index }}][checked_baggage_kg]" value="{{ $classData['checked_baggage_kg'] ?? 23 }}" min="0" max="100" required data-checked-baggage>
                                @error("classes.$index.checked_baggage_kg")<small class="error">{{ $message }}</small>@enderror
                            </label>
                        </div>
                        <p>Total seats in this class: <strong data-row-total>0</strong></p>
                        <div class="stack"><h4>Seat map preview</h4><div class="table-wrap" data-seat-map aria-live="polite"></div></div>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="notice">Total seats across cabin classes: <strong data-total-seats aria-live="polite">0</strong></p>
    </section>

    <template data-cabin-template>
        <article class="panel stack" data-cabin-row data-index="__INDEX__">
            <div class="flight-card">
                <h3>Cabin</h3>
                <button type="button" class="quiet" data-remove-cabin>Remove</button>
            </div>
            <div class="stack">
                <h4>Class and price</h4>
                <div class="form-grid">
                    <label class="field"><span>Class</span><select name="classes[__INDEX__][class]" required><option value="economy">Economy</option><option value="business">Business</option><option value="first">First</option></select></label>
                    <label class="field"><span>Price</span><input type="number" name="classes[__INDEX__][price]" min="1" step="0.01" required></label>
                </div>
            </div>
            <div class="stack">
                <h4>Seats and baggage</h4>
                <div class="form-grid">
                    <label class="field">
                        <span>Choose a layout</span>
                        <select data-layout-preset>
                            <option value="">Custom layout</option>
                            @foreach($allPresets as $preset)
                                <option value='@json($preset)'>{{ $preset['label'] }}</option>
                            @endforeach
                        </select>
                        <small class="muted">Select a real-world or saved layout to fill fields; you can still edit the numbers.</small>
                    </label>
                </div>
                <div class="form-grid">
                    <label class="field"><span>Seat rows</span><input type="number" name="classes[__INDEX__][seat_rows]" value="10" min="1" max="100" required data-seat-rows></label>
                    <label class="field"><span>Seats per row</span><input type="number" name="classes[__INDEX__][seats_per_row]" value="6" min="1" max="10" required data-seats-per-row></label>
                    <label class="field"><span>Cabin baggage (kg)</span><input type="number" name="classes[__INDEX__][cabin_baggage_kg]" value="7" min="0" max="100" required data-cabin-baggage></label>
                    <label class="field"><span>Checked baggage (kg)</span><input type="number" name="classes[__INDEX__][checked_baggage_kg]" value="23" min="0" max="100" required data-checked-baggage></label>
                </div>
                <p>Total seats in this class: <strong data-row-total>0</strong></p>
                <div class="stack"><h4>Seat map preview</h4><div class="table-wrap" data-seat-map aria-live="polite"></div></div>
            </div>
        </article>
    </template>

    <div class="actions">
        <button type="submit" class="button">Create Flight</button>
        <a class="button secondary" href="{{ route('airline.flights') }}">Cancel</a>
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
            const rowTotal = row.querySelector('[data-row-total]');
            if (rowTotal) rowTotal.textContent = count;
            const map = row.querySelector('[data-seat-map]');
            if (!map) return;
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
        if (total) total.textContent = totalSeats;
    };

    root.addEventListener('input', event => {
        if (event.target.matches('[data-seat-rows], [data-seats-per-row]')) update();
    });
    root.addEventListener('change', event => {
        if (event.target.matches('[data-seat-rows], [data-seats-per-row]')) update();
        const preset = event.target.closest('[data-layout-preset]');
        if (preset && preset.value) {
            try {
                const data = JSON.parse(preset.value);
                const row = preset.closest('[data-cabin-row]');
                if (!row) return;
                const classSelect = row.querySelector('select[name$="[class]"]');
                if (classSelect && data.class) classSelect.value = data.class;
                const set = (selector, value) => { const el = row.querySelector(selector); if (el && value !== undefined && value !== null) el.value = value; };
                set('[data-seat-rows]', data.seat_rows);
                set('[data-seats-per-row]', data.seats_per_row);
                set('[data-cabin-baggage]', data.cabin_baggage_kg);
                set('[data-checked-baggage]', data.checked_baggage_kg);
                update();
            } catch (e) {}
        }
    });
    root.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-cabin]');
        if (remove) {
            remove.closest('[data-cabin-row]').remove();
            update();
        }
    });
    const addButton = root.querySelector('[data-add-cabin]');
    if (addButton) addButton.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        update();
    });
    update();
})();
</script>
@endsection