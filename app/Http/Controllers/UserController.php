<?php

namespace App\Http\Controllers;

use App\Country;
use App\User;
use App\Withdrawal_preference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{

    public function index(){
        $title = trans('app.users');
        $users = User::orderBy('name', 'asc')->paginate(20);
        $users_count = User::count();
        return view('admin.users', compact('title', 'users', 'users_count'));
    }

    public function show($id = 0){
        if ($id){
            $title = trans('app.profile');
            $user = User::find($id);

            $is_user_id_view = true;
            return view('admin.profile', compact('title', 'user', 'is_user_id_view'));
        }
    }

    /**
     * @param $id
     * @param null $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function statusChange($id, $status = null){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = User::find($id);
        if ($user && $status){
            if ($status == 'approve'){
                $user->active_status = 1;
                $user->save();

            }elseif($status == 'block'){
                $user->active_status = 2;
                $user->save();
            }
        }
        return back()->with('success', trans('app.status_updated'));
    }



    public function profile(){
        $title = trans('app.profile');
        $user = Auth::user();
        return view('admin.profile', compact('title', 'user'));
    }

    public function profileEdit($id = null){
        $title = trans('app.profile_edit');
        $user = Auth::user();

        if ($id){
            $user = User::find($id);
        }

        $countries = Country::orderBy('name', 'asc')->get();

        return view('admin.profile_edit', compact('title', 'user', 'countries'));
    }

    public function profileEditPost($id = null, Request $request){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }

        $user = Auth::user();
        if ($id){
            $user = User::find($id);
        }
        //Validating
        $rules = [
            'email'    => 'required|email|unique:users,email,'.$user->id,
        ];
        $this->validate($request, $rules);

        $inputs = array_except($request->input(), ['_token', 'photo']);
        $user->update($inputs);

        if ($request->hasFile('photo')){
            $rules = ['photo'=>'mimes:jpeg,jpg,png'];
            $this->validate($request, $rules);

            $image = $request->file('photo');
            $file_base_name = str_replace('.'.$image->getClientOriginalExtension(), '', $image->getClientOriginalName());


            $resized = Image::make($image)->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $image_name = strtolower(time().str_random(5).'-'.str_slug($file_base_name)).'.' . $image->getClientOriginalExtension();

            $upload_dir = './uploads/avatar/';
            if ( ! file_exists($upload_dir)){
                mkdir($upload_dir, 0777, true);
            }

            $imageFileName = $upload_dir.$image_name;

            try{
                //Uploading thumb
                $resized->save($imageFileName);

                $previous_photo= $user->photo;
                $user->photo = $image_name;
                $user->save();

                if ($previous_photo){
                    if (file_exists($upload_dir.$previous_photo)){
                        unlink($upload_dir.$previous_photo);
                    }
                }

            } catch (\Exception $e){
                return $e->getMessage();
            }

        }

        return back()->with('success', trans('app.profile_edit_success_msg'));
    }

    public function withdrawalPreference(){
        $title = trans('app.withdrawal_preference');
        $user = Auth::user();

        $countries = Country::orderBy('name', 'asc')->get();
        return view('admin.withdrawal_preference', compact('title', 'user', 'countries'));
    }

    public function withdrawalPreferenceUpdate(Request $request){
        $user_id = Auth::user()->id;
        $rules = [
            'default_withdrawal_account'    =>'required'
        ];
        $this->validate($request, $rules);

        $data = [
            'default_withdrawal_account'    => $request->default_withdrawal_account,
            'paypal_email'                  => $request->paypal_email,
            'bank_account_holders_name'     => $request->bank_account_holders_name,
            'bank_account_number'           => $request->bank_account_number,
            'swift_code'                    => $request->swift_code,
            'bank_name_full'                => $request->bank_name_full,
            'bank_branch_name'              => $request->bank_branch_name,
            'bank_branch_city'              => $request->bank_branch_city,
            'bank_branch_address'           => $request->bank_branch_address,
            'country_id'                    => $request->country_id,
            'user_id'                       => $user_id,
        ];

        $withdrawal_preference = Withdrawal_preference::whereUserId($user_id)->first();
        if ($withdrawal_preference){
            $withdrawal_preference->update($data);
        }else{
            Withdrawal_preference::create($data);
        }

        return redirect()->back()->with('success', trans('app.changes_has_been_saved'));

    }

    public function changePassword()
    {
        $title = trans('app.change_password');
        return view('admin.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request)
    {
        if(config('app.is_demo')){
            return redirect()->back()->with('error', 'This feature has been disable for demo');
        }
        $rules = [
            'old_password'  => 'required',
            'new_password'  => 'required|confirmed',
            'new_password_confirmation'  => 'required',
        ];
        $this->validate($request, $rules);

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        //$new_password_confirmation = $request->new_password_confirmation;

        if(Auth::check())
        {
            $logged_user = Auth::user();

            if(Hash::check($old_password, $logged_user->password))
            {
                $logged_user->password = Hash::make($new_password);
                $logged_user->save();
                return redirect()->back()->with('success', trans('app.password_changed_msg'));
            }
            return redirect()->back()->with('error', trans('app.wrong_old_password'));
        }
    }
    
    

}
