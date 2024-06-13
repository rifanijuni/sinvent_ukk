<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $akategori = array(
            'M' => 'Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );

        if ($request->search){
            //query builder
            $rsetKategori = DB::table('kategori')->select('id','deskripsi',DB::raw('ketKategori(kategori) as kat'))
                                                 ->where('id','like','%'.$request->search.'%')
                                                 ->orWhere('deskripsi','like','%'.$request->search.'%')
                                                 ->orWhere('kategori','like','%'.$request->search.'%')
                                                 ->paginate(10);
           
        }else {
            $rsetKategori = DB::table('kategori')->select('id','deskripsi',DB::raw('ketKategori(kategori) as kat'))->paginate(10);
        }

        return view('v_kategori.index', compact('rsetKategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $akategori = array(
            'blank' => 'Pilih Kategori',
            'M' => 'Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );
        return view('v_kategori.create', compact('akategori'));

        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //cek data
        //echo "data deskripsi";
        //return $request->deskripsi;
        //die('asd');
        
        $request->validate([
            'deskripsi' => 'required',
            'kategori' => 'required',
        ]);

        // Create a new Kategori
        Kategori::create([
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);
        

        // Redirect to index
        return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rsetKategori = Kategori::find($id);
        return view('v_kategori.show', compact('rsetKategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $akategori = array(
            'blank' => 'Pilih Kategori',
            'M' => 'Kategori Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );

        $rsetKategori = Kategori::find($id);
        return view('v_kategori.edit', compact('rsetKategori', 'akategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'deskripsi' => 'required',
            'kategori' => 'required',
        ]);

        $rsetKategori = Kategori::find($id);
        $rsetKategori->update($request->all());

        return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()){
            return redirect()->route('kategori.index')->with(['gagal' => 'gagal dihapus']);
        } else {
            $rseKategori = Kategori::find($id);
            $rseKategori->delete();
            return redirect()->route('kategori.index')->with(['success' => 'berhasil dihapus']);
        }
    }

    // API

    function getAPIKategori() {
	    $kategori = Kategori::all();
	    $data = array("data"=>$kategori);

	    return response()->json($data);
	}

	function getAPIOneKategori($id) {
		$kategori = Kategori::find($id);
		if(null == $kategori){
			return response()->json(['status' => "Kategori tidak ditemukan"]);
		}
	    return response()->json(["data" => $kategori]);
	}

    function createAPIKategori(Request $request)
    {
        $validatedData = $request->validate([
            'deskripsi' => 'required|string|max:255',
            'kategori' => 'required|string|max:3'
        ]);

        $kategori = Kategori::create([
            'deskripsi' => $validatedData['deskripsi'],
            'kategori' => $validatedData['kategori']
        ]);

        return response()->json([
            'data' => [
                'id' => $kategori->id,
                'created_at' => $kategori->created_at,
                'updated_at' => $kategori->updated_at,
                'deskripsi' => $kategori->deskripsi,
                'kategori' => $kategori->kategori
            ]
        ], 201); 
    }
    
    function updateAPIKategori(Request $request, string $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['status' => "Kategori Tidak Ditemukan"], 404);
        }
        //dd($request->deskripsi);

        $kategori->deskripsi=$request->deskripsi;
        $kategori->kategori=$request->kategori;
        $kategori->save();

        return response()->json(['status' => "Kategori Berhasil Diupdate", "data" => $kategori], 200);
    }

    
    function deleteAPIKategori(string $id)
    {
        if (DB::table('barang')->where('kategori_id', $id)->exists()){
            // Menambahkan return response dengan status 500
            return response()->json(['error' => 'kategori tidak dapat dihapus'], 500);
        } else {
            $rseKategori = Kategori::find($id);
            if ($rseKategori) {
                $rseKategori->delete();
                return response()->json(['success' => 'Berhasil dihapus'], 200);
            } else {
                return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
            }
        }
    }

}