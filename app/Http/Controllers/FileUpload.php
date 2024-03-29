<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\File;
class FileUpload extends Controller
{
  public function createForm(){
    //return view('file-upload');
  }
  public function fileUpload(Request $req){
        $req->validate([
        'file' => 'required|mimes:csv,txt,xlx,xls,pdf,jpg,png,webp|max:2048'
        ]);
        $fileModel = new File;
        if($req->file()) {
            $fileName = time().'_'.$req->file->getClientOriginalName();
            $filePath = $req->file('file')->storeAs('uploads', $fileName, 'public');
            $fileModel->name = time().'_'.$req->file->getClientOriginalName();
            $fileModel->file_path = '/storage/' . $filePath;
            $fileModel->save();
            return response()->json(['status' => true, 'filename' => $fileModel->file_path, 'fileid' => $fileModel->id]);
        }
   }
}