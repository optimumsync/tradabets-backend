<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\User; // Ensure this is the correct path to your User model
use Illuminate\Support\Facades\Auth; // Use Auth facade for clarity with JWT

class UserProfileAPIController extends Controller
{
    public function __construct()
    {
        // This constructor can be used to apply middleware to all methods in this controller.
        // It's often cleaner to apply middleware in routes/api.php for better visibility,
        // but applying it here works too.
        // If applying here, ensure the 'jwt.auth' middleware is correctly registered in Kernel.php.
        // For example:
        // $this->middleware('jwt.auth'); // Applies to all methods in this controller
        // OR
        // $this->middleware('jwt.auth', ['except' => ['somePublicMethod']]); // Applies to all except 'somePublicMethod'
    }

    /**
     * Get the authenticated user's profile.
     * This method assumes it's protected by the 'jwt.auth' middleware.
     *
     * @param int|null $userId Optional ID of the user to retrieve (only allowed for authenticated user's own profile).
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId = null)
    {
        // auth()->user() will automatically return the authenticated user's model
        // after the jwt.auth middleware has successfully validated the token.
        $authUser = Auth::user(); // Using Auth::user() for consistency, same as auth()->user()

        // If no userId is passed, return the authenticated user's profile
        if (is_null($userId)) {
            return response()->json([
                'status' => 'success',
                'user' => $this->formatUser($authUser)
            ]);
        }

        // Block access if the user is trying to view someone else's profile
        // This is a crucial authorization check for the /api/user/{id} endpoint.
        if ((int) $userId !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You can only view your own profile.'
            ], 403);
        }

        // If a userId was passed and it matches the authenticated user's ID, fetch the user.
        // While $authUser already holds the user model, fetching again ensures consistency
        // if your formatUser method needs a fresh instance, or if you were to expand
        // this to allow admins to view other profiles (with additional authorization checks).
        $user = User::findOrFail($userId); // User model should correctly map to 'user' table

        return response()->json([
            'status' => 'success',
            'user' => $this->formatUser($user)
        ]);
    }

    /**
     * Update the authenticated user's profile.
     * This method assumes it's protected by the 'jwt.auth' middleware.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param int|null $userId Optional ID of the user to update (only allowed for authenticated user's own profile).
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $userId = null)
    {
        $authUser = Auth::user(); // Get the authenticated user

        // Determine the target user: if userId is provided and matches authUser, use that, otherwise use authUser.
        // Simplified: since we're restricting updates to the authenticated user, $user will always be $authUser.
        $user = $authUser; // The user to update is always the authenticated user.

        // Authorization check is now implicitly handled by only ever targeting the authenticated user.
        // The previous explicit check 'if ($user->id !== $authUser->id)' becomes redundant if $user is always $authUser.
        // However, if $userId could be other than null and you were allowing admins to update others,
        // the explicit check would remain relevant, but the logic would be more complex.
        if (!is_null($userId) && (int) $userId !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You can only update your own profile.'
            ], 403);
        }

        // Validation for profile fields
        $validator = \Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:150'], // Added 'string' type
            'last_name' => ['required', 'string', 'max:150'],  // Added 'string' type
            // Ensure the 'unique' rule correctly points to your 'user' table and ignores the current user's ID
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('user', 'email')->ignore($user->id)], // Added 'string' type, explicit column 'email'
            'state' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:10'],
            'image' => ['nullable', 'image', 'max:2048']
        ]);
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/profile_images', $filename);
            $imageUrl = asset('storage/profile_images/' . $filename);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user fields
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->state = $request->state;
        $user->city = $request->city;
        $user->phone = $request->phone;

        // Handle password update
        if ($request->filled('password')) {
            $passwordValidator = \Validator::make($request->all(), [
                'password' => ['required', 'string', 'min:6', 'max:50', 'same:password_confirmation'],
                'password_confirmation' => ['required', 'string', 'min:6', 'max:50'],
            ]);

            if ($passwordValidator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $passwordValidator->errors()
                ], 422);
            }

            $user->password = Hash::make($request->password); // Use Hash facade for clarity
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $this->formatUser($user),
            'image_url' => $imageUrl
        ]);
    }

    /**
     * Admin-only: Return all users.
     * This method requires an additional authorization check (e.g., user has 'admin' role).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userList()
    {
        // IMPORTANT: Add authorization check here.
        // For example, if you have a 'role' column on your user table:
        // if (Auth::user()->role !== 'admin') {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Forbidden. Only administrators can view the user list.'
        //     ], 403);
        // }

        $users = User::all()->map(function ($user) {
            return $this->formatUser($user);
        });

        return response()->json([
            'status' => 'success',
            'users' => $users
        ]);
    }

    /**
     * Format the user data for API responses.
     *
     * @param \App\User $user The user model to format.
     * @return array
     */
    private function formatUser($user)
    {
        $imagePath = null;
        $storagePath = storage_path('app/public/profile_images');
        $pattern = $storagePath . '/user_' . $user->id . '_*.*';
        $files = glob($pattern);
        if ($files && count($files) > 0) {
            // Get the latest file by modification time
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $latestFile = basename($files[0]);
            $imagePath = asset('storage/profile_images/' . $latestFile);
        }
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->getPhoneNumber(), // Assumes getPhoneNumber() exists on User model
            'country' => $user->country,
            'state' => $user->state,
            'city' => $user->city,
            'date_of_birth' => $user->date_of_birth,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'profile_image_url' => $imagePath
            // 'role' => $user->role, // Example of adding a role if it exists
        ];
    }
}