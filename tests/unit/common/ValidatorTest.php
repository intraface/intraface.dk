<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Validator.php';
require_once 'Intraface/Error.php';

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    private $validator;
    private $error;

    function setUp()
    {
        $this->validator = new Validator(new Error);
    }

    function testIdentifierReturnsFalseOnInvalidIdentifier()
    {
        $this->validator = new Validator(new Error);
        $this->assertFalse($this->validator->isIdentifier('this.*.is.pretty/invalid', 'Not valid'));
    }

    function testIdentifierReturnsFalseOnEmptyIdentifier()
    {
        $this->validator = new Validator(new Error);
        $this->assertFalse($this->validator->isIdentifier('', 'Not valid'));
    }


    function testIdentifierReturnsTrueOnValidIdentifier()
    {
        $this->validator = new Validator(new Error);
        $this->assertTrue($this->validator->isIdentifier('this-is-a-valid-identifier', 'Not valid'));
    }
}
?>