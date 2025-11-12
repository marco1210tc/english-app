<!-- Sidebar -->
<aside class="fixed top-0 left-0 h-screen w-64 bg-white border-r flex flex-col p-6">
  <!-- Profile -->
  <div class="text-center mb-8">
    <img src="https://placehold.co/80" class="rounded-full mx-auto mb-3" alt="profile">
    <h4 class="font-semibold text-gray-800">Good Morning Teacher</h4>
    <p class="text-gray-500 text-sm">Take a look at your classroom</p>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-4 text-gray-600">
    <a href="{{ route('teacher') }}" class="flex items-center gap-3 text-primary-600 font-medium">
      <i class="bi bi-house"></i> Dashboard
    </a>
    <a href="{{ route('groups') }}" class="flex items-center gap-3 hover:text-primary-600">
      <i class="bi bi-book"></i> Groups
    </a>
    <a href="#" class="flex items-center gap-3 hover:text-primary-600">
      <i class="bi bi-journal-text"></i> Lesson
    </a>
    <a href="#" class="flex items-center gap-3 hover:text-primary-600">
      <i class="bi bi-list-task"></i> Tasks
    </a>
    <a href="#" class="flex items-center gap-3 hover:text-primary-600">
      <i class="bi bi-people"></i> Students
    </a>
  </nav>

  <!-- Footer Sidebar Options -->
  <div class="mt-auto pt-6 border-t border-gray-200 space-y-3">
    <a href="#" class="flex items-center gap-3 text-gray-600 hover:text-primary-600">
      <i class="bi bi-gear"></i> Settings
    </a>
    <form action="{{ route('logout') }}" method="POST" class="flex items-center gap-3 text-red-500 hover:text-red-600">
      @csrf
      <button type="submit" class="flex items-center gap-2 font-medium">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </form>
  </div>
</aside>