<?php

namespace Sanitor;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-22 at 12:35:44.
 */
class SanitizerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Sanitizer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Sanitizer(FILTER_SANITIZE_EMAIL);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers Sanitor\Sanitizer::getSanitizeFilter
     */
    public function testGetSanitizeFilter() {
        $this->assertEquals(FILTER_SANITIZE_EMAIL, $this->object->getSanitizeFilter());
    }

    /**
     * @covers Sanitor\Sanitizer::getSanitizeFilterName
     */
    public function testGetSanitizeFilterName() {
        $this->assertEquals('email', $this->object->getSanitizeFilterName());
    }

    /**
     * @covers Sanitor\Sanitizer::getSanitizeFlags
     */
    public function testGetSanitizeFlags() {
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
    }

    /**
     * @covers Sanitor\Sanitizer::setSanitizeFilter
     * @covers Sanitor\Sanitizer::findFilter
     */
    public function testSetSanitizeFilter() {
        $testValue = 'f9';
        $this->assertEquals($testValue, $this->object->filter($testValue));
        $this->object->setSanitizeFilter(FILTER_SANITIZE_NUMBER_INT);
        $this->assertEquals(9, $this->object->filter($testValue));
        $this->assertEquals(FILTER_SANITIZE_NUMBER_INT, $this->object->getSanitizeFilter());
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
        
        try {
            $this->object->setSanitizeFilter('foobar');
        } catch (\Exception $ex) {
            return;
        }
        
        $this->fail('Exception was not thrown on invalid filter');
    }

    /**
     * @covers Sanitor\Sanitizer::setSanitizeFlags
     */
    public function testSetSanitizeFlags() {
        $testValue = 'f&9';
        $this->object->setSanitizeFilter(FILTER_UNSAFE_RAW);
        $this->assertEquals($testValue, $this->object->filter($testValue));
        $this->object->setSanitizeFlags(FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals('f&#38;9', $this->object->filter($testValue));
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
    }
    
    /**
     * @covers Sanitor\Sanitizer::__construct
     */
    public function testConstructor() {
        $sanitizer = new Sanitizer(FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals(FILTER_UNSAFE_RAW, $sanitizer->getSanitizeFilter());
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $sanitizer->getSanitizeFlags());
        $this->assertEquals('f&#38;9', $sanitizer->filter('f&9'));
        
        try {
            new Sanitizer();
        } catch (\Exception $ex) {
            return;
        }
        $this->fails('Creating a Sanitizer without filter argument should throw an exception');
    }

    /**
     * @covers Sanitor\Sanitizer::addSanitizeFlag
     */
    public function testAddSanitizeFlag() {
        $this->assertEquals(FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
        $this->object->addSanitizeFlag(FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
        $this->object->addSanitizeFlag(FILTER_FLAG_STRIP_LOW);
        $this->assertEquals(FILTER_FLAG_ENCODE_AMP | FILTER_FLAG_STRIP_LOW | FILTER_NULL_ON_FAILURE, $this->object->getSanitizeFlags());
    }

    /**
     * Email provider, provides email adresses as raw and sanitized values
     * 
     * @return array[]
     */
    public function emailProvider() {
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
     * @covers Sanitor\Sanitizer::filter
     * @covers Sanitor\Sanitizer::checkSanitizedValue
     * @dataProvider emailProvider
     * @param string $rawValue
     * @param string $expectedFilteredValue
     */
    public function testFilter($rawValue, $expectedFilteredValue) {
        $this->assertEquals($expectedFilteredValue, $this->object->filter($rawValue));
    }

    /**
     * @covers Sanitor\Sanitizer::filterHas
     */
    public function testFilterHas() {        
        $exceptionOkay = false;
        try {
            $this->object->filterHas(\INPUT_GET, array());
        } catch (\Exception $ex) {
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal variable name is not thrown');
        }
        
        $exceptionOkay = false;
        try {
            $this->object->filterHas(7952, 'mail');
        } catch (\Exception $ex) {
            $this->assertEquals('Illegal type. INPUT_-constant expected.', $ex->getMessage());
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal INPUT_-type not thrown');
        }
        
        $exceptionOkay = false;
        try {
            $this->object->filterHas('abc', 'mail');
        } catch (\Exception $ex) {
            $exceptionOkay = true;
        }
        
        if(!$exceptionOkay) {
            $this->fail('Exception on illegal INPUT_-type not thrown');
        }
        
        $this->assertFalse($this->object->filterHas(INPUT_GET, 'username'));
        $this->assertFalse($this->object->filterHas(INPUT_POST, 'username'));
        $this->assertFalse($this->object->filterHas(INPUT_REQUEST, 'username'));
        $this->assertFalse($this->object->filterHas(INPUT_SESSION, 'abcdefg'));
        $this->assertFalse($this->object->filterHas(INPUT_SERVER, 'abcdefg'));
        $this->assertFalse($this->object->filterHas(INPUT_ENV, 'abcdefg'));    
    }
    
    /**
     * @covers Sanitor\Sanitizer::filterPost
     * @covers Sanitor\Sanitizer::filterInput
     * @covers Sanitor\Sanitizer::checkSanitizedValue
     * @covers Sanitor\Sanitizer::filterRequest
     */
    public function testFilterPost() {
       $curl = curl_init('localhost:8080/post.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('email' => 'mail@benedict\roeser.de'));
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output);
        if(!is_array($output)) {
            throw new \Exception('Invalid JSON data');
        }
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));
        
        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_REQUEST['email'] = 'mail@benedict\roeser.de';
        $this->object->filterPost('email');
        $this->object->filterPost('username');
        $this->object->filterRequest('email');
        $this->object->filterRequest('username');
        $wasThrown = false;
        try {
            $this->object->filterRequest(42);
        } catch (\Exception $ex) {
            $wasThrown = true;
        }
        if(!$wasThrown) {
            $this->fail('An Exception should be thrown');
        }
        try {
            $this->object->filterPost(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }

    /**
     * @covers Sanitor\Sanitizer::filterGet
     * @covers Sanitor\Sanitizer::filterRequest
     * @covers Sanitor\Sanitizer::checkSanitizedValue
     * @covers Sanitor\Sanitizer::filterInput
     */
    public function testFilterGet() {
        $curl = curl_init('localhost:8080/get.php?email='.  rawurlencode('mail@benedict\roeser.de'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output);
        if(!is_array($output)) {
            throw new \Exception('Invalid JSON data');
        }
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));
        
        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_REQUEST['email'] = 'mail@benedict\roeser.de';
        $this->object->filterGet('email');
        $this->object->filterGet('username');
        $this->object->filterRequest('email');
        $this->object->filterRequest('username');
        $wasThrown = false;
        try {
            $this->object->filterRequest(42);
        } catch (\Exception $ex) {
            $wasThrown = true;
        }
        if(!$wasThrown) {
            $this->fail('An Exception should be thrown');
        }
        try {
            $this->object->filterGet(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }

    /**
     * @covers Sanitor\Sanitizer::filterCookie
     * @covers Sanitor\Sanitizer::checkSanitizedValue
     */
    public function testFilterCookie() {
        $curl = curl_init('localhost:8080/cookie.php?set=set');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output);
        if(!is_array($output)) {
            throw new \Exception('Invalid JSON data');
        }
        $this->assertNull(array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));
        $curl = curl_init('localhost:8080/cookie.php');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cookie: email='.rawurlencode('mail@benedict\roeser.de')));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output);
        if(!is_array($output)) {
            throw new \Exception('Invalid JSON data');
        }
        $this->assertEquals('mail@benedictroeser.de', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));

        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        $_COOKIE['email'] ='mail@benedictroeser.de';
        $this->object->filterCookie('email');
        $this->object->filterCookie('username');
        try {
            $this->object->filterCookie(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }

    /**
     * @covers Sanitor\Sanitizer::filterServer
     * @covers Sanitor\Sanitizer::filterInput
     */
    public function testFilterServer() {
        $this->object->setSanitizeFilter(FILTER_SANITIZE_URL);
        $testArr = explode(DIRECTORY_SEPARATOR, $this->object->filterServer('PHP_SELF'));
        $phpunit = array_pop($testArr);
        $this->assertEquals('phpunit', strtolower($phpunit));
        $this->assertNull($this->object->filterServer('abcfoo123'));
        try {
            $this->object->filterServer(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }

    /**
     * @covers Sanitor\Sanitizer::filterEnv
     * @covers Sanitor\Sanitizer::checkSanitizedValue
     */
    public function testFilterEnv() {
        $curl = curl_init('localhost:8080/env.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($output);
        if(!is_array($output)) {
            throw new \Exception('Invalid JSON data');
        }
        $this->assertEquals('BAR', array_shift($output));
        $this->assertNull(array_shift($output));
        $this->assertEquals('EXCEPTION', array_shift($output));

        // The rest of this method just ensures that code coverage reports are
        // correct; This is extremely ugly, but it works
        putenv('BAZ=DING');
        $this->object->filterEnv('BAZ');
        $this->object->filterEnv('username');
        try {
            $this->object->filterEnv(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }

    /**
     * @covers Sanitor\Sanitizer::filterSession
     * @covers Sanitor\Sanitizer::filterInput
     */
    public function testFilterSession() {
        $_SESSION['test'] = 'session@benedict\roeser.de';
        $this->assertEquals('session@benedictroeser.de', $this->object->filterSession('test'));
        $this->assertNull($this->object->filterSession('toast'));
        session_unset();
        $this->assertNull($this->object->filterSession('test'));
        session_destroy();
        $this->assertNull($this->object->filterSession('test'));
        try {
            $this->object->filterSession(42);
        } catch (\Exception $ex) {
            return;
        }
        $this->fail('An Exception should be thrown');
    }
}
