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
            $data = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                            ->select("reservations.*","outils.nom")
                            ->get();

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
            //\DB::enableQueryLog();

            $validateReq = Validator::make($request->all(),
                [
                    'nom' => 'required',
                    'prenom' => 'required',
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

            //Verifie la disponibilite
            $data = Reservations::leftjoin("outils","reservations.outil_id","=","outils.id")
                                  ->select("outils.nombre")
                                  ->where('reservations.outil_id', $request->outil_id)
                                  ->whereDate('fin','>=',$request->debut)
                                  ->whereDate('debut','<=',$request->fin);
            \Log::info($data->toRawSql());
            $res=$data->get();

            /*$nb=0;
            $nombre=0;
            foreach ($data->get() as $d) {
                $nb++;
                $nombre= $d->nombre;
                \Log::info(print_r($d,true));
            }
            if (($nb>0) && ($nb>=$nombre)) { */
            if ((count($res)>0) && ($res[0]->nombre<=count($res))) {

                \Log::info("Outil $request->outil_id reservé ".count($res)." fois sur ".$res[0]->nombre);
                return response()->json(['status' => false, 'message' => "L'article n'est pas disponible sur la période"]);
            }

            $req = $request->all();
            $req['paiement_state'] = Reservations::PAIEMENT_STATE_NON_PAYE;
            $req['state'] = Reservations::STATE_RESERVE;
            $data = Reservations::create($req);
            return response()->json(['status' => true, 'data' => $data], 201);
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
            $data = Reservations::find($id);

            if (($user->id != $request->user_id) || ($user_id != $data->user_id)) {
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
        public function destroy(int $id): \Illuminate\Http\JsonResponse
        {
            throw_if(!$id, 'reservation Id is missing');
            $data = Reservations::findOrFail($id);
            //Reservations::findOrFail($id)->delete();
            if ($data) {
                if ($data->state == Reservations::STATE_RESERVE) {
                    $data->state = Reservations::STATE_ANNULE;
                }
                JournalReservations::create($data->toArray());
                $data->delete();
            }
            return response()->json(['status' => true, 'message' => 'reservation supprimée']);
        }
}


