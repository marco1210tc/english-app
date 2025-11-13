@extends('layouts.teacher')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
  <div class="w-full max-w-3xl bg-white rounded-3xl shadow-lg p-8">

    <!-- Encabezado -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-semibold text-gray-800">Assign new group</h2>
      <p class="text-gray-500 text-sm mt-1">Write a classname, then add students.</p>
    </div>

    <!-- Formulario -->
    <form id="claseForm" action="{{ route('groups.store') }}" method="POST" class="space-y-8">
      @csrf

      <!-- Profesor ID oculto -->
      <input type="hidden" name="profesorId" value="{{ auth()->user()->id ?? 1 }}">

      <!-- Paso 1: Nombre de la clase -->
      <div>
        <label for="name" class="block text-gray-700 font-medium mb-2">Classroom Name</label>
        <input type="text" id="name" name="name" placeholder="E.g. Advanced - Speaking B2"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none transition placeholder-gray-400">
      </div>
      <!-- Paso 1.1: Nivel de la clase-->
      <div>
        <label for="level" class="block text-gray-700 font-medium mb-2">Grade Level</label>
        <select name="grade_level" id="grade_level"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none transition placeholder-gray-400">
          <option value="0" selected class="text-gray-400">Select a level</option>
          <option value="1">Level 1</option>
          <option value="2">Level 2</option>
          <option value="3">Level 3</option>
          <option value="4">Level 4</option>
        </select>
      </div>

      <!-- Paso 2: Selección de alumnos -->
      <div class="border-t pt-6">
        <div class="flex justify-between items-center mb-3">
          <h3 class="text-gray-700 font-medium">Added Students</h3>
          <span id="studentCount" class="text-sm text-gray-500">0 selected</span>
        </div>

        <!-- Chips de alumnos -->
        <div id="selectedStudents"
          class="flex flex-wrap gap-2 bg-gray-50 p-3 rounded-lg border border-gray-200 min-h-[3rem]">
          <p id="placeholderText" class="text-gray-400 text-sm">There is no students selected</p>
        </div>
      </div>

      <!-- Buscar alumno -->
      <div>
        <h3 class="text-gray-700 font-medium mb-3">Select Students</h3>
        <input type="text" id="searchStudent" placeholder="Search students..."
          class="w-full mb-3 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 placeholder-gray-400 focus:outline-none transition">

        <!-- Lista de alumnos -->
        <div class="max-h-[395px] overflow-y-auto border border-gray-200 rounded-lg divide-y shadow-sm">

          <!-- Ejemplo de alumnos -->
          {{-- @php
          $students = [
          ['id' => 1, 'name' => 'Ana Torres', 'img' => 'https://randomuser.me/api/portraits/women/1.jpg'],
          ['id' => 2, 'name' => 'Luis Gutiérrez', 'img' => 'https://randomuser.me/api/portraits/men/11.jpg'],
          ['id' => 3, 'name' => 'Sofía Ramírez', 'img' => 'https://randomuser.me/api/portraits/women/44.jpg'],
          ['id' => 4, 'name' => 'Pedro Castillo', 'img' => 'https://randomuser.me/api/portraits/men/22.jpg'],
          ['id' => 5, 'name' => 'Valeria Díaz', 'img' => 'https://randomuser.me/api/portraits/women/66.jpg'],
          ['id' => 6, 'name' => 'Solange Díaz', 'img' => 'https://randomuser.me/api/portraits/women/62.jpg'],
          ['id' => 7, 'name' => 'Sofía Ramírez', 'img' => 'https://randomuser.me/api/portraits/women/44.jpg'],
          ['id' => 8, 'name' => 'Pedro Castillo', 'img' => 'https://randomuser.me/api/portraits/men/22.jpg'],
          ['id' => 9, 'name' => 'Valeria Díaz', 'img' => 'https://randomuser.me/api/portraits/women/66.jpg'],
          ['id' => 10, 'name' => 'Solange Díaz', 'img' => 'https://randomuser.me/api/portraits/women/62.jpg'],
          ];
          @endphp --}}

          @foreach ($students as $student)
          <div class="student-item flex items-center justify-between p-3 hover:bg-primary-100 transition rounded-lg">
            <div class="flex items-center space-x-3">
              <img src="{{ $student['img'] }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
              <span class="text-gray-700 font-medium">{{ $student['name'] }}</span>
            </div>
            <button type="button"
              class="add-btn flex items-center space-x-1 text-primary-600 font-medium hover:text-primary-700 transition"
              data-id="{{ $student['id'] }}" data-name="{{ $student['name'] }}" data-img="{{ $student['img'] }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span> Add </span>
            </button>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Inputs ocultos -->
      <div id="hiddenInputs"></div>

      <!-- Botones -->
      <div class="flex justify-end space-x-4 pt-4">
        <button type="reset"
          class="px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition">
          Cancelar
        </button>
        <button type="submit" id="saveBtn"
          class="px-5 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white font-semibold flex items-center space-x-2 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span> Save Group </span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Animaciones Tailwind -->
<style>
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }

    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  .animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
  }
</style>

<!-- Script UX -->
<script>
  /* --------- Elementos globales --------- */
  const addButtons = document.querySelectorAll('.add-btn');
  const selectedContainer = document.getElementById('selectedStudents');
  const hiddenInputs = document.getElementById('hiddenInputs');
  const studentCount = document.getElementById('studentCount');

  /* --------- Helpers --------- */
  const removePlaceholderIfExists = () => {
    const ph = document.getElementById('placeholderText');
    if (ph) ph.remove();
  };

  const ensurePlaceholder = () => {
    // Si ya existe placeholder no hacemos nada
    if (document.getElementById('placeholderText')) return;

    const p = document.createElement('p');
    p.id = 'placeholderText';
    p.className = 'text-gray-400 text-sm';
    p.textContent = 'No one added yet';
    selectedContainer.appendChild(p);
  };

  const updateCount = () => {
    const count = selectedContainer.querySelectorAll('.student-chip').length;
    studentCount.textContent = `${count} selected${count !== 1 ? 's' : ''}`;
  };

  /* Inicializar (en caso de que no haya chips al cargar) */
  if (!selectedContainer.querySelector('.student-chip')) ensurePlaceholder();
  updateCount();

  /* --------- Buscar alumno (filtro) --------- */
  document.getElementById('searchStudent').addEventListener('input', e => {
    const term = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.student-item').forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(term) ? 'flex' : 'none';
    });
  });

  /* --------- Agregar alumnos --------- */
  addButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const name = btn.dataset.name;
      const img = btn.dataset.img;

      // Si ya fue agregado, no repetir
      if (document.getElementById(`student-${id}`)) return;

      // Quitar cualquier placeholder actual
      removePlaceholderIfExists();

      // Crear chip
      const chip = document.createElement('div');
      chip.id = `student-${id}`;
      chip.className = 'student-chip flex items-center bg-primary-500 text-gray-900 px-3 py-1 rounded-full shadow-sm animate-fadeIn';
      chip.innerHTML = `
        <img src="${img}" class="w-6 h-6 rounded-full mr-2 border border-gray-200">
        <span class="mr-2">${name}</span>
        <button type="button" class="remove-chip text-primary-700 hover:text-red-600 font-bold" data-remove="${id}" aria-label="Delete ${name}">&times;</button>
      `;
      selectedContainer.appendChild(chip);

      // Crear input oculto
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'alumnos[]';
      input.value = id;
      input.id = `input-${id}`;
      hiddenInputs.appendChild(input);

      // Cambiar estado del botón "Agregar"
      btn.innerHTML = '<span class="text-gray-400">Added ✓</span>';
      btn.disabled = true;
      btn.classList.add('cursor-not-allowed');

      updateCount();

      // Añadir handler de eliminación del chip (delegado por elemento del chip)
      const removeBtn = chip.querySelector('.remove-chip');
      removeBtn.addEventListener('click', () => {
        // Eliminar chip y input
        chip.remove();
        const inputEl = document.getElementById(`input-${id}`);
        if (inputEl) inputEl.remove();

        // Restaurar estado del botón original
        btn.disabled = false;
        btn.classList.remove('cursor-not-allowed');
        btn.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Add</span>
        `;

        // Si ya no quedan chips, asegurar placeholder (pero sin duplicarlo)
        if (selectedContainer.querySelectorAll('.student-chip').length === 0) {
          ensurePlaceholder();
        }

        updateCount();
      });
    });
  });

  /* --------- Envío: mostrar loading --------- */
  document.getElementById('claseForm').addEventListener('submit', e => {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = `
      <svg class="animate-spin w-5 h-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 4z"/>
      </svg>
      <span>Saving...</span>
    `;
  });
</script>

@endsection