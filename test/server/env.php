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

/*
 * This file returns a json_encoded array of test results. It can be accessed
 * via GET or POST.
 * 
 * These are the resulting array elements:
 * - BAR (string)
 * - null
 * - EXCEPTION (string)
 */


require_once __DIR__.'/../../vendor/autoload.php';

$sanitizer = new \Sanitor\Sanitizer(FILTER_SANITIZE_EMAIL);
$results = array();

$results[] = $sanitizer->filterEnv('FOO');
$results[] = $sanitizer->filterEnv('username');
try {
    $exc = $sanitizer->filterEnv(42);
} catch(\Exception $e) {
    $exc = 'EXCEPTION';
}
$results[] = $exc;
        
print(json_encode($results));