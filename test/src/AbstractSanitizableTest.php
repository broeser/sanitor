<?php

namespace Sanitor;

class TestSanitizable extends AbstractSanitizable {
    public function __construct() {
        $this->sanitizer = new Sanitizer(FILTER_SANITIZE_EMAIL);
    }
    
    public function getRawValue() {
        return 'mail@benedict\roeser.de';
    }
    
    public function killSanitizer() {
        $this->sanitizer = null;
    }
}

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-25 at 11:21:46.
 */
class AbstractSanitizableTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var TestSanitizable
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new TestSanitizable();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers Sanitor\AbstractSanitizable::getSanitizer
     */
    public function testGetSanitizer() {
        $this->assertInstanceOf('Sanitor\Sanitizer', $this->object->getSanitizer());
    }

    /**
     * @covers Sanitor\AbstractSanitizable::getFilteredValue
     */
    public function testGetFilteredValue() {
        $this->assertEquals('mail@benedictroeser.de', $this->object->getFilteredValue());
        $this->object->killSanitizer();
        try {
            $this->object->getFilteredValue();
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('Calling getFilteredValue() without setting a Sanitizer first should throw an Exception');
    }

}
