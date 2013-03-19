<?php  
class MyTestClass extends PHPUnit_Framework_TestCase  
{  
/**  
* Testing the answer to Òdo you love unit tests?Ó  
*/ 
public function testDoYouLoveUnitTests()  
{  
$love = true;  
$this->assertTrue($love);  
}  
} 