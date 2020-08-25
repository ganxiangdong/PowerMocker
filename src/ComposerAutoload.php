<?php
namespace PowerMocker;

use Composer\Autoload\ClassLoader;
use PowerMocker\Filter\Autoload;

/**
 * composer autoload
 * Class ComposerAutoload
 * @package PowerMocker
 */
class ComposerAutoload extends ClassLoader
{
    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if ($this->isWhite($class)) {
            return null;
        }
        if ($file = $this->findFile($class)) {
            include "php://filter/read=".Autoload::NAME."/resource={$file}";
            return true;
        }
    }

    /**
     * is class white
     * @param $class
     * @return bool
     */
    public function isWhite($class)
    {
        if (strpos($class, 'PowerMocker\Tests') === 0) {
            return false; //inject tests
        }
        $whiteClassPre = [
            'PowerMocker\\',
            'PhpParser\\',
            'PHPUnit\\',
            'Doctrine\\',
            'phpDocumentor\\',
            'SebastianBergmann\\',
        ];
        foreach ($whiteClassPre as $classPre) {
            if (strpos($class, $classPre) === 0) { //exclude class
                return true;
            }
        }
        return false;
    }
}
