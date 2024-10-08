<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Outils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
 
        $data = Categories::get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexChilds(): \Illuminate\Http\JsonResponse
    {
 
        $data = Categories::get();

        foreach($data as &$cat) {
            $o = Outils::leftjoin("files","outils.file_id","=","files.id")
            ->select("outils.*","files.file_path")
            ->where("categorie_id","=",$cat->id);
        $cat["outils"] = $o->get();
        }

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
        $data = Categories::where('nom', $request->nom);
        if ($data->first()) {
            return response()->json(['status' => false, 'message' => 'Already exist']);
        }
        $req = $request->all();        
        $data = Categories::create($req);
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
        $response = Categories::where("id",'=',$id);

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
                'nom' => 'required'
            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $data = Categories::find($id);
        $data->nom = $request->nom;
        $data->description = $request->description;
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
        throw_if(!$id, 'Id is missing');
        Categories::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'categorie deleted']);
    }
}
