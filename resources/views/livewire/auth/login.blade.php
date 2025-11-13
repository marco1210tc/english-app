<div class="flex items-center justify-center min-h-screen p-4">
  <div class="flex w-full max-w-4xl bg-white shadow-lg rounded-2xl overflow-hidden">

    <div class="hidden md:flex w-1/2 bg-primary-500 p-12 flex-col justify-center items-center">
      <div class="relative w-full">
        <h1 class="text-4xl font-bold text-white mb-10 text-center">Welcome to English App</h1>

        <div class="relative h-64">
          <div class="absolute top-0 left-10 transform -rotate-6 transition-transform hover:scale-105">
            <div class="p-2 bg-white rounded-xl shadow-lg">
              <img src="https://mma.prnewswire.com/media/1087274/Studycat_Logo.jpg?p=facebook" alt="Student 1"
                class="w-40 h-28 object-cover rounded-md border-4 border-primary-300">
            </div>
          </div>
          <div class="absolute bottom-0 right-10 transform rotate-6 transition-transform hover:scale-105">
            <div class="p-2 bg-white rounded-xl shadow-lg">
              <img
                src="https://images.pexels.com/photos/4145347/pexels-photo-4145347.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                alt="Student 2" class="w-40 h-28 object-cover rounded-md border-4 border-primary-300">
            </div>
          </div>

          <svg class="absolute top-16 right-8 w-10 h-10 text-yellow-400 opacity-80" fill="currentColor"
            viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.959a1 1 0 00.95.69h4.168c.969 0 1.371 1.24.588 1.81l-3.372 2.45a1 1 0 00-.364 1.118l1.286 3.959c.3.921-.755 1.688-1.54 1.118l-3.372-2.45a1 1 0 00-1.175 0l-3.372 2.45c-.784.57-1.838-.197-1.54-1.118l1.286-3.959a1 1 0 00-.364-1.118L2.053 9.386c-.783-.57-.38-1.81.588-1.81h4.168a1 1 0 00.95-.69L9.049 2.927z" />
          </svg>
          <svg class="absolute bottom-12 left-4 w-10 h-10 text-yellow-400 opacity-80" fill="currentColor"
            viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.959a1 1 0 00.95.69h4.168c.969 0 1.371 1.24.588 1.81l-3.372 2.45a1 1 0 00-.364 1.118l1.286 3.959c.3.921-.755 1.688-1.54 1.118l-3.372-2.45a1 1 0 00-1.175 0l-3.372 2.45c-.784.57-1.838-.197-1.54-1.118l1.286-3.959a1 1 0 00-.364-1.118L2.053 9.386c-.783-.57-.38-1.81.588-1.81h4.168a1 1 0 00.95-.69L9.049 2.927z" />
          </svg>
        </div>
      </div>
    </div>


    <div class="w-full md:w-1/2 p-8 md:p-12 flex items-center justify-center">
      <div class="w-full max-w-sm">
        <h2 class="text-2xl font-bold text-neutral-800 mb-8 text-center uppercase">Please Login</h2>

        <form wire:submit.prevent="login" class="space-y-6">

          <div>
            <label for="email" class="block text-neutral-600 text-sm font-bold tracking-wider mb-2">USERNAME</label>
            <input wire:model="email" type="text" placeholder="Insert your username"
              class="w-full px-4 py-3 bg-primary-10 border-2 border-primary-200 rounded-xl focus:outline-none focus:border-primary-500 transition">
            <p class="text-xs text-red-600 mt-1">{{ $errors->first('email') }}</p>
          </div>

          <div>
            <label for="password" class="block text-neutral-600 text-sm font-bold tracking-wider mb-2">PASSWORD</label>
            <input wire:model="password" type="password" placeholder="Insert your password"
              class="w-full px-4 py-3 bg-primary-10 border-2 border-primary-200 rounded-xl focus:outline-none focus:border-primary-500 transition">
            <p class="text-xs text-red-600 mt-1"> {{ $errors->first('password') }} </p>
          </div>

          <x-button>
            Login
          </x-button>

        </form>
      </div>
    </div>
  </div>
</div>