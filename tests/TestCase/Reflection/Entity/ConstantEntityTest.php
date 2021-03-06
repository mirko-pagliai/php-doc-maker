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

use App\Animals\Cat;
use App\Vehicles\Car;
use PhpDocMaker\Reflection\Entity\ClassEntity;
use PhpDocMaker\Reflection\Entity\ConstantEntity;
use PhpDocMaker\TestSuite\TestCase;

/**
 * ConstantEntityTest class
 */
class ConstantEntityTest extends TestCase
{
    /**
     * Internal method to get a `ConstantEntity` instance
     * @param string $constant Constant name
     * @param string $class Class name
     * @return ConstantEntity
     */
    protected function getConstantEntity(string $constant, string $class = Cat::class): ConstantEntity
    {
        return ClassEntity::createFromName($class)->getConstant($constant);
    }

    /**
     * Test for `getSignature()` magic method
     * @test
     */
    public function testGetSignature()
    {
        $this->assertSame('LEGS', $this->getConstantEntity('LEGS')->getSignature());
        $this->assertSame('GENUS', $this->getConstantEntity('GENUS')->getSignature());
        $this->assertSame('TYPES', $this->getConstantEntity('TYPES', Car::class)->getSignature());
    }

    /**
     * Test for `__toString()` magic method
     * @test
     */
    public function testToString()
    {
        $this->assertSame('App\Animals\Cat::LEGS', (string)$this->getConstantEntity('LEGS'));
        $this->assertSame('App\Animals\Cat::GENUS', (string)$this->getConstantEntity('GENUS'));
        $this->assertSame('App\Vehicles\Car::TYPES', (string)$this->getConstantEntity('TYPES', Car::class));
    }

    /**
     * Test for `getDeclaringClass()` method
     * @test
     */
    public function testGetDeclaringClass()
    {
        $class = $this->getConstantEntity('LEGS')->getDeclaringClass();
        $this->assertInstanceOf(ClassEntity::class, $class);
        $this->assertSame(Cat::class, $class->getName());
    }

    /**
     * Test for `getValueAsString()` method
     * @test
     */
    public function testGetValueAsString()
    {
        $this->assertSame('4', $this->getConstantEntity('LEGS')->getValueAsString());
        $this->assertSame('Felis', $this->getConstantEntity('GENUS')->getValueAsString());
        $this->assertSame('sedan|station wagon|sport', $this->getConstantEntity('TYPES', Car::class)->getValueAsString());
    }

    /**
     * Test for `getVisibility()` method
     * @test
     */
    public function testGetVisibility()
    {
        $this->assertSame('protected', $this->getConstantEntity('LEGS')->getVisibility());
        $this->assertSame('public', $this->getConstantEntity('GENUS')->getVisibility());
    }
}
