@extends('layouts.teacher')

@section('content')
@php
$groups = [
['title' => 'Class B1 Room A45', 'level' => 'Listening', 'image' => 'https://placehold.co/250x140', 'instructor' =>
'Prashant Kumar Singh'],
['title' => 'Class A1 Room 542', 'level' => 'English B1', 'image' => 'https://placehold.co/250x140', 'instructor' =>
'Prashant Kumar Singh'],
['title' => 'Class Kinder Room 654', 'level' => 'English A+', 'image' => 'https://placehold.co/250x140', 'instructor' =>
'Prashant Kumar Singh'],
['title' => 'Class Kinder Room 654', 'level' => 'English A+', 'image' => 'https://placehold.co/250x140', 'instructor' =>
'Prashant Kumar Singh'],
];
@endphp

<div class="text-neutral-400">
  Groups
</div>

<div class="w-full p-4">

  <div class="my-5">
    <button class="px-4 py-3 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600">
      + Create new group
    </button>
  </div>

  <div class="overflow-x-auto w-full">
    <table class="min-w-full border border-gray-200 bg-white rounded-lg shadow-sm">
      <thead class="bg-gray-100 text-gray-700 uppercase text-sm">
        <tr>
          <th class="py-3 px-4 text-left border-b">Title</th>
          <th class="py-3 px-4 text-left border-b">Level</th>
          <th class="py-3 px-4 text-left border-b">Instructor</th>
          <th class="py-3 px-4 text-left border-b">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 text-gray-800">
        @foreach ($groups as $group)
        <tr class="hover:bg-gray-50 transition">
          <td class="py-3 px-4">{{ $group['title'] }}</td>
          <td class="py-3 px-4">{{ $group['level'] }}</td>
          <td class="py-3 px-4">{{ $group['instructor'] }}</td>
          <td class="py-3 px-4">
            <a href="#" class="text-green-600 hover:underline">Show Details</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


{{-- Cards --}}
{{-- <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition mb-4">
  <img src="{{ $group['image'] }}" alt="course" class="w-full h-40 object-cover">
  <div class="p-4">
    <p class="text-sm text-primary-600 font-medium">{{ strtoupper($group['level']) }}</p>
    <h4 class="text-gray-800 font-semibold">{{ $group['title'] }}</h4>
    <p class="text-gray-500 text-sm mt-1">{{ $group['instructor'] }}</p>
  </div>
</div> --}}
</div>

@endsection