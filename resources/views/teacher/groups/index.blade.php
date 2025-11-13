@extends('layouts.teacher')

@section('content')
{{-- @php
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
@endphp --}}

<div class="text-neutral-400">
  Groups
</div>

<div class="w-full p-4">

  <div class="my-5">
    <a href="{{ route('groups.create') }}" class="px-4 py-3 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600">
      + Create new group
    </a>
  </div>

  <div class="overflow-x-auto w-full">
    <table class="min-w-full border border-gray-200 bg-white rounded-lg shadow-sm">
      <thead class="bg-gray-100 text-gray-700 uppercase text-sm">
        <tr>
          <th class="py-3 px-4 text-left border-b">Teacher</th> <!--<cabecera temporal para testear -->
            <th class="py-3 px-4 text-left border-b">Group Name</th>
            <th class="py-3 px-4 text-left border-b">Level</th>
            <th class="py-3 px-4 text-left border-b">Students enrolled</th>
          <th class="py-3 px-4 text-left border-b">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 text-gray-800">
        @foreach ($groups as $group)
        <tr class="hover:bg-gray-50 transition">
          <td class="py-3 px-4">{{ $group->teacher->name }}</td>
          <td class="py-3 px-4">{{ $group['name'] }}</td>
          <td class="py-3 px-4">{{ $group['grade_level'] }}</td>
          <td class="py-3 px-4">{{ $group->students->count() }}</td>
          <td class="py-3 px-4">
            <a href="{{ route('groups.show', $group->id) }}" class="text-green-600 hover:underline">Show Details</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>

@endsection

