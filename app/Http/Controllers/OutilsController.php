<?php

namespace App\Http\Controllers;

use App\Models\Outils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutilsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
 
        //$data = Outils::query()->get();
        $data = Outils::leftjoin("files","outils.file_id","=","files.id")
                        ->select("outils.*","files.file_path")
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
        $data = Outils::where('nom', $request->nom);
        if ($data->first()) {
            return response()->json(['status' => false, 'message' => 'Already exist']);
        }
        $req = $request->all();        
        $data = Outils::create($req);
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
        //$response = Outils::findOrFail($id);
        $response = Outils::leftjoin("files","outils.file_id","=","files.id")
                        ->select("outils.*","files.file_path")
                        ->where("outils.id",'=',$id)
                        ->get();

        return response()->json($response->first(), 200);
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
        $validateUser = Validator::make($request->all(),
            [
                'description' => 'required',
                'nom' => 'required',
            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $data = Outils::find($id);
        $data->nom = $request->nom;
        $data->description = $request->description;
        $data->prix = $request->prix;
        $data->duree = $request->duree;
        $data->nombre = $request->nombre;
        $data->file_id = $request->file_id;
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
        throw_if(!$id, 'Outil Id is missing');
        Outils::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'outil deleted']);
    }
}
