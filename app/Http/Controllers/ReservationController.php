<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservations;
use App\Models\JournalReservations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index(): \Illuminate\Http\JsonResponse
        {
           /* $data = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil")
                            ->get();

            return response()->json(['status' => true, 'data' => $data]);*/
            return response()->json(['status' => false, 'data' => 'not implemented']);
        }
    
        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function create()
        {
            //
        }
    
        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function store(Request $request): \Illuminate\Http\JsonResponse
        {            
            //\DB::enableQueryLog();

            $validateReq = Validator::make($request->all(),
                [
                    'nom' => 'required',
                    'telephone' => 'required',
                    'email' => 'required',
                    'outil_id' => 'required',
                    'debut' => 'required',
                    'fin' => 'required'
                ]);
    
            if ($validateReq->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateReq->errors()
                ], 401);
            }


            if (Reservations::est_possible($request->outil_id,$request->debut,$request->fin)) {
                $req = $request->all();
                $req['paiement_state'] = Reservations::PAIEMENT_STATE_NON_PAYE;
                $req['state'] = Reservations::STATE_RESERVE;
                $data = Reservations::create($req);
                return response()->json(['status' => true, 'data' => $data], 201);

            } else {
                return response()->json(['status' => false, 'message' => "L'article n'est pas disponible sur la période"]);
            }
        }
    
        /**
         * Display the specified resource.
         *
         * @param int $reference
         * @return \Illuminate\Http\JsonResponse
         */
        public function show($reference): \Illuminate\Http\JsonResponse
        {
            $response = Reservations::where('reference','=',$reference)->first();
            
            return response()->json($response, 200);
        }
    
        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         * @return \Illuminate\Http\Response
         */
        public function edit($id)
        {
            //
        }
    
        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function update(Request $request, $reference): \Illuminate\Http\JsonResponse
        {
            $validateReq = Validator::make($request->all(),
                [
                    'nom' => 'required',
                    'outil_id' => 'required',
                    'debut' => 'required',
                    'fin' => 'required'
                ]);
    
            if ($validateReq->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateReq->errors()
                ], 401);
            }
            $user = Auth()->user();
            $data = Reservations::where('reference','=',$reference)->first();

            if (($user == null) || (!$user->is_admin()) ) {  
                return response()->json([
                    'status' => false,
                    'message' => 'Non authorisé',
                    'errors' => $validateReq->errors()
                ], 401);
            }
    
            $data->outil_id = $request->outil_id;
            $data->debut = $request->debut;
            $data->fin = $request->fin;
            $data->update();
            return response()->json(['status' => true, 'data' => $data], 202);
        }
    
        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         * @throws \Throwable
         */
        public function destroy(string $reference): \Illuminate\Http\JsonResponse
        {
            throw_if(!$reference, 'reservation Id is missing');
            $data = Reservations::where('reference','=',$reference)->first();
            //Reservations::findOrFail($id)->delete();
            if ($data) {
                if (($data->state == Reservations::STATE_RESERVE) || ($data->state == Reservations::STATE_PAIEMENT)) {
                    $data->state = Reservations::STATE_ANNULE;
                    JournalReservations::create($data->toArray());
                    $data->delete();
                    return response()->json(['status' => true, 'message' => 'reservation supprimée']);
                }
            }
            return response()->json(['status' => false, 'message' => 'Non authorisé'],401);
        }
}


