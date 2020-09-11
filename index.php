<?php

session_start();
require_once("vendor/autoload.php");

use \Projeto\Page;
use \Projeto\PageAdmin;
use \Slim\Slim;
use \Projeto\Model\User;
use \Projeto\Model\Category;
use Projeto\Model\Product;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function()
 {
    $products = Product::listAll();
    $page = new Page();   
    $page->setTpl("index", [
      'products'=>Product::checkList($products)
   ]);
});

$app->get('/admin', function()
 {
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});

$app->get('/admin/login', function(){
   User::verifyLogin();
   $page = new PageAdmin([
        "header"=>false,
       "footer"=>false
   ]);

   $page->setTpl("login");

});

$app->post('/admin/login', function(){
   User::verifyLogin();
   User::login($_POST["login"], $_POST["password"]);

   header("Location: /admin");

   exit;

});

$app->get('/admin/logout', function(){
    User::verifyLogin();
    User::logout();

    header("Location: /admin/login");

    exit;

});

$app->get('/admin/users', function(){

      User::verifyLogin();

      $users = User::listAll();

      $page = new PageAdmin();

      $page->setTpl("users", array(
          "users"=>$users
      ));
});

$app->get('/admin/users/create', function(){

      User::verifyLogin();

      $page = new PageAdmin();

      $page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete", function($iduser){

   User::verifyLogin();
   $user = new User();
   $user->get((int)$iduser);
   $user->delete();

   header("Location: /admin/users");
   exit;

});

$app->get('/admin/users/:iduser', function($iduser){

   User::verifyLogin();

   $user = new User();

   $user->get((int)$iduser);

   $page = new PageAdmin();

   $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));

});

$app->post("/admin/users/create", function(){

   User::verifyLogin();
   $user = new User();
   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
   $user->setData($_POST);
   $user->saveData();

   header("Location: /admin/users");
   exit;

});

$app->post("/admin/users/:iduser", function($iduser){

   User::verifyLogin();
   $user = new User();
   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
   $user->get((int)$iduser);
   $user->setData($_POST);
   $user->update();

   header("Location: /admin/users");
   exit;

});

$app->get("/admin/forgot", function(){
   User::verifyLogin();
   $page = new PageAdmin([
      "header"=>false,
     "footer"=>false
   ]);

   $page->setTpl("forgot");

});

$app->post("/admin/forgot", function(){
   User::verifyLogin();
   $user = User::getForgot($_POST["email"]);
   header("Location: /admin/forgot/sent");
   exit;
});

$app->get("/admin/forgot/sent", function(){
   User::verifyLogin();
   $page = new PageAdmin([
      "header"=>false,
     "footer"=>false
   ]);

   $page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){
   User::verifyLogin();
   $user = User::validForgotDecrypt($_GET["code"]);

   $page = new PageAdmin([
      "header"=>false,
      "footer"=>false
   ]);
   $user = User::validForgotDecrypt($_GET["code"]);

   $page->setTpl("forgot-reset", array(
      "name"=>$user["desperson"],
      "code"=>$_GET["code"]
   ));
});

$app->post("/admin/forgot/reset", function (){
   User::verifyLogin();
   $forgot = User::validForgotDecrypt($_POST["code"]);

   User::setForgotUsed($forgot["idrecovery"]);

   $user = new User();

   $user->get((int)$forgot["iduser"]);

   $password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

   $user->setPassword($password);

   $page = new PageAdmin([
      "header"=>false,
     "footer"=>false
   ]);

   $page->setTpl("forgot-reset-success");

});

$app->get("/admin/categories", function(){
     User::verifyLogin();
     $categories = Category::ListAll();
     $page = new PageAdmin();

     $page->setTpl("categories", ["categories"=>$categories]);
});

$app->get("/admin/categories/create", function(){
  User::verifyLogin();
  $page = new PageAdmin();
  $page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function(){
  User::verifyLogin();
  $category = new Category();
  $category->setData($_POST);
  $category->save();
  header("Location: /admin/categories");
  exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $category->delete();
  header("Location: /admin/categories");
  exit;

});

$app->get("/admin/categories/:idcategory", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $page = new PageAdmin();
  $page->setTpl("categories-update", ['category'=>$category->getValues()]);

});

$app->post("/admin/categories/:idcategory", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $category->setData($_POST);
  $category->save();
  header("Location: /admin/categories");
  exit;
});

$app->get("/categories/:idcategory", function($idcategory){
  $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

  $category = new Category();

  $category->get((int)$idcategory);

  $pagination = $category->getProductsPage($page);

  $pages = [];

  for ($i=1; $i <= $pagination['pages']; $i++) { 
  array_push($pages, [
  'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
  'page'=>$i
    ]);
  }
});

$app->get("/admin/products", function (){
   User::verifyLogin();
   $products = new Product();
   $products = Product::listAll();
   $page = new PageAdmin();
   $page->setTpl("products", [
      "products"=>$products
   ]);
});

$app->get("/admin/products/create", function(){
   User::verifyLogin();
   $page = new PageAdmin();
   $page->setTpl("products-create");
});

$app->post("/admin/products/create", function(){
   User::verifyLogin();
   $product = new Product();
   $product->setData($_POST);
   $product->save();
   header("Location: /admin/products");
   exit;
});

$app->get("/admin/products/:idproduct", function($idproduct){
   User::verifyLogin();
   $product = new Product();
   $product->get((int)$idproduct);
   $page = new PageAdmin();
   $page->setTpl("products-update", ['product'=>$product->getValues()]);
});

$app->post("/admin/products/:idproduct", function($idproduct){
   User::verifyLogin();
   $product = new Product();
   $product->get((int)$idproduct);
   $product->setData($_POST);
   $product->save();
   $product->setPhoto($_FILES["file"]);
   header("Location: /admin/products");
   exit;
});

$app->get("/admin/products/:idproduct/delete", function($idproduct){
   $product = new Product();
   $product->get((int)$idproduct);
   $product->delete();
   header("Location: /admin/products");
   exit;
});

$app->get("/admin/categories/:idcategory/products", function($idcategory){
   User::verifyLogin();
   $categories = new Category();
   $categories->get((int)$idcategory);
   $page = new PageAdmin();

   $page->setTpl("categories-products", [
      'category'=>$categories->getValues(),
      'productsRelated'=>$categories->getProducts(),
      'productsNotRelated'=>$categories->getProducts(false)
      ]);

});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
   User::verifyLogin();
   $categories = new Category();
   $categories->get((int)$idcategory);
   $product = new Product();
   $product->get((int)$idproduct);
   $categories->addProduct($product);
   header("Location: /admin/categories/".$idcategory."/products");
   exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
   User::verifyLogin();
   $categories = new Category();
   $categories->get((int)$idcategory);
   $product = new Product();
   $product->get((int)$idproduct);
   $categories->removeProduct($product);
   header("Location: /admin/categories/".$idcategory."/products");
   exit;
});





$app->run();
