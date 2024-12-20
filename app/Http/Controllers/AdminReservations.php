<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservations;
use App\Models\JournalReservations;
use Illuminate\Support\Facades\Validator;

class AdminReservations extends Controller
{
    
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index(): \Illuminate\Http\JsonResponse
        {
           // \DB::enableQueryLog();
            $data = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom as nomoutil")
                            ->get();

           // \Log::info(\DB::getQueryLog());
           // \Log::info(print_r($data,true));
                
            return response()->json(['status' => true, 'data' => $data]);
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
            if (Reservation::est_possible($request->outil_id,$request->debut,$request->fin)) {
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
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function show($id): \Illuminate\Http\JsonResponse
        {
            $response = Reservations::findOrFail($id);
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
        public function update(Request $request, $id): \Illuminate\Http\JsonResponse
        {
            $validateReq = Validator::make($request->all(),
                [
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
    
            $data = Reservations::find($id);
            $data->outil_id = $request->outil_id;
            $data->debut = $request->debut;
            $data->fin = $request->fin;
            $data->paiement_state = $request->paiement_state;
            $data->commentaire = $request->commentaire;            
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
        public function destroy(int $id): \Illuminate\Http\JsonResponse
        {
            throw_if(!$id, 'reservation Id is missing');
            $data = Reservations::find($id);
            //Reservations::findOrFail($id)->delete();
            if ($data) {
                JournalReservations::create($data->toArray());
                $data->delete();
            }
            return response()->json(['status' => true, 'message' => 'reservation supprimée']);
        }


        /**
         * Display the specified resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function historique(): \Illuminate\Http\JsonResponse
        {
            $data = JournalReservations::leftjoin("outils","journal_reservations.outil_id","=","outils.id")
            ->select("journal_reservations.*","outils.nom as nomoutil")
            ->get();

            return  response()->json(['status' => true, 'data' => $data]);
        }

}
