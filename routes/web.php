<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

 // Password Reset Routes...
 Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
 Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
 Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
 Route::post('password/reset', 'Auth\ResetPasswordController@reset');
 
// Authentication Route
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::middleware(['auth','web'])->group(function(){
    Route::get('/', function(){ 
        return redirect('/login');
    }); 
});


Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('admin', 'DashboardController@index')->name('dashboard');
    // bikin link ke controller profile controller
    Route::get('profile', 'ProfileController@index')->name('profile');
    
 // Registration Routes...s
 Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
 Route::post('register', 'Auth\RegisterController@register');
 
 
 Route::group(['prefix' => 'Books'], function () {
    Route::get('/', 'BooksController@index');
    Route::match(['get'], 'listrealtime', 'BooksControlle[r@listbooks');   
    Route::match(['get', 'post'], 'create', 'BooksController@create');
    Route::match(['get', 'put'], 'update/{id_books}', 'BooksController@update');
    Route::match(['get', 'put'], 'viewer/{id_books}', 'BooksController@show');
    Route::delete('delete/{id_books}', 'BooksController@delete');
 });


 Route::group(['prefix' => 'payment_approval'], function () {
    Route::get('/', 'PaymentController@payment_approval');
    Route::match(['get'], 'listpayment', 'PaymentController@listpayment');   
    Route::match(['get', 'put'], 'update/{id_pembayaran}', 'PaymentController@update');
    Route::match(['get', 'put'], 'viewer/{path_struct_transaction}', 'PaymentController@viewerstruct');
    Route::delete('delete/{id}', 'PaymentController@delete');
 });

 Route::group(['prefix' => 'reject_books'], function () {
    Route::get('/', 'RejectbooksController@index');
    Route::get('pdf', 'RejectbooksbarangController@pdf');
    Route::match(['get', 'post'], 'create', 'RejectbooksController@create');
    Route::match(['get', 'put'], 'update/{id_reject_books}', 'RejectbooksController@update');
    Route::match(['get', 'put'], 'show/{id_reject_books}', 'RejectbooksController@show');
 });
 Route::group(['prefix' => 'borrow_books'], function () {
    Route::get('/', 'BorrowbooksController@index');
    Route::get('pdf', 'BorrowbooksController@pdf');
    Route::get('detail_borrow_books/{id_transaction}','BorrowbooksController@modalborrowbooks');
    Route::match(['get', 'put'], 'pdfindividual/{id_transaction}', 'PeminjamanbarangController@pdfindividu');
    Route::match(['get', 'post'], 'create', 'PeminjamanbarangController@create');
    Route::match(['get', 'post'], 'tambahrow', 'PeminjamanbarangController@tambahrow');
    Route::match(['get', 'put'], 'update/{no_pinjam}', 'PeminjamanbarangController@update');
    Route::match(['get', 'put'], 'show/{no_pinjam}', 'PeminjamanbarangController@show');
    Route::delete('delete/{no_peminjaman}', 'PeminjamanbarangController@deleterow');
 });
  Route::group(['prefix' => 'rejectbooks'], function () {
    Route::get('/', 'InventarisController@index');
    Route::get('pdf', 'InventarisController@pdf');
    Route::match(['get', 'put'], 'pdfindividual/{id_reject_books}', 'RejectbooksController@pdfindividu'); 
    Route::match(['get','post'], 'create', 'RejectnbooksController@create');
    Route::match(['get', 'put'], 'update/{id_reject_books}', 'RejectbooksController@update');
    Route::match(['get'], 'show/{id_barang}', 'RejectbooksController@show');
 });
 Route::group(['prefix' => 'returnbooks'], function () {
    Route::get('/', 'ReturnbooksController@index');
    Route::get('pdf', 'ReturnbooksController@pdf');
    Route::match(['get','put'],'detailreturnbooks/{id_return_books}','ReturnbooksController@modalreturnbooks');
    Route::match(['get', 'put'], 'pdfindividual/{id_borrowing_books}', 'ReturnbooksController@pdfindividu');
    Route::match(['get','post'], 'create', 'ReturnbooksController@create');
    Route::match(['get', 'put'], 'update/{id_return_books}', 'ReturnbooksController@update');
    Route::match(['get'], 'show/{id_return_books', 'ReturnbooksController@show');
 });
 Route::group(['prefix' => 'bank'], function () {
    Route::get('/', 'BankController@index');
    Route::match(['get','post'], 'create', 'BankController@create');
    Route::match(['get', 'put'], 'update/{id_bank}', 'InkeluarController@update');
    Route::match(['get'], 'show/{id_bank}', 'InventariskeluarController@show');
 });
});
Route::group(['middleware' => ['auth', 'role:customer']], function () {
    Route::get('books', 'CustomerController@indexpegawai')->name('dashboard');
    Route::group(['prefix' => 'product'], function () {
        Route::match(['get', 'post'], 'books', 'BooksController@books_customer');
        Route::match(['get', 'put'], 'borrowing_books', 'BorrowbooksController@borrowing_customer');
        Route::match(['get','put'], 'PaymentController@pembayaran');
     }); 
    //  Route::group(['prefix' => 'returnbooks'], function () {
    //     Route::get('/', 'ReturnbooksController@index_customer');
    //     Route::match(['get','post'], 'create','ReturnbooksController');
    //  });     
});



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
