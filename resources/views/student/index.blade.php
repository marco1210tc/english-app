@extends('layouts.student')

@section('content')
<div class="max-w-6xl mx-auto p-4">

  <!-- Subtítulo -->
  <p class="text-center text-gray-600 mt-6 text-lg">
    Today you have <span class="text-yellow-600 font-semibold">2 pendings</span>. Here we go!
  </p>

  <!-- Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">

    <!-- Ranking -->
    <div class="bg-primary-10 rounded-2xl p-6 text-center shadow-sm">
      <div class="flex justify-center mb-2"> 
        <img width="64" height="64" src="{{ asset('icons/trophy-64.png')}}" alt="ranking icon">
      </div>
      <h3 class="text-gray-700 font-semibold mb-1 text-lg">YOUR RANKING</h3>
      <p class="text-gray-600">You are in the <span class="text-green-600 font-bold">#5 position</span> in your class
      </p>

      <div class="mt-[4rem]">
        <x-button>
          <a href="{{ route('ranking') }}"> CHECK THE TOP LIST </a>
        </x-button>
      </div>
    </div>

    <!-- Homework -->
    <div class="bg-primary-10 rounded-2xl p-6 shadow-sm">
      <div class="flex items-center gap-3 mb-4">
        <div class="bg-yellow-400 p-3 rounded-xl"><img width="32" height="32" src="{{ asset('icons/scroll-50.svg') }}" alt="homework icon"> </div>
        <h3 class="text-neutral-700 font-semibold text-lg">HOMEWORK</h3>
      </div>
      <div class="bg-red-500 text-white rounded-full px-4 py-1 inline-block mb-4 font-semibold">2 Pendings</div>

      <div class="bg-white rounded-xl p-3 shadow-sm mb-3 flex justify-between items-center">
        <div>
          <p class="font-medium text-gray-700">Game: Memory</p>
          <p class="text-sm text-gray-500">Elapse: 4 min</p>
        </div>
        <a href="challenge1.html" class="text-primary-600 font-semibold">Go to ></a>
      </div>

      <div class="bg-white rounded-xl p-3 shadow-sm mb-3 flex justify-between items-center">
        <div>
          <p class="font-medium text-gray-700">Activity: Listen and Repeat</p>
          <p class="text-sm text-gray-500">Elapse: 3 min</p>
        </div>
        <a href="challenge1.html" class="text-primary-600 font-semibold">Go to ></a>
      </div>

      <p class="text-sm text-gray-500 mt-3">Finish date: Saturday 28</p>
    </div>

    <!-- Challenges -->
    <div class="bg-primary-10 rounded-2xl p-6 shadow-sm">
      <div class="flex items-center gap-3 mb-4">
        <div class="bg-primary-500 p-3 rounded-xl"> <img width="32" height="32" src="{{ asset('icons/rocket-64.png')}}" alt="challenges icon"></div>
        <h3 class="text-gray-700 font-semibold text-lg">CHALLENGES</h3>
      </div>
      <p class="text-gray-600 mb-4">Games, songs and more</p>

      <div class="bg-white rounded-xl p-3 shadow-sm mb-3 flex justify-between items-center">
        <div>
          <p class="font-medium text-gray-700">Songs</p>
          <p class="text-sm text-gray-500">Sing to learn</p>
        </div>
        <a href="#" class="text-primary-600 font-semibold">Explore</a>
      </div>

      <div class="bg-white rounded-xl p-3 shadow-sm mb-5 flex justify-between items-center">
        <div>
          <p class="font-medium text-gray-700">Stories</p>
          <p class="text-sm text-gray-500">Short stories</p>
        </div>
        <a href="#" class="text-primary-600 font-semibold">Explore </a>
      </div>

      <x-button> 
        <a href="#">
          Explore More
        </a>
      </x-button>
    </div>

  </div>
</div>
@endsection