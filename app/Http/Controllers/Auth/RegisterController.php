<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login'; // Redirect to the login page

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z0-9\s]*$/'], // Name cannot start with special characters, allows spaces
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Ensures email is in proper format and unique
            'phone' => ['required', 'regex:/^9[0-9]{8}$/'], // Phone number validation for Ethiopian numbers starting with 9xxxxxxx
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Password validation
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => '+251' . ltrim($data['phone'], '0'), // Add +251 to the phone number and remove leading 0
            'password' => Hash::make($data['password']),
            'remainingReserve' => 0, // Set the remainingReserve to 0 by default
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request
        $this->validator($request->all())->validate();

        // Create the user
        $user = $this->create($request->all());

        // Redirect to the login page with a success message
        return redirect($this->redirectTo)
            ->with('success', 'Registration successful! Please log in.');
    }
}
