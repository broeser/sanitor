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
 * Interface for everything that can be sanitized
 *
 * @author Benedict Roeser <b-roeser@gmx.net>
 */
interface SanitizableInterface {
    /**
     * Returns the filtered value of this
     * 
     * @return boolean
     */
    public function getFilteredValue();
    
    /**
     * Returns the unfiltered value of this
     * 
     * !!! Do not display or store this value anywhere !!!
     * 
     * @return mixed
     * @throws exceptions\Flow
     */
    public function getRawValue();
    
    /**
     * Returns the Sanitizer assigned to this
     * 
     * @return Sanitizer
     */
    public function getSanitizer();
}