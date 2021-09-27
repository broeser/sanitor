<?php

namespace Sanitor;

use PHPUnit\Framework\TestCase;

class TestSanitizable extends AbstractSanitizable {
    public function __construct() {
        $this->sanitizer = new Sanitizer(FILTER_SANITIZE_EMAIL);
    }
    
    public function getRawValue(): string {
        return 'mail@benedict\roeser.de';
    }
}

class AbstractSanitizableTest extends TestCase {

    /**
     * @var TestSanitizable
     */
    protected AbstractSanitizable $objectUnderTest;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void {
        $this->objectUnderTest = new TestSanitizable();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void {
        
    }

    /**
     * @covers AbstractSanitizable::getSanitizer
     */
    public function testGetSanitizer(): void
    {
        $this->assertEquals(new Sanitizer(FILTER_SANITIZE_EMAIL), $this->objectUnderTest->getSanitizer());
    }

    /**
     * @covers AbstractSanitizable::getFilteredValue
     */
    public function testGetFilteredValue(): void
    {
        $this->assertEquals('mail@benedictroeser.de', $this->objectUnderTest->getFilteredValue());
    }

}
