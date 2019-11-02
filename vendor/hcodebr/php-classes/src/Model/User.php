<?php

namespace Hcode\Model;
use \Hcode\DB\Sql; //o '\' antes de Hcode significa buscar a partir da raiz. 
use \Hcode\Model;
use \Hcode\Mailer; 

class User extends Model
{
	const SESSION = "User"; //definindo a constante SESSION.
	const SECRET = "HcodePhp7_Secret"; //essa chave deve ter no mín, 16 caracteres. pode ter mais, mas são valores fixos, tipo: 16, 24, 32, etc. NÃO SUBIR A CHAVE EM REPOSITÓRIOS PÚBLICOS!
	
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
	
	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");	
	}
	
	public function get($iduser)
	{
	 
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
		":iduser"=>$iduser
		));

		$data = $results[0];

		$this->setData($data);
	}
	
	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser" => $this->getiduser(),
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));
		
		$this->setData($results[0]);
	}
	
	public function delete()
	{
		$sql = new Sql();
		
		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser" => $this->getiduser()
		));
		
	}
	
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		));
		
		$this->setData($results[0]);
	}
	
	public static function getForgot($email)
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT * 
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email;", array(
				":email" => $email
			));
		
		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser" => $data["iduser"],
				":desip" => $_SERVER["REMOTE_ADDR"]
			));
			
			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha.");				
			}
			else
			{
				$dataRecovery = $results2[0];
				
				//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				$code = $dataRecovery["idrecovery"]; ///teste
				
				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name" => $data["desperson"],
					"link" => $link				
				));
				
				$mailer->send();
				
				return $data;				
			}
		}
	}
	
	public static function validForgotDecrypt($code)
	{
		//$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
		$idrecovery = $code;
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
					"idrecovery" => $idrecovery,			
				));
		
		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}
	}
	
	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();
		
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW WHERE idrecovery = :idrecovery", array(
			":idrecovery" => $idrecovery		
		));		
	}
	
	public function setPassword($password)
	{
		$sql = new Sql();
		
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password" => $password,
			":iduser" => $this->getiduser()
		));
		
	}
}


?>