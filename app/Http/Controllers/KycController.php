<?php

namespace App\Http\Controllers;

use App\KycDocument;
use App\Models\InboxNotification;
use App\User;
use finfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Image;
use Exception;

class KycController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    public function index(Request $request)
    {
         $user=auth()->user();
         $kyc_list=kycDocument::where('user_id',$user->id)->get()->all();
         $view_data=['kyc_list'=>$kyc_list];
        return view('kyc-list.kyc-list-index',$view_data);
    }
    public function documentShow(Request $request)
    {
        return view('kyc-list.kyc-upload-form');
    }
    public function upload(Request $request)
    {
         $user=auth()->user();
        if($request->hasfile('content_file')){
            //$file = $request->file('content_file');
            $image_file = $request->content_file;
        try {
          $image = Image::make($image_file);

            Response::make($image->encode('jpeg'));

            // Get the contents of the file
            //$contents = $file->openFile()->fread($file->getSize());
            //var_dump($contents);

            kycDocument::create(['user_id'=>$user->id,
                'name'=>$request->form['name'],
                'id_number'=>$request->form['id_number'],
                'document_type'=>$request->form['document_type'],
                'image_data' =>$image,
            ]);
            $super_admin = user::where('role', 'admin')->first()->id;
            InboxNotification::create([
                'receiver'=>$super_admin,
                'subject'=>'Regarding KYC upload',
                'body'=>'KYC has been updated by the user '.$user->first_name .' '.$user->last_name,
            ]);
            $message="Document uploaded successfully";

        } catch (\Exception $e) {
                    return redirect('/kyc-upload-form')->with('error', 'Error uploading file!');
        }


            // $image = Image::make($image_file);

            // Response::make($image->encode('jpeg'));

            // // Get the contents of the file
            // //$contents = $file->openFile()->fread($file->getSize());
            // //var_dump($contents);

            // kycDocument::create(['user_id'=>$user->id,
            //     'name'=>$request->form['name'],
            //     'id_number'=>$request->form['id_number'],
            //     'document_type'=>$request->form['document_type'],
            //     'image_data' =>$image,
            // ]);
            // $super_admin = user::where('role', 'admin')->first()->id;
            // InboxNotification::create([
            //     'receiver'=>$super_admin,
            //     'subject'=>'Regarding KYC upload',
            //     'body'=>'KYC has been updated by the user '.$user->first_name .' '.$user->last_name,
            // ]);
            // $message="Document uploaded successfully";
        }
        else{
            $message="Please try again";
        }

        session()->flash('message-success',$message);

        return redirect('/document-upload');
    }

    public function docList(Request $request)
    {
        $user=Auth()->user();
        if($user->role=="admin") {
            $images = kycDocument::all();
            $view_data = ['images' => $images];
            return view('kyc-show', $view_data);
        }
        else{
            return view('_security.restricted-area.show');
        }
    }

    public function viewDoc(Request $request,kycDocument $document)
    {
        $user=Auth()->user();
        if($user->role=="admin") {
            $view_data = ['document' => $document];
            return view('kyc-show-detail', $view_data);
        }
        else{
            return view('_security.restricted-action.show');
        }
    }

    public function update(Request $request,kycDocument $document)
    {
        $form=[];
        $user=Auth()->user();
        if($user->role=="admin") {
            $form = $request->form;
            $document->update($form);
            if($form['status']=='rejected')
            {
                 session([
                    'kyc_status' => 0
                    ]); 
            }
            else{
                session([
                    'kyc_status' => 1
                    ]);
            }
            InboxNotification::create([
                'receiver' => $document->user_id,
                'subject' => 'Regarding KYC update',
                'body' => $document->remarks,
            ]);
            return redirect('/kyc-list');
        }
        else
        {
            return view('_security.restricted-action.show');
        }
    }

    public function show(Request $request, $id){
      $view_data=[];
      $user=auth()->user();
      $images=kycDocument::findOrFail($id);
        $image_file = Image::make($images->image_data);

       $response = Response::make($image_file->encode('jpeg'));

        $response->header('Content-Type', 'image/jpeg');

        return $response;
      /*if($images!=null) {
          foreach ($images as $row) {
              $image_data = $row->image_data;
          }
      }*/
      /*$view_data=['images'=> $images];

      return view('kyc-show',$view_data);*/
    }
}


// KYC verification by paystack with BVN
// https://paystack.com/docs/identity-verification/verify-bvn-match/