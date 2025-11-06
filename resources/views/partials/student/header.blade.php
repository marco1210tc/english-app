<!-- Header -->
<div class="bg-white shadow-md rounded-2xl flex justify-between items-center p-4">
  <div class="flex items-center gap-4">
    <div class="bg-primary-500 rounded-xl p-3">
      <img src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="owl" class="w-10 h-10">
      <div>
        {{-- {{ auth()->user()->initials() }} --}}
      </div>
    </div>
    <div>
      <p class="text-sm text-gray-500">Virtual Classroom</p>

      <h2 class="text-xl font-bold text-gray-800">
        @isset(auth()->user()->name )
        Hello, {{ auth()->user()->name }}!
        @else
        Welcome, it seems your are new!
        @endisset
      </h2>

      <p class="text-sm text-primary-600 font-semibold">I'm Tindell the Dog</p>
    </div>

  </div>

  <div class="">
    @if (auth()->user())
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">Logout</button>
    </form>
    @else
     <span class="text-neutral-400"> Not loggued </span>
    @endif
  </div>

  <div class="text-right">
    <p class="text-gray-600 text-sm">Your progress</p>
    <div class="w-40 bg-gray-200 rounded-full h-2 mt-1">
      <div class="bg-primary-500 h-2 rounded-full w-[35%]"></div>
    </div>
    <p class="text-gray-700 font-semibold text-sm mt-1">35/100</p>
  </div>
</div>