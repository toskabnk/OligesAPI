<?php

namespace App\Http\Controllers\API;

use App\Models\Address;
use App\Models\Farm;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FarmController extends ResponseController
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

    //Create the farm for farmer with that id
    public function create(Request $request, $id)
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
        $checkPermission=false;
        //Chek if the user is a cooperative
        if($currentUser->cooperative && $currentUser->cooperative->farmers->contains($currentFarmer)){
            $checkPermission=true;
        } else {
            //Check if the current user is the same as the user from the farmer
            if($currentFarmer->user->id == $currentUser->id){
                $checkPermission=true;
            }
        }

        //If not a cooperative or the current farmer, respond unauthorized
        if(!$checkPermission){
            return $this-> respondUnauthorized();
        }

        //Validation rules
        $rules = [
            'name' => 'required|max:150',
            'polygon' => 'required|max:150',
            'plot' => 'required|max:150',
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

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }
        
        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            //Create the address
            $address = Address::create($data);

            //Adding the foreigns keys
            $data['address_id'] = $address['id'];
            $data['farmer_id'] = $id;

            //Create the farm
            $farm = Farm::create($data);

            //End the transaction
            DB::commit();
            
            //Return success message
            return $this->respondSuccess(['message' => 'Farm registered!']);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();
           
            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    //Update the farm
    public function update(Request $request, $id)
    {
        //Check if farm exist
        $currentFarm = Farm::find($id);
        if(!$currentFarm){
            return $this->respondNotFound();
        }

        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if user is a farmer
        if(!($currentUser->farmer)) {
            return $this-> respondUnauthorized();
        }

        //Check if farm is from the farmer
        if($currentFarm->farmer_id != $currentUser->farmer->id){
            return $this-> respondUnauthorized();
        }

        //Validation rules
        $rules = [
            'name' => 'required|max:150',
            'polygon' => 'required|max:150',
            'plot' => 'required|max:150',
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

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            //Update the farm data
            $currentFarm->update($data);
            $currentFarm->address->update($data);

            //End the transaction
            DB::commit();
            
            //Return success message
            return $this->respondSuccess(['message' => 'Farm edited!']);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();
           
            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    public function viewFarmerFarms($id)
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

        //Check if the user is a farmer
        if($currentUser->farmer){
            //Check if the user is the same as the farmer
            if($currentFarmer->id == $currentUser->farmer->id){
                return $this->respondSuccess(['farm' => $currentFarmer->farms]);
            } else {
                return $this->respondUnauthorized();
            }
        } else {
            //Chek if the user is a cooperative
            if($currentUser->cooperative && $currentUser->cooperative->farmers->contains($currentFarmer)){
                return $this->respondSuccess(['farm' => $currentFarmer->farms]);
            }
        }

        return $this->respondUnauthorized();
    }

    //View details from a farm
    public function view($id)
    {
        //Check if farmer exist
        $currentFarm = Farm::find($id);
        if(!$currentFarm){
            return $this->respondNotFound();
        }

        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Load relation data
        $currentFarm->address;

        //Check if the user is a farmer
        if($currentUser->farmer){
            //Check if the user is the same as the farmer
            if($currentFarm->id == $currentUser->farmer->farms->contains($currentFarm)){
                return $this->respondSuccess(['farm' => $currentFarm]);
            } else {
                return $this->respondUnauthorized();
            }
        } else {
            //Chek if the user is a cooperative
            if($currentUser->cooperative && $currentUser->cooperative->farmers->contains($currentFarmer)){
                return $this->respondSuccess(['farm' => $currentFarm]);
            }
        }

        return $this->respondUnauthorized();
    }
}
