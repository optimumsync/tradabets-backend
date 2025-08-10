<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\User;
use Illuminate\Support\Facades\Response;

class UserProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // data
        $logged_user = auth()->user();
        if ($logged_user->id === $user->id) {
            $view_data = ['user' => $user];
        } else {
            return view('_security.restricted-area.show');
        }

        // view
        return view('user-profile.show', $view_data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        // data
        $logged_user = auth()->user();
        if ($logged_user->id === $user->id) {
            $view_data = ['user' => $user];
        } else {
            return view('_security.restricted-area.show');
        }
        // view
        return view('user-profile.edit', $view_data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // validate
        request()->validate([
            'form.first_name' => ['required', 'max:150'],
            'form.last_name' => ['required', 'max:150'],
            'form.email' => ['required', 'string', 'email', 'max:150', Rule::unique('user', 'email')->ignore($user->id, 'id')],
            'form.state' => ['required', 'string', 'max:150'],
            'form.city' => ['required', 'string', 'max:150'],
            'form.phone' => ['required', 'string', 'max:10'],
            //'content_file' => ['file', 'sometimes', 'required', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);
        if ($request->password) {
            request()->validate([
                'password' => ['required', 'string', 'min:6', 'max:50', 'required_with:password_confirmation', 'same:password_confirmation'],
                'password_confirmation' => ['required', 'string', 'min:6', 'max:50']
            ]);

            // update
            $request = $request->merge([
                'form' => array_merge($request->form, ['password' => Hash::make($request->password)])
            ]);
        }

        // save
        $user->update($request->form);

        // upload image
        //ContentFileHelper::upload_user_profile_image($user, $request);

        // msg
        session()->flash('message-success', 'Your profile was successfully updated.');

        // redirect
        return redirect('/users/profile/' . $user->id);
    }

    /**
     * Build the user query based on request filters.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildUserQuery(Request $request)
    {
        $search = $request->input('search');
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Display a paginated and searchable list of users.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userList(Request $request)
    {
        $perPage = $request->input('per_page', 24); 
        $query = $this->buildUserQuery($request);

        // MODIFIED: Changed sorting from latest() to orderBy('id', 'asc')
        $users = $query->orderBy('id', 'asc')->paginate($perPage);

        // Pass the paginated list and filter values to the view
        $view_data = [
            'user_list' => $users->appends($request->except('page')), 
        ];

        return view('user-list.user-list-index', $view_data);
    }

    /**
     * Export all users to a CSV file, ignoring any filters.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportUsersToCsv()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=users_export_" . now()->format('Y-m-d_H-i-s') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        // MODIFIED: Changed from all() to an ordered query for consistency.
        $users = User::orderBy('id', 'asc')->get();

        $columns = [
            'id',
            'first_name',
            'last_name',
            'date_of_birth',
            'email',
            'email_verified_at',
            'country_code',
            'phone',
            'city',
            'state',
            'country',
            'role',
            'created_at',
            'updated_at'
        ];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->date_of_birth,
                    $user->email,
                    $user->email_verified_at,
                    $user->country_code,
                    $user->phone,
                    $user->city,
                    $user->state,
                    $user->country,
                    $user->role ?? 'N/A',
                    $user->created_at,
                    $user->updated_at,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}