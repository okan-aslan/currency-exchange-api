<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Register a new user.
     *
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            $token = $user->createToken('token')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token
            ], 'User registered successfully');
        } catch (Exception $e) {
            return $this->error(null, 'Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Authenticate a user and issue a token.
     *
     * @param LoginUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginUserRequest $request)
    {
        try {
            $data = $request->validated();

            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                $this->error(null, "The provided credentials are incorrect.", 500);
            }

            $token = $user->createToken('token')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'User logged in successfully.');
        } catch (\Exception $e) {
            return $this->error(null, 'Login failed :' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        try {
            return $this->success(new UserResource($request->user()), 'User profile retrieved successfully');
        } catch (Exception $e) {
            return $this->error(null, 'Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout the authenticated user by revoking all tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->success(null, 'User logged out successfully');
        } catch (Exception $e) {
            return $this->error(null, 'Logout failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete the authenticated user's account and revoke all tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();
            $user->delete();

            return $this->success(null, 'User account deleted successfully');
        } catch (Exception $e) {
            return $this->error(null, 'Account deletion failed: ' . $e->getMessage(), 500);
        }
    }
}
