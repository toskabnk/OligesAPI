<?php

namespace App\Http\Controllers\API;

use App\Models\Farmer;
use App\Models\Receipt;
use App\Models\Weight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends ResponseController
{
    protected function getCampaign()
    {
        //Actual date
        $actualDate = Carbon::now();

        // Check if the current date is before or after September 1st
        $campaignYear = $actualDate->month >= 9 ? $actualDate->year : $actualDate->year - 1;

        //Campaign start date (September 1)
        $startDate = Carbon::create($campaignYear, 9, 1);

        //Campaign end date (August 31)
        $endDate = Carbon::create($campaignYear + 1, 8, 31);

        //Check if the current date is within the range of the current campaign
        if ($actualDate->gte($startDate) && $actualDate->lte($endDate)) {
            //Return campaign name
            return "$campaignYear-" . ($campaignYear + 1);
        }
    }

    protected function generateAlabaranNumber($cooperativeId, $campaignYear)
    {
        //Get the last number from the campaign and cooperative
        $lastNumber = Receipt::select('albaran_number')
        ->where('cooperative_id', $cooperativeId)
        ->where('campaign', $campaignYear)
        ->max(DB::raw('CAST(albaran_number AS UNSIGNED)'));

        //If null, start at 1, else lastNumber +1
        $newNumber = $lastNumber ? $lastNumber + 1 : 1;

        return $newNumber;
    }


    protected function validateData(Request $request, $rules )
    {
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

    //Creates a new receipt with the weights
    public function create(Request $request)
    {
        //Validation rules
        $rules = [
            'date' => 'required|date',
            'sign' => 'required',
            'farmer_id' => 'required',
            'farm_id' => 'required',
            'weights.*.type' => 'required',
            'weights.*.kilos' => 'required',
            'weights.*.sampling' => 'max:5',
            'weights.*.container' => 'max:10',
            'weights.*.purple_percentage' => 'max:10',
            'weights.*.rehu_percentage' => 'max:10',
            'weights.*.leaves_percentage' => 'max:10',
        ];

        //Validate the data
        $data = $this->validateData($request, $rules);

        //If data is a response, return the response
        if($data instanceof JsonResponse){
            return $data;
        }

        //Get authenticate user
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if user is a cooperative
        if(!($currentUser->cooperative)) {
            return $this-> respondUnauthorized();
        }

        //Check if the farmer exist
        $farmer = Farmer::find($data['farmer_id']);
        if(!$farmer) {
            return $this->respondNotFound();
        }

        //Check if the farmer is from the cooperative
        if(!$currentUser->cooperative->farmers->contains($farmer)){
            return $this->respondUnauthorized();
        }

        //Generate the campaign number
        $data['campaign'] = $this->getCampaign();

        //Generate the albaran number
        $data['albaran_number'] = $this->generateAlabaranNumber($currentUser->cooperative->id, $data['campaign']);

        //Insert the cooperative_id
        $data['cooperative_id'] = $currentUser->cooperative->id;

        //Transaction for creating the entrys in the BD
        DB::beginTransaction();
        try
        {
            //Save the weights in a variable and create a receipts array
            $weights = $data['weights'];
            $addedWeights = [];

            //Create the receipt
            $receipt = Receipt::create($data);

            //Iterate through all the weights
            foreach($weights as $weight) {
                //Added the id of the receipt created
                $weight['receipt_id'] = $receipt->id;

                //Create the weight
                $addedWeight = Weight::create($weight);

                //Save the weight added to the array
                array_push($addedWeights, $addedWeight);
            }

            //Response data
            $data = [
                'receipt' => $receipt,
                'weights' => $addedWeights
            ];

            DB::commit();

            //TODO: Send email with the receipt

            //? Send pdf with the receipt or generate it in the frontend?
            //TODO: Send pdf back in response

            return $this->respondSuccess($data);
        } catch (\Exception $e) {
            //If the transaction hace errors, do a rollback
            DB::rollBack();

            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    //View the receipt
    public function viewReceiptsCooperative(Request $request)
    {
        //Get authenticate user id
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        $query = null;

        //Check if user is a cooperative
        if($currentUser->cooperative) {
            //Create the query with the farmer data and filter by the cooperative at the start
            $query = Receipt::query()->with('farmer:id,name,surname,dni')->where('cooperative_id', $currentUser->cooperative->id);
        }

        //Check if user is a farmer
        if($currentUser->farmer) {
            //Create the query with the farmer data and filter by the farmer at the start
            $query = Receipt::query()->with('farmer:id,name,surname,dni')->where('farmer_id', $currentUser->farmer->id);
        }

        //Check if filter is present and query the data
        if($request->has('campaign')){
            $query->where('campaign', $request->input('campaign'));
        }

        if($request->has('farmerName')){
            $farmerInfo = $request->input('farmerName');
            $query->whereHas('farmer', function ($query) use ($farmerInfo) {
                $query->where('name', 'like', '%' . $farmerInfo . '%');
            });
        }

        if($request->has('farmerDNI')){
            $farmerInfo = $request->input('farmerDNI');
            $query->whereHas('farmer', function ($query) use ($farmerInfo) {
                $query->where('dni',$farmerInfo);
            });
        }

        if($request->has('farmerSurname')){
            $farmerInfo = $request->input('farmerSurname');
            $query->whereHas('farmer', function ($query) use ($farmerInfo) {
                $query->where('surname', 'like', '%' . $farmerInfo . '%');
            });
        }

        if($request->has('albaran_number')){
            $query->where('albaran_number', $request->input('albaran_number'));
        }

        if($request->has('date')){
            $query->where('date', $request->input('date'));
        }

        //Save the results
        $receipts = $query->select('id', 'date','albaran_number', 'farmer_id', 'campaign')->get();

        //Return the results
        return $this->respondSuccess($receipts);

    }

    public function viewDetails($id){
        //Get authenticate user
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if the farmer exist
        $receipt = Receipt::with('farmer.address','farm.address','weights', 'cooperative.address')->find($id);
        if(!$receipt) {
            return $this->respondNotFound();
        }

        //Check if cooperative own the receipt
        if($currentUser->cooperative) {
            if($currentUser->cooperative->receipts->contains($receipt)){
                return $this->respondSuccess(['receipt' => $receipt]);
            } else {
                return $this-> respondUnauthorized();
            }
        } else {
            if($currentUser->farmer && $currentUser->farmer->receipts->contains($receipt)){
                return $this->respondSuccess(['receipt' => $receipt]);
            } else {
                return $this-> respondUnauthorized();
            }
        }
    }

    //Delete the receipt
    public function delete($id)
    {
        //Get authenticate user
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if user is a cooperative
        if(!($currentUser->cooperative)) {
            return $this-> respondUnauthorized();
        }

        //Check if the farmer exist
        $receipt = Receipt::find($id);
        if(!$receipt) {
            return $this->respondNotFound();
        }

        //Check if cooperative own the receipt
        if(!($currentUser->cooperative->receipts->contains($receipt))) {
            return $this-> respondUnauthorized();
        }

        //Save the weights in a variable
        $weights = $receipt->weights;

        //Begin transaction
        DB::beginTransaction();
        try
        {
            //Delete each weight asociated with the receipt
            foreach($weights as $weight){
                $weight->delete();
            }

            //Delete the receipt
            $receipt->delete();

            //End the transaction
            DB::commit();

            //Return success message
            return $this->respondSuccess(['message' => 'Receipt deleted']);
        } catch (\Exception $e) {
            //If the transaction have errors, do a rollback
            DB::rollback();

            //Return the error message (Debugging only)
            return $this->respondError($e->getMessage(), 500);
            //TODO: Change in production
            //return $this->respondError('Internal server error', 500);
        }
    }

    //Load last receipt from the cooperative
    public function loadLastReceipt()
    {
        //Get authenticate user
        $currentUser = Auth::user();

        //Check if not null
        if(!$currentUser){
            return $this-> respondUnauthorized();
        }

        //Check if user is a cooperative
        if(!($currentUser->cooperative)) {
            return $this-> respondUnauthorized();
        }

        //Get the last receipt from the cooperative
        $receipt = Receipt::where('cooperative_id', $currentUser->cooperative->id)->orderBy('id', 'desc')->first();

        //Check if the receipt exist
        if(!$receipt) {
            return $this->respondNotFound();
        }

        //Return the receipt
        return $this->respondSuccess(['receipt' => $receipt->only('albaran_number', 'campaign')]);
    }
}
