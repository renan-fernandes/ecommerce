<?php

namespace Hcode;

class Model
{
	private $values = []; //vai ter todos os valores dos campos que nós temos no objeto. 
	
	public function __call($name, $args) //__call: método mágico
	{
		$method = substr($name, 0, 3); //string, posição, qtd contando com a posição em diante. 
		$fieldName = substr($name, 3, strlen($name)); //strlen traz o tamanho da string. 
		
		switch($method)
		{
			case "get":
				return $this->values[$fieldName];
				break;
			case "set":
				$this->values[$fieldName] = $args[0];
				break;			
		}
	}
	
	public function setData($data = array())
	{
		foreach($data as $key => $value)
		{
			$this->{"set".$key}($value); //essa sintaxe {"set".$key} cria o método set'algumacoisa' dinamicamente!
		}
	}
	
	public function getValues()
	{
		return $this->values;
	}
}



?>