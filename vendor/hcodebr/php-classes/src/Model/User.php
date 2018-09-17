<?php

namespace Hcode\Model;
use \Hcode\DB\Sql; //o '\' antes de Hcode significa buscar a partir da raiz. 
use \Hcode\Model;

class User extends Model
{
	const SESSION = "User"; //definindo a constante SESSION.
	
	public static function login($login, $password)
	{
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN" => $login //bind dos parâmetros		
		));
		
		if(count($results) === 0) //se não encontrou nada, podemos lançar uma exceção. 
		{
			throw new \Exception("Usuário inexistente ou senha inválida."); //a exceção está no escopo/namespace principal do PHP e não dentro do namespace Hcode\Model, pois não criamos nossa própria exception, por isso precisamos colocar a '\' pra ele achar a Exception principal. 
		}
		
		//se passar por esse if, é porque pelo menos 1 usuário tem. 
		$data = $results[0];
		
		//verificar a senha do usuário. password_verify recebe a senha informada, a senha do banco e ele retorna true ou false. 
		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User(); //por esse(método login) ser um método estático, podemos criar uma instância de User aqui dentro. 
		
			//a classe User é um Model, e todo Model terá getters e setters. faremos uma classe Model e todas as outras classes extenderão dessa. 
			
			//$user->setiduser($data["iduser"]); //como o método 'setiduser' não existe, ele chama o '__call'.
			$user->setData($data);
			
			$_SESSION[User::SESSION] = $user->getValues(); //criando a sessão do usuário. passando o nome da sessão como parâmetro. 
			
			return $user;
		}
		else
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}		
	}
	
	public static function verifyLogin($inadmin = true)
	{
		if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] || !(int)$_SESSION[User::SESSION]["iduser"] > 0 || (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin)
		{
			header("Location: /admin/login");
			exit;			
		}		
	}
	
	public static function logOut()
	{
		$_SESSION[User::SESSION] = null;
	}
}


?>