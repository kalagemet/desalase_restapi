<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Alamat;
use App\Models\Toko;
use App\Models\Mitra;
use App\Models\Kategori;
use App\Models\Tentang;
use App\Models\Detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
                'alamat'=> Alamat::select('id','icon','header','data','link')->get(),
                'toko'=> Toko::select('id','icon','header','data','link')->get()
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
        $data = Detail::select(DB::raw("id,nama,produsen,format(harga,0) as harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->orderby('order','DESC')
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }
    public function getProdukNew(Request $request)
    {
        $base = url('');
        $data = Detail::select(DB::raw("id,nama,produsen,format(harga,0) as harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img, created_at"))->orderby('created_at','DESC')
            ->simplePaginate((int) $request->get('limit',10));
        return response()->json($data)->withCallback($request->callback); 
    }

    public function getProdukDetail(Request $request){
        if($request->id != null || $request->id != ""){
            $detail = Detail::select('nama','id_kategori','desk','produsen','harga','url','img','tags')->where('id',$request->id)->first();
            $kategori = Kategori::select('nama')->where('id',$detail->id_kategori)->first()->nama;
            $umkm = '';
            if($detail->id_kategori<7){
                $umkm = 'umkm/';
            }
            if ($detail === null || $detail===''){
                $data = [
                    'error' => true
                ];
            }else{
                $image = [];
                if($detail->img !== ''){
                    $image = explode('|', $detail->img);
                    for ($i=0; $i < count($image); $i++) { 
                        $image[$i] = url($image[$i]);
                    }
                }
                $data = 
                [    
                    'error' => false,
                    'nama' => $detail->nama,
                    'deskripsi'=> $detail->desk,
                    'image' => $image,
                    'produsen'=>' '.$detail->produsen,
                    'harga'=>number_format($detail->harga,0,'.',','),
                    'breadcrumb'=>[
                        [
                            'nama'=>'etalase',
                            'link'=>'/etalase'
                        ],[
                            'nama'=>$kategori,
                            'link'=>'/etalase/'.$umkm.$kategori
                        ],
                    ],
                    'link'=>explode('|',$detail->url),
                    'tags'=>explode('|',$detail->tags),
                    'id_kategori'=>$detail->id_kategori
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
        $data = Detail::select(DB::raw("id,nama,produsen,format(harga,0) as harga,CONCAT('$base',SUBSTRING_INDEX(img,'|',1)) as img"))->where('id_kategori','=',$kategori)
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

    //================================================================
    //cms session
    //================================================================
    function setBanner(Request $request){
        if($request->file('image')->isValid()){
            $file = $request->file('image');
            $db = Profile::find(1);
            if (File::exists(public_path($db->banner))) {
                File::delete(public_path($db->banner));
            }
            $filename = Carbon::now()->format('d-m-yy-His').'.jpg';
            $file->move(public_path('/img/banner/'), $filename);
            $db->banner = '/img/banner/'.$filename;
            $db->save();
            $data = [
                'error' => false,
                'msg' => $file->getClientOriginalName()
            ];
        }else{
            $data = [
                'error' => true,
                'msg' => 'gagal mengunggah !!',
            ];
        }
        return response($data);
    }

    public function getTentangAdmin()
    {
        $data = DB::table('tentang')->select('id','data','header')->get();

        return response($data);
    }
    function putTentang(Request $request)
    {
        // $this->validate($request, [
        //     'header' => 'required',
        //     'data' => 'required'
        // ]);
        $validator = Validator::make($request->all(), [
            'header' => 'required|unique:tentang',
            'data' => 'required'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $tentang = new Tentang;
            $tentang->header = $request->input('header');
            $tentang->data = $request->input('data');
            $exe = $tentang->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }

    function updateTentang(Request $request)
    {
        // $this->validate($request, [
        //     'header' => 'required',
        //     'data' => 'required'
        // ]);
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tentang',
            'header' => 'required',
            'data' => 'required'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $tentang = Tentang::find($request->input('id'));
            $tentang->header = $request->input('header');
            $tentang->data = $request->input('data');
            $exe = $tentang->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }

    function delTentang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tentang',
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $table = Tentang::find($request->input('id'));
            $exe = $table->delete();
            if($exe){
                $data = [
                    'error' => false,
                    'msg' => '',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menghapus !!',
                ];
            }
        }

        return response($data);
    }

    function tambahToko(Request $request){
        $validator = Validator::make($request->all(), [
            'icon' => 'required',
            'header' => 'required|unique:toko',
            'data' => 'required',
            'link' => 'required',
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = new Toko;
            $data->icon = $request->input('icon');
            $data->header = $request->input('header');
            $data->data = $request->input('data');
            $data->link = $request->input('link');
            $exe = $data->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }

    function hapusToko(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:toko'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = Toko::find($request->input('id'));
            $exe = $data->delete();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menghapus !!',
                ];
            }
        }

        return response($data);
    }

    function updateToko(Request $request)
    {
        // $this->validate($request, [
        //     'header' => 'required',
        //     'data' => 'required'
        // ]);
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:toko',
            'icon' => 'required',
            'header' => 'required',
            'data' => 'required',
            'link' => 'required',
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $tentang = Toko::find($request->input('id'));
            $tentang->icon = $request->input('icon');
            $tentang->header = $request->input('header');
            $tentang->data = $request->input('data');
            $tentang->link = $request->input('link');
            $exe = $tentang->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }

    function tambahFoot(Request $request){
        $validator = Validator::make($request->all(), [
            'icon' => 'required',
            'header' => 'required|unique:alamat',
            'data' => 'required',
            'link' => 'required',
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = new Alamat;
            $data->icon = $request->input('icon');
            $data->header = $request->input('header');
            $data->data = $request->input('data');
            $data->link = $request->input('link');
            $exe = $data->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }

    function hapusFoot(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:alamat'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = Alamat::find($request->input('id'));
            $exe = $data->delete();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menghapus !!',
                ];
            }
        }

        return response($data);
    }

    function updateFoot(Request $request)
    {
        // $this->validate($request, [
        //     'header' => 'required',
        //     'data' => 'required'
        // ]);
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:alamat',
            'icon' => 'required',
            'header' => 'required',
            'data' => 'required',
            'link' => 'required',
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $tentang = Alamat::find($request->input('id'));
            $tentang->icon = $request->input('icon');
            $tentang->header = $request->input('header');
            $tentang->data = $request->input('data');
            $tentang->link = $request->input('link');
            $exe = $tentang->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menambahkan !!',
                ];
            }
        }

        return response($data);
    }


    function hapusProduk(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:detail'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = Detail::find($request->input('id'));
            $file = $data->img;
            $file = explode('|', $file);
            foreach($file as $img){
                if (File::exists(public_path($img))) {
                    File::delete(public_path($img));
                }
            }
            $exe = $data->delete();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menghapus !!',
                ];
            }
        }

        return response($data);
    }

    function getKat(){
        $data = Kategori::select('id as key',DB::raw('upper(nama) as text'),'id as value')->get();
        if(count($data)>0){
            $data = [
                'error' => false,
                'data' => $data
            ];
        }
        else{
            $data = [
                'error' => true,
            ];
        }
        return response($data);
    }

    function getTags(){
        $data = Detail::select('tags')->GroupBy('tags')->get();
        $tmp = [];
        if(count($data)>0){
            for($i=0;$i<count($data);$i++){
                $tmp = array_merge($tmp,explode('|',$data[$i]->tags));
            }
            foreach ($tmp as $key => $value) {
                $tmp[$key] = [
                    'key'=>$tmp[$key], 
                    'value' => $tmp[$key], 
                    'text' => $tmp[$key]
                ];
            }
            $data = [
                'error' => false,
                'data' => $tmp
            ];
        }
        else{
            $data = [
                'error' => true,
            ];
        }
        return response($data);
    }

    function addProduk(Request $request){
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'desk' => 'required',
            'img' => 'required',
            'img.*' => 'required|mimes:jpg,jpeg,png|max:2048',
            'produsen' => 'required',
            'harga' => 'required',
            'url' => 'required',
            'tags' => 'required',
            'id_kategori' => 'required'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = new Detail;
            $data->nama = $request->input('nama');
            $data->desk = $request->input('desk');
            $data->img = '';
            $fileName='';
            $file = $request->file('img');
            $upload=true;
            foreach($file as $d) { 
                if($d->isValid()){
                    $fileName = Carbon::now()->format('d-m-yy-His').'_'.str_replace(' ','',$data->nama).'_'.uniqid().'_'.$d->getClientOriginalName();
                    $d->move(public_path('/img/produk/'), $fileName);
                    $data->img = $data->img.'/img/produk/'.$fileName;
                    if($d!==end($file)){
                        $data->img = $data->img.'|';
                    }
                }else{
                    $upload=false;
                }
            }
            if($upload){
                $data->order=0;
                $data->produsen = $request->input('produsen');
                $data->harga = $request->input('harga');
                $data->url = str_replace(' ','',$request->input('url'));
                $data->tags =str_replace(' ','',$request->input('tags'));
                $data->id_kategori = $request->input('id_kategori');
                $exe = $data->save();

                if($exe){
                    $data = [
                        'error' => false,
                        'msg' => 'Berhasil !!',
                    ]; 
                }else{
                    $data = [
                        'error' => true,
                        'msg' => 'query gagal, gagal menyimpan !!',
                    ];
                }
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'gagal mengunggah gambar!! beberapa gambar korup',
                ];
            }
        }

        return response($data);
    }

    function updateProduk(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:detail',
            'nama' => 'required',
            'desk' => 'required',
            // 'img' => 'required',
            'img.*' => 'mimes:jpg,jpeg,png|max:2048',
            'produsen' => 'required',
            'harga' => 'required',
            'url' => 'required',
            'tags' => 'required',
            'id_kategori' => 'required'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = Detail::find($request->input('id'));
            $data->nama = $request->input('nama');
            $data->desk = $request->input('desk');
            $data->produsen = $request->input('produsen');
            $data->harga = $request->input('harga');
            $data->url = str_replace(' ','',$request->input('url'));
            $data->tags =str_replace(' ','',$request->input('tags'));
            $data->id_kategori = $request->input('id_kategori');
            if($request->hasFile('img')){
                $fileName='';
                $file = $request->file('img');
                $upload=true;
                foreach($file as $d) { 
                    if($d->isValid()){
                        $fileName = Carbon::now()->format('d-m-yy-His').'_'.str_replace(' ','',$data->nama).'_'.uniqid().'_'.$d->getClientOriginalName();
                        $d->move(public_path('/img/produk/'), $fileName);
                        if($data->img!==''){
                            $data->img = $data->img.'|';
                        }
                        $data->img = $data->img.'/img/produk/'.$fileName;
                        if($d!==end($file)){
                            $data->img = $data->img.'|';
                        }
                    }
                }
            }
            $exe = $data->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menyimpan !!',
                ];
            }
        }

        return response($data);
    }

    function delGambarProduk(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:detail',
            'data' => 'required'
        ]);
        if($validator->fails()){
            $data = [
                'error' => true,
                'msg' => $validator->errors()->first(),
            ];
        }else{
            $data = Detail::find($request->input('id'));
            $img = explode('/',$request->input('data'));
            $img = '/img/produk/'.$img[count($img)-1];
            $tmp = explode('|',$data->img);
            $data->img='';
            foreach($tmp as $a){
                if($a!==$img){
                    $data->img = $data->img.$a;
                    if($a!==end($tmp)){
                        $data->img = $data->img.'|';
                    }
                }
            }
            if(substr($data->img,-1)==='|'){
                $data->img = substr($data->img, 0, -1);
            }
            if (File::exists(public_path($img))) {
                File::delete(public_path($img));
            }
            $exe = $data->save();

            if($exe){
                $data = [
                    'error' => false,
                    'msg' => 'Berhasil !!',
                ]; 
            }else{
                $data = [
                    'error' => true,
                    'msg' => 'query gagal, gagal menghapus !!',
                ];
            }
        }

        return response($data);
    }
}
