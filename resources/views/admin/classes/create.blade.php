@extends('layouts.admin')

@section('content')
<x-breadcrumb :links="['Clases' => route('admin.classes.index'), 'Registrar' => '']" />
<div class="mb-6">
    <a href="{{ route('admin.classes.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
        <i class="fa-solid fa-arrow-left mr-1"></i> Volver a Clases
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Crear Nueva Clase</h1>

    <form action="{{ route('admin.classes.store') }}" method="POST" id="classForm">
        @csrf

        {{-- Nombre --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Nombre de la Clase <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                placeholder="Ej: Yoga Matutino, CrossFit Avanzado...">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Descripción --}}
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea id="description" name="description" rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Describe brevemente la clase...">{{ old('description') }}</textarea>
        </div>

        {{-- Entrenador --}}
        <div class="mb-4">
            <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-1">Entrenador</label>
            <select id="trainer_id" name="trainer_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">— Sin asignar —</option>
                @foreach($trainers as $trainer)
                    <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                        {{ $trainer->name }}{{ $trainer->specialty ? ' — ' . $trainer->specialty : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Selector de Horario --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Horario <span class="text-red-500">*</span>
                <span class="text-xs text-gray-400 font-normal ml-1">Selecciona los días y define el horario de cada uno</span>
            </label>

            @error('schedule')
                <p class="text-red-500 text-xs mb-2">{{ $message }}</p>
            @enderror

            {{-- Días de la semana --}}
            <div class="flex flex-wrap gap-2 mb-4" id="dayButtons">
                @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $day)
                <button type="button"
                    data-day="{{ $day }}"
                    class="day-btn px-3 py-2 rounded-lg border-2 text-sm font-semibold transition-all duration-200
                           border-gray-300 text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50">
                    {{ $day }}
                </button>
                @endforeach
            </div>

            {{-- Horarios por día seleccionado --}}
            <div id="timeSlots" class="space-y-3"></div>

            {{-- Campo oculto con el JSON --}}
            <input type="hidden" name="schedule" id="scheduleInput">
        </div>

        {{-- Cupo --}}
        <div class="mb-4">
            <label for="max_capacity" class="block text-sm font-medium text-gray-700 mb-1">
                Cupo Máximo <span class="text-red-500">*</span>
            </label>
            <input type="number" id="max_capacity" name="max_capacity" value="{{ old('max_capacity', 20) }}" min="1"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('max_capacity') border-red-500 @enderror">
            @error('max_capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Estado --}}
        <div class="mb-6">
            <label class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Clase activa (visible para consulta)</span>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.classes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                <i class="fa-solid fa-save mr-2"></i> Guardar
            </button>
        </div>
    </form>
</div>

<style>
.day-btn.active {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: white;
}
.day-btn.active:hover {
    background-color: #4338ca;
    border-color: #4338ca;
}
.time-slot-card {
    animation: slideDown 0.2s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
const DAY_ORDER = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
let selectedDays = {}; // { day: {start, end} }

// Init from old() if validation failed
const oldSchedule = @json(is_array(old('schedule')) ? old('schedule') : (old('schedule') ? json_decode(old('schedule'), true) : []));
if (Array.isArray(oldSchedule) && oldSchedule.length > 0) {
    oldSchedule.forEach(item => {
        selectedDays[item.day] = { start: item.start, end: item.end };
    });
}

function renderDays() {
    // Update button styles
    document.querySelectorAll('.day-btn').forEach(btn => {
        const day = btn.dataset.day;
        if (selectedDays[day] !== undefined) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Render time slot cards in order
    const container = document.getElementById('timeSlots');
    container.innerHTML = '';

    DAY_ORDER.forEach(day => {
        if (selectedDays[day] === undefined) return;
        const item = selectedDays[day];

        const card = document.createElement('div');
        card.className = 'time-slot-card flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3';
        card.innerHTML = `
            <div class="w-24 text-sm font-semibold text-indigo-700">${day}</div>
            <div class="flex items-center gap-2 flex-1">
                <i class="fa-solid fa-clock text-indigo-400 text-xs"></i>
                <label class="text-xs text-gray-500">Inicio</label>
                <input type="time" value="${item.start || '07:00'}" step="900"
                    class="border border-indigo-200 rounded px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    onchange="updateTime('${day}', 'start', this.value)">
                <span class="text-gray-400 text-sm">→</span>
                <label class="text-xs text-gray-500">Fin</label>
                <input type="time" value="${item.end || '08:00'}" step="900"
                    class="border border-indigo-200 rounded px-2 py-1 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    onchange="updateTime('${day}', 'end', this.value)">
            </div>
            <button type="button" onclick="removeDay('${day}')"
                class="text-red-400 hover:text-red-600 ml-2 text-sm" title="Quitar día">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        container.appendChild(card);
    });

    buildHidden();
}

function toggleDay(day) {
    if (selectedDays[day] !== undefined) {
        delete selectedDays[day];
    } else {
        selectedDays[day] = { start: '07:00', end: '08:00' };
    }
    renderDays();
}

function removeDay(day) {
    delete selectedDays[day];
    renderDays();
}

function updateTime(day, field, value) {
    if (selectedDays[day]) {
        selectedDays[day][field] = value;
        buildHidden();
    }
}

function buildHidden() {
    const arr = DAY_ORDER
        .filter(d => selectedDays[d] !== undefined)
        .map(d => ({ day: d, start: selectedDays[d].start, end: selectedDays[d].end }));
    document.getElementById('scheduleInput').value = JSON.stringify(arr);
}

// Bind click handlers
document.querySelectorAll('.day-btn').forEach(btn => {
    btn.addEventListener('click', () => toggleDay(btn.dataset.day));
});

// Validate before submit
document.getElementById('classForm').addEventListener('submit', function(e) {
    if (Object.keys(selectedDays).length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Horario requerido',
            text: 'Selecciona al menos un día con su horario.',
        });
        return;
    }
    buildHidden();
});

// Render on load (for old() restore)
renderDays();
</script>
@endsection
