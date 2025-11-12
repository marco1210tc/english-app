@extends('layouts.teacher')

@section('content')
<!-- Banner -->
<section class="bg-primary-500 text-white rounded-2xl p-8 flex justify-between items-center shadow-md">
    <div>
        <p class="text-sm uppercase">Online Course</p>
        <h2 class="text-2xl font-bold leading-tight">
            Sharpen Your Skills With <br> Professional Online Courses
        </h2>
    </div>
</section>

@php
$courses = [
['title' => 'Class B1 Room A45', 'level' => 'Listening', 'image' => 'https://placehold.co/250x140',
'instructor' => 'Prashant Kumar Singh'],
['title' => 'Class A1 Room 542', 'level' => 'English B1', 'image' => 'https://placehold.co/250x140',
'instructor' => 'Prashant Kumar Singh'],
['title' => 'Class Kinder Room 654', 'level' => 'English A+', 'image' =>
'https://placehold.co/250x140', 'instructor' => 'Prashant Kumar Singh'],
];

$colleagues = [
['name' => 'Prashant Kumar Singh', 'date' => '25/2/2023', 'course' => 'Understanding Concept Of English'],
['name' => 'Ravi Kumar', 'date' => '25/2/2023', 'course' => 'Understanding Concept Of English'],
];
@endphp

<!-- Courses -->
<section>
    <h3 class="text-xl font-semibold mb-4">Your Courses</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($courses as $course)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition">
            <img src="{{ $course['image'] }}" alt="course" class="w-full h-40 object-cover">
            <div class="p-4">
                <p class="text-sm text-primary-600 font-medium">{{ strtoupper($course['level']) }}</p>
                <h4 class="text-gray-800 font-semibold">{{ $course['title'] }}</h4>
                <p class="text-gray-500 text-sm mt-1">{{ $course['instructor'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Colleagues -->
<section>
    <h3 class="text-xl font-semibold mb-4">Your Colleagues</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-2xl shadow-md text-left">
            <thead class="bg-gray-100 text-sm text-gray-600">
                <tr>
                    <th class="py-3 px-4">Instructor Name & Date</th>
                    <th class="py-3 px-4">Course Type</th>
                    <th class="py-3 px-4">Course Title</th>
                    <th class="py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach ($colleagues as $c)
                <tr class="border-t hover:bg-gray-50">
                    <td class="py-3 px-4">
                        {{ $c['name'] }}
                        <span class="text-gray-400 ml-2">{{ $c['date'] }}</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="bg-green-100 text-primary-600 text-xs px-2 py-1 rounded-md">ENGLISH</span>
                    </td>
                    <td class="py-3 px-4">{{ $c['course'] }}</td>
                    <td class="py-3 px-4">
                        <button class="text-blue-500 hover:underline">Show Details</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection