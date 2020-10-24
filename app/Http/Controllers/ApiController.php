<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Alamat;
use App\Models\Toko;
use App\Models\Mitra;
use App\Models\Kategori;
use App\Models\Detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{

    public function __construct()
    {
        //
    }

    public function getLogo(){
        $data  = json_encode( url(Profile::select('logo')->first()->logo), JSON_UNESCAPED_SLASHES);
        return response($data);
    }

    public function getFooterData(){
        $data  = [
                'alamat'=> Alamat::select('icon','header','data','link')->get(),
                'toko'=> Toko::select('icon','header','data','link')->get()
        ];
        return response($data);
    }

    public function getBanner()
    {
        $data  = json_encode(
            url(Profile::select('banner')->first()->banner)
        , JSON_UNESCAPED_SLASHES);
        return response($data);
    }

    public function getProduk(Request $request)
    {
        $base = url('');
        $data = Detail::select(DB::raw("id,nama,produsen,format(harga,0),CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->orderby('order','DESC')
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }
    public function getProdukNew(Request $request)
    {
        $base = url('');
        $data = Detail::select(DB::raw("id,nama,produsen,harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img, created_at"))->orderby('created_at','DESC')
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }

    public function getProdukDetail(Request $request){
        if($request->id != null || $request->id != ""){
            $detail = Detail::select('nama','desk','produsen','harga','url','img','tags')->where('id',$request->id)->first();
            // $kategori = Kategori::select(DB::raw("a.nama, a.url FROM kategori a JOIN detail b ON a.id = b.id_kategori WHERE b.id = '$request->id'"))->get();
            $kategori = Kategori::join('detail', 'kategori.id','=','detail.id_kategori')->select('kategori.nama as nama','kategori.url as url')->where('detail.id','=',$request->id)->first();
            if ($detail === null || $detail===''){
                $data = [
                    'error' => true
                ];
            }else{
                $image = explode('|', $detail->img);
                for ($i=0; $i < count($image); $i++) { 
                    $image[$i] = url($image[$i]);
                }
                $data = 
                [    
                    'error' => false,
                    'nama' => $detail->nama,
                    'deskripsi'=> $detail->desk,
                    'image' => $image,
                    'produsen'=>$detail->produsen,
                    'harga'=>number_format($detail->harga,0,'.',','),
                    
                    'link'=>explode('|',$detail->url),
                    'tags'=>explode('|',$detail->tags),
                    'breadcrumb'=>[
                        [
                            'nama'=>'etalase',
                            'link'=>'/etalase'
                        ],
                        [
                            'nama'=>$kategori->nama,
                            'link'=>$kategori->url
                        ],
                    ],
                ];
            }
        }else{
            $data = [
                'error' => true
            ];
        }
        return response($data);
    }

    public function getKategori(Request $request){
        $base = url('');
        $filter = $request->kategori;
        $kategori = Kategori::select('id')->where('nama','=',$filter)->first();
        if($kategori===0||$kategori===null)$kategori=0;
        else $kategori=$kategori->id;
        $data = Detail::select(DB::raw("id,nama,produsen,harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->where('id_kategori','=',$kategori)
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }

    public function getTag(Request $request){
        $base = url('');
        $tag = $request->tag;
        $data = Detail::select(DB::raw("id,nama,produsen,harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->where('tags','LIKE',"%$tag%")
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }

    public function getCari(Request $request){
        $base = url('');
        $key = $request->key;
        $data = Detail::select(DB::raw("id,nama,produsen,harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->where('nama','LIKE',"%$key%")
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }

    public function getTentang()
    {
        $data = DB::table('tentang')->select('data','header')->get();

        return response($data);
    }

}
