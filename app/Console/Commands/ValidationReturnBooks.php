<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon;
class ValidationReturnBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $borrowbooks = \DB::table('borrowing_books')
        ->whereDate('tgl_pengembalian', '=', \Carbon::now())
        ->get();
        $detailborrowbooks = \DB::table('detail_borrowing_books')
        ->whereDate('tgl_pengembalian', '=', \Carbon::now())
        ->where('id_borrowing')
        ->get();
        if($borrowbooks->id_borrowing_books == \Carbon::now() && $detailborrowbooks->tgl_pengembalian == \Carbon::now() ){
            if(count($borrowbooks->id_borrowing_books) > 0){
                foreach($borrowbooks->id_borrowing_books as $item => $v){
                    $datong = array(
                      'status_penalty' => $request->kode_supplier[$item],
                    );
                    Detailinventariskeluar::insert($datong);
                }
            }
            if(count($detailborrowbooks->id_borrowing_books) > 0){
                foreach($detailborrowbooks->id_borrowing_books as $item => $v){
                    $datong = array(
                      'id_keluar_barang' => $request->id_keluar_barang,
                      'id_inventaris' => $request->id_inventaris[$item],
                      'jumlah_barang_keluar' => $request->jumlah_barang_keluar[$item],
                      'kode_supplier' => $request->kode_supplier[$item],
                    );
                    Detailinventariskeluar::insert($datong);
                }
            }
        }
    }
}
