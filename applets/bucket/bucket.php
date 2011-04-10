<?php
class Bucket
{
	protected $plugin;
	protected $name;
	protected $ci;
	
	public function __construct()
	{
		$this->ci =& get_instance();
		$this->plugin = OpenVBX::$currentPlugin;
		
		if(!(PluginData::get('tableCreated', false))){
			$this->createTable();
		}
	}
	
	public function addCall($bucket, $sid)
	{
		$this->ci->db->insert($this->getTableName(), array('bucket' => $bucket, 'sid' => $sid));
	}
	
	public function createTable()
	{
		//TODO: check if table exsists
		
		$sql = 'CREATE TABLE `' . $this->getTableName() . '` (
		  `sid` char(34)  NOT NULL,
		  `bucket` varchar(32)  NOT NULL,
		  `start` int,
		  `to` char(15),
		  `from` char(15),
		  `duration` int,
		  `price` decimal(10,2),
		  PRIMARY KEY (`sid`, `bucket`)
		)';
		
		if(!$this->ci->db->query($sql)){
			error_log('could not create call bucket table');
			return;
		} 
		
		PluginData::set('tableCreated', true);
	}
	
	public function getTableName()
	{
		if(empty($this->name)){
			$info = $this->plugin->getInfo();
			$this->setTableName('call_bucket_' . $info['plugin_id']);
		}
		return $this->name;
	}
	
	public function setTableName($name)
	{
		$this->name = $name;
	}

	public function getRange($start, $stop)
	{
		if(!is_numeric($start)){
			$start = strtotime($start);
		}

		if(!is_numeric($stop)){
			$stop = strtotime($stop);
		}

		$this->ci->db->where('start >=', $start)->where('start <', $stop)->order_by('start', 'desc');
		return $this->ci->db->get($this->getTableName());

	}
	
	public function syncLog()
	{
		$this->ci->db->where('price IS NULL');
		$query = $this->ci->db->get($this->getTableName());
	
		$client = $this->getClient();
	
		foreach($query->result() as $call){
			///2008-08-01/Accounts/{YourAccountSid}/Calls/{CallSid}
			$call_data = $client->request("Accounts/".$this->getAccountSid()."/Calls/".$call->sid, "GET");
		
			$update['start'] = strtotime((string) $call_data->ResponseXml->Call->StartTime);
			$update['to'] = (string) $call_data->ResponseXml->Call->To;	
			$update['from'] = (string) $call_data->ResponseXml->Call->From;	
			$update['duration'] = (int) $call_data->ResponseXml->Call->Duration;
			
			if(empty($call_data->ResponseXml->Call->Price)){
				continue;
			}

			$price = (float) $call_data->ResponseXml->Call->Price;

			//look for all possible subcalls
			$calls = $client->request(
				"Accounts/".$this->getAccountSid()."/Calls/", 
				"GET", 
				array('StartTime' => (string) $call_data->ResponseXml->Call->StartTime, 'EndTime' => (string) $call_data->ResponseXml->Call->EndTime));
			foreach($calls->ResponseXml->Calls->Call as $subcall){
				if((string) $subcall->ParentCallSid == (string) $call->sid){
					if(empty($subcall->Price)){
						continue;
					}

					$price += (float) $subcall->Price;
				}
			}
				
			$update['price'] = $price;	
				
			$this->ci->db->update($this->getTableName(), $update, array('sid' => (string) $call->sid));
		}
	}

	public function getClient()
	{
		$client = new TwilioRestClient($this->ci->twilio_sid, $this->ci->twilio_token);
		return $client;
	}
	
	public function getAccountSid()
	{
		return $this->ci->twilio_sid;
	}
}
