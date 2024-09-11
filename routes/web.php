<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


//redirect user to this route if they are not logged in
Volt::route('/login', 'login')->name('login');
Volt::route('/register', 'register')->name('register');

//Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

//protected routes
Route::middleware('auth')->group(function () {
    Volt::route('/', 'index');  // Home
    Volt::route('/users', 'users.index');   //User(list)
    Volt::route('/users/create', 'users.create');   // User(create)
    Volt::route('/users/{user}/edit', 'users.edit');    //User(edit)
});
