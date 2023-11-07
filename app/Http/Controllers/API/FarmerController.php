<?php

namespace App\Http\Controllers\API;

use App\Mail\RegisterMail;
use App\Models\Address;
use App\Models\User;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class FarmerController extends ResponseController
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

    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'dni' => 'required|max:10|unique:farmers',
            'name' => 'required|max:150',
            'surname' => 'required|max:150',
            'email' => 'required|email|max:255|unique:users',
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
            'postal_code' => 'required|max:10',
            'partner' => 'boolean',
            'active' => 'boolean'
        ];

        //Validate request
        $data = $this->validateData($request, $rules);
        
        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }
        
        //Get authenticate user id
        $currentUser = Auth::user();
        
        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }
        
        //Check if the farmer is created from a cooperative
        if(!$currentUser->cooperative){
            return $this-> respondUnauthorized();
        }

        //Generate random password
        $permitedCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ?¿+-/';
        $length = 8;
        $password = substr(str_shuffle($permitedCharacters), 0, $length);

        //Insert the password into the data
        $data['password'] = $password;

        //Data sent to the email
        $emailData = [
            'email' => $data['email'],
            'password' => $password
        ];

        //TODO: Descomentar para mandar email de registro
        //Mail::to($data['email'])->queue(new RegisterMail($emailData));

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            $data = $this->createUserAndAddress($data);

            //Creating farmer with the foreigns keys data
            $farmer = Farmer::create($data);

            //Intermediate data
            $intermediateData = [
                'partner' => $data['partner'],
                'active' => $data['active'],
            ];

            //Register the user to the cooperative
            $farmer->cooperatives()->attach($currentUser->cooperative->id, $intermediateData);
            
            //End the transaction
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

    public function update(Request $request, $id)
    {
        //Check if farmer exist
        $currentFarmer = Farmer::find($id);
        if(!$currentFarmer){
            return $this->respondNotFound();
        }

        //Validation rules
        $rules = [
            'dni' => ['required','max:10', Rule::unique('farmers')->ignore($currentFarmer->id),],
            'name' => 'required|max:150',
            'surname' => 'required|max:150',
            'email' => ['required','email','max:255', Rule::unique('users')->ignore($currentFarmer->user->id)],
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
            'postal_code' => 'required|max:10',
        ];

        //Get authenticate user id
        $currentID = Auth::id();

        //Check if not null
        if(!$currentID){
            return $this-> respondUnauthorized();
        }

        //Chek if the user is a farmer
        if($currentFarmer->user->id != $currentID){
            return $this->respondUnauthorized();
        }

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Update the data
        $currentFarmer->update($data);
        $currentFarmer->user->update($data);
        $currentFarmer->address->update($data);

        //Response data
        $response = [
            'message' => 'Cooperative edited',
            'farmer' => $currentFarmer
        ];

        //Send the response
        return $this->respondSuccess($response);
    }

    public function view($id)
    {
        //Check if farmer exist
        $currentFarmer = Farmer::find($id);
        if(!$currentFarmer){
            return $this->respondNotFound();
        }

        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Loading relationships data
        $currentFarmer->address;
        $currentFarmer->user;
        //Chek if the user is a cooperative
        if($currentUser->cooperative && $currentUser->cooperative->farmers->contains($currentFarmer)){
            return $this->respondSuccess(['farmer' => $currentFarmer]);
        } else {
            //Check if the current user is the same as the user from the farmer
            if($currentFarmer->user->id == $currentUser->id){
                return $this->respondSuccess(['farmer' => $currentFarmer]);
            }
        }

        //If nothing, return unauthorized
        return $this-> respondUnauthorized();
    }

    public function viewAll()
    {
        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Chek if the user is a cooperative
        if($currentUser->cooperative){
            return $this->respondSuccess(['farmer' => $currentUser->cooperative->farmers]);
        }

        //If not, return unauthorized
        return $this-> respondUnauthorized();
    }
}
