<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>