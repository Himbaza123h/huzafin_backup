<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->success("User created Successfully!");
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->error([], "Invalid credentials", Response::HTTP_UNAUTHORIZED);
        }

        return response()->success(["token" => $user->createToken($request->email)->plainTextToken, "user" => $user]);
    }
    public function updateProfile(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->error([], "User not found", Response::HTTP_NOT_FOUND);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->update();

        return response()->success("Profile updated Successfully!");
    }
    public function changePassword(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->error([], "User not found", Response::HTTP_NOT_FOUND);
        }
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->error([], "Old password incorrect!", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (Hash::check($request->new_password, $user->password)) {
            return response()->error([], "New password Can't be the same as the old password!", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user->password = Hash::make($request->new_password);
        $user->update();

        return response()->success("Password updated Successfully!");
    }
    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->error([], "User not found", Response::HTTP_NOT_FOUND);
        }
        $user->delete();

        return response()->success("User deleted Successfully!");
    }
}
