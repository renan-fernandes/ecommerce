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

$app->run();

 ?>