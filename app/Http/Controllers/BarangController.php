<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        //$rsetBarang = Barang::with('barang')->latest()->paginate(10);

        if ($request->search){
            //query builder
            $rsetBarang = DB::table('barang')->select('kategori')
					    ->join('kategori', 'barang.kategori_id', '=', 'kategori.id')
						->select('barang.id','barang.merk','barang.seri','barang.spesifikasi','barang.stok','barang.kategori_id',DB::raw('ketKategori(kategori.kategori) as kat'))
                        ->where('barang.id','like','%'.$request->search.'%')
                        ->orWhere('barang.merk','like','%'.$request->search.'%')
               		    ->paginate(10);
           
        }else {
            $rsetBarang = DB::table('barang')->select('kategori')
                        ->join('kategori', 'barang.kategori_id', '=', 'kategori.id')
                        ->select('barang.id','barang.merk','barang.seri','barang.spesifikasi','barang.stok','barang.kategori_id',DB::raw('ketKategori(kategori.kategori) as kat'))
                        ->paginate(10);
        }


        return view('v_barang.index', compact('rsetBarang'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $akategori = Kategori::all();
        return view('v_barang.create',compact('akategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'merk'          => 'required',
            'seri'          => 'required',
            'spesifikasi'   => 'required',
            'stok'          => 'required',
            'kategori_id'   => 'required',

        ]);
        // Barang::create([
        //     'merk'             => $request->merk,
        //     'seri'             => $request->seri,
        //     'spesifikasi'      => $request->spesifikasi,
        //     'stok'             => $request->stok,
        //     'kategori_id'      => $request->kategori_id,
        // ]);

        try {
            DB::beginTransaction(); // <= Mulai transaksi
        
            // Simpan data barang
            $barang = new Barang();
            $barang->merk = $request->merk;
            $barang->seri = $request->seri;
            $barang->spesifikasi = $request->spesifikasi;
            $barang->stok = 0;
            $barang->kategori_id = $request->kategori_id;
            $barang->save();
        
            DB::commit(); // <= Commit perubahan
        } catch (\Exception $e) {
            report($e);
        
            DB::rollBack(); // <= Rollback jika terjadi kesalahan
            // return redirect()->route('barang.index')->with(['error' => 'gagal menyimpan data.']);
        }

        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rsetBarang = Barang::find($id);
        return view('v_barang.show', compact('rsetBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    //$abarang = Barang::all();
    //$rsetBarang = Barang::find($id);
    //$selectedBarang = Barang::find($rsetBarang->kategori_id);
    //return view('v_barang.edit', compact('rsetBarang', 'abarang', 'selectedBarang'));
    $akategori = Kategori::all();
    $rsetBarang = Barang::find($id);
    $selectedKategori = Kategori::find($rsetBarang->kategori_id);

    return view('v_barang.edit', compact('rsetBarang', 'akategori', 'selectedKategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'merk'        => 'required',
            'seri'        => 'required',
            'spesifikasi' => 'required',
            'stok'        => 'required',
            'kategori_id' => 'required',
        ]);
        
        $rsetBarang = Barang::find($id);

            //update post without image
            $rsetBarang->update([
                'merk'          => $request->merk,
                'seri'          => $request->seri,
                'spesifikasi'   => $request->spesifikasi,
                'stok'          => $request->stok,
                'kategori_id'   => $request->kategori_id,
            ]);

        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Diubah!']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    if (DB::table('barangmasuk')->where('barang_id', $id)->exists()) {
        return redirect()->route('barang.index')->with(['gagal' => 'Gagal dihapus']);
    } elseif (DB::table('barangkeluar')->where('barang_id', $id)->exists()) {
        return redirect()->route('barang.index')->with(['gagal' => 'Gagal dihapus']);
    } else {
        $rsetBarang = Barang::find($id);
        $rsetBarang->delete();
        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
    }

}