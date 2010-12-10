<?php
/**
 * @author xiaoxia xu <x_824@sina.com> 2010-12-9
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */
require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'BaseTestCase.php');
L::import(R_P . '/test/component/container/WindModuleTest.php');
L::import(R_P . '/test/component/form/WindFormFilterTest.php');
L::import(R_P . '/test/component/message/WindErrorMessageTest.php');
L::import(R_P . '/test/component/message/WindMessageTest.php');
L::import(R_P . '/test/component/db/AllDBTest.php');

class AllComponentTest extends BaseTestSuite {
	public function __construct() {
    	$this->setName('AllComponentTest');
    }
    
    public static function suite() { 
		$suite = new self();
		$suite->addTestSuite('WindModuleTest'); 
		$suite->addTestSuite('WindActionFormTest'); 
		$suite->addTestSuite('WindFormFilterTest'); 
		
		$suite->addTestSuite('WindMessageTest'); 
		$suite->addTestSuite('WindErrorMessageTest'); 
		//$suite->addTest(AllDBTest::suite());
 		return $suite;
    }
}