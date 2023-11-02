<?php

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\Cooperative;
use App\Models\User;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
            'phone_number' => 'required|max:15',
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
            return $this->respondSuccess(['message' => 'Cooperative registered!']);
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

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            $data = $this->createUserAndAddress($data);

            //Creating farmer with the foreigns keys data
            $farmer = Farmer::create($data);

            //Finalice the transaction
            DB::commit();
            
            //Return success message
            return $this->respondSuccess(['message' => 'Farmer registered!']);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();
           
            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    protected function login(Request $request)
    {
        //Validation rules
        $rules = [
            'email' => 'email|required',
            'password' => 'required'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);

        //Check credentials
        if(!auth()->attempt($data)) {
            return $this->respondUnauthorized('Invalid credentials.');
        }

        //Get the auth user and generate access token
        /** @var \App\Models\User */
        $currentUser = Auth::user();

        //Creamos la estructura de la respuesta
        $data = [
            'user' => $currentUser
        ];

        //Return the data
        return $data;
    }

    public function cooperativeLogin(Request $request)
    {
        //Login with the data
        $data = $this->login($request);

        //Get the cooperative data
        $cooperative = $data['user']->cooperative;

        //If null, respond unauthorized
        if(!$cooperative){
            return $this->respondUnauthorized('Invalid credentials.');
        }
        //Save the cooperative data, create access token and save it
        $data['access_token'] = $data['user']->createToken('authToken')->accessToken;

        //Respond success with the data
        return $this->respondSuccess($data);
    }

    public function farmerLogin(Request $request)
    {
        //Login with the data
        $data = $this->login($request);

        //Get the cooperative data
        $farmer = $data['user']->farmer;

        //If null, respond unauthorized
        if(!$farmer){
            return $this->respondUnauthorized('Invalid credentials.');
        }
        //Save the cooperative data, create access token and save it
        $data['access_token'] = $data['user']->createToken('authToken')->accessToken;

        //Respond success with the data
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
}
