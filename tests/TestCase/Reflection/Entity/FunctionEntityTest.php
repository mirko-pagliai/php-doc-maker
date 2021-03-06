<?php
declare(strict_types=1);

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
namespace PhpDocMaker\Test\Reflection\Entity;

use PhpDocMaker\Reflection\Entity\ParameterEntity;
use PhpDocMaker\TestSuite\TestCase;

/**
 * FunctionEntityTest class
 */
class FunctionEntityTest extends TestCase
{
    /**
     * @var \PhpDocMaker\Reflection\Entity\FunctionEntity
     */
    protected $Function;

    /**
     * Called before each test
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Function = $this->getFunctionEntityFromTests('a_test_function');
    }

    /**
     * Test for `getSignature()` method
     * @test
     */
    public function testGetSignature()
    {
        $this->assertSame('a_test_function(string $string = \'my string\')', $this->Function->getSignature());
    }

    /**
     * Test for `__toString()` magic method
     * @test
     */
    public function testToString()
    {
        $this->assertSame('a_test_function()', (string)$this->Function);
    }

    /**
     * Test for `getParameters()` method
     * @test
     */
    public function testGetParameters()
    {
        $this->assertContainsOnlyInstancesOf(ParameterEntity::class, $this->Function->getParameters());
    }

    /**
     * Test for `getParametersAsString()` method
     * @test
     */
    public function testGetParametersAsString()
    {
        $this->assertSame('string $string = \'my string\'', $this->Function->getParametersAsString());
        $this->assertSame('', $this->getFunctionEntityFromTests('get_woof')->getParametersAsString());
    }

    /**
     * Test for `getVisibility()` method
     * @test
     */
    public function testGetVisibility()
    {
        $this->assertSame('', $this->Function->getVisibility());
    }

    /**
     * Test for `isAbstract()` method
     * @test
     */
    public function testIsAbstract()
    {
        $this->assertFalse($this->Function->isAbstract());
    }

    /**
     * Test for `isStatic()` method
     * @test
     */
    public function testIsStatic()
    {
        $this->assertFalse($this->Function->isStatic());
    }
}
