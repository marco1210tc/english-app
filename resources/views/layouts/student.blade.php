@include('partials.head')

<body class="bg-white">
  <div class="min-h-screen"> 
    @include('partials.student.header')
    @yield('content')
  </div>
  @include('partials.student.footer')
</body>