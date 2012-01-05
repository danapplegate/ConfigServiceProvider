<?php

/*
 * This file is part of ConfigServiceProvider.
 *
 * (c) Igor Wiedler <igor@wiedler.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Igorw\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    private $filename;

    private $replacements = array();

    public function __construct($filename, array $replacements = array())
    {
        $this->filename = $filename;

        if ($replacements) {
            foreach ($replacements as $key => $value) {
                $this->replacements['%'.$key.'%'] = $value;
            }
        }
    }

    public function register(Application $app)
    {
        if (!file_exists($this->filename)) {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' does not exist.", $this->filename));
        }

        $config = json_decode(file_get_contents($this->filename), true);
		var_dump($config);

        if (null === $config) {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' appears to be invalid JSON.", $this->filename));
        }

        foreach ($config as $name => $value) {
            $app[$name] = $this->doReplacements($app, $value, $name.'.');
        }
    }

    private function &doReplacements(Application $app, $value, $path = '')
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
				$newpath = $path . $k;
				$app[$newpath] = $value[$k] = &$this->doReplacements($app, $v, $newpath . '.');
            }

            return $value;
        }

		$newvalue = strtr($value, $this->replacements); 
        return $newvalue;
    }
}
