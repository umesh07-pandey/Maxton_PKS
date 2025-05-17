<?php

namespace App\Http\Controllers;
use App\Models\Forget_Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Registration;
use App\Models\Profile;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AuthController extends Controller
{


    public function register(Request $request)
    {
        try {
            DB::beginTransaction();


            $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|string|max:255|unique:registration,email',
                'password' => 'required|string|max:255|min:6',
                'country' => 'required|string|max:225'
            ]);

            $registration = Registration::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'country' => $request->country,
            ]);

            $image = [
                'https://static.vecteezy.com/system/resources/thumbnails/027/951/137/small_2x/stylish-spectacles-guy-3d-avatar-character-illustrations-png.png',
                'https://i.pinimg.com/474x/0a/a8/58/0aa8581c2cb0aa948d63ce3ddad90c81.jpg',
                'https://cdn-icons-png.flaticon.com/512/168/168732.png',
                'https://www.w3schools.com/w3images/avatar2.png',
                'https://st.depositphotos.com/46542440/55685/i/450/depositphotos_556850840-stock-illustration-square-face-character-stiff-art.jpg',
                'https://static.vecteezy.com/system/resources/previews/024/183/502/original/male-avatar-portrait-of-a-young-man-with-a-beard-illustration-of-male-character-in-modern-color-style-vector.jpg'
            ];

            $randomImage = $image[array_rand($image)];

            $profile = Profile::create([
                'country' => $request->country,
                'profile_pic' => $randomImage,
                'registration_id' => $registration->id,
            ]);




            DB::commit();


            return response()->json([
                "message" => "user register successfully",
                "data" => $registration,
                "status" => "true"
            ], 201);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login', 'register']]);
    // }


    public function login(Request $request)
    {
        try {


            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid Credentials']);
            }
            return response()->json([
                'message' => 'login successfully',
                'token' => $token
            ]);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function getprofile(Request $request)
    {
        $authUser = auth()->user();
        $profile = $authUser->Profile;

        $profileUser = [
            'email' => $authUser->email,
            'profile' => $profile,

        ];
        return response()->json([
            "message" => "fetching the profile data successfully",
            "data" => $profileUser,
            "status" => "true"
        ], 201);

    }

    public function updateOrAdd(Request $request)
    {
        try {
            DB::beginTransaction();
            $authUser = auth()->user();
            $profile = $authUser->profile;

            if (!$profile) {
                return response()->json([
                    "message" => "User not found",
                    "status" => "false",
                ]);
            }

            $data = $profile->updateOrCreate(
                ['id' => $profile->id],
                [
                    'name' => $request->name,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'phone' => $request->phone,
                    'country' => $request->country,
                ]
            );

            DB::commit();


            return response()->json(['message' => 'Profile updated', 'profile' => $data]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function fetchAllUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $authUser = auth()->user();
            // Log::info("auth user",['authUser' => $authUser]);
            $userdata = $authUser->profile;

            $allData = Profile::where('id', '!=', $userdata->id)->get();
            $otherdata = [];

            foreach ($allData as $data) {
                Log::info("all data", ["data" => $data]);
                $otherdata[] = [
                    'name' => $data->name,
                    'profile_pic' => $data->profile_pic,
                    'email' => $authUser->email,
                    'username' => $authUser->username,
                    'dob' => $data->dob,
                    'gender' => $data->gender,
                    'country' => $data->country
                ];
            }

            DB::commit();

            return response()->json([
                "message" => "other user data",
                "status" => "true",
                "data" => $otherdata
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["error" => $e->getMessage()], 500);

        }

    }

    public function forgetPassword(Request $request)
    {
        try {

            DB::beginTransaction();

            $numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
            $otp = '';
            for ($i = 0; $i < 4; $i++) {
                $otp .= $numbers[array_rand($numbers)];
            }

            $request->validate([
                'email' => 'required|string|max:255',
            ]);

            $exist = Registration::where('email', $request->email)->first();
            if ($exist) {
                $exitemail = $exist->email;

                if ($exitemail != $request->email) {
                    return response()->json([
                        "message" => "Invalid email",
                        "status" => "false",
                    ]);
                }

                $forgetpassword = Forget_Password::create([
                    'email' => $request->email,
                    "otp" => $otp,
                    "status"=>false,
                    'created_at' => now(),
                ]);
                DB::commit();


                return response()->json([
                    "message" => "the is sent in your mail id",
                    "data" => $forgetpassword,
                    "status" => "true",
                ]);
            }
            return response()->json([
                "message" => "the email is not exists",
                "status" => "false"
            ], 400);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "erroe" => $e->getMessage()
            ], 500);
        }

    }

    public function resetpassword(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([

                'email' => 'required|string|max:255',
                'password' => 'required|string|max:255|min:6',
                'otp' => 'required|string|max:4'

            ]);

            Log::info("Request body",[
                 'email' => $request->email,
                'otp' => $request->otp,
                'password' => $request->password,
            ]);
            // $count=0;
            

            $data = Forget_Password::where('email', $request->email)
                ->where('otp', $request->otp)->first();
                if($data->status==true){
                    return response()->json([
                        "message"=>"the otp has been already used",
                        "status"=>"false"
                    ]);
                }


            if ($data) {
                $data->status=true;
                $data->save();
                $now = Carbon::now();
                $otpCreatedTime = $data->created_at;

                $diffInMinutes = $now->diffInMinutes($otpCreatedTime);

                if ($diffInMinutes > 5 ) {
                    return response()->json(['message' => 'OTP expired. Please request a new one.'], 400);
                } else {
                  $user= Registration::where('email', $request->email)->first();
                  $user->password=Hash::make($request->password);
                  $user->save();
                
                DB::commit();
                    return response()->json([
                        "message" => "password reset successfully",
                        "status" => "true"
                    ]);

                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => $e->getMessage()
            ], 500);

        }









    }

}



