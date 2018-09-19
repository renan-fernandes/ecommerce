<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();
	$page->setTpl("index");
});

$app->get('/admin', function() {
    
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header" => false, //porque nessa página não precisamos do header padrão
		"footer" => false //porque nessa página não precisamos do footer padrão
	]);
	$page->setTpl("login");
});

$app->post('/admin/login', function() { //aqui nós validamos o login
    User::login($_POST["login"], $_POST["password"]); //se não lançar uma exception, um erro, então direciona para admin:
	
	header("Location: /admin");
	exit; //para parar a execução aqui
	
});

$app->get('/admin/logout', function() {
	User::logOut();
	
	header("Location: /admin/login");
	exit;	
});

$app->get('/admin/users', function() {
	
	User::verifyLogin();
	
	$users = User::listAll();
	
	$page = new PageAdmin(); //aqui queremos o header e o footer.
	$page->setTpl("users", array(
		"users" => $users
	));
});

$app->get('/admin/users/create', function() {
	
	User::verifyLogin();
	
	$page = new PageAdmin(); 
	$page->setTpl("users-create");
});

$app->get('/admin/users/:iduser/delete', function ($iduser) { //para excluir. //tem que ficar antes do $app->get('/admin/users/:iduser', para funcionar. senão, o slim pode achar que o /delete faz parte do :iduser caso a função $app->get('/admin/users/:iduser', seja lida primeiro. 
	User::verifyLogin();
	
	$user = new User();
	
	$user->get((int)$iduser);
	
	$user->delete();
	
	header("Location: /admin/users");
	exit;

});

$app->get('/admin/users/:iduser', function($iduser) {
	User::verifyLogin();
	$user = new User();
 
    $user->get((int)$iduser);
 
	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user" => $user->getValues()
	));
});

$app->post('/admin/users/create', function () { //vai receber em post e enviar para o banco de dados. 
	User::verifyLogin();
	
	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	
	$user->setData($_POST); //vai criar o objeto a partir do array $_POST. cada índice do array tem o mesmo nome de atributo da classe, que é criado dinâmicamente. 
	
	$user->save();
	header("Location: /admin/users");
	exit;
});

$app->post('/admin/users/:iduser', function ($iduser) { //para salvar a edição.
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
	$page = new PageAdmin([
		"header" => false, //porque nessa página não precisamos do header padrão
		"footer" => false //porque nessa página não precisamos do footer padrão
	]);
	$page->setTpl("forgot");
	
});

$app->post("/admin/forgot", function(){
	$user = User::getForgot($_POST["email"]);
	
	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function() {
	$page = new PageAdmin([
		"header" => false, //porque nessa página não precisamos do header padrão
		"footer" => false //porque nessa página não precisamos do footer padrão
	]);
	$page->setTpl("forgot-sent");
	
});

$app->get("/admin/forgot/reset", function() {
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new PageAdmin([
		"header" => false, //porque nessa página não precisamos do header padrão
		"footer" => false //porque nessa página não precisamos do footer padrão
	]);
	
	$page->setTpl("forgot-reset", array(
		"name" => $user["desperson"],
		"code" => $_GET["code"]
	));
});

$app->post("/admin/forgot/reset", function() {
	$forgot = User::validForgotDecrypt($_POST["code"]); //verifica de novo para aumentar a segurança. a diferença é que agora é $_POST.
	User::setForgotUsed($forgot["idrecovery"]);
	
	$user = new User();
	$user->get((int)$forgot["iduser"]);
	
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, array(
		"cost" => 12
	));
	
	$user->setPassword($password);
	
		$page = new PageAdmin([
		"header" => false, //porque nessa página não precisamos do header padrão
		"footer" => false //porque nessa página não precisamos do footer padrão
	]);
	
	$page->setTpl("forgot-reset-success");
	
});

$app->run();

 ?>