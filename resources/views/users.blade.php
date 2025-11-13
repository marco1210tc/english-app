<div>
  <table>
    <thead>
      <tr>
        <th class="px-4">Name</th>
        <th class="px-4">Email</th>
        <th class="px-4">role</th>
        <th class="px-4">Password</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
      <tr class="border-b">
        <td class="p-4">
          {{ $user->name }}
        </td>
        <td class="p-4">
          {{ $user->email }}
        </td>
        <td class="p-4">
          {{ $user->role }}
        </td>
        <td class="p-4">
          password
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>