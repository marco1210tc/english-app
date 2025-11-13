@extends('layouts.teacher')

@section('content')
    <div>
      <h2>Classroom {{ $group->name }}</h2>
      <div>
        <p>Teacher: {{ $group->teacher->name }}</p>
        <p>Level: {{ $group->grade_level }}</p>
        <p>Students: {{ $group->students->count() }}</p>
        <p>Created at: {{ $group->created_at->format('d-m-Y') }}</p>
        <p>Updated at: {{ $group->updated_at->format('d-m-Y') }}</p>
      </div>

      <h2 class="mt-5 font-semibold"> List of students: </h2>
      <ul>
        @foreach ($group->students as $student)
          <table>
            <tr>
              <td>{{ $student->id }}</td>
              <td>{{ $student->name }}</td>
            </tr>
          </table>
        @endforeach
      </ul>
    </div>

@endsection