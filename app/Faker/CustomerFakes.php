<?php
namespace App\Faker;

use \Faker\Provider\Base as Fakes;

class CustomerFakes extends Fakes{

	public function justReturnOne(){
		return 1;
	}

	public function companyNumber(){
		$char = $this->generator->randomLetter;
		$number = $this->generator->numerify("##############");
		return $char . $number;
	}

	public function booleanish(){
		return $this->generator->randomElement(array('Y','N'));
	}

	public function customerNumber(){
		switch ($this->randomElement(["simple","dash","letter","hard","broken","bullshit"])) {
			case 'simple':
			return $this->generator->numerify("########");

			case 'dash':
			return $this->generator->numerify("##-#####");

			case 'letter':
			return $this->generator->bothify("?#######");

			case 'hard':
			return $this->generator->bothify("?####-###");

			case 'broken':
			return $this->generator->word;

			default:
			return $this->generator->asciify("*******");
		}

	}

	public function price(){
		return $this->generator->randomFloat(2,0,1000);
	}

	public function billType(){
		return $this->generator->randomElement(array('RES','WMR','SW',0));
	}

	public function transaction_key(){
		return $this->generator->bothify('{****-***-***}');
	}

	public function source(){
		return $this->generator->randomElement(array('R','U','O','P'));
	}

}
