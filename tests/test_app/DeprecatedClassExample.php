<?php

/**
 * This file is part of php-doc-maker.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/php-doc-maker
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

/**
 * An useless class example
 * @deprecated Useless, just for tests
 */
abstract class DeprecatedClassExample
{
    /**
     * This an anonymous method
     * @deprecated This method is deprecated
     * @return string A string as result
     * @throws \RuntimeException
     */
    public function anonymousMethod(): string {}

    /**
     * This an anonymous method with some vars
     * @param string $first First argument
     * @param array $second Second argument
     * @deprecated This method is deprecated
     * @return string A string as result
     * @link http://example.com/first-link
     * @link http://example.com/second-link
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function anonymousMethodWithSomeVars(string $first, array $second = []): string {}

    public function anonymousMethodWithoutDocBlock(): string {}

    public function anonymousMethodWithParameterAndWithoutDocBlock($parameterWithoutDocBlock): string {}

    /**
     * This is another anonymous method
     * @deprecated This method is deprecated
     * @return string A string as result
     * @throws \RuntimeException
     */
    public function anotherAnonymousMethod(): string {}

    /**
     * This method has an invalid `see` tag
     * @return string
     * @see 1
     */
    public function methodWithInvalidTag(): string {}

    /**
     * This method has an invalid `see` tag
     * @return string
     * @@todo This `todo` is invalid
     * @throws \RuntimeException
     */
    public function methodWithAnotherInvalidTag(): string {}
}
