<?php

namespace Sanitor;

use Error;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use const INPUT_GET;

class SanitizerTest extends TestCase {

    /**
     * @var Sanitizer
     */
    protected Sanitizer $sanitizerUnderTest;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->sanitizerUnderTest = new Sanitizer(FILTER_SANITIZE_EMAIL);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers Sanitizer::getSanitizeFilter
     */
    public function testGetSanitizeFilter(): void
    {
        $this->assertEquals(FILTER_SANITIZE_EMAIL, $this->sanitizerUnderTest->getSanitizeFilter());
    }

    /**
     * @covers Sanitizer::getSanitizeFilterName
     */
    public function testGetSanitizeFilterName(): void
    {
        $this->assertEquals('email', $this->sanitizerUnderTest->getSanitizeFilterName());
    }

    /**
     * @covers Sanitizer::getSanitizeFlags
     */
    public function testGetSanitizeFlags(): void
    {
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());
    }

    /**
     * @covers Sanitizer::setSanitizeFilter
     * @covers Sanitizer::findFilterName
     */
    public function testSetSanitizeFilter(): void
    {
        $testValue = 'f9';
        $this->assertEquals($testValue, $this->sanitizerUnderTest->filter($testValue));
        $this->sanitizerUnderTest->setSanitizeFilter(FILTER_SANITIZE_NUMBER_INT);
        $this->assertEquals(9, $this->sanitizerUnderTest->filter($testValue));
        $this->assertEquals(FILTER_SANITIZE_NUMBER_INT, $this->sanitizerUnderTest->getSanitizeFilter());
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());

        $thrown = false;
        try {
            $this->sanitizerUnderTest->setSanitizeFilter(82374);
        } catch (UnexpectedValueException) {
            $thrown = true;
        }

        if (!$thrown) {
            $this->fail('Exception was not thrown on invalid filter');
        }

        try {
            $this->sanitizerUnderTest->setSanitizeFilter('foobar');
        } catch (Error) {
            return;
        }
        
        $this->fail('Exception was not thrown on invalid filter');
    }

    /**
     * @covers Sanitizer::setSanitizeFlags
     */
    public function testSetSanitizeFlags(): void
    {
        $testValue = 'f&9';
        $this->sanitizerUnderTest->setSanitizeFilter(FILTER_UNSAFE_RAW);
        $this->assertEquals($testValue, $this->sanitizerUnderTest->filter($testValue));
        $this->sanitizerUnderTest->setSanitizeFlags(FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals('f&#38;9', $this->sanitizerUnderTest->filter($testValue));
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());
    }
    
    /**
     * @covers Sanitizer::__construct
     */
    public function testConstructor(): void
    {
        $sanitizer = new Sanitizer(FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals(FILTER_UNSAFE_RAW, $sanitizer->getSanitizeFilter());
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $sanitizer->getSanitizeFlags());
        $this->assertEquals('f&#38;9', $sanitizer->filter('f&9'));
        
        try {
            new Sanitizer();
        } catch (Error $ex) {
            return;
        }
        $this->fail('Creating a Sanitizer without filter argument should throw an exception');
    }

    /**
     * @covers Sanitizer::addSanitizeFlag
     */
    public function testAddSanitizeFlag(): void
    {
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());
        $this->sanitizerUnderTest->addSanitizeFlag(FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());
        $this->sanitizerUnderTest->addSanitizeFlag(FILTER_FLAG_STRIP_LOW);
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_FLAG_STRIP_LOW | FILTER_NULL_ON_FAILURE, $this->sanitizerUnderTest->getSanitizeFlags());
    }

    /**
     * Email provider, provides email adresses as raw and sanitized values
     * 
     * @return array[]
     */
    public function emailProvider(): array
    {
        return array(
            array('@example.org', '@example.org'),
            array('example@', 'example@'),
            array('@@@', '@@@'),
            array('example', 'example'),
            array(false, false),
            array(null, null),
            array(42, 42),
            array('mail@benedict\roeser.de', 'mail@benedictroeser.de'),
            array('valid.mail@example.org', 'valid.mail@example.org'),
            array('f@example.info', 'f@example.info')
        );
    }

    /**
     * @covers Sanitizer::filter
     * @covers Sanitizer::checkSanitizedValue
     * @dataProvider emailProvider
     * @param mixed $rawValue
     * @param mixed $expectedFilteredValue
     */
    public function testFilter($rawValue, $expectedFilteredValue): void
    {
        $this->assertEquals($expectedFilteredValue, $this->sanitizerUnderTest->filter($rawValue));
    }

    /**
     * @covers Sanitizer::filterHas
     */
    public function testFilterHas(): void
    {
        $exceptionOkay = false;
        try {
            $this->sanitizerUnderTest->filterHas(INPUT_GET, array());
        } catch (Error) {
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal variable name is not thrown');
        }
        
        $exceptionOkay = false;
        try {
            $this->sanitizerUnderTest->filterHas(7952, 'mail');
        } catch (UnexpectedValueException $ex) {
            $this->assertEquals('Illegal type. INPUT_-constant expected.', $ex->getMessage());
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal INPUT_-type not thrown');
        }
        
        $exceptionOkay = false;
        try {
            $this->sanitizerUnderTest->filterHas('abc', 'mail');
        } catch (Error) {
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal INPUT_-type not thrown');
        }
        
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_GET, 'username'));
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_POST, 'username'));
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_REQUEST, 'username'));
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_SESSION, 'abcdefg'));
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_SERVER, 'abcdefg'));
        $this->assertFalse($this->sanitizerUnderTest->filterHas(INPUT_ENV, 'abcdefg'));
    }
    
    /**
     * @covers Sanitizer::checkSanitizedValue
     * @covers SanitizationException::__construct
     */
    public function testSanitizationException(): void
    {
        $bogusSanitizer = new Sanitizer(FILTER_VALIDATE_EMAIL);
        try {
            $bogusSanitizer->filter('mail@benedict\roeser.de');
        } catch (SanitizationException $ex) {
            $this->assertStringStartsWith('Sanitization failed', $ex->getMessage());
            return;
        }
        $this->fail('Expected SanitizationException was not thrown');
    }
    
    /**
     * @covers Sanitizer::filterPost
     * @covers Sanitizer::filterInput
     * @covers Sanitizer::checkSanitizedValue
     * @covers Sanitizer::filterRequest
     */
    public function testFilterPost(): void
    {
       $curl = curl_init('localhost:8080/post.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('email' => 'mail@benedict\roeser.de'));
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        
        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_REQUEST['email'] = 'mail@benedict\roeser.de';
        $this->sanitizerUnderTest->filterPost('email');
        $this->sanitizerUnderTest->filterPost('username');
        $this->sanitizerUnderTest->filterRequest('email');
        $this->sanitizerUnderTest->filterRequest('username');
        $this->sanitizerUnderTest->filterRequest(42);
        $this->sanitizerUnderTest->filterPost(42);
    }

    /**
     * @covers Sanitizer::filterGet
     * @covers Sanitizer::filterRequest
     * @covers Sanitizer::checkSanitizedValue
     * @covers Sanitizer::filterInput
     */
    public function testFilterGet(): void
    {
        $curl = curl_init('localhost:8080/get.php?email='.  rawurlencode('mail@benedict\roeser.de'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        
        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_REQUEST['email'] = 'mail@benedict\roeser.de';
        $this->sanitizerUnderTest->filterGet('email');
        $this->sanitizerUnderTest->filterGet('username');
        $this->sanitizerUnderTest->filterRequest('email');
        $this->sanitizerUnderTest->filterRequest('username');
        $this->sanitizerUnderTest->filterRequest(42);
        $this->sanitizerUnderTest->filterGet(42);
    }

    /**
     * @covers Sanitizer::filterCookie
     * @covers Sanitizer::checkSanitizedValue
     */
    public function testFilterCookie(): void
    {
        $curl = curl_init('localhost:8080/cookie.php?set=set');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        $curl = curl_init('localhost:8080/cookie.php');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cookie: email='.rawurlencode('mail@benedict\roeser.de')));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));

        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_COOKIE['email'] ='mail@benedictroeser.de';
        $this->sanitizerUnderTest->filterCookie('email');
        $this->sanitizerUnderTest->filterCookie('username');
        $this->sanitizerUnderTest->filterCookie(42);
    }

    /**
     * @covers Sanitizer::filterServer
     * @covers Sanitizer::filterInput
     */
    public function testFilterServer(): void
    {
        $this->sanitizerUnderTest->setSanitizeFilter(FILTER_SANITIZE_URL);
        $testArr = explode(DIRECTORY_SEPARATOR, $this->sanitizerUnderTest->filterServer('PHP_SELF'));
        $phpunit = array_pop($testArr);
        $this->assertEquals('phpunit', strtolower($phpunit));
        $this->assertNull($this->sanitizerUnderTest->filterServer('abcfoo123'));
        $this->assertNull($this->sanitizerUnderTest->filterServer(42));
    }

    /**
     * @covers Sanitizer::filterEnv
     * @covers Sanitizer::checkSanitizedValue
     */
    public function testFilterEnv(): void
    {
        $curl = curl_init('localhost:8080/env.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('BAR', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));

        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        putenv('BAZ=DING');
        $this->sanitizerUnderTest->filterEnv('BAZ');
        $this->sanitizerUnderTest->filterEnv('username');
        $this->sanitizerUnderTest->filterEnv(42);
    }

    /**
     * @covers Sanitizer::filterSession
     */
    public function testFilterSession(): void
    {
        $_SESSION['test'] = 'session@benedict\roeser.de';
        $this->assertEquals('session@benedictroeser.de', $this->sanitizerUnderTest->filterSession('test'));
        $this->assertNull($this->sanitizerUnderTest->filterSession('toast'));
        unset($_SESSION['test']);
        $this->assertNull($this->sanitizerUnderTest->filterSession('test'));
        $this->assertNull($this->sanitizerUnderTest->filterSession(42));
    }
}
