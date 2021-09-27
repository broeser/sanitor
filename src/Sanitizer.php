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

use UnexpectedValueException;

const INPUT_SESSION = 6;
const INPUT_REQUEST = 99;

use const INPUT_COOKIE, INPUT_GET, INPUT_ENV, INPUT_POST, INPUT_SERVER;

/**
 * A wrapper around PHP's filter_-functions for sanitization
 */
class Sanitizer {
    /**
     * ID of PHP sanitize filter that shall be used
     */
    protected int $sanitizeFilter;

    /**
     * Optional filter flags supplied to the sanitizeFilter
     * Multiple flags are pipe-chained (bitwise OR): FLAG1 | FLAG2 | FLAG3 ...
     */
    protected ?int $sanitizeFlags;
    
    /**
     * The name of the sanitization filter, useful for debugging and logging
     */
    private string $filterName;
    
    /**
     * Constructor
     * 
     * @param int $filter Sanitization filter
     * @param int|null $flags Optional sanitization flags
     */
    public function __construct(int $filter, int $flags = null) {
        $this->setSanitizeFilter($filter);
        $this->setSanitizeFlags($flags);
    }

    public function getSanitizeFilter(): int {
        return $this->sanitizeFilter;
    }

    public function getSanitizeFilterName(): string {
        return $this->filterName;
    }

    public function getSanitizeFlags(): int {
        return $this->sanitizeFlags;
    } 
    
    /**
     * Finds the filter name by filter id
     * 
     * @param int $filterId
     * @return string
     * @throws UnexpectedValueException
     */
    private function findFilterName(int $filterId): string {
        foreach(filter_list() as $filterName) {
            if(filter_id($filterName)===$filterId) {
                return $filterName;
            }
        }
        
        throw new UnexpectedValueException('Could not find filter '.$filterId);
    }

    public function setSanitizeFilter(int $filter): void {
        $this->filterName = $this->findFilterName($filter);
        $this->sanitizeFilter = $filter;
    }

    public function setSanitizeFlags(?int $flags): void {
        $this->sanitizeFlags = $flags;
                
        /*
         * NULL is used internally for "something went wrong" while filtering
         * 
         * This is better than false, because you might want to filter boolean
         * values
         */
        $this->addSanitizeFlag(FILTER_NULL_ON_FAILURE);
    }
    
    /**
     * Adds a flag
     * 
     * It was a deliberate choice not to include a removeSanitizeFlag()-
     * method
     * 
     * @param int $flag
     */
    public function addSanitizeFlag(int $flag): void {
        $this->sanitizeFlags |= $flag;
    }
    
    /**
     * Sanitizes the value with the sanitization filter and flags assigned to this sanitizer
     * 
     * @param mixed $value The value to filter
     * @return mixed
     * @throws SanitizationException
     */
    public function filter(mixed $value): mixed
    {
        return $this->checkSanitizedValue(filter_var($value, $this->sanitizeFilter, $this->sanitizeFlags));
    }

    /**
     * Sanitizes a POST-variable
     *
     * Returns null, if the variable does not exist
     *
     * @param string $variableName
     * @return mixed
     * @throws SanitizationException
     */
    public function filterPost(string $variableName): mixed
    {
        return $this->filterInput(INPUT_POST, $variableName);
    }

    /**
     * Sanitizes a GET-variable
     *
     * Returns null, if the variable does not exist
     *
     * @param string $variableName
     * @return mixed
     * @throws SanitizationException
     */
    public function filterGet(string $variableName): mixed
    {
        return $this->filterInput(INPUT_GET, $variableName);
    }

    /**
     * Sanitizes a COOKIE-variable
     *
     * Returns null, if the variable does not exist
     *
     * @param string $variableName
     * @return mixed
     * @throws SanitizationException
     */
    public function filterCookie(string $variableName): mixed
    {
        return $this->filterInput(INPUT_COOKIE, $variableName);
    }

    /**
     * Sanitizes a SERVER-variable
     *
     * Returns null, if the variable does not exist
     *
     * @param string $variableName
     * @return mixed
     * @throws SanitizationException
     */
    public function filterServer(string $variableName): mixed
    {
        return $this->filterInput(INPUT_SERVER, $variableName);
    }

    /**
     * Sanitizes a ENV-variable
     *
     * Returns null, if the variable does not exist
     *
     * @param string $variableName
     * @return mixed
     * @throws SanitizationException
     */
    public function filterEnv(string $variableName): mixed
    {
        if(!$this->filterHas(INPUT_ENV, $variableName)) {
            return null;
        }

        // Sadly INPUT_ENV is broken and does not work
        // getenv() has to be used
        return $this->filter(getenv($variableName));
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
     * @throws SanitizationException
     */
    public function filterSession(string $variableName): mixed
    {
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
     * @throws SanitizationException
     */
    public function filterRequest(string $variableName): mixed
    {
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
     * @throws UnexpectedValueException
     */
    public function filterHas(int $type, string $variableName): bool {
        return match ($type) {
            INPUT_COOKIE, INPUT_GET, INPUT_POST, INPUT_SERVER => filter_has_var($type, $variableName),
            INPUT_REQUEST => isset($_REQUEST[$variableName]),
            INPUT_SESSION => isset($_SESSION[$variableName]),
            INPUT_ENV => (getenv($variableName) !== false),
            default => throw new UnexpectedValueException('Illegal type. INPUT_-constant expected.'),
        };
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
     * @throws SanitizationException
     */
    protected function filterInput(int $type, string $variableName): mixed
    {
        if(!$this->filterHas($type, $variableName)) {
            return null;
        }
        
        return $this->checkSanitizedValue(filter_input($type, $variableName, $this->sanitizeFilter, $this->sanitizeFlags));        
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
    private function checkSanitizedValue(mixed $sanitizedValue): mixed
    {
        if(is_null($sanitizedValue)) {
            throw new SanitizationException('Filtering with filter ' . $this->getSanitizeFilterName() . ' did not work');
        }

        return $sanitizedValue;
    }

}
