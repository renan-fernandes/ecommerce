<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin/', function() {
    
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

?>