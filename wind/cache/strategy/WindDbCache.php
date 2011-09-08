<?php

Wind::import('WIND:cache.AbstractWindCache');
/**
 * 
 * the last known user to change this file in the repository  <LastChangedBy: xiaoxiao >
 * @author xiaoxiao <x_824@sina.com>
 * @version 2011-7-26  xiaoxiao
 */
class WindDbCache extends AbstractWindCache {
	
	/**
	 * 链接句柄
	 * @var AbstractWindDbAdapter 
	 */
	protected $connection;
	
	/**
	 * 缓存表
	 * @var string 
	 */
	private $table = 'pw_cache';
	
	/**
	 * 缓存表的键字段
	 * @var string 
	 */
	private $keyField = 'key';
	
	/**
	 * 缓存表的值字段
	 * @var string 
	 */
	private $valueField = 'value';
	
	/**
	 * 缓存表过期时间字段
	 * @var string 
	 */
	private $expireField = 'expire';
	
	/**
	 * 配置文件
	 * @var string 
	 */
	private $dbConfigName = '';

	public function __construct(WindConnection $connection = null, $config = array()) {
		$connection && $this->setConnection($connection);
		$config && $this->setConfig($config);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return $this->store($key, $value, $expire);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` =? AND (`' . $this->expireField . '`=0 OR `' . $this->expireField . '`>?)';
		$data = $this->getConnection()->createStatement($sql)->getOne(array($key, time()));
		return $data[$this->valueField];
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::batchFetch()
	 */
	public function batchGet(array $keys) {
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->buildSecurityKey($value);
		}
		list($sql, $result) = array('', array());
		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` IN ' . $this->getConnection()->quoteArray(
			$keys) . ' AND (`' . $this->expireField . '`=0 OR `' . $this->expireField . '`>?)';
		$data = $this->getConnection()->createStatement($sql)->queryAll(array(time()));
		foreach ($data as $tmp) {
			$result[] = $this->formatData(array_search($tmp[$this->keyField], $keys), $tmp[$this->valueField]);
		}
		return $result;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		$sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` = ? ';
		return $this->getConnection()->createStatement($sql)->update(array($key));
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::batchDelete()
	 */
	public function batchDelete(array $keys) {
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->buildSecurityKey($value);
		}
		$sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` IN ' . $this->getConnection()->quoteArray(
			$keys);
		return $this->getConnection()->execute($sql);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::clear()
	 * 删除过期数据或是全部删除
	 */
	public function clear($expireOnly = false) {
		$sql = sprintf('DELETE FROM `%s`', $this->getTableName());
		if ($expireOnly) {
			$sql = sprintf('DELETE FROM `%s` WHERE `%s` < ', $this->getTableName(), $this->expireField) . $this->getConnection()->quote(time());
		}
		return $this->getConnection()->execute($sql);
	}

	/* (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->table = $this->getConfig('table-name', '', 'pw_cache', $config);
		$this->keyField = $this->getConfig('field-key', '', 'key', $config);
		$this->valueField = $this->getConfig('field-value', '', 'value', $config);
		$this->expireField = $this->getConfig('field-expire', '', 'expire', $config);
		$this->dbConfigName = $this->getConfig('dbconfig-name', '', '', $config);
	}

	/**
	 * 设置链接对象
	 * @param WindConnection $connection
	 */
	public function setConnection($connection) {
		if ($connection instanceof WindConnection)
			$this->connection = $connection;
	}

	/**
	 * 返回表名
	 * @return string
	 */
	private function getTableName() {
		return $this->table;
	}

	/**
	 * 存储数据
	 * @param string $key
	 * @param string $value
	 * @param int $expires
	 * @param IWindCacheDependency $denpendency
	 * @return boolean
	 */
	private function store($key, $value, $expires = 0) {
		($expires > 0) ? $expires += time() : $expire = 0;
		$db = array($this->keyField => $key, $this->valueField => $value, $this->expireField => $expires);
		$sql = 'REPLACE INTO ' . $this->getTableName() . ' SET ' . $this->getConnection()->sqlSingle($db);
		return $this->getConnection()->createStatement($sql)->update();
	}

	/**
	 * 获得链接对象
	 * @return WindConnection 
	 */
	private function getConnection() {
		return $this->_getConnection();
	}
}