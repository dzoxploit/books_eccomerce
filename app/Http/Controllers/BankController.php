<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bank;
use DB;
use PDF;
use Auth;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $books = DB::table('bank')
        ->get();
        return view('bank.listbank', ['bank' => $bank]);
    }   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id_bank)
    {
        Bank::destroy($id_bank);
        return redirect('bank');
    }
    public function create(Request $request)
    {
             if ($request->isMethod('get')){
             return view('bank.bank_add');
             } else {
                $rules = [
                    'nama_bank' => 'required|string',
                    'alamat' => 'required|string',
                    'no_telepon' => 'required|string', 
                ];
                $this->validate($request, $rules);
                //tambah data barang
                $bank= new Bank;
                $bank->nama_bank = $request->nama_bank;
                $bank->alamat = $request->alamat;
                $bank->no_telepon = $request->no_telepon;
                $bank->save();
                return redirect('bank')->with('success','Bank created successfully');    
               }
    }
    public function update(Request $request, $id_bank)
    {

        if ($request->isMethod('get')) {
            return view('bank.bank_edit', ['bank' => Bank::where('id_bank', $id_bank)->firstOrFail()]);
         } else {
            $rules = [
                'nama_bank' => 'required|string',
                'alamat' => 'required|string',
                'no_telepon' => 'required|string', 
            ];
            $this->validate($request, $rules);
            //tambah data barang
            $bank= Bank::where('id_bank','=',$request->id_bank);
            $bank->nama_bank = $request->nama_bank;
            $bank->alamat = $request->alamat;
            $bank->no_telepon = $request->no_telepon;
            $bank->save();

               return redirect('bank')->with('success','bank created successfully');  
        }
        
    }

     public function show(Request $request, $id_bank)
    {
       
        
        if ($request->isMethod('get')) {
            return view('bank.bank_show', ['bank' => Bank::where('id_bank', $id_bank)->firstOrFail()]);
         } else {
            $rules = [
                'nama_bank' => 'required|string',
                'alamat' => 'required|string',
                'no_telepon' => 'required|string', 
            ];
            $this->validate($request, $rules);
            //tambah data barang
            $bank= Bank::where('id_bank','=',$request->id_bank);
            $bank->nama_bank = $request->nama_bank;
            $bank->alamat = $request->alamat;
            $bank->no_telepon = $request->no_telepon;
            $bank->save();

               return redirect('bank')->with('success','bank created successfully');  
        }
    }
}
