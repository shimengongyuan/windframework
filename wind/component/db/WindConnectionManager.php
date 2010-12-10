<?php
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com> 2010-12-1
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */
L::import('WIND:component.exception.WindSqlException');
L::import('WIND:component.db.base.IWindDbConfig');
/**
 * 分步式数据库操作管理
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @version $Id$ 
 * @package 
 */
class WindConnectionManager{
	
	private   $config = array();
	private   $drivers = array();
	private   $builders = array();
	private   $linked = array();
	private   $connection = null;

	public function __construct($config = array()){
		if($config && ($config[IWindDbConfig::CONNECTIONS] || $config[IWindDbConfig::DRIVERS] || $config[IWindDbConfig::BUILDERS])){
			throw new WindSqlException(WindSqlException::DB_CONN_FORMAT);
		}
		$this->config = $config[IWindDbConfig::CONNECTIONS] ? $config[IWindDbConfig::CONNECTIONS] : c::getDataBaseConnection();
		$this->drivers = $config[IWindDbConfig::DRIVERS] ? $config[IWindDbConfig::DRIVERS]:C::getDataBaseDriver();
		$this->builders = $config[IWindDbConfig::BUILDERS] ? $config[IWindDbConfig::BUILDERS]:C::getDataBaseBuilder();
	}
	
	/**
	 * 添加数据库配置
	 * @param array $config
	 * @param int|string $identify
	 */
	public function registerConnectionConfig($config,$identify = ''){
		if($identify && empty($this->config[$identify])){
			throw new WindSqlException(WindSqlException::DB_CONN_EXIST);
		}
		$identify ? $this->config[$identify] = $config : $this->config[] = $config;
	}
	
	/**
	 * 添加数据库驱动
	 * @param array $config 驱动配置
	 * @param string $driver 驱动类型
	 */
	public function registerConnectionDriver($driver,$config){
		if(empty($this->drivers[$driver])){
			throw new WindSqlException(WindSqlException::DB_DRIVER_EXIST);
		}
		$this->drivers[$driver] = $config;
	}
	
	/**
	 * 添加sql语句生成器
	 * @param array $config 生成器配置
	 * @param string $builder 驱动类型
	 */
	public function registerConnectionBuilder($builder,$config){
		if(empty($this->drivers[$builder])){
			throw new WindSqlException(WindSqlException::DB_BUILDER_EXIST);
		}
		$this->builders[$builder] = $config;
	}

	
	
	/**
	 * 取得数据库服务器
	 * @param string $identify 数据库标识
	 * @param string $type 是否是主从
	 * @return resource
	 */
	public  function getConnection($identify = '',$type = IWindDbConfig::CONN_MASTER){
		if($identify && empty($this->config[$identify])){
			throw new WindSqlException(WindSqlException::DB_CONNECT_NOT_EXIST);
		}
		$identify = $identify ? $identify : $this->getRandomDbDriverIdentify($type);
		if(empty($this->linked[$identify])){
			$config = $this->config[$identify];
			$driverPath = $this->getDriver($config[IWindDbConfig::CONN_DRIVER],IWindDbConfig::DRIVER_CLASS);
			if(empty($driverPath)){
				throw new WindSqlException(WindSqlException::DB_DRIVER_NOT_EXIST);
			}
			L::import($driverPath);
			$class = substr ( $driverPath, strrpos ( $driverPath, '.' ) + 1 );
			$this->linked[$identify] = new $class($config);
		}
		return $this->connection = $this->linked[$identify];
	}
	
	/**
	 * 取得主服务器
	 * @return resource
	 */
	public function getMasterConnection(){
		return $this->getConnection('',IWindDbConfig::CONN_MASTER);
	}
	
	
	/**
	 * 取得从服务器
	 * @return Ambigous <resource, multitype:>
	 */
	public function getSlaveConnection(){
		return $this->getConnection('',IWindDbConfig::CONN_SLAVE);
	}
	
	/**
	 * 取得配置里面的驱动
	 * @param string $name
	 * @return mixed
	 */
	public function getDriver($name = '',$subname=''){
		return  $name ? $subname ? $this->drivers[$name][$subname] : $this->drivers[$name] : $this->drivers ;
	}
	
	/**
	 * 取得配置里面的生成器
	 * @param string $name
	 * @return mixed
	 */
	public function getBuilder($name = '',$subname=''){
		return  $name ? $subname ? $this->builders[$name][$subname] : $this->builders[$name] : $this->builders ;
	}
	/**
	 * 随机取得数据库配置
	 * @param string $type 是否是主从服务器
	 * @return array
	 */
	private function getRandomDbDriverIdentify($type = ''){
		$masterSlave = $this->getMasterSlave ();
		$config = (empty ( $masterSlave ) || empty ( $type )) ? $this->config : $masterSlave [$type];
		return $this->getConfigIdentifyByPostion ( $config, mt_rand ( 0, count ( $config ) - 1 ) );
	}
	
	/**
	 * 查看是是否要主从数据库设置，并按主从配置返回数据库配置信息
	 * @return array
	 */
	private function getMasterSlave() {
		$array = array ();
		foreach ( $this->config as $key => $value ) {
			if (in_array ( $value [IWindDbConfig::CONN_TYPE], array (IWindDbConfig::CONN_MASTER, IWindDbConfig::CONN_SLAVE ) )) {
				$array [$value [IWindDbConfig::CONN_TYPE]] [$key] = $value;
			}
		}
		return $array;
	}
	
	/**
	 *根据config的pos返回key
	 * @param array $config 数据库配置
	 * @param int $pos config的位置
	 * @return string 返回config的key
	 */
	 private function getConfigIdentifyByPostion($config, $pos = 0) {
		$i = 0;
		foreach ( ( array ) $config as $key => $value ) {
			if ($pos === $i)
				return $key;
			$i ++;
		}
		return '';
	}

	
}