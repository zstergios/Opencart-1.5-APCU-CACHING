<?php
/**
 * @package		Opencart v1.5 Advanced Caching
 * @version     1.1
 * @author      Stergios Zgouletas <info@web-expert.gr>
 * @link        http://www.web-expert.gr
 * @copyright   Copyright (C) 2010 Web-Expert.gr All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/

class Cache
{
	private $expire = 3600;
	private $APCmode=false;
	
	public function __construct($expire=3600)
	{
		if(!isset($_SESSION['opencartCache'])) $_SESSION['opencartCache']=array();
		
		$this->expire = $expire;
		$this->APCmode=false;
		
		if(extension_loaded('apcu')) //APC new version
			$this->APCmode='apcu';
		elseif (extension_loaded('apc')) //APC old version
			$this->APCmode='apc';
	}
	
	public function __destruct()
	{
		unset($_SESSION['opencartCache']); //clear session on close, prevent storing
	}

	public function get($key)
	{	
		//APC(u)
		if($this->APCmode!==false)
		{
			return $this->APCmode=='apcu'?apcu_fetch(HTTP_SERVER.$key):apc_fetch(HTTP_SERVER.$key);
		}
		
		$hashKey=md5(HTTP_SERVER.$key);
		
		//Session Cache
		$sessCache=$this->sessionCache($hashKey);
		if($sessCache!==false && isset($sessCache['time']) && $sessCache['time']+$this->expire>=time()) return $sessCache['data'];
		
		//File Cache
		$file=DIR_CACHE. $hashKey.'.cache';
		if(file_exists($file))
		{
			$cache = file_get_contents($file);
			$data = unserialize($cache);
			if($data!==false && isset($data['time']) && $data['time']+$this->expire>=time())
			{
				return $data['data'];
			}
			$this->delete($key); //expired;
		}
		return false;
	}

	public function set($key, $value)
	{
		//APC(u)
		if($this->APCmode!==false)
		{
			$this->delete($key);
			return $this->APCmode=='apcu'? apcu_add(HTTP_SERVER . $key, $value, $this->expire) : apc_store(HTTP_SERVER . $key, $value, $this->expire);
		}
		
		$hashKey=md5(HTTP_SERVER.$key);
		$file=DIR_CACHE.$hashKey.'.cache';
		
		$cache=array(
			'time'=>time(),
			'data'=>$value,
		);
		
		//Session Cache
		$this->sessionCache('write',$hashKey,$cache);
		//File Cache
		file_put_contents($file,serialize($cache),LOCK_EX);
	}

	public function delete($key)
	{
		//APC(u)
		if($this->APCmode!==false)
		{
			return $this->APCmode=='apcu'? apcu_delete(HTTP_SERVER.$key) : apc_delete(HTTP_SERVER.$key);
		}
		
		$hashKey=md5(HTTP_SERVER.$key);
		$file=DIR_CACHE.$hashKey.'.cache';
		
		
		//Session Cache
		$this->sessionCache('delete',$hashKey);
		
		//File Cache
		if(file_exists($file))
		{
			unlink($file);
		}
	}
	
	//session cache prevents re-opening the file when multiple files asking for specific key
	protected function sessionCache($mode,$cacheKey,$newData=NULL)
	{
		if(isset($_SESSION['opencartCache'][$cacheKey]) && $mode=='delete')
		{
			unset($_SESSION['opencartCache'][$cacheKey]);
		}
		elseif($mode=='write')
		{
			$_SESSION['opencartCache'][$cacheKey]=serialize($newData);
		}
		else
		{
			return isset($_SESSION['opencartCache'][$cacheKey])? unserialize($_SESSION['opencartCache'][$cacheKey]) :false;
		}
		return false;
	}
}