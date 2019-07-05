<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Detail_borrow_books;
use App\Payment;
use App\Borrowbooks;
use Carbon\Carbon;
use DB;
use PDF;
use Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $pembayaran = DB::table("pembayaran")
        ->join('bank', 'bank.id_bank','=','pembayaran.id_bank')
        ->select('pembayaran.id_pembayaran','pembayaran.id_borrowing_books','bank.nama_bank', 'pembayaran.no_rekening','pembayaran.atas_nama', 'pembayaran.status')
        ->get();
        return view('payment.listpayment', ['pembayaran' => $pembayaran]);
    } 
    public function index_customer(Request $request)
    {
        $pembayaran = DB::table("pembayaran")->where('id_user','=',Auth::user()->id)
        ->join('bank', 'bank.id_bank','=','pembayaran.id_bank')
        ->select('pembayaran.id_pembayaran','pembayaran.id_borrowing_books','bank.nama_bank', 'pembayaran.no_rekening','pembayaran.atas_nama', 'pembayaran.status')
        ->get();
        return view('borrow_books.list_borrow_customer', ['borrowbooks' => $borrowbooks, 'detailborrowbooks' => $detailborrowbooks]);
    }
    public function index_payment_user($request){
        $payment = Payment::where('status','=','0' && 'id_users','=',Auth::user()->id)->latest()->first();
        $borrowbooks = Borrowbooks::where('status_payment','=','0' && 'id_users','=',Auth::user()->id)->latest()->first();
        $detailborrowbooks = DB::table('detail_borrowing_books')->where('id_borrow_books','=',$borrowbooks->id_detail_books && 'id_users','=',Auth::user()->id)
            ->where('status_payment','=','0')->where('status_transaction','=','0')
            ->join('borrowing_books', 'borrowing_books.id_borrow_books', '=', 'detail_borrowing_books.id_inventaris')
            ->select('detail_borrowing_books.id_borrowing_books', 'detail_borrowing_books.id_users', 'detail_borrowing_books.status_transaction','detail_borrowing_books.id_books', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'status_condition_books', 'status_return','total_price' )
            ->get();
        return view('borrow_books.list_books_customer', ['productbooks' => $productbooks]);
    }
    public function add_payment_user(Request $request){
        $pembayarandefault = Payment::where('status','=','0' && 'id_users','=',Auth::user()->id)->latest()->first();
        $paymentid = Payment::latest()->first();
        $expNum = explode('-', $paymentid->id_pembayaran);
            if ( date('l',strtotime(date('Y-01-01'))) ){
                $nextInvoiceNumber = date('Y').'-0001';
            } else {
                //increase 1 with last invoice number
                $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
            }            
        $rules = [
            'id_pembayaran' => 'string',
            'id_borrowing_books' => 'string',
            'id_users' => 'string',
            'id_bank' => 'string',
            'no_rekening' => 'integer',
            'atas_nama' => 'string'    
        ];
        $this->validate($request, $rules);
        if($pembayarandefault->id_pembayaran == null){
        $pembayaran = new Pembayaran;
        $pembayaran->id_pembayaran = $nextInvoiceNumber;
        $pembayaran->id_users = Auth::user()->id;
        $pembayaran->id_bank = $request->id_bank;
        $pembayaran->atas_nama = $request->atas_nama;
        $pembayaran->no_rekening = $request->no_rekening;
        $pembayaran->status = '0';
        $pembayaran->save();
        return redirect('/pembayaran_customer');
        }elseif($borrowdefault->id_borrowing_books != null){
            return redirect('/pembayaran_customer');
        } 
    } 
    public function history_payment_admin(Request $request)
    {
        $payment = Payment::get();
        return view('payment.history_payment_admin', ['payment' => $payment]);
    }
    public function history_payment_customer(Request $request)
    {
        $payment = Payment::where('id_users','=',Auth::user()->id)->get();
        return view('payment.history_payment_customer', ['payment' => $payment]);
    } 
    
    public function pdf()
    {
     $pdf = \App::make('dompdf.wrapper');
     $payment = DB::table('payment')
     ->join('users', 'users.id', '=', 'pembayaran.id_users')
     ->select('pembayaran.id_pembayaran','pembayaran.id_borrowing_books','users.name','pembayaran.id_bank', 'pembayaran.no_rekening','pembayaran.atas_nama', 'pembayaran.status')
     ->get();
     $pdf->loadView('payment.pdfpayemnt', ['payment' => $payment]);
     return $pdf->download('payment_admin.pdf');
    }    
    public function pdf_customer(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $payment = DB::table('payment')
     ->join('users', 'users.id', '=', 'pembayaran.id_users')
     ->select('pembayaran.id_pembayaran','pembayaran.id_borrowing_books','users.name','pembayaran.id_bank', 'pembayaran.no_rekening','pembayaran.atas_nama', 'pembayaran.status')
     ->get();
     $borrowbooks = Borrowbooks::where('id_borrowing_books','=',$payment->id_borrowing_books)
     ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
     ->select('borrowing_books.id_borrowing_books', 'users.name', 'borrowing_books.tgl_peminjaman','borrowing_books.tgl_pengembalian', 'borrowing_books.total_price', 'borrowing_books.status_payment')
     ->get();
     $detail_borrowing_books = DB::table('detail_borrowing_books')
     ->join('books', 'books.id_books', '=', 'detail_borrowing_books.id_books')
     ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
     ->select('id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
     ->where('detail_borrowing_books.id_borrowing_books','=',$payment->id_borrowing_books)
     ->get();
     $pdf->loadView('payment.pdf_customer', ['payment' => $payment,'borrowbooks' => $borrowbooks,'detail_borrowing_books']);
     return $pdf->download('payment_customer.pdf');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function convertdate(){
        date_default_timezone_set('Asia/jakarta');
        $date = date('ddmmyy');
        return $date;
    }
    public static function approveindex() {
        $peminjamanapproved = DB::table('pembayaran')
        ->join('bank', 'bank.id_bank', '=', 'pembayaran.id_bank')
        ->join('users','users.id','=', 'users.id')
        ->select('pembayaran.id_pembayaran','pembayaran.id_borrowing_books','users.name','bank.nama_bank', 'pembayaran.no_rekening','pembayaran.atas_nama', 'pembayaran.status')
        ->where('peminjaman_barang.approval_status','=','0')
        ->get();
        return view('peminjaman_approve.listpeminjaman_approve', compact('peminjamanapproved'));
    }
    public function approve(Request $request,$id_pembayaran){
        $rules = [
            'status' => 'boolean'    
        ];
        $this->validate($request, $rules);
        $pembayaran = Pembayaran::where('id_pembayaran','=',$id_pembayaran);
        $pembayaran->status = '1';
        $pembayaran->save();
        return redirect('/pembayaran_approval');
    }
    public function create(Request $request)
    {
        if ($request->isMethod('get')){
            $bank = DB::table('bank')->pluck('id_bank','nama_bank');
            $borrowing = DB::table("borrowing_books")
            ->pluck("id_borrowing","id_users");
            $paymentid = Payment::latest()->first();
            $expNum = explode('-', $paymentid->id_pembayaran);
                if ( date('l',strtotime(date('Y-01-01'))) ){
                    $nextInvoiceNumber = date('Y').'-0001';
                } else {
                    //increase 1 with last invoice number
                    $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
                }            
            return view('payment.payment_add', ['bank' => $bank,'borrowing' => $borrowing, 'nextInvoiceNumber' => $nextInvoiceNumber,'detailpeminjaman' => $detailpeminjaman]);
          } else {
            $rules = [
                'id_pembayaran' => 'string',
                'id_borrowing_books' => 'string',
                'id_users' => 'string',
                'id_bank' => 'string',
                'no_rekening' => 'integer',
                'atas_nama' => 'string'    
            ];
            $this->validate($request, $rules);
            if($pembayarandefault->id_pembayaran == null){
            $pembayaran = new Pembayaran;
            $pembayaran->id_pembayaran = $nextInvoiceNumber;
            $pembayaran->id_users = Auth::user()->id;
            $pembayaran->id_bank = $request->id_bank;
            $pembayaran->atas_nama = $request->atas_nama;
            $pembayaran->no_rekening = $request->no_rekening;
            $pembayaran->status = '0';
            $pembayaran->save();
            return redirect('/pembayaran_customer');
            }elseif($borrowdefault->id_borrowing_books != null){
                return redirect('/pembayaran_customer');
            }     
         }
    }
    public function update(Request $request, $id_pembayaran)
    {
        if ($request->isMethod('get')){
            $bank = DB::table('bank')->pluck('id_bank','nama_bank');
            $borrowing = DB::table("borrowing_books")
            ->pluck("id_borrowing","id_users");
            $paymentid = Payment::latest()->first();
            $expNum = explode('-', $paymentid->id_pembayaran);
                if ( date('l',strtotime(date('Y-01-01'))) ){
                    $nextInvoiceNumber = date('Y').'-0001';
                } else {
                    //increase 1 with last invoice number
                    $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
                }            
            return view('payment.payment_add', ['bank' => $bank,'borrowing' => $borrowing, 'nextInvoiceNumber' => $nextInvoiceNumber,'detailpeminjaman' => $detailpeminjaman]);
          } else {
            $rules = [
                'id_pembayaran' => 'string',
                'id_borrowing_books' => 'string',
                'id_users' => 'string',
                'id_bank' => 'string',
                'no_rekening' => 'integer',
                'atas_nama' => 'string'    
            ];
            $this->validate($request, $rules);
            if($pembayarandefault->id_pembayaran == null){
            $pembayaran = new Pembayaran;
            $pembayaran->id_pembayaran = $nextInvoiceNumber;
            $pembayaran->id_users = Auth::user()->id;
            $pembayaran->id_bank = $request->id_bank;
            $pembayaran->atas_nama = $request->atas_nama;
            $pembayaran->no_rekening = $request->no_rekening;
            $pembayaran->status = '0';
            $pembayaran->save();
            return redirect('/pembayaran_customer');
            }elseif($borrowdefault->id_borrowing_books != null){
                return redirect('/pembayaran_customer');
            }     
         }
    }

     public function show(Request $request, $id)
    {
        if ($request->isMethod('get')){
            $barang = DB::table('barangs')->where('status','=','1')
            ->pluck('id_barang','nama_barang','quantity','satuan');
            $pegawais = DB::table("pegawais")
            ->pluck("nip","first_name","last_name");
            return view('peminjaman.peminjaman_add', ['barang' => $barang, 'pegawais' => $pegawais]);
          } else {
            $rules = [
                    'no_pinjam' => 'required|string',
                    'tgl_pengembalian' => 'required|date',
                    'id_pegawai' => 'required|string',
                    'keterangan' => 'string',
                    'id_barang' => 'required|string',
                    'jumlah_barang_pinjam' => 'required_with:quantity|integer|min:1|digits_between: 1,3',
                    'quantity' => 'required_with:jumlah_barang_pinjam|integer|greater_than_field:jumlah_barang_pinjam|digits_between:1,3',
                ];
                $this->validate($request, $rules);
                $peminjamanbarang = new Peminjamanbarang;
                $peminjamanbarang->no_pinjam = $request->no_pinjam;
                $peminjamanbarang->tgl_pinjam = \Carbon\Carbon::now();
                $peminjamanbarang->tgl_pengembalian = $request->tgl_pengembalian;
                $peminjamanbarang->id_pegawai = $request->id_pegawai;
                $peminjamanbarang->keterangan = $request->keterangan;
                $peminjamanbarang->status_peminjaman = '1';
                $peminjamanbarang->user_id = Auth::user('id');
                $peminjamanbarang->save();

                $detailpeminjaman = new Detailpeminjaman;
                $detailpeminjaman->no_peminjaman = $request->no_pinjam;
                $detailpeminjaman->id_barang = $request->id_barang;
                $detailpeminjaman->quantity = $request->jumlah_barang_pinjam;
                $detailpeminjaman->status_detail = '1';        
                $detailpeminjaman->save();

                $barangdipinjam = Barang::where('id_barang','=',$request->id_barang)->firstOrFail();
                $barangdipinjam->quantity = $barangdipinjam->quantity - $request->jumlah_barang_pinjam;
                $barangdipinjam->save();
                return redirect('peminjamaninventaris')->with('success','Peminjaman barang baru created successfully');
         }
        
    }
    public function indexhistory(){
        return view('historypeminjaman.historypeminjaman');
    }
    // public function rekapdatabulan(Request $request){
    //     $tglawal = $request->tgl_awal;
    //     $tglakhir = $request->tgl_akhir;
    //     $rekapbulanan = Peminjamanbarang::whereBetween('created_at', [$tglawal, $tglakhir])->get();
    //     return view('historypeminjaman.historypeminjamanfinal', ['rekapbulanan' => $rekapbulanan]);
    // }
    public function fetch_data(Request $request)
    {
     if($request->ajax())
     {
      if($request->from_date != '' && $request->to_date != '')
      {
       $data = DB::table('peminjaman_barang')
         ->whereBetween('tgl_pinjam', array($request->from_date, $request->to_date))
         ->get();
      }
      else
      {
       $data = DB::table('peminjaman_barang')->orderBy('tgl_pinjam', 'desc')->get();
      }
      echo json_encode($data);
     }
    }
    public function reportdatabulan(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $tglawal = $request->from_date;
     $tglakhir = $request->to_date;
     $rekapbulanan = DB::table('peminjaman_barang')
     ->join('pegawais', 'pegawais.nip', '=', 'peminjaman_barang.id_pegawai')
     ->select('peminjaman_barang.no_pinjam', 'peminjaman_barang.tgl_pinjam','peminjaman_barang.tgl_pengembalian','pegawais.first_name','peminjaman_barang.keterangan')
     ->whereBetween('peminjaman_barang.tgl_pinjam', [$tglawal, $tglakhir])
     ->get();
     $pdf->loadView('historypeminjaman.pdfhistory', ['rekapbulanan' => $rekapbulanan]);
     return $pdf->download('peminjaman_per_tanggal.pdf');
    }    
    public function rekapdatauser(Request $request){
        $rekapdataterbesar = DB::table('peminjaman_barang')
        ->join('detail_peminjaman', 'detail_peminjaman.no_peminjaman', '=', 'peminjaman_barang.no_pinjam')
        ->join('pegawais', 'pegawais.nip', '=', 'peminjaman_barang.id_pegawai')
        ->select('peminjaman_barang.no_pinjam','peminjaman_barang.tgl_pinjam','peminjaman_barang.tgl_pengembalian','pegawais.first_name','peminjaman_barang.keterangan')
        ->where('detailpeminjaman.quantity','!=','0')
        ->orderByRow('detail_peminjaman.quantity DESC')
        ->get();
        return response()->json(['rekapdataterbesar' => $rekapdataterbesar]);
    }
    public function reportdatauser(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $tglawal = $request->tgl_awal;
     $tglakhir = $request->tgl_akhir;
     $rekapdataterbesar = DB::table('peminjaman_barang')
     ->join('detail_peminjaman', 'detail_peminjaman.no_peminjaman', '=', 'peminjaman_barang.no_pinjam')
     ->join('pegawais', 'pegawais.nip', '=', 'peminjaman_barang.id_pegawai')
     ->select('peminjaman_barang.no_pinjam','peminjaman_barang.tgl_pinjam','peminjaman_barang.tgl_pengembalian','pegawais.first_name','peminjaman_barang.keterangan')
     ->where('detailpeminjaman.quantity','!=','0')
     ->orderByRow('detail_peminjaman.quantity DESC')
     ->get();
     $pdf->loadView('peminjaman.pdfpeminjamanterbesar', ['peminjaman' => $peminjaman]);
     return $pdf->download('peminjaman_data_terbesar.pdf');
    }
}
