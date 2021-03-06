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
namespace PhpDocMaker;

use Cake\Collection\Collection;
use PhpDocMaker\Reflection\Entity\ClassEntity;
use PhpDocMaker\Reflection\Entity\FunctionEntity;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\Reflector\FunctionReflector;
use Roave\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\FileIteratorSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Symfony\Component\Finder\Finder;
use Tools\Exceptionist;

/**
 * ClassesExplorer.
 *
 * It finds and gets all classes and all functions in the desired path.
 */
class ClassesExplorer
{
    /**
     * @var \Roave\BetterReflection\Reflector\ClassReflector
     */
    protected $ClassReflector;

    /**
     * @var \Roave\BetterReflection\Reflector\FunctionReflector
     */
    protected $FunctionReflector;

    /**
     * @var \Roave\BetterReflection\SourceLocator\Type\SourceLocator
     */
    protected $SourceLocator;

    /**
     * Construct
     * @param string $path Source path
     * @throws \Tools\Exception\FileNotExistsException
     * @throws \Tools\Exception\NotReadableException
     */
    public function __construct(string $path)
    {
        //Requires Composer autoloader
        $classLoader = add_slash_term($path) . 'vendor' . DS . 'autoload.php';
        Exceptionist::isReadable($classLoader, 'Missing Composer autoloader on `' . rtr($classLoader) . '`');
        $classLoader = require $classLoader;

        $finder = new Finder();
        $finder->in($path)->files()->name('*.php')->notPath('tests')->notPath('vendor')->notPath('/.+\/cache/');

        $astLocator = (new BetterReflection())->astLocator();
        $this->SourceLocator = new AggregateSourceLocator([
            new FileIteratorSourceLocator($finder->getIterator(), $astLocator),
            new ComposerSourceLocator($classLoader, $astLocator),
            new PhpInternalSourceLocator($astLocator, new ReflectionSourceStubber()),
        ]);
    }

    /**
     * Internal method to get a `ClassReflector` instance
     * @return \Roave\BetterReflection\Reflector\ClassReflector
     */
    protected function getClassReflector(): ClassReflector
    {
        $this->ClassReflector = $this->ClassReflector ?: new ClassReflector($this->SourceLocator);

        return $this->ClassReflector;
    }

    /**
     * Internal method to get a `FunctionReflector` instance
     * @return \Roave\BetterReflection\Reflector\FunctionReflector
     * @uses getClassReflector()
     */
    protected function getFunctionReflector(): FunctionReflector
    {
        $this->FunctionReflector = $this->FunctionReflector ?: new FunctionReflector($this->SourceLocator, $this->getClassReflector());

        return $this->FunctionReflector;
    }

    /**
     * Gets all classes found in the path
     * @return \Cake\Collection\Collection Collection of `ClassEntity`
     * @uses getClassReflector()
     */
    public function getAllClasses(): Collection
    {
        return collection($this->getClassReflector()->getAllClasses())->map(function (ReflectionClass $class) {
            return new ClassEntity($class);
        })->sortBy('name', SORT_ASC, SORT_STRING);
    }

    /**
     * Gets all functions found in the path
     * @return \Cake\Collection\Collection Collection of `FunctionEntity`
     * @uses getFunctionReflector()
     */
    public function getAllFunctions(): Collection
    {
        return collection($this->getFunctionReflector()->getAllFunctions())->map(function (ReflectionFunction $function) {
            return new FunctionEntity($function);
        });
    }
}
