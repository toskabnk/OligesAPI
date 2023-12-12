<?php

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends ResponseController
{

    protected $rules;

    public function __construct()
    {
        $this->rules = [
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

    protected function checkAuthorized(Address $address, User $user, $id){
        //If authenticated user is a cooperative
        if ($user->cooperative) {
            //If the address is from the cooperative
            if ($address->cooperative) {
                return true;
            //If the address is from a farmer
            } if ($address->farmer && $address->farmer->cooperatives->contains($user->cooperative)) {
                return true;
            }
        } 
        
        //If authenticated user is a farmer
        if ($user->farmer) {
            //If the address is from the farmer or from a farm that belong to the farmer
            if($user->farmer->address->id == $id || $user->farmer->farms->contains($address)){
                return true;
            }
        }
    }

    public function create(Request $request)
    {
        //Get authenticate user id
        $currentID = Auth::id();

        //Check if not null
        if(!$currentID){
            return $this-> respondUnauthorized();
        }

        //Get the current user from id
        $currentUser = User::find($currentID);

        //Get the cooperative
        $cooperative = $currentUser->cooperative;

        //Check if the authenticated user is a cooperative
        if(!$cooperative) {
            return $this-> respondUnauthorized();
        }

        //Validate the data
        $data = $this->validateData($request, $this->rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Create the address
        $address = Address::create($data);

        //Respond success
        return $this->respondSuccess(['message' => 'Address created'],201);
    }

    public function update(Request $request, string $id)
    {

        //Get authenticate user id
        $currentID = Auth::id();
  
        //Check if not authenticated
        if(!$currentID){
            return $this-> respondUnauthorized();
        }
        
        //Check if the address exist
        $address = Address::find($id);
        if(!$address){
            return $this->respondNotFound();
        }

        //Validate the data
        $data = $this->validateData($request, $this->rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }
        
        //Get the current user from id
        $currentUser = User::find($currentID);

        //Check if user can edit the address
        $permission = $this->checkAuthorized($address, $currentUser, $id);
        if($permission) {
            $address->update($data);
            return $this->respondSuccess(['message' => 'Address updated']);
        } else {
            return $this->respondUnauthorized();
        }
    }
}
