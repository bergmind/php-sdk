<?php  
class MyTestClass extends PHPUnit_Framework_TestCase  
{  
/**  
* Testing the answer to �do you love unit tests?�  
*/ 
public function testDoYouLoveUnitTests()  
{  
$love = true;  
$this->assertTrue($love);  
}  
} 