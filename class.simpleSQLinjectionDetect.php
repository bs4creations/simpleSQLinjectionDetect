class simpleSQLinjectionDetect
	{				
		public function detect()
		{
			if(!empty($_GET)){
				$result = self::parseQuery();
				if ($result){
					$data  = date("d-m-Y H:i:s") . ' - ';
					$data .= $_SERVER['REMOTE_ADDR'] . ' - ';
					$data .= urldecode($_SERVER['QUERY_STRING']);
					@file_put_contents('./logs/sql.injection.txt', $data . PHP_EOL, FILE_APPEND);
					unset($_GET);
					exit();
				}
			}
		}
		
		private function parseQuery()
		{
			$operators = array(
				'select * ',
				'select ',
				'union all ',
				'union ',
				' from ',
				' all ',
				' where ',
				' 1=1 ',
				' and 1 ',
				' and ',
				' or 1',
			);
			
			foreach($_GET as $key => $value)
			{
				$k = urldecode(strtolower($key));
				$v = urldecode(strtolower($value));
				foreach($operators as $operator)
				{
					if (preg_match("/".$operator."/i", $k) || preg_match("/".$operator."/i", $v)) {
						return true;
					}
				}
			}
		}
	}
	
	$inj = new simpleSQLinjectionDetect();
	$inj->detect();
