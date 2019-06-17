<?php

namespace Hcode\Model;
use \Hcode\DB\Sql; //o '\' antes de Hcode significa buscar a partir da raiz. 
use \Hcode\Model;
use \Hcode\Mailer; 

class Product extends Model
{
	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");	
	}
	
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct" => $this->getidproduct(),
			":desproduct" => $this->getdesproduct(),
			":vlprice" => $this->getvlprice(),
			":vlwidth" => $this->getvlwidth(),
			":vlheight" => $this->getvlheight(),
			":vllength" => $this->getvllength(),
			":vlweight" => $this->getvlweight(),
			":desurl" => $this->getdesurl()
		));
		
		$this->setData($results[0]);		

	}
	
	public function get($idproduct)
	{
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
			"idproduct" => $idproduct
		]);

		$this->setData($results[0]);
	}
	
	public function delete()
	{
		$sql = new Sql();
		
		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			"idproduct" => $this->getidproduct()
		]);
		
	}
	
	public function checkPhoto()
	{
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg"))
		{
			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg"; 
		}
		else
		{
			$url = "/res/site/img/product.jpg"; 
		}
		
		$this->setdesphoto($url);
	}
	
	public function getValues()
	{
		$this->checkPhoto();
		
		$values = parent::getValues();
		
		return $values;
		
	}
	
	public function setPhoto($file)
	{
		$extension = explode('.', $file["name"]); //cria um array dividindo no '.'
		$extension = end($extension); //end - avança para última posição do array
		
		switch($extension)
		{
			case "jpg":
			case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]); //nome temporário do arquivo que está no servidor
			break;
			
			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]); //nome temporário do arquivo que está no servidor			
			break;
			
			case "png":
			$image = imagecreatefrompng($file["tmp_name"]); //nome temporário do arquivo que está no servidor
			break;
		}
		
		$dest = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";
		
		imagejpeg($image, $dest);
		
		imagedestroy($image);
		
		$this->checkPhoto();
	}
	
}

?>