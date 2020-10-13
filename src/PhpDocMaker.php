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

use League\CommonMark\CommonMarkConverter;
use PhpDocMaker\ClassesExplorer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tools\Event\EventDispatcherTrait;
use Tools\Exceptionist;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * PhpDocMaker
 */
class PhpDocMaker
{
    use EventDispatcherTrait;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected static $templatePath = ROOT . DS . 'templates' . DS . 'default';

    /**
     * Construct
     * @param string $source Path from which to read the sources
     * @param array $options Options array
     */
    public function __construct(string $source, array $options = [])
    {
        $this->source = add_slash_term($source);

        $this->setOption($options);
    }

    /**
     * "Get" magic method.
     *
     * Allows secure access to the class properties.
     * @param string $name Property name
     * @return mixed Property value
     * @throws \Tools\Exception\PropertyNotExistsException
     */
    public function __get(string $name)
    {
        Exceptionist::objectPropertyExists($this, $name);

        return $this->$name;
    }

    /**
     * Sets the default options
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver An `OptionsResolver` instance
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $titleFromPath = array_value_last(array_filter(explode(DS, $this->source)));

        $resolver->setDefaults([
            'cache' => true,
            'debug' => false,
            'title' => $titleFromPath ?? 'My project',
        ]);
        $resolver->setAllowedTypes('cache', 'bool');
        $resolver->setAllowedTypes('debug', 'bool');
        $resolver->setAllowedTypes('title', ['null', 'string']);
    }

    /**
     * Sets options at runtime.
     *
     * It's also possible to pass an array with names and values to set multiple
     *  options at the same time.
     * @param string|array $name Option name or an array with names and values
     * @param mixed $value Value
     * @return \self
     */
    public function setOption($name, $value = null)
    {
        $options = (is_array($name) ? $name : [$name => $value]) + $this->options;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        return $this;
    }

    /**
     * Gets the template path
     * @return string
     */
    public static function getTemplatePath(): string
    {
        return add_slash_term(self::$templatePath);
    }

    /**
     * Gets the `Twig` instance
     * @param bool $debug Debug
     * @return \Twig\Environment
     */
    public function getTwig(bool $debug = false): Environment
    {
        $loader = new FilesystemLoader(self::getTemplatePath());
        $twig = new Environment($loader, compact('debug') + [
            'autoescape' => false,
            'strict_variables' => true,
        ]);
        $twig->addFilter(new TwigFilter('is_url', 'is_url'));
        $twig->addFilter(new TwigFilter('to_html', function (string $string) {
            return trim((new CommonMarkConverter())->convertToHtml($string));
        }));

        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }

    /**
     * Builds
     * @param string $target Target directory where to write the documentation
     * @return void
     */
    public function build(string $target): void
    {
        $target = add_slash_term($target);
        $ClassesExplorer = new ClassesExplorer($this->source);
        $Filesystem = new Filesystem();
        $Twig = $this->getTwig($this->options['debug']);
        $Twig->addGlobal('project', array_intersect_key($this->options, array_flip(['title'])));

        $Filesystem->mkdir($target, 0755);

        //Handles temporary directory
        $temp = $target . 'temp' . DS;
        $Filesystem->mkdir($temp, 0755);
        $Twig->getLoader()->addPath($temp, 'temp');

        //Handles cache
        $cache = $target . 'cache' . DS;
        if ($this->options['cache']) {
            $Filesystem->mkdir($cache, 0755);
            $Twig->setCache($cache);
        } else {
            unlink_recursive($cache, false, true);
        }

        //Copies assets files
        if (is_readable($this->getTemplatePath() . 'assets')) {
            $Filesystem->mirror($this->getTemplatePath() . 'assets', $target . 'assets');
        }

        //Gets all classes
        $classes = $ClassesExplorer->getAllClasses();
        $this->dispatchEvent('classes.founded', [$classes]);

        //Gets all functions
        $functions = $ClassesExplorer->getAllFunctions();
        $this->dispatchEvent('functions.founded', [$functions]);

        //Renders menu and footer
        $output = $Twig->render('layout/footer.twig');
        $Filesystem->dumpFile($temp . 'footer.html', $output);
        $output = $Twig->render('layout/menu.twig', compact('classes') + ['hasFunctions' => !empty($functions)]);
        $Filesystem->dumpFile($temp . 'menu.html', $output);

        //Renders index page
        $output = $Twig->render('index.twig', compact('classes'));
        $Filesystem->dumpFile($target . 'index.html', $output);
        $this->dispatchEvent('index.rendered');

        //Renders functions page
        if ($functions) {
            $this->dispatchEvent('functions.rendering');
            $output = $Twig->render('functions.twig', compact('functions'));
            $Filesystem->dumpFile($target . 'functions.html', $output);
            $this->dispatchEvent('functions.rendered');
        }

        //Renders each class page
        foreach ($classes as $class) {
            $this->dispatchEvent('class.rendering', [$class]);
            $output = $Twig->render('class.twig', compact('class'));
            $Filesystem->dumpFile($target . 'Class-' . $class->getSlug() . '.html', $output);
            $this->dispatchEvent('class.rendered', [$class]);
        }

        unlink_recursive($temp, false, true);
    }
}
