@extends('layouts.teacher')

@section('content')
<div class="flex min-h-screen bg-gray-50 text-gray-800">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-10">
                <div class="bg-primary-500 text-white font-bold rounded-full w-8 h-8 flex items-center justify-center">E</div>
                <span class="font-semibold text-lg">ENGLISH</span>
            </div>

            <nav class="space-y-4 text-gray-600">
                <a href="#" class="flex items-center gap-3 text-primary-600 font-medium">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 hover:text-primary-600"><i class="bi bi-envelope"></i> Inbox</a>
                <a href="#" class="flex items-center gap-3 hover:text-primary-600"><i class="bi bi-book"></i> Lesson</a>
                <a href="#" class="flex items-center gap-3 hover:text-primary-600"><i class="bi bi-list-task"></i> Task</a>
                <a href="#" class="flex items-center gap-3 hover:text-primary-600"><i class="bi bi-people"></i> Group</a>
            </nav>
        </div>

        <div class="mt-10">
            <a href="#" class="block mb-2 hover:text-primary-600"><i class="bi bi-gear"></i> Settings</a>
            <a href="#" class="text-red-500 font-medium"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8 space-y-8">
        <!-- Banner -->
        <section class="bg-primary-500 text-white rounded-2xl p-8 flex justify-between items-center shadow-md">
            <div>
                <p class="text-sm uppercase">Online Course</p>
                <h2 class="text-2xl font-bold leading-tight">Sharpen Your Skills With <br> Professional Online Courses</h2>
                <button class="mt-4 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800">Join Now</button>
            </div>
        </section>

        @php
            $courses = [
                ['title' => 'Class B1 Room A45', 'level' => 'Listening', 'image' => 'https://via.placeholder.com/250x140', 'instructor' => 'Prashant Kumar Singh'],
                ['title' => 'Class A1 Room 542', 'level' => 'English B1', 'image' => 'https://via.placeholder.com/250x140/ff0', 'instructor' => 'Prashant Kumar Singh'],
                ['title' => 'Class Kinder Room 654', 'level' => 'English A+', 'image' => 'https://via.placeholder.com/250x140/09f', 'instructor' => 'Prashant Kumar Singh'],
            ];

            $colleagues = [
                ['name' => 'Prashant Kumar Singh', 'date' => '25/2/2023', 'course' => 'Understanding Concept Of English'],
                ['name' => 'Ravi Kumar', 'date' => '25/2/2023', 'course' => 'Understanding Concept Of English'],
            ];

            $students = [
                ['name' => 'Prashant Kumar Singh'],
                ['name' => 'Amit Kumar'],
                ['name' => 'Ravi Sharma'],
                ['name' => 'Priya Patel'],
            ];
        @endphp

        <!-- Courses -->
        <section>
            <h3 class="text-xl font-semibold mb-4">Your Courses</h3>
            <div class="grid grid-cols-3 gap-6">
                @foreach ($courses as $course)
                <div class="bg-white rounded-2xl shadow-md overflow-hidden">
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
            <table class="min-w-full bg-white rounded-2xl shadow-md overflow-hidden text-left">
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
                    <tr class="border-t">
                        <td class="py-3 px-4">{{ $c['name'] }} <span class="text-gray-400 ml-2">{{ $c['date'] }}</span></td>
                        <td class="py-3 px-4"><span class="bg-green-100 text-primary-100 text-xs px-2 py-1 rounded-md">ENGLISH</span></td>
                        <td class="py-3 px-4">{{ $c['course'] }}</td>
                        <td class="py-3 px-4"><button class="text-blue-500 hover:underline">Show Details</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>

    <!-- Right sidebar -->
    <aside class="w-80 bg-white border-l p-6 flex flex-col justify-between">
        <div>
            <!-- Profile -->
            <div class="text-center mb-6">
                <img src="https://via.placeholder.com/80" class="rounded-full mx-auto mb-3" alt="profile">
                <h4 class="font-semibold text-gray-800">Good Morning Teacher</h4>
                <p class="text-gray-500 text-sm">Take A Look At Your Classroom</p>
            </div>

            <!-- Graph simulation -->
            <div class="flex justify-center gap-2 mb-8">
                <div class="w-4 bg-green-300 h-16 rounded-md"></div>
                <div class="w-4 bg-green-400 h-20 rounded-md"></div>
                <div class="w-4 bg-primary-500 h-24 rounded-md"></div>
                <div class="w-4 bg-primary-600 h-28 rounded-md"></div>
            </div>

            <!-- Students -->
            <h3 class="text-lg font-semibold mb-3">Students</h3>
            <div class="space-y-3">
                @foreach ($students as $s)
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-2">
                    <div class="flex items-center gap-3">
                        <img src="https://via.placeholder.com/40" class="rounded-full" alt="">
                        <span class="text-sm font-medium">{{ $s['name'] }}</span>
                    </div>
                    <button class="bg-primary-500 text-white px-3 py-1 rounded-md text-sm hover:bg-primary-600">Follow</button>
                </div>
                @endforeach
            </div>
        </div>
    </aside>

</div>
@endsection
