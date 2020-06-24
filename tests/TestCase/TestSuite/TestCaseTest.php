<?php
declare(strict_types=1);

/**
 * This file is part of api-maker.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/api-maker
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace ApiMaker\Test\TestSuite;

use ApiMaker\Reflection\Entity\ClassEntity;
use ApiMaker\Reflection\Entity\FunctionEntity;
use ApiMaker\TestSuite\TestCase;
use App\Animals\Cat;
use PHPUnit\Framework\AssertionFailedError;

/**
 * TestCaseTest class
 */
class TestCaseTest extends TestCase
{
    /**
     * Test for `getClassEntityFromTests()` method
     * @test
     */
    public function testGetClassEntityFromTests()
    {
        $result = $this->getClassEntityFromTests(Cat::class);
        $this->assertInstanceOf(ClassEntity::class, $result);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Impossible to find the `\App\NoExistingClass` class from test files');
        $this->getClassEntityFromTests('\\App\\NoExistingClass');
    }

    /**
     * Test for `getFunctionEntityFromTests()` method
     * @test
     */
    public function testGetFunctionEntityFromTests()
    {
        $result = $this->getFunctionEntityFromTests('a_test_function');
        $this->assertInstanceOf(FunctionEntity::class, $result);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Impossible to find the `a_no_existing_function()` function from test files');
        $this->getFunctionEntityFromTests('a_no_existing_function');
    }
}