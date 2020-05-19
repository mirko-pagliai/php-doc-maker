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
namespace ApiMaker\Reflection\Entity;

use ApiMaker\Reflection\Entity;
use ApiMaker\Reflection\Entity\Traits\DeprecatedTrait;
use ApiMaker\Reflection\Entity\Traits\SeeTagsTrait;
use ApiMaker\Reflection\Entity\Traits\VisibilityTrait;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Method entity
 */
class MethodEntity extends Entity
{
    use DeprecatedTrait;
    use SeeTagsTrait;
    use VisibilityTrait;

    /**
     * @var \Roave\BetterReflection\Reflection\ReflectionMethod
     */
    protected $reflectionObject;

    /**
     * Construct
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionObject A `ReflectionMethod` instance
     */
    public function __construct(ReflectionMethod $reflectionObject)
    {
        $this->reflectionObject = $reflectionObject;
    }

    /**
     * `__toString()` magic method
     * @return string
     */
    public function __toString(): string
    {
        return $this->reflectionObject->getName() . '(' . $this->getParametersAsString() . ')';
    }

    /**
     * Gets parameters
     * @return array Array of `ParameterEntity` instances
     */
    public function getParameters(): array
    {
        return array_map(function (ReflectionParameter $parameter) {
            return new ParameterEntity($parameter);
        }, $this->reflectionObject->getParameters());
    }

    /**
     * Gets parameters as string, separated by a comma
     * @return string
     */
    public function getParametersAsString(): string
    {
        return implode(', ', array_map('strval', $this->getParameters()));
    }

    /**
     * Gets return types as string, separated by a comma
     * @return string
     */
    public function getReturnTypeAsString(): string
    {
        $returnType = array_map(function (Return_ $return) {
            return (string)$return->getType();
        }, $this->getDocBlockInstance()->getTagsByName('return'));

        return implode(', ', $returnType);
    }

    /**
     * Gets the return description
     * @return string
     */
    public function getReturnDescription(): string
    {
        $returnTag = $this->getDocBlockInstance()->getTagsByName('return');

        return $returnTag ? (string)$returnTag[0]->getDescription() : '';
    }
}
