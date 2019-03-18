<?php
class Cache {
	private $expire = 3600;
	private $apcEnabled=false;
	
	public function __construct($expire=3600)
	{
		$this->expire = $expire;
		$this->apcEnabled=extension_loaded('apc');
	}

	public function get($key)
	{
		if($this->apcEnabled) return apc_fetch(HTTP_SERVER.$key);
	
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files)
		{
			$cache = file_get_contents($files[0]);
			$data = unserialize($cache);
			foreach ($files as $file)
			{
				$time = substr(strrchr($file, '.'), 1);
				if ($time < time() && file_exists($file))
				{
					unlink($file);
				}
			}
			return $data;
		}
	}

	public function set($key, $value)
	{
		if($this->apcEnabled) return apc_store(HTTP_SERVER . $key, $value, $this->expire);
		
		$this->delete($key);
		$file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);
		$handle = fopen($file, 'w');
		fwrite($handle, serialize($value));
		fclose($handle);
	}

	public function delete($key)
	{
		if($this->apcEnabled) return apc_delete(HTTP_SERVER.$key);
		
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files)
		{
			foreach ($files as $file)
			{
				if (file_exists($file))
				{
					unlink($file);
				}
			}
		}
	}
}