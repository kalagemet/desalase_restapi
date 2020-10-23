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
