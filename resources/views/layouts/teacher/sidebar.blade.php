<!-- Sidebar -->
<aside class="fixed top-0 left-0 h-screen w-64 bg-white border-r flex flex-col p-6">
<!-- Profile con arco verde -->
<div class="flex flex-col items-center mt-3 mb-6">
  <div class="relative w-32 h-32 sm:w-36 sm:h-36 md:w-40 md:h-40">
    <!-- Fondo gris -->
    <svg class="absolute top-0 left-0 w-full h-full transform -rotate-90 text-primary-500">
      <circle
        cx="50%"
        cy="50%"
        r="40%"
        stroke="#E5E7EB"
        stroke-width="10"
        fill="none"
      />
      <!-- Arco verde (más corto) -->
      <circle
        cx="50%"
        cy="50%"
        r="40%"
        stroke="currentColor"
        stroke-width="10"
        stroke-linecap="round"
        fill="none"
        stroke-dasharray="283"
        stroke-dashoffset="175" 
      />
    </svg>

    <!-- Imagen centrada -->
    <div class="absolute inset-0 flex items-center justify-center">
      <img
        src="https://placehold.co/100"
        alt="profile"
        class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-full object-cover border-4 border-white"
      />
    </div>
  </div>

  <!-- Texto debajo -->
  <h4 class="mt-4 font-semibold text-gray-800 text-center text-base sm:text-lg">
    Good Morning Teacher
  </h4>
  <p class="text-gray-500 text-sm sm:text-base text-center">
    Take a look at your classroom
  </p>
</div>



  <!-- Navigation -->
  <nav class="flex-1 space-y-4 text-gray-600">
    <a href="{{ route('teacher') }}" class="flex items-center gap-3 text-primary-600 font-medium">
      <i class="bi bi-house"></i> Dashboard
    </a>
    <a href="{{ route('groups.index') }}" class="flex items-center gap-3 hover:text-primary-600">
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
    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 text-gray-600 hover:text-black">

      <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="20" height="20">
        <path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z" />
        <path
          d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3A3,3,0,0,0,9,3v.513A8.977,8.977,0,0,0,6.152,5.159L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.646l.447.258a3,3,0,0,0,3-5.2Zm-2.548-3.776a7.048,7.048,0,0,1,0,3.75,1,1,0,0,0,.464,1.133l1.084.626a1,1,0,0,1-1,1.733l-1.086-.628a1,1,0,0,0-1.215.165,6.984,6.984,0,0,1-3.243,1.875,1,1,0,0,0-.751.969V21a1,1,0,0,1-2,0V19.748a1,1,0,0,0-.751-.969A6.984,6.984,0,0,1,7.006,16.9a1,1,0,0,0-1.215-.165l-1.084.627a1,1,0,1,1-1-1.732l1.084-.626a1,1,0,0,0,.464-1.133,7.048,7.048,0,0,1,0-3.75A1,1,0,0,0,4.79,8.992L3.706,8.366a1,1,0,0,1,1-1.733l1.086.628A1,1,0,0,0,7.006,7.1a6.984,6.984,0,0,1,3.243-1.875A1,1,0,0,0,11,4.252V3a1,1,0,0,1,2,0V4.252a1,1,0,0,0,.751.969A6.984,6.984,0,0,1,16.994,7.1a1,1,0,0,0,1.215.165l1.084-.627a1,1,0,1,1,1,1.732l-1.084.626A1,1,0,0,0,18.746,10.125Z" />
      </svg>

      Settings
    </a>
    
    <form action="{{ route('logout') }}" method="POST" class="flex items-center gap-3 text-red-500 hover:text-red-600">
      @csrf
      <button class="flex items-center gap-3 font-medium">
        <span class="h-full">
          <svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="20"
            height="20" fill='currentColor' stroke='currentColor'>
            <path
              d="M9,23c0,.553-.448,1-1,1h-3.5c-2.481,0-4.5-2.019-4.5-4.5V4.5C0,2.019,2.019,0,4.5,0h3.5c.552,0,1,.448,1,1s-.448,1-1,1h-3.5c-1.378,0-2.5,1.122-2.5,2.5v15c0,1.379,1.122,2.5,2.5,2.5h3.5c.552,0,1,.447,1,1Zm13.864-8.183c-.007,.007-6.768,6.368-6.768,6.368-.561,.562-1.299,.861-2.064,.861-.385,0-.778-.076-1.158-.232-1.134-.467-1.843-1.518-1.849-2.744v-2.074l-2.037-.022c-2.18,0-3.963-1.782-3.963-3.974v-1.982c0-2.191,1.783-3.974,3.974-3.974l2,.005v-2.054c.004-1.231,.712-2.283,1.846-2.75,1.135-.467,2.379-.217,3.246,.651l6.746,6.281c1.572,1.574,1.573,4.093,.027,5.641Zm-1.416-4.203l-6.746-6.281c-.457-.457-.953-.299-1.095-.24s-.605,.296-.607,.904v3.053c0,.266-.105,.521-.294,.708s-.487,.272-.708,.292l-3-.007c-1.086,0-1.972,.886-1.972,1.974v1.982c0,1.088,.886,1.974,1.974,1.974l3.037,.034c.548,.006,.989,.452,.989,1v3.058c.003,.603,.467,.841,.609,.899s.639,.215,1.069-.215l6.755-6.356c.758-.769,.754-2.013-.011-2.779Z" />
          </svg>
        </span>
        Logout

      </button>
    </form>
  </div>
</aside>