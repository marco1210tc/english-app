@include('partials.head')

<div class="flex bg-gray-50 text-gray-800">
  @include('layouts.teacher.sidebar')
  <!-- Main content -->
  <main class="flex-1 ml-64 p-8 space-y-8 overflow-y-auto">

    @yield('content')
    
  </main>
</div>

{{-- @include('partials.teacher.footer') --}}