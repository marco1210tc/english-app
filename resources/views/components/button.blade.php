<!-- Contenedor fijo -->
<div class="relative inline-block w-full h-14 text-[1.3rem]">
  <!-- Botón con efecto hover -->
  <button
  class="relative overflow-hidden cursor-pointer w-full uppercase text-white bg-primary-500 font-bold py-3 px-6 rounded-2xl border-b-[6px] border-primary-600 shadow-md transition-[transform,border,box-shadow] duration-90 ease-in-out active:translate-y-[3px] active:border-b-[3px] active:shadow-sm
  before:content-[''] before:absolute before:inset-0 before:bg-gradient-to-r before:from-primary-500 before:via-primary-500 before:to-primary-400 before:-translate-x-full before:transition-transform before:duration-700 hover:before:translate-x-0 before:z-0
  "
  >
  <span class="relative z-10">{{ $slot }}</span>
</button>
</div>



{{-- OTRAS OPCIONES --}}

<!-- Contenedor fijo -->
{{-- <div class="relative inline-block w-full h-14 text-[1.3rem]">
  <!-- Botón con efecto hover -->
  <button
  class="relative overflow-hidden cursor-pointer w-full uppercase text-white bg-primary-500 font-bold py-3 px-6 rounded-2xl border-b-[6px] border-primary-600 shadow-md transition-[transform,border,box-shadow] duration-90 ease-in-out active:translate-y-[3px] active:border-b-[3px] active:shadow-sm
  before:content-[''] before:absolute before:inset-0 before:bg-primary-400 before:translate-x-full before:transition-transform before:duration-500 hover:before:translate-x-0 before:z-0
  "
  >
  <span class="relative z-10">{{ $slot }}</span>
</button>
</div> --}}


{{-- <!-- Contenedor fijo (no se mueve) -->
<div class="relative inline-block w-full h-14 text-[1.3rem]">

  <!-- Botón -->
  <button class="cursor-pointer w-full absolute left-1/2 top-0 -translate-x-1/2
  uppercase text-white bg-primary-500 font-bold py-3 px-6 rounded-2xl
             border-b-[6px] border-primary-600 shadow-md
             transition-[transform,border,box-shadow] duration-90 ease-in-out
             active:translate-y-[3px] active:border-b-[3px] active:shadow-sm">
    {{ $slot }}
  </button>

</div> --}}