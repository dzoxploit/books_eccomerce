<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BorrowBooks;
use App\Detail_borrow_books;
use App\Books;
use Carbon\Carbon;
use DB;
use PDF;
use Auth;



class BorrowbooksController extends Controller
{
    public function index(Request $request)
    {
        $borrowbooks = BorrowBooks::all();
        $detailborrowbooks = DB::table('detail_borrowing_books')->where('id_borrow_books','=',$borrowbooks->id_detail_books)
        ->join('borrowing_books', 'borrowing_books.id_borrow_books', '=', 'detail_borrowing_books.id_inventaris')
        ->select('detail_borrowing_books.id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_pricey')
        ->get();
        return view('borrow_books.listbooks', ['borrowbooks' => $borrowbooks, 'detailborrowbooks' => $detailborrowbooks]);
    } 
    public function index_customer(Request $request)
    {
        $borrowbooks = Borrowbooks::where('status_payment','=','0')->latest()->first();
        $detailborrowbooks = DB::table('detail_borrowing_books')->where('id_borrow_books','=',$borrowbooks->id_detail_books)
        ->where('status_payment','=','0')
        ->join('borrowing_books', 'borrowing_books.id_borrow_books', '=', 'detail_borrowing_books.id_inventaris')
        ->select('detail_borrowing_books.id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
        ->get();
        return view('borrow_books.list_borrow_customer', ['borrowbooks' => $borrowbooks, 'detailborrowbooks' => $detailborrowbooks]);
    }
    public function add_borrow_user($request){
        if($request->isMethod('get')){
        $productbooks = Books::get();
        return view('borrow_books.list_books_customer', ['productbooks' => $productbooks]);
        }else{
        $borrowdefault = Borrowbooks::where('status_payment','=','0')->latest()->first();
        $borrowingbooks = Borrowbooks::latest()->first();
        $expNum = explode('-', $borrowingbooks->id_borrowing_books);
            if ( date('l',strtotime(date('Y-01-01'))) ){
                $nextInvoiceNumber = date('Y').'-0001';
            } else {
                //increase 1 with last invoice number
                $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
            }            
        $rules = [
            'id_borrowing_books' => 'string',
            'id_users' => 'string',
            'tgl_peminjaman' => 'date',
            'tgl_pengembalian' => 'date',    
        ];
        $this->validate($request, $rules);
        if($borrowdefault->id_borrowing_books == null){
        $borrowinput = new Borrowbooks;
        $borrowinput->id_borrow_books = $nextInvoiceNumber;
        $borrowinput->id_users = Auth::user()->id;
        $borrowinput->status_payment = '0';
        $borrowinput->save();

        $detailborrowbooks = new Detail_borrow_books;
        $detailborrowbooks->id_borrowing_books = $nextInvoiceNumber;
        $detailborrowbooks->id_users = Auth::user()->id;
        $detailborrowbooks->status_transaction = '0';
        $detailborrowbooks->tgl_peminjaman = $borrowvalidation->tgl_peminjaman;
        $detailborrowbooks->tgl_pengembalian = $borrowvalidation->tgl_pengembalian;
        $detailborrowbooks->save();
        }elseif($borrowdefault->id_borrowing_books != null){
            $detailborrowbooks = new Detail_borrow_books;
            $detailborrowbooks->id_borrowing_books = $nextInvoiceNumber;
            $detailborrowbooks->id_users = Auth::user()->id;
            $detailborrowbooks->status_transaction = '0';
            $detailborrowbooks->tgl_peminjaman = $borrowvalidation->tgl_peminjaman;
            $detailborrowbooks->tgl_pengembalian = $borrowvalidation->tgl_pengembalian;
            $detailborrowbooks->save();
        } 
       }
    } 
    public function history_borrow_books_admin(Request $request)
    {
        $borrowbooks = Borrowbooks::where('status_payment','=','1')->get();
        return view('borrow_books.history_books_admin', ['borrow_books' => $borrow_books]);
    } 
    public function history_borrow_books_customer(Request $request)
    {
        $borrowbooks = Borrowbooks::where('status_payment','=','1')->get();
        return view('borrow_books.history_books_customer', ['borrow_books' => $borrow_books]);
    } 
    public function modalborrowadmin(Request $request,$no_pinjam)
    {
        $detail_borrowing_books = DB::table('detail_borrowing_books')
        ->join('books', 'books.id_books', '=', 'detail_borrowing_books.id_books')
        ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
        ->select('detail_borrowing_books.id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
        ->where('detail_borrowing_books.id_borrowing_books','=',$id_borrowing_books)
        ->get();
        return view('borrow_books.modalborrow', ['detail_borrowing_books' => $detail_borrowing_books]);
    } 
    public function modalborrowcustomer(Request $request,$id_borrowing_books)
    {
        $detail_borrowing_books = DB::table('detail_borrowing_books')
        ->join('books', 'books.id_books', '=', 'detail_borrowing_books.id_books')
        ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
        ->select('id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
        ->where('detail_borrowing_books.id_borrowing_books','=',$id_borrowing_books)
        ->where('detail_borrowing_books.id_users','=',Auth::user()->id)
        ->get();
        return view('borrow_books.modalborrowcustomer', ['detail_borrowing_books' => $detail_borrowing_books]);
    } 
    public function pdf()
    {
     $pdf = \App::make('dompdf.wrapper');
     $borrowbooks = Borrowbooks::join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
     ->select('borrowing_books.id_borrowing_books', 'users.name', 'borrowing_books.tgl_peminjaman','borrowing_books.tgl_pengembalian', 'borrowing_books.total_price', 'borrowing_books.status_payment')
     ->get();     
     $pdf->loadView('borrowing_books.pdfborrowing_books', ['borrowbooks' => $borrowbooks]);
     return $pdf->download('borrowing_books.pdf');
    }    
    public function pdfindividu(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $borrowbooks = Borrowbooks::where('id_borrowing_books','=',$request->id_borrowing_books)
     ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
     ->select('borrowing_books.id_borrowing_books', 'users.name', 'borrowing_books.tgl_peminjaman','borrowing_books.tgl_pengembalian', 'borrowing_books.total_price', 'borrowing_books.status_payment')
     ->get();
     $detail_borrowing_books = DB::table('detail_borrowing_books')
     ->join('books', 'books.id_books', '=', 'detail_borrowing_books.id_books')
     ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
     ->select('id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
     ->where('detail_borrowing_books.id_borrowing_books','=',$request->id_borrowing_books)
     ->get();
     $pdf->loadView('borrowing_books.pdf', ['borrowbooks' => $borrowbooks,'detail_borrowing_books' => $detail_borrowing_books]);
     return $pdf->download('borrowing_books_individual.pdf');
    }

    public static function convertdate(){
        date_default_timezone_set('Asia/jakarta');
        $date = date('ddmmyy');
        return $date;
    }
    public static function approveindex() {
        $borrowbooks = Borrowbooks::where('status_borrow','=','1')
        ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
        ->select('borrowing_books.id_borrowing_books', 'users.name', 'borrowing_books.tgl_peminjaman','borrowing_books.tgl_pengembalian', 'borrowing_books.total_price', 'borrowing_books.status_payment')
        ->get();
        return view('borrow_approve.litborrowing_approve', compact('borrowbooks'));
    }
    public function getBooks(Request $request){
        $idcuy = $request->id_books;
        $books = DB::table('books')->where('id_inventaris',$idcuy)
        ->firstOrFail();
        return response()->json(['booksdata' => $booksdata]);
    }
    public function approve(Request $request,$id_borrowing_books){
        if ($request->isMethod('get')){
            $borrowbooks = Borrowbooks::where('id_borrowing_books','=',$id_borrowing_books)->firstOrFail();
            $detail_borrowing_books = DB::table('detail_borrowing_books')
            ->join('books', 'books.id_books', '=', 'detail_borrowing_books.id_books')
            ->join('users', 'users.id', '=', 'detail_borrowing_books.id_users')
            ->select('id_borrowing_books', 'users.name', 'detail_borrowing_books.status_transaction','books.title', 'detail_borrowing_books.tgl_peminjaman', 'detail_borrowing_books.tgl_pengembalian', 'detail_borrowing_books.status_penalty', 'detail_borrowing_books.status_condition_books', 'detail_borrowing_books.status_return','detail_borrowing_books.total_price')
            ->where('detail_borrowing_books.id_borrowing_books','=',$request->id_borrowing_books)
            ->get();
            return view('peminjaman_approve.peminjamanadd', ['peminjamanapproved' => $peminjamanapproved,'detailpeminjaman' => $detailpeminjaman,'pegawais' => $pegawais]);
          } else {
            $rules = [
                'id_borrowing_books' => 'string',
                'id_users' => 'string',
                'tgl_peminjaman' => 'date',
                'tgl_pengembalian' => 'date',    
            ];
            $this->validate($request, $rules);
                if(count($request->id_inventaris) > 0){
                    foreach($request->id_inventaris as $item => $v){
                        $inventarisdipinjam = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->firstOrFail();
                        $datang = array(
                          'jumlah' =>  $inventarisdipinjam->jumlah - $request->jumlah_approval[$item],
                        );
                            Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($datang);
                    }
                    foreach($request->id_inventaris as $item => $v){
                        $inventarisbaik = Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->get();
                        $ahmad = array(
                            'status_detail' => '1'
                        );
                        $musadek = array(
                            'status_detail' => '0'
                        );
                        if($request->jumlah_approval[$item] < $request->jumlah[$item]){
                            $peminjamanapprove = Peminjamanbarang::findOrFail($no_pinjam);
                            $peminjamanapprove->approval_status = '0';
                            $peminjamanapprove->save();
                            Detailpeminjaman::where('id_inventaris','=',$request->id_inventaris[$item])->update($musadek);
                            return redirect('/approval')->withMessage('peminjaman approved successfully');
                        }elseif($request->jumlah_approval[$item] == $request->jumlah[$item]){
                            $peminjamanapprove = Peminjamanbarang::findOrFail($no_pinjam);
                            $peminjamanapprove->approval_status = '1';
                            $peminjamanapprove->save();
                            Detailpeminjaman::where('id_inventaris','=',$request->id_inventaris[$item])->update($ahmad);
                            return redirect('/approval')->withMessage('peminjaman approved successfully');                            
                        }
                    }
                }            
        }    
    }
    public function createadmin(Request $request)
    {
    if($request->isMethod('get')){
        $productbooks = Books::get();
        $borrowingbooks = Borrowbooks::latest()->first();
        $expNum = explode('-', $borrowingbooks->id_borrowing_books);
            if ( date('l',strtotime(date('Y-01-01'))) ){
                $nextInvoiceNumber = date('Y').'-0001';
            } else {
                //increase 1 with last invoice number
                $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
            }            
        return view('borrow_books.create_books_admin', ['productbooks' => $productbooks,'nextinvoicenumber' => $nextInvoiceNumber]);    
    } else {
        $borrowdefault = Borrowbooks::where('status_payment','=','0')->latest()->first();
        $rules = [
            'id_borrowing_books' => 'string',
            'id_users' => 'string',
            'tgl_peminjaman' => 'date',
            'tgl_pengembalian' => 'date',    
        ];
        $this->validate($request, $rules);
        $borrowinput = new Borrowbooks;
        $borrowinput->id_borrowing_books = $nextInvoiceNumber;
        $borrowinput->id_users = Auth::user()->id;
        $borrowinput->tgl_peminjaman = $request->tgl_peminjaman;
        $borrowinput->tgl_pengembalian = $request->tgl_pengembalian;
        $borrowinput->status_payment = '0';
        $borrowinput->save();
        if(count($request->id_books) > 0){
            foreach($request->id_books as $item => $v){
                $formated_dt1[$item] = Carbon::parse($request->tgl_peminjaman[$item]);
                $formated_dt2[$item] = Carbon::parse($request->tgl_pengembalian[$item]);
                $get_number_days[$item] = $formated_dt1[$item]->diffInDays($formated_dt2[$item]);
                $get_data_books =  Books::where('id_books','=',$request->id_books[$item])->firstOrFail();   
                $detailborrowing = array(
                  'id_users' =>  Auth::user()->id,
                  'status_transaction' => '0',
                  'id_books' => $request->id_books[$item],
                  'tgl_peminjaman' => $request->tgl_peminjaman[$item],
                  'tgl_pengembalian' => $request->tgl_pengembalian[$item],
                  'status_condition_books' => '1',
                  'status_return' => '0',
                  'total_price' => $get_data_books->main_price + ($get_data_books->daily_price * $get_number_days[$item])  
                );
                Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($keluar);
            }
        }
        return redirect('borrowbooksadmin')->with('success','Borrow books baru created successfully');
    }
    }

    public function update(Request $request, $id_borrowing_books)
    {

        if($request->isMethod('get')){
            $productbooks = Books::get();
            $borrowingbooks = Borrowbooks::latest()->first();
            $expNum = explode('-', $borrowingbooks->id_borrowing_books);
                if(date('l',strtotime(date('Y-01-01'))) ){
                    $nextInvoiceNumber = date('Y').'-0001';
                } else {
                    //increase 1 with last invoice number
                    $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
                }            
            return view('borrow_books.list_books_customer', ['borrowing_books' => Borrowbooks::where('id_borrowing_books','=',$id_borrowing_books)->firstOrFail(),'detailborrowingbooks' => Detail_borrowing_books::where('id_borrowing_books','=',$id_borrowing_books)->get(),'productbooks' => $productbooks,'nextinvoicenumber' => $nextInvoiceNumber]);    
        } else {
            $borrowdefault = Borrowbooks::where('status_payment','=','0')->latest()->first();
            $rules = [
                'id_users' => 'string',
                'tgl_peminjaman' => 'date',
                'tgl_pengembalian' => 'date',    
            ];
            $this->validate($request, $rules);
            $borrowinput = Borrowbooks::where('id_borrowing_books','=',$request->id_borrowing_books)->firstOrFail();
            $borrowinput->id_users = Auth::user()->id;
            $borrowinput->tgl_peminjaman = $request->tgl_peminjaman;
            $borrowinput->tgl_pengembalian = $request->tgl_pengembalian;
            $borrowinput->status_payment = '0';
            $borrowinput->save();
            if(count($request->id_books) > 0){
                foreach($request->id_books as $item => $v){
                    $formated_dt1[$item] = Carbon::parse($request->tgl_peminjaman[$item]);
                    $formated_dt2[$item] = Carbon::parse($request->tgl_pengembalian[$item]);
                    $get_number_days[$item] = $formated_dt1[$item]->diffInDays($formated_dt2[$item]);
                    $get_data_books =  Books::where('id_books','=',$request->id_books[$item])->firstOrFail();   
                    $detailborrowing = array(
                      'id_users' =>  Auth::user()->id,
                      'status_transaction' => '0',
                      'id_books' => $request->id_books[$item],
                      'tgl_peminjaman' => $request->tgl_peminjaman[$item],
                      'tgl_pengembalian' => $request->tgl_pengembalian[$item],
                      'status_condition_books' => '1',
                      'status_return' => '0',
                      'total_price' => $get_data_books->main_price + ($get_data_books->daily_price * $get_number_days[$item])  
                    );
                    Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($keluar);
                }
            }
            return redirect('borrowbooksadmin')->with('success','Borrow books baru created successfully');
        }
    }

     public function show(Request $request, $id)
    {
        
        if($request->isMethod('get')){
            $productbooks = Books::get();
            $borrowingbooks = Borrowbooks::latest()->first();
            $expNum = explode('-', $borrowingbooks->id_borrowing_books);
                if(date('l',strtotime(date('Y-01-01'))) ){
                    $nextInvoiceNumber = date('Y').'-0001';
                } else {
                    //increase 1 with last invoice number
                    $nextInvoiceNumber = $expNum[0].'-'. $expNum[1]+1;
                }            
            return view('borrow_books.list_books_customer', ['borrowing_books' => Borrowbooks::where('id_borrowing_books','=',$id_borrowing_books)->firstOrFail(),'detailborrowingbooks' => Detail_borrowing_books::where('id_borrowing_books','=',$id_borrowing_books)->get(),'productbooks' => $productbooks,'nextinvoicenumber' => $nextInvoiceNumber]);    
        } else {
            $borrowdefault = Borrowbooks::where('status_payment','=','0')->latest()->first();
            $rules = [
                'id_users' => 'string',
                'tgl_peminjaman' => 'date',
                'tgl_pengembalian' => 'date',    
            ];
            $this->validate($request, $rules);
            $borrowinput = Borrowbooks::where('id_borrowing_books','=',$request->id_borrowing_books)->firstOrFail();
            $borrowinput->id_users = Auth::user()->id;
            $borrowinput->tgl_peminjaman = $request->tgl_peminjaman;
            $borrowinput->tgl_pengembalian = $request->tgl_pengembalian;
            $borrowinput->status_payment = '0';
            $borrowinput->save();
            if(count($request->id_books) > 0){
                foreach($request->id_books as $item => $v){
                    $formated_dt1[$item] = Carbon::parse($request->tgl_peminjaman[$item]);
                    $formated_dt2[$item] = Carbon::parse($request->tgl_pengembalian[$item]);
                    $get_number_days[$item] = $formated_dt1[$item]->diffInDays($formated_dt2[$item]);
                    $get_data_books =  Books::where('id_books','=',$request->id_books[$item])->firstOrFail();   
                    $detailborrowing = array(
                      'id_users' =>  Auth::user()->id,
                      'status_transaction' => '0',
                      'id_books' => $request->id_books[$item],
                      'tgl_peminjaman' => $request->tgl_peminjaman[$item],
                      'tgl_pengembalian' => $request->tgl_pengembalian[$item],
                      'status_condition_books' => '1',
                      'status_return' => '0',
                      'total_price' => $get_data_books->main_price + ($get_data_books->daily_price * $get_number_days[$item])  
                    );
                    Inventaris::where('id_inventaris','=',$request->id_inventaris[$item])->update($keluar);
                }
            }
            return redirect('borrowbooksadmin')->with('success','Borrow books baru created successfully');
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
       $data = DB::table('borrowing_books')
         ->whereBetween('tgl_peminjaman', array($request->from_date, $request->to_date))
         ->get();
      }
      else
      {
       $data = DB::table('borrowing_books')->orderBy('tgl_peminjaman', 'desc')->get();
      }
      echo json_encode($data);
     }
    }
    public function reportdatabulan(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $tglawal = $request->from_date;
     $tglakhir = $request->to_date;
     $rekapbulanan = DB::table('borrowing_books')
     ->join('users', 'users.id', '=', 'borrowing_books.id_users')
     ->select('borrowing_books.id_borrowing_books', 'users.name', 'borrowing_books.tgl_peminjaman','borrowing_books.tgl_pengembalian', 'borrowing_books.total_price', 'borrowing_books.status_payment','borrowing_books.status_borrow')
     ->whereBetween('borrowing_books.tgl_peminjaman', [$tglawal, $tglakhir])
     ->get();
     $pdf->loadView('historyborrowing.pdfhistory', ['rekapbulanan' => $rekapbulanan]);
     return $pdf->download('borrowing_per_days.pdf');
    }    
}
