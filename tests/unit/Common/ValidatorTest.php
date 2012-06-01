<?php
require_once 'Intraface/Validator.php';
require_once 'Intraface/Error.php';

/**
 * this test should only test the extend to Ilib_Validator. The actual test should be in Ilib_Validator
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{
    private $validator;
    private $error;

    function setUp()
    {
        $this->validator = new Intraface_Validator(new Intraface_Error);
    }

    function testIdentifierReturnsFalseOnInvalidIdentifier()
    {
        $this->validator = new Intraface_Validator(new Intraface_Error);
        $this->assertFalse($this->validator->isIdentifier('this.*.is.pretty/invalid', 'Not valid'));
    }

    function testIdentifierReturnsFalseOnEmptyIdentifier()
    {
        $this->validator = new Intraface_Validator(new Intraface_Error);
        $this->assertFalse($this->validator->isIdentifier('', 'Not valid'));
    }


    function testIdentifierReturnsTrueOnValidIdentifier()
    {
        $this->validator = new Intraface_Validator(new Intraface_Error);
        $this->assertTrue($this->validator->isIdentifier('this-is-a-valid-identifier', 'Not valid'));
    }
}
?>
