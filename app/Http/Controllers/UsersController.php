<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::all();
        return view('users', compact('users'));
    }
}
