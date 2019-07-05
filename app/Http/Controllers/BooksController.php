<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Books;
use App\Slider_product;
use Carbon\Carbon;
use App\Rejectbooks;
use DB;
use PDF;
use Auth;

class BooksController extends Controller
{
    public function index(Request $request)
    {
        $books = DB::table('books')
        ->get();
        return view('books.listbooks', ['books' => $books]);
    }   
    public function indexcustomer(Request $request)
    {
        $bookscustomer = DB::table('books')
        ->select('books.id_books','books.title', 'books.quantity','books.main_price','books.daily_price')
        ->get();
        $slider_product = DB::table('slider_product')->where('id_books','=',$bookscustomer->id_books)->get();
        return view('books_customer.list_books_customer', ['bookscustomer' => $bookscustomer,'slider_product' => $slider_product]);
    }
    public function indexpage(Request $request,$id_books)
    {
        $books= DB::table('books')->where('id_books','=',$id_books)
        ->select('books.id_books','books.title', 'books.quantity','books.main_price','books.daily_price')
        ->get();
        $slider_product = DB::table('slider_product')->where('id_books','=',$id_books)->get();
        return view('books_customer.list_books_customer', ['bookscustomer' => $bookscustomer,'slider_product','=',$slider_product]);
    }   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pdf()
    {
     $pdf = \App::make('dompdf.wrapper');
     $bookscustomer = DB::table('books')
     ->select('books.id_books','books.title', 'books.quantity','books.main_price','books.daily_price')
     ->get();
     $pdf->loadView('books_customer.catalog_books', ['bookscustomer' => $bookscustomer]);
     return $pdf->download('catalog_books.pdf');
    }    
    public function pdfindividu(Request $request)
    {
     $pdf = \App::make('dompdf.wrapper');
     $bookscustomer = DB::table('books')->where('id_books','=',$request->id_books)
     ->select('books.id_books','books.title', 'books.quantity','books.main_price','books.daily_price')
     ->first();
     $detail_books = DB::table('slider_product')->where('id_books','=',$request->id_books)->first();
     $pdf->loadView('books_customer.catalog_individual_books', ['bookscustomer' => $bookscustomer,'detail_books' => $detail_books]);
     return $pdf->download('catalog'.$bookscustomer->catalog_books.'.pdf');
    }

    public function delete($id_books)
    {
        Books::destroy($id_books);
        return redirect('books');
    }
    public function create(Request $request)
    {
             if ($request->isMethod('get')){
             return view('books.books_add');
             } else {
                $rules = [
                    'title' => 'required|string',
                    'author' => 'required|string',
                    'description' => 'required|string',
                    'quantity' => 'required|string',
                    'main_price' => 'required|string',
                    'daily_price' => 'required|string',
                    'penalty_price' => 'required|string', 
                ];
                $this->validate($request, $rules);
                //tambah data barang
                $books= new Books;
                $books->id_books = $request->id_books;
                $books->title = $request->title;
                $books->author = $request->author;
                $books->description = $request->description;
                $books->quantity = $request->quantity;
                $books->main_price = $request->main_price;
                $books->daily_price = $request->daily_price;
                $books->penalty_price = $request->penalty_price;
                $books->save();

                if(count($request->path_image) > 0){
                    foreach($request->path_image as $item => $v){
                        $datong = array(
                          'id_slider_product' => $request->id_slider_product[$item],
                          'id_books' => $request->id_books,
                          'path_image' => $request->path_image[$item],
                        );
                        Detail_borrow_books::insert($datong);
                    }
                }
                   return redirect('books')->with('success','Books created successfully');    
               }
    }
    public function update(Request $request, $id_inventaris)
    {

        if ($request->isMethod('get')) {
            $books = DB::table("books")->pluck('id_books', 'title', 'author','description', 'quantity', 'main_price', 'daily_price', 'penalty_price');
            $slider_product = DB::table("slider_product")->pluck('id_slider_product', 'id_books', 'path_image');                    
            return view('books.books_edit', ['books' => Books::where('id_books', $id_books)->firstOrFail(),'kategoris' => $kategoris]);
         } else {
            $rules = [
                'title' => 'required|string',
                'author' => 'required|string',
                'description' => 'required|string',
                'quantity' => 'required|string',
                'main_price' => 'required|string',
                'daily_price' => 'required|string',
                'penalty_price' => 'required|string', 
            ];
            $this->validate($request, $rules);
            //tambah data barang
            $books= Books::where('id_books','=',$request->id_books);
            $books->title = $request->title;
            $books->author = $request->author;
            $books->description = $request->description;
            $books->quantity = $request->quantity;
            $books->main_price = $request->main_price;
            $books->daily_price = $request->daily_price;
            $books->penalty_price = $request->penalty_price;
            $books->save();

            if(count($request->path_image) > 0){
                foreach($request->path_image as $item => $v){
                    $datong = array(
                      'id_slider_product' => $request->id_slider_product[$item],
                      'id_books' => $request->id_books,
                      'path_image' => $request->path_image[$item],
                    );
                    ReturnBooks::whereId($id_slider_product[$item])->update($datong);
                }
            }
               return redirect('books')->with('success','Books created successfully');    
           }
        
    }

     public function show(Request $request, $id)
    {
        if ($request->isMethod('get')) {
            $books = DB::table("books")->pluck('id_books', 'title', 'author','description', 'quantity', 'main_price', 'daily_price', 'penalty_price');
            $slider_product = DB::table("slider_product")->pluck('id_slider_product', 'id_books', 'path_image');                    
            return view('books.books_show', ['books' => Books::where('id_books', $id_books)->firstOrFail(),'kategoris' => $kategoris]);
         } else {
            $rules = [
                'title' => 'required|string',
                'author' => 'required|string',
                'description' => 'required|string',
                'quantity' => 'required|string',
                'main_price' => 'required|string',
                'daily_price' => 'required|string',
                'penalty_price' => 'required|string', 
            ];
            $this->validate($request, $rules);
            //tambah data barang
            $books= Books::where('id_books','=',$request->id_books);
            $books->title = $request->title;
            $books->author = $request->author;
            $books->description = $request->description;
            $books->quantity = $request->quantity;
            $books->main_price = $request->main_price;
            $books->daily_price = $request->daily_price;
            $books->penalty_price = $request->penalty_price;
            $books->save();

            if(count($request->path_image) > 0){
                foreach($request->path_image as $item => $v){
                    $datong = array(
                      'id_slider_product' => $request->id_slider_product[$item],
                      'id_books' => $request->id_books,
                      'path_image' => $request->path_image[$item],
                    );
                    ReturnBooks::whereId($id_slider_product[$item])->update($datong);
                }
            }
               return redirect('books')->with('success','Books created successfully');    
           }
    
    }
}
