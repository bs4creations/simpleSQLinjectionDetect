
<?php
/**
 * simpleSQLinjectionDetect Class
 * @link      https://github.com/bs4creations/simpleSQLinjectionDetect 
 * @version   1.1
 */
	 
class simpleSQLinjectionDetect
{	
	protected $_method 	= array();
	protected $_suspect = null;	
	
	public $_options = array(
							'log' 	 => true,
							'unset'  => true,
							'exit'   => true,
							'errMsg' => 'Not allowed',
						);
	
	public function detect()
	{
		self::setMethod();
		
		if(!empty($this->_method))
		{
			$result = self::parseQuery();
			
			if ($result)
			{
				if ($this->_options['log']) {
					self::logQuery();
				}
				
				if ($this->_options['unset']){
					unset($_GET, $_POST);
				}
				
				if ($this->_options['exit']){
					exit($this->_options['errMsg']);
				}
			}
		}
	}
	
	private function setMethod()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$this->_method = $_GET;
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->_method = $_POST;
		}
	}
	
	private function parseQuery()
	{
		$operators = array(
			'select * ',
			'select ',
			'union all ',
			'union ',
			' all ',
			' where ',
			' and 1 ',
			' and ',
			' or ',
			' 1=1 ',
			' 2=2 ',
			' -- ',
		);
		
		foreach($this->_method as $key => $val)
		{
			$k = urldecode(strtolower($key));
			$v = urldecode(strtolower($val));
			
			foreach($operators as $operator)
			{
				if (preg_match("/".$operator."/i", $k)) {
					$this->_suspect = "operator: '".$operator."', key: '".$k."'";
					return true;
				}
				if (preg_match("/".$operator."/i", $v)) {
					$this->_suspect = "operator: '".$operator."', val: '".$v."'";
					return true;
				}
			}
		}
	}
	
	private function logQuery()
	{
		$data  = date('d-m-Y H:i:s') . ' - ';
		$data .= $_SERVER['REMOTE_ADDR'] . ' - ';
		$data .= 'Suspect: ['.$this->_suspect.'] ';
		$data .= json_encode($_SERVER);
		@file_put_contents('./logs/sql.injection.txt', $data . PHP_EOL, FILE_APPEND);
	}
}

$inj = new simpleSQLinjectionDetect();
$inj->detect();
