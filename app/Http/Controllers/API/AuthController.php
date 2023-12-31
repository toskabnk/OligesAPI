<?php

namespace App\Http\Controllers\API;

use App\Mail\RegisterMail;
use App\Models\Address;
use App\Models\Cooperative;
use App\Models\User;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends ResponseController
{
    protected function createUserAndAddress($data)
    {
        //Storing the password hashed
        $data['password'] = Hash::make($data['password']);

        //Creating User and Address entries
        $user = User::create($data);
        $address = Address::create($data);

        //Adding the foreigns keys
        $data['user_id'] = $user['id'];
        $data['address_id'] = $address['id'];

        return $data;
    }

    protected function validateData(Request $request, $rules ){
        //Request validation with the rules
        $validation = Validator::make($request->all(),$rules);

        //If validation fails, send a reponse with the errors
        if($validation->fails())
        {
            return $this->respondUnprocessableEntity('Validation errors', $validation->errors());
        }

        //Save validated data
        return $validation->validated();
    }

    public function registerCooperative(Request $request)
    {
        //Validation rules
        $rules = [
            'nif' => 'required|max:10|unique:cooperatives',
            'name' => 'required|max:150',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|same:password',
            'phone_number' => 'required|max:15|unique:cooperatives',
            'road_type' => 'required|max:30',
            'road_name' => 'required|max:150',
            'road_number' => 'required|max:5',
            'road_letter' => 'max:5',
            'road_km' => 'max:10',
            'block' => 'max:10',
            'portal' => 'max:10',
            'stair' => 'max:10',
            'floor' => 'max:5',
            'door' => 'max:5',
            'town_entity' => 'max:50',
            'town_name' => 'required|max:50',
            'province' => 'required|max:50',
            'country' => 'required|max:50',
            'postal_code' => 'required|max:10'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            $data = $this->createUserAndAddress($data);

            //Creating cooperative with the foreigns keys data
            $cooperative = Cooperative::create($data);

            //Finalice the transaction
            DB::commit();

            //Return success message
            return $this->respondSuccess(['message' => 'Cooperative registered!'],201);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();

            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    public function registerFarmer(Request $request)
    {
        //Validation rules
        $rules = [
            'dni' => 'required|max:10|unique:farmers',
            'name' => 'required|max:150',
            'surname' => 'required|max:150',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|same:password',
            'phone_number' => 'max:15',
            'movil_number' => 'max:15',
            'road_type' => 'required|max:30',
            'road_name' => 'required|max:150',
            'road_number' => 'required|max:5',
            'road_letter' => 'max:5',
            'road_km' => 'max:10',
            'block' => 'max:10',
            'portal' => 'max:10',
            'stair' => 'max:10',
            'floor' => 'max:5',
            'door' => 'max:5',
            'town_entity' => 'max:50',
            'town_name' => 'required|max:50',
            'province' => 'required|max:50',
            'country' => 'required|max:50',
            'postal_code' => 'required|max:10'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            $data = $this->createUserAndAddress($data);

            //Creating farmer with the foreigns keys data
            $farmer = Farmer::create($data);

            //Finalice the transaction
            DB::commit();

            //Data sent to the email
            $emailData = [
                'email' => $data['email'],
                'password' => null
            ];

            //TODO: Descomentar para mandar email de registro
            Mail::to($data['email'])->queue(new RegisterMail($emailData));

            //Return success message
            return $this->respondSuccess(['message' => 'Farmer registered!'],201);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();

            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    public function login(Request $request)
    {
        //Validation rules
        $rules = [
            'email' => 'email|required',
            'password' => 'required'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $this->respondUnauthorized($data);
        }

        //Check credentials
        if(!auth()->attempt($data)) {
            return $this->respondUnauthorized('Invalid credentials.');
        }

        //Get the auth user and generate access token
        /** @var \App\Models\User */
        $currentUser = Auth::user();
        $currentUser->load('cooperative', 'farmer');
        $accessToken = $currentUser->createToken('authToken')->accessToken;

        //Creamos la estructura de la respuesta
        $data = [
            'user' => $currentUser,
            'access_token' => $accessToken
        ];

        //Return the data
        return $this->respondSuccess($data);
    }

    public function logout()
    {
        //Get current user
        $currentUser = Auth::user();

        //If null, respond unauthorized
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Get current token and revoke it
        $token = $currentUser->token();
        $token->revoke();

        //Return success message
        return $this->respondSuccess(['message' => 'User logout']);
    }

    public function profile()
    {
        //Get current user
        $currentUser = Auth::user();

        //If null, respond unauthorized
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Cheking if the user is a cooperative or a farmer
        if($currentUser->cooperative){
            $currentUser->cooperative;
            $currentUser->cooperative->address;
        }else{
            $currentUser->farmer;
            $currentUser->farmer->address;
        }

        //Return the user data
        return $this->respondSuccess($currentUser);
    }

    public function changePassword(Request $request)
    {
        //Validation rules
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
            'new_password_confirmation' => 'required|same:new_password'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Get current user
        /** @var \App\Models\User */
        $currentUser = Auth::user();

        //If null, respond unauthorized
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if the old password is correct
        if(!Hash::check($data['old_password'], $currentUser->password)){
            return $this->respondUnauthorized('Invalid credentials.');
        }

        //Update the password
        $currentUser->password = Hash::make($data['new_password']);
        $currentUser->save();

        //Revoke old access tokens except the current
        $currentUser->tokens()->where('id', '!=', $currentUser->token()->id)->delete();

        //Return success message
        return $this->respondSuccess(['message' => 'Password changed!']);
    }
}
