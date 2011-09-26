<?php
/**
 * 字符、路径过滤等安全处理
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind.utility
 */
class WindSecurity {

	/**
	 * 转义输出字符串
	 * 
	 * @param string $str 被转义的字符串
	 * @return string
	 */
	public static function escapeHTML($str) {
		if (is_array($str)) {
			$_tmp = array();
			foreach ($str as $key => $value) {
				is_string($key) && $key = self::escapeHTML($str);
				$_tmp[$key] = self::escapeHTML($value);
			}
			return $_tmp;
		}
		return htmlspecialchars($str, ENT_QUOTES, Wind::getApp()->getResponse()->getCharset());
	}

	/**
	 * 路径检查转义
	 * 
	 * @param string $fileName 被检查的路径
	 * @param boolean $ifCheck 是否需要检查文件名，默认为false
	 * @return string
	 */
	public static function escapePath($filePath, $ifCheck = false) {
		$_tmp = array("'" => '', '#' => '', '=' => '', '`' => '', '$' => '', '%' => '', '&' => '', ';' => '');
		$_tmp['://'] = $_tmp["\0"] = '';
		$ifCheck && $_tmp['..'] = '';
		if (strtr($filePath, $_tmp) == $filePath) return preg_replace('/[\/\\\]{1,}/i', '/', $filePath);
		if (WIND_DEBUG & 2) {
			Wind::getApp()->getComponent('windLogger')->info("[utility.WindSecurity.escapePath] file path is illegal.\r\n\tFilePath:" . $filePath);
		}
		throw new WindException('[utility.WindSecurity.escapePath] file path is illegal');
	}
}