<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Books;
use App\Rejectbooks;
use App\ReturnBooks;
use App\Detailborrowbooks;
use App\Borrowbooks;
use Carbon\Carbon;
use PDF;
use DB;
use Auth;

class ReturnbooksController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
     $pengembalian = Pengembalianbarang::all();
    //  $detailpeminjaman = DB::table('detail_peminjaman')->where('no_pinjam','=',$pengembalian->kode_peminjaman)->pluck('nama_barang','quantity','satuan');
     return view('pengembalian.listpengembalian', ['pengembalian' => $pengembalian]);
    }  
    public function indexoperator(Request $request)
    {
     $pengembalian = Pengembalianbarang::all();
    //  $detailpeminjaman = DB::table('detail_peminjaman')->where('no_pinjam','=',$pengembalian->kode_peminjaman)->pluck('nama_barang','quantity','satuan');
    $detailpeminjaman = DB::table('detail_peminjaman')->where('no_peminjaman','=','36')
    ->join('inventaris', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
    ->select('detail_peminjaman.no_peminjaman', 'Inventaris.nama','detail_peminjaman.quantity')
    ->get();
     return view('pengembalian.listoperator', ['pengembalian' => $pengembalian,'detailpeminjaman' => $detailpeminjaman]);
    }   
    public function modalpengembalian(Request $request,$no_pengembalian)
    {
        $detailpengembalian = DB::table('detail_peminjaman')
        ->join('inventaris', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
        ->select('detail_peminjaman.no_peminjaman', 'Inventaris.nama','detail_peminjaman.quantity')
        ->where('detail_peminjaman.no_peminjaman','=',$no_pinjam && 'detail_peminjaman.status_detail','=','1')
        ->get();
        return view('pengembalian.modalpengembalian', ['detailpengembalian' => $detailpengembalian]);
    } 
    public function modalpengembalianoperator(Request $request,$no_pinjam)
    {
        $detailpengembalian = DB::table('detail_peminjaman')
        ->join('inventaris', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
        ->select('detail_peminjaman.no_peminjaman', 'Inventaris.nama','detail_peminjaman.quantity')
        ->where('detail_peminjaman.no_peminjaman','=',$no_pinjam && 'detail_peminjaman.status_detail','=','1')
        ->get();
        return view('pengembalianoperator.modalpengembalianoperator', ['detailpengembalian' => $detailpengembalian]);
    }  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    function pdf()
    {
     $pdf = \App::make('dompdf.wrapper');
     $pdf->loadHTML($this->convert_pengembalian_data_to_html());
     return $pdf->stream();
    }

    function convert_pengembalian_data_to_html()
    {
     $pengembalian_data = Pengembalianbarang::all();
     $output = '
     <h3 align="center">Pengembalian Data</h3>
     <table width="100%" style="border-collapse: collapse; border: 0px;">
      <tr>
    <th style="border: 1px solid; padding:12px;" width="20%">No pengembalian</th>
    <th style="border: 1px solid; padding:12px;" width="30%">kode pinjam</th>
    <th style="border: 1px solid; padding:12px;" width="15%">Tanggal mengembalikan</th>
    <th style="border: 1px solid; padding:12px;" width="15%">status pengembalian</th>
    <th style="border: 1px solid; padding:12px;" width="20%">keterangan</th>
   </tr>
     ';  
     foreach($pengembalian_data as $peminjamans)
     {
    if($peminjamans->status_pengembalian == '1'){
        $sts='belum dikembalikan';
    } else{
        $sts='sudah dikembalikan';
    }
      $output .= '
      <tr>
       <td style="border: 1px solid; padding:12px;">'.$peminjamans->no_pengembalian.'</td>
       <td style="border: 1px solid; padding:12px;">'.$peminjamans->kode_peminjaman.'</td>
       <td style="border: 1px solid; padding:12px;">'.$peminjamans->tanggal_pengembalian.'</td>
       <td style="border: 1px solid; padding:12px;">'.$sts.'</td>
       <td style="border: 1px solid; padding:12px;">'.$peminjamans->keterangan.'</td>
      </tr>
      ';
     }
     $output .= '</table>';
     return $output;
    }
    public function getPeminjaman(Request $request){
        $peminjaman1 = Peminjamanbarang::where('no_pinjam','=',$request->no_pinjam)->firstOrFail();
        $detailpeminjaman1 = Detailpeminjaman::where('no_peminjaman','=',$request->no_pinjam)->join('inventaris', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
        ->select('detail_peminjaman.id_inventaris', 'inventaris.nama','detail_peminjaman.quantity')
        ->get();
        return response()->json(['peminjaman1' => $peminjaman1,'detailpeminjaman1' => $detailpeminjaman1]);
    }
    
    public function delete($no_pengembalian, $no_pinjam)
    {
        Pengembalian::destroy($no_pengembalian);
        Peminjaman::destory($no_pinjam);
        Detailpeminjamam::destroy($no_pinjam);
        return redirect('pengembalian');
    }
    public function create(Request $request)
    {
          if ($request->isMethod('get')){
            $peminjaman = DB::table('peminjaman_barang')->where('status_peminjaman','=','1')->where('approval_status','=','1')->pluck('no_pinjam');
            $peminjamanvalidasi = Peminjamanbarang::all();
            $inventaris = DB::table('inventaris')
            ->join('ruang', 'ruang.id_ruang', '=', 'inventaris.id_ruang')
            ->join('kategoris', 'kategoris.id_kategori', '=', 'inventaris.id_jenis')
            ->select('inventaris.id_inventaris', 'inventaris.nama','ruang.nama_ruang','ruang.kode_ruang')->where('status_inventaris','=','0')
            ->get();
            $pegawais = DB::table("pegawais")->where('nip','=','1')
            ->pluck("nip","first_name","last_name");
            $record = Pengembalianbarang::orderBy('no_pengembalian', 'DESC')->first();
            $expNum = explode('-', $record->no_pengembalian);
            if($expNum == TRUE){
                $nextInvoiceNumber = $expNum[0]+1;
            } else {
                $nextInvoiceNumber = '171200001';
            }
            return view('pengembalian.pengembalian_add', ['peminjaman' => $peminjaman, 'inventaris' => $inventaris, 'pegawais' => $pegawais,'nextInvoiceNumber' => $nextInvoiceNumber]);
          }
            else {
                $rules = [
                    'no_pengembalian' => 'required|string',
                    'no_pinjam' => 'required|string',
                    'tgl_pinjam' => 'required|date',
                    'id_pegawai' => 'required|string',
                    'keterangan' => 'string',
                    ''
                ];
                $this->validate($request, $rules);
                $pengembalianbarang = new Pengembalianbarang;
                $pengembalianbarang->no_pengembalian = $request->no_pengembalian;
                $pengembalianbarang->kode_peminjaman = $request->no_pinjam;
                $pengembalianbarang->tanggal_pengembalian = Carbon::now();
                $pengembalianbarang->status_pengembalian = '1';
                $pengembalianbarang->keterangan = $request->keterangan;
                $pengembalianbarang->user_id=Auth::user()->id;
                $pengembalianbarang->save();

                $peminjamanbarang = Peminjamanbarang::where('no_pinjam','=',$request->no_pinjam)->firstOrFail();
                $peminjamanbarang->status_peminjaman='0';
                $peminjamanbarang->save();
            
                if(count($request->id_inventaris) > 0){
                    foreach($request->id_inventaris as $item => $v){
                        $datong = array(
                          'status_inventaris' => '0',
                        );
                        Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                    }
                }


                if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                } else if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak == null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                        }
                    }
                }
                else if($request->jumlah_barang_baik == NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                 }     
             }
             return redirect('pengembalianinventaris')->with('success','Peminjaman barang baru created successfully');
    }
    public function createoperator(Request $request)
    {
          if ($request->isMethod('get')){
            $peminjaman = DB::table('peminjaman_barang')->where('status_peminjaman','=','1')->where('approval_status','=','1')->pluck('no_pinjam');
            $peminjamanvalidasi = Peminjamanbarang::all();
            $inventaris = DB::table('inventaris')
            ->join('ruang', 'ruang.id_ruang', '=', 'inventaris.id_ruang')
            ->join('kategoris', 'kategoris.id_kategori', '=', 'inventaris.id_jenis')
            ->select('inventaris.id_inventaris', 'inventaris.nama','ruang.nama_ruang','ruang.kode_ruang')->where('status_inventaris','=','0')
            ->get();
            $pegawais = DB::table("pegawais")->where('nip','=','1')
            ->pluck("nip","first_name","last_name");
            $record = Pengembalianbarang::orderBy('no_pengembalian', 'DESC')->first();
            $expNum = explode('-', $record->no_pengembalian);
            if($expNum == TRUE){
                $nextInvoiceNumber = $expNum[0]+1;
            } else {
                $nextInvoiceNumber = '171200001';
            }
            return view('pengembalian.pengembalianoperator', ['peminjaman' => $peminjaman, 'inventaris' => $inventaris, 'pegawais' => $pegawais,'nextInvoiceNumber' => $nextInvoiceNumber]);
          }
            else {
                $rules = [
                    'no_pengembalian' => 'required|string',
                    'no_pinjam' => 'required|string',
                    'tgl_pinjam' => 'required|date',
                    'id_pegawai' => 'required|string',
                    'keterangan' => 'string',
                ];
                $this->validate($request, $rules);
                $pengembalianbarang = new Pengembalianbarang;
                $pengembalianbarang->no_pengembalian = $request->no_pengembalian;
                $pengembalianbarang->kode_peminjaman = $request->no_pinjam;
                $pengembalianbarang->tanggal_pengembalian = Carbon::now();
                $pengembalianbarang->status_pengembalian = '1';
                $pengembalianbarang->keterangan = $request->keterangan;
                $pengembalianbarang->user_id=Auth::user()->id;
                $pengembalianbarang->save();

                $peminjamanbarang = Peminjamanbarang::where('no_pinjam','=',$request->no_pinjam)->firstOrFail();
                $peminjamanbarang->status_peminjaman='0';
                $peminjamanbarang->save();
            
                if(count($request->id_inventaris) > 0){
                    foreach($request->id_inventaris as $item => $v){
                        $datong = array(
                          'status_inventaris' => '0',
                        );
                        Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                    }
                }


                if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                } else if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak == null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                        }
                    }
                }
                else if($request->jumlah_barang_baik == NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                 } 
               
                return redirect('pengembalianoperator')->with('success','Peminjaman barang baru created successfully');    
             }
    }
    public function update(Request $request, $no_pinjam, $id_barang)
    {
        if ($request->isMethod('get')){
            $peminjaman = DB::table('peminjaman_barang')->where('status_peminjaman','=','1')->pluck('no_pinjam');
            $peminjamanvalidasi = Peminjamanbarang::all();
            $inventaris = DB::table('inventaris')
            ->join('ruang', 'ruang.id_ruang', '=', 'inventaris.id_ruang')
            ->join('kategoris', 'kategoris.id_kategori', '=', 'inventaris.id_jenis')
            ->select('inventaris.id_inventaris', 'inventaris.nama','ruang.nama_ruang','ruang.kode_ruang')->where('status_inventaris','=','0')
            ->get();
            $pegawais = DB::table("pegawais")->where('nip','=','1')
            ->pluck("nip","first_name","last_name");
            $record = Pengembalianbarang::latest()->first();
            $expNum = explode('-', $record->no_pengembalian);

            if($expNum == TRUE){
                $nextInvoiceNumber = $expNum[0]+1;
            } else {
                $nextInvoiceNumber = '161100001';
            }
            return view('pengembalian.pengembalianoperator', ['peminjaman' => $peminjaman, 'inventaris' => $inventaris, 'pegawais' => $pegawais,'nextInvoiceNumber' => $nextInvoiceNumber]);
          }
            else {
                $rules = [
                    'no_pengembalian' => 'required|string',
                    'no_pinjam' => 'required|string',
                    'tgl_pinjam' => 'required|date',
                    'id_pegawai' => 'required|string',
                    'keterangan' => 'string',
                ];
                $this->validate($request, $rules);
                $pengembalianbarang = new Pengembalianbarang;
                $pengembalianbarang->no_pengembalian = $request->no_pengembalian;
                $pengembalianbarang->kode_peminjaman = $request->no_pinjam;
                $pengembalianbarang->tanggal_pengembalian = Carbon::now();
                $pengembalianbarang->status_pengembalian = '1';
                $pengembalianbarang->keterangan = $request->keterangan;
                $pengembalianbarang->user_id=Auth::user()->id;
                $pengembalianbarang->save();

                $peminjamanbarang = Peminjamanbarang::where('no_pinjam','=',$request->no_pinjam)->firstOrFail();
                $peminjamanbarang->status_peminjaman='0';
                $peminjamanbarang->save();
            
                if(count($request->id_inventaris) > 0){
                    foreach($request->id_inventaris as $item => $v){
                        $datong = array(
                          'status_inventaris' => '0',
                        );
                        Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                    }
                }


                if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                } else if($request->jumlah_barang_baik != NULL && $request->jumlah_barang_rusak == null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datang = array(
                              'jumlah' =>  $inventarisbaik->jumlah + $request->jumlah_barang_baik[$item],
                            );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                        }
                    }
                }
                else if($request->jumlah_barang_baik == NULL && $request->jumlah_barang_rusak != null){
                    if(count($request->id_inventaris) > 0){
                        foreach($request->id_inventaris as $item => $v){
                            $inventarisrusak = Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                            $datong = array(
                                'quantity_rusak' =>  $inventarisrusak->quantity_rusak + $request->jumlah_barang_rusak[$item],
                              );
                            Inventarisrusak::where('id_inventaris','=',$request->id_inventaris[$item])->update($datong);
                        }
                    }
                 } 
               
                return redirect('pengembalianoperator')->with('success','Peminjaman barang baru created successfully');    
             }    
    }
     public function show(Request $request, $id)
    {
        if ($request->isMethod('get')){
            $peminjaman = DB::table('peminjaman_barang')->where('status_peminjaman','=','1')->pluck('no_pinjam');
            $peminjamanvalidasi = Peminjamanbarang::all();
            $barang = DB::table('barangs')->where('status','=','1')
            ->pluck('id_barang','nama_barang','quantity','satuan');
            $pegawais = DB::table("pegawais")->where('nip','=','1')
            ->pluck("nip","first_name","last_name");
            return view('pengembalian.pengembalian_add', ['peminjaman' => $peminjaman, 'barang' => $barang, 'pegawais' => $pegawais]);
          }
            else {
                $rules = [
                    'no_pengembalian' => 'required|string',
                    'no_pinjam' => 'required|string',
                    'tgl_pinjam' => 'required|date',
                    'id_pegawai' => 'required|string',
                    'keterangan' => 'string',
                    'id_barang' => 'required|string',
                    'nama_barang' => 'required|string',
                    'quantity' => 'string',
                    'status_barang' => 'required|string',
                ];
                $this->validate($request, $rules);
                $pengembalianbarang = new Pengembalianbarang;
                $pengembalianbarang->no_pengembalian = $request->no_pengembalian;
                $pengembalianbarang->kode_peminjaman = $request->no_pinjam;
                $pengembalianbarang->tanggal_pengembalian = Carbon::now();
                $pengembalianbarang->status_pengembalian = '1';
                $pengembalianbarang->keterangan = $request->keterangan;
                $pengembalianbarang->save();

                $peminjamanbarang = Peminjamanbarang::where('no_pinjam','=',$request->no_pinjam)->firstOrFail();
                $peminjamanbarang->status_peminjaman='0';
                $peminjamanbarang->save();
            
                $detailpeminjaman = Detailpeminjaman::where('no_peminjaman','=',$request->no_pinjam)->firstOrFail();
                $detailpeminjaman->status_detail = '0';        
                $detailpeminjaman->save();


                if($request->status_barang == '1' && $request->jumlah_barang_baik != null && $request->jumlah_barang_rusak == null){
                   
                    $barangbenar = Barang::where('id_barang','=',$request->id_barang)->firstOrFail();
                    $barangbenar->quantity = $barangbenar->quantity + $request->jumlah_barang_baik;
                    $barangbenar->save();
                } else if($request->status_barang == '0' && $request->jumlah_barang_rusak != null && $request->jumlah_barang_baik == null ){
                   
                    $barangrusak = Barangrusak::where('id_barang','=', $request->id_barang)->firstOrFail();
                    $barangrusak->quantity_rusak = $basrangrusak->quantity_rusak + $request->jumlah_barang_buruk;
                    $barangrusak->save();
                } else if($request->status_barang == '1' && $request->jumlah_barang_rusak == null && $request->jumlah_barang_baik == null){
                  
                    $barangbenar = Barang::where('id_barang','=', $request->id_barang)->firstOrFail();
                    $barangbenar->quantity = $barangbenar->quantity + $request->quantity;
                    $barangbenar->save();
                
              } else if($request->status_barang == '0' && $request->jumlah_barang_rusak == null && $request->jumlah_barang_baik == null ){
                 $barangrusak = Barangrusak::where('id_barang','=', $request->id_barang)->firstOrFail();
                 $barangrusak->quantity_rusak = $barangrusak->quantity_rusak + $request->quantity;
                 $barangrusak->save();
            } else if($request->status_barang == '0' && $request->jumlah_barang_rusak != null && $request->jumlah_barang_baik != null ){
                $barangbenar = Barang::where('id_barang','=',$request->id_barang)->firstOrFail();
                $barangbenar->quantity = $barangbenar->quantity + $request->jumlah_barang_baik;
                $barangbenar->save();

                $barangrusak = Barangrusak::where('id_barang','=', $request->id_barang)->firstOrFail();
                $barangrusak->quantity_rusak = $barangrusak->quantity_rusak + $request->jumlah_barang_buruk;
                $barangrusak->save();

            }
                return redirect('pengembalianbarang')->with('success','Peminjaman barang baru created successfully');    
             }
    }
    public function rekapdatabulan(Request $request){
        $tglawal = $request->tgl_awal;
        $tglakhir = $request->tgl_akhir;
        $rekapbulanan = Pengembalianbarang::whereBetween('created_at', [$tglawal, $tglakhir])->get();
        return response()->json(['rekapbulanan' => $rekapbulanan]);
    }
    public function reportdatabulan(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $tglawal = $request->tgl_awal;
     $tglakhir = $request->tgl_akhir;
     $detailpeminjaman = DB::table('detail_peminjaman')->where('no_peminjaman','=','36')
     ->join('inventaris', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
     ->select('detail_peminjaman.no_peminjaman', 'Inventaris.nama','detail_peminjaman.quantity')
     ->whereBetween('created_at', [$tglawal, $tglakhir])->get();
     $pdf->loadView('pengembalian.pdfpengembaliantanggal', ['peminjaman' => $peminjaman]);
     return $pdf->download('peminjaman_data_terbesar.pdf');
    }
}
