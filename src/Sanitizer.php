<?php

/*
 * The MIT License
 *
 * Copyright 2016 Benedict Roeser <b-roeser@gmx.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Sanitor;

/**
 * A wrapper around PHP's filter_-functions for sanitization
 *
 * @author Benedict Roeser <b-roeser@gmx.net>
 */
class Sanitizer {
    /**
     * ID of PHP sanitize filter that shall be used
     * 
     * @var integer
     */
    protected $sanitizeFilter;

    /**
     * Optional filter flags supplied to the sanitizeFilter
     * Multiple flags are pipe-chained (bitwise OR): FLAG1 | FLAG2 | FLAG3 ...
     * 
     * @var integer
     */
    protected $sanitizeFlags;
    
    /**
     * The name of the sanitization filter, useful for debugging and logging
     * 
     * @var string
     */
    private $filterName;
    
    /**
     * Constructor
     * 
     * @param int $filter Sanitization filter
     * @param int $flags Optional sanitization flags
     */
    public function __construct($filter, $flags = null) {
        $this->setSanitizeFilter($filter);
        $this->setSanitizeFlags($flags);
        
        /*
         * NULL is used internally for "something went wrong" while filtering
         * 
         * This is better than false, because you might want to filter boolean
         * values
         */
        $this->addSanitizeFlag(FILTER_NULL_ON_FAILURE);
    }
    
    /**
     * Returns the sanitization filter
     * 
     * @return int
     */
    public function getSanitizeFilter() {
        return $this->sanitizeFilter;
    }
    
    /**
     * Returns the name of the sanitization filter
     * 
     * @return string
     */
    public function getSanitizeFilterName() {
        return $this->filterName;
    }
    
    /**
     * Returns the sanitization flags
     * 
     * @return int
     */
    public function getSanitizeFlags() {
        return $this->sanitizeFlags;
    } 
    
    /**
     * Finds the filter name by filter id
     * 
     * @param int $filterId
     * @return string
     * @throws SanitizationException
     */
    private function findFilter($filterId) {
        foreach(filter_list() as $filterName) {
            if(filter_id($filterName)===$filterId) {
                return $filterName;
            }
        }
        
        throw new \Exception('Could not find filter '.$filterId);
    }
    
    /**
     * Sets sanitization filter
     * 
     * @param int $filter
     */
    public function setSanitizeFilter($filter) {
        $this->filterName = $this->findFilter($filter);        
        $this->sanitizeFilter = $filter;
    }
    
    /**
     * Sets sanitization flags
     * 
     * @param int $flags
     */
    public function setSanitizeFlags($flags) {
        $this->sanitizeFlags = $flags;
    }
    
    /**
     * Adds a flag
     * 
     * It was a deliberate choice not to include a removeSanitizeFlag()-
     * method
     * 
     * @param int $flag
     */
    public function addSanitizeFlag($flag) {
        $this->sanitizeFlags |= $flag;
    }
    
    /**
     * Checks whether there is a problem with a value that has been created
     * by one of the filter_…-methods and throws an exception in that case
     * 
     * Returns the value otherwise
     * 
     * @param mixed $sanitizedValue
     * @return mixed
     * @throws SanitizationException
     */
    private function checkSanitizedValue($sanitizedValue) {
        if(is_null($sanitizedValue)) {
            throw new SanitizationException('Filtering with filter '.$this->getSanitizeFilterName().' did not work');
        }
        
        return $sanitizedValue;
    }
    
    /**
     * Sanitizes the value with the sanitization filter and flags assigned to this sanitizer
     * 
     * @param mixed $value The value to filter
     * @return mixed
     * @throws SanitizationException
     */
    public function filter($value) {
        return $this->checkSanitizedValue(filter_var($value, $this->sanitizeFilter, $this->sanitizeFlags));
    } 
    
    /**
     * Helper.
     * Returns the filtered value of the variable $variableName from the 
     * data source specified by $type.
     * 
     * Returns null, if the variable does not exist. If you prefer an Exception,
     * override this method.
     * 
     * @param int $type INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_SERVER or INPUT_ENV
     * @param string $variableName
     * @return mixed
     * @throws \Exception
     */
    protected function filterInput($type, $variableName) {
        if(!is_string($variableName)) {
            throw new \Exception('Variable name expected as string');
        }
        
        if(!filter_has_var($type, $variableName)) {
            return null;
        }
        
        return $this->checkSanitizedValue(filter_input($type, $variableName, $this->sanitizeFilter, $this->sanitizeFlags));        
    }
    
    /**
     * Sanitizes a POST-variable
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterPost($variableName) {
        return $this->filterInput(\INPUT_POST, $variableName);
    }
    
    /**
     * Sanitizes a GET-variable
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterGet($variableName) {
        return $this->filterInput(\INPUT_GET, $variableName);
    }
        
    /**
     * Sanitizes a COOKIE-variable
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterCookie($variableName) {
        return $this->filterInput(\INPUT_COOKIE, $variableName);
    }
    
    /**
     * Sanitizes a SERVER-variable
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterServer($variableName) {
        return $this->filterInput(\INPUT_SERVER, $variableName);
    }
    
    /**
     * Sanitizes a ENV-variable
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterEnv($variableName) {
        return $this->filterInput(\INPUT_ENV, $variableName);
    }

    /**
     * Sanitizes a SESSION-variable
     * 
     * Experimental.
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterSession($variableName) {
        if(!is_string($variableName)) {
            throw new \Exception('Variable name expected as string');
        }
        
        if(!isset($_SESSION[$variableName])) {
            return null;
        }
        
        return $this->filter($_SESSION[$variableName]);
    }    
    
    /**
     * Sanitizes a REQUEST-variable
     * 
     * Experimental.
     * 
     * Returns null, if the variable does not exist
     * 
     * @param string $variableName
     * @return mixed
     */
    public function filterRequest($variableName) {
        if(!is_string($variableName)) {
            throw new \Exception('Variable name expected as string');
        }
        
        if(!isset($_REQUEST[$variableName])) {
            return null;
        }
        
        return $this->filter($_REQUEST[$variableName]);
    }
    
    /**
     * Wrapper for filter_has_var()
     * 
     * Returns whether a variable exists
     * 
     * @param int $type INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_SERVER, INPUT_ENV, INPUT_REQUEST or INPUT_SESSION
     * @param string $variableName
     * @return boolean
     * @throws \Exception
     */
    public function filterHas($type, $variableName) {
        if(!is_string($variableName)) {
            throw new \Exception('Variable name expected as string');
        }
        
        switch($type) {
            case INPUT_COOKIE:
            case INPUT_ENV:
            case INPUT_GET:
            case INPUT_POST:
            case INPUT_SERVER:
                return filter_has_var($type, $variableName);
            case INPUT_REQUEST:
                return isset($_REQUEST[$variableName]);
            case INPUT_SESSION:
                return isset($_SESSION) && isset($_SESSION[$variableName]);
            default:
                throw new \Exception('Illegal type. INPUT_-constant expected.');
        }
    }
}
