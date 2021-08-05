<?php

$router->get('/api-data', 'ApiController@getApiData');
$router->get('/getlogo', 'ApiController@getLogo');
$router->get('/getfooterdata', 'ApiController@getFooterData');
$router->get('/getbanner', 'ApiController@getBanner');
$router->get('/getproduk', 'ApiController@getProduk');
$router->get('/getprodukbaru', 'ApiController@getProdukNew');

$router->get('/getprodukbykategori', 'ApiController@getKategori');
$router->get('/caritag', 'ApiController@getTag');
$router->get('/cariproduk', 'ApiController@getCari');

$router->get('/getdetail', 'ApiController@getProdukDetail');

$router->get('/gettentang', 'ApiController@getTentang');

$router->get('/', function () use ($router) {
    return redirect('https://desalase.id');
});


//================================================================
//cms session
//================================================================
$router->group([
    'middleware' => 
    'auth:api'
    // 'client'
], function (\Laravel\Lumen\Routing\Router $router) {
    $router->post('/setbanner', 'ApiController@setBanner');
    $router->get('/gettentangadmin', 'ApiController@getTentangAdmin');

    $router->post('/puttentang', 'ApiController@putTentang');
    $router->post('/updatetentang', 'ApiController@updateTentang');
    $router->get('/deletetentang', 'ApiController@delTentang');

    $router->post('/tambahtoko', 'ApiController@tambahToko');
    $router->get('/hapustoko', 'ApiController@hapusToko');
    $router->post('/updatetoko', 'ApiController@updateToko');

    $router->post('/tambahfoot', 'ApiController@tambahFoot');
    $router->get('/hapusfoot', 'ApiController@hapusFoot');
    $router->post('/updatefoot', 'ApiController@updateFoot');

    $router->get('/hapusproduk', 'ApiController@hapusProduk');

    $router->get('/getkategori', 'ApiController@getKat');
    $router->get('/gettags', 'ApiController@getTags');

    $router->post('/saveproduk', 'ApiController@addProduk');
    $router->post('/updateproduk', 'ApiController@updateProduk');
    $router->post('/delgambarproduk', 'ApiController@delGambarProduk');
});



