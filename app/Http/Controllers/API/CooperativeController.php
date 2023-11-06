<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cooperative;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CooperativeController extends ResponseController
{
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

    public function create(Request $request)
    {
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

        //Get authenticate user id
        $currentID = Auth::id();

        //Check if not null
        if(!$currentID){
            return $this-> respondUnauthorized();
        }

        //Get the current user from id
        $currentUser = User::find($currentID);

        //TODO: Create for admin

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //transaction for creatin the entrys in the BD
        DB::beginTransaction();
        try
        {
            $data = $this->createUserAndAddress($data);

            //Creating cooperative with the foreigns keys data
            $cooperative = Cooperative::create($data);

            //Finalice the transaction
            DB::commit();

            $data = [
                'message' => 'Cooperative created',
                'cooperative' => $cooperative
            ];
            
            //Return success message
            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();
           
            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'nif' => 'required|max:10|unique:cooperatives',
            'name' => 'required|max:150',
            'email' => 'required|email|max:255|unique:users',
            'phone_number' => 'required|max:15'
        ];

        //Get authenticate user id
        $currentID = Auth::id();

        //Check if not null
        if(!$currentID){
            return $this-> respondUnauthorized();
        }

        //Get the current user from id
        $currentUser = User::find($currentID);

        //TODO: Create for admin

        //Check if the current user belongs to the cooperative
        $currentCooperative = Cooperative::find($id);
        if($currentCooperative->user->id != $currentID){
            return $this->respondUnauthorized();
        }

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Update the cooperative
        $currentCooperative->update($data);

        $response = [
            'message' => 'Cooperative edited',
            'cooperative' => $currentCooperative
        ];

        return $this->respondSuccess($response);
    }

    public function view($id)
    {
        $cooperative = Cooperative::find($id);
        $cooperative->user;
        $cooperative->address;

        if(!$cooperative){
            $this->respondNotFound();
        }

        return $this->respondSuccess(['cooperative' => $cooperative]);
    }

    public function viewAll()
    {
        $cooperatives = Cooperative::with(['user', 'address'])->get();

        return $this->respondSuccess(['cooperative' => $cooperatives]);
    }
}
