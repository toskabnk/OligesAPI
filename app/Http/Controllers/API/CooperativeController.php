<?php

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\Cooperative;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    //TODO: Create for admin, send email like farmer
    //Create a Cooperative
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
            return $this->respondSuccess($data, 201);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();

            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    //Update the Cooperative
    public function update(Request $request, $id)
    {
        //Check if cooperative exist
        $currentCooperative = Cooperative::find($id);
        if(!$currentCooperative){
            return $this->respondNotFound();
        }

        $rules = [
            'nif' => ['sometimes','required','max:10',Rule::unique('cooperatives')->ignore($currentCooperative->id)],
            'name' => 'sometimes|required|max:150',
            'email' => ['sometimes','required','email','max:255', Rule::unique('users')->ignore($currentCooperative->user->id)],
            'phone_number' => ['sometimes','max:15',Rule::unique('cooperatives')->ignore($currentCooperative->id)],
            'road_type' => 'sometimes|required|max:30',
            'road_name' => 'sometimes|required|max:150',
            'road_number' => 'sometimes|required|max:5',
            'road_letter' => 'max:5',
            'road_km' => 'max:10',
            'block' => 'max:10',
            'portal' => 'max:10',
            'stair' => 'max:10',
            'floor' => 'max:5',
            'door' => 'max:5',
            'town_entity' => 'max:50',
            'town_name' => 'sometimes|required|max:50',
            'province' => 'sometimes|required|max:50',
            'country' => 'sometimes|required|max:50',
            'postal_code' => 'sometimes|required|max:10'
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
        $currentCooperative->user->update($data);
        $currentCooperative->address->update($data);

        $response = [
            'message' => 'Cooperative edited',
            'cooperative' => $currentCooperative
        ];

        return $this->respondSuccess($response);
    }

    //View the datils from a cooperative
    public function view($id)
    {
        $cooperative = Cooperative::find($id);

        //Check if cooperative exist
        if(!$cooperative){
            return $this->respondNotFound();
        }

        //Load the data
        $cooperative->user;
        $cooperative->address;

        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Chek if the user is a cooperative and is the same as the id
        if($currentUser->cooperative){
            if($currentUser->cooperative->id == $id){
                return $this->respondSuccess(['cooperative' => $cooperative]);
            } else {
                return $this-> respondUnauthorized();
            }
        } else {
            //Check if the current user is registered in the cooperative
            if($currentUser->farmer->cooperatives->contains($cooperative)){
                return $this->respondSuccess(['cooperative' => $cooperative]);
            }
        }

        return $this-> respondUnauthorized();
    }

    //View all cooperatives
    //TODO: Only if admin
    public function viewAll()
    {
        $cooperatives = Cooperative::with(['user', 'address'])->get();

        return $this->respondSuccess(['cooperative' => $cooperatives]);
    }

    //View the farmers from the cooperative
    //? Show only active farmers?
    public function viewCooperativeFarmers()
    {
        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if the user is a cooperative
        if($currentUser->cooperative){
            //Load farmer data with the user info
            $farmers = $currentUser->cooperative->farmers()->with('user')->get();
            return $this->respondSuccess(['farmer' => $farmers]);
        }

        //If not, return unauthorized
        return $this-> respondUnauthorized();
    }


    //Register the farmer to the cooperative
    //? Should this function be in FarmerController?
    public function addFarmerToCooperative(Request $request, $id)
    {
        $rules = [
            'partner' => 'required|boolean',
            'active' => 'required|boolean'
        ];

        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Chek if the user is not a cooperative
        if(!$currentUser->cooperative){
            return $this->respondUnauthorized();
        }

        //Check if farmer exist
        $farmer = Farmer::find($id);
        if(!$farmer){
            return $this->respondNotFound();
        }

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Intermediate data
        $intermediateData = [
            'partner' => $data['partner'],
            'active' => $data['active'],
        ];

        //Check if farmer is registered in the cooperative
        if($farmer->cooperatives->contains($currentUser->cooperative)) {
            //Save farmer active status
            $active = $farmer->cooperatives->find($currentUser->cooperative->id)->pivot->active;

            //Check if farmer is not active
            if(!$active){

                //Update the active data
                $farmer->cooperatives()->updateExistingPivot($currentUser->cooperative->id, ['active' => true]);

                //Responde success
                return $this->respondSuccess(['message' => 'Farmer is added to the cooperative, again']);
            } else {
                //? Other type of response if already registered?
                return $this->respondSuccess(['message' => 'Farmer is alredy registered in the cooperative']);
            }
        }

        //Register the user to the cooperative
        $farmer->cooperatives()->attach($currentUser->cooperative->id, $intermediateData);

        return $this->respondSuccess(['message' => 'Farmer added to the cooperative']);
    }

    //Delete the farmer to the cooperative
    //? Should this function be in FarmerController?
    public function deleteFarmerFromCooperative($id)
    {
        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Chek if the user is not a cooperative
        if(!$currentUser->cooperative){
            return $this->respondUnauthorized();
        }

        //Check if farmer exist
        $farmer = Farmer::find($id);
        if(!$farmer){
            return $this->respondNotFound();
        }

        //Intermediate data
        $intermediateData = [
            'partner' => false,
            'active' => false,
        ];

        //Check if farmer is registered in the cooperative and the farmer is active
        if($farmer->cooperatives->contains($currentUser->cooperative)) {
            //Save farmer active status
            $active = $farmer->cooperatives->find($currentUser->cooperative->id)->pivot->active;

            //Check if farmer is active
            if($active){

                //Update active data
                $farmer->cooperatives()->updateExistingPivot($currentUser->cooperative->id, ['active' => false]);

                //Respond success
                return $this->respondSuccess(['message' => 'Farmer is deleted to the cooperative']);
            } else {
                //? Other type of response if already deleted?
                return $this->respondSuccess(['message' => 'Farmer already deleted from the cooperative']);
            }
        }
        return $this->respondSuccess(['message' => 'Farmer is not from the cooperative']);
    }
}
