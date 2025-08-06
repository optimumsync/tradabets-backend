<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\User;

//use App\Helpers\ContentFileHelper;

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
        $logged_user=auth()->user();
        if($logged_user->id===$user->id)
            {
            $view_data = ['user' => $user];
            }
        else{
               return view( '_security.restricted-area.show');
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
        $logged_user=auth()->user();
        if($logged_user->id===$user->id) {
            $view_data = ['user' => $user];
        }
        else{
            return view( '_security.restricted-area.show');
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
            'form.state'=> ['required','string','max:150'],
            'form.city'=> ['required','string','max:150'],
            'form.phone' =>['required','string','max:10'],
            //'content_file' => ['file', 'sometimes', 'required', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);
        if($request->password){
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
        return redirect('/users/profile/'.$user->id);
    }

    public function userList()
    {
        $users = User::All();
        $view_data=['user_list'=>$users];
        return view('user-list.user-list-index',$view_data);
    }


}
