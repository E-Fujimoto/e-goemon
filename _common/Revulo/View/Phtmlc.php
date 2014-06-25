<?php

/**
 * A compiling template engine for .phtml files.
 *
 * PHP versions 5
 *
 * Copyright (c) 2008-2009 revulo
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category   Zend
 * @package    Revulo_View
 * @subpackage Phtmlc
 * @author     revulo <revulon@gmail.com>
 * @copyright  2008-2009 revulo
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    Release: 0.6
 * @link       http://www.revulo.com/ZendFramework/Component/Phtmlc.html
 */


/**
 * Zend_View_Abstract
 */
require_once 'Zend/View/Abstract.php';


/**
 * Revulo_View_Phtmlc
 *
 * This class provides additional functions to Zend_View:
 *
 * - Variables can be referred to as $foo instead of $this->foo.
 * - Variables in '<?=...?>' delimiters are escaped automatically.
 * - View scripts are compiled to get better performance.
 *
 * @category   Zend
 * @package    Revulo_View
 * @subpackage Phtmlc
 * @author     revulo <revulon@gmail.com>
 * @copyright  2008-2009 revulo
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    Release: 0.6
 * @link       http://www.revulo.com/ZendFramework/Component/Phtmlc.html
 */
class Revulo_View_Phtmlc extends Zend_View_Abstract
{
    /**
     * Directory path to compiled view scripts
     * @var    string
     */
    private $_compilePath = null;

    /**
     * Compile template fragments into one .phtml file
     * @var    boolean
     */
    private $_compileFragments = false;

    /**
     * Callback for escaping
     * @var    mixed
     */
    private $_escape2 = 'htmlspecialchars';

    /**
     * Constructor.
     *
     * @param  array|Zend_Config   $config Configuration key-value pairs
     */
    public function __construct($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (isset($config['compilePath'])) {
            $this->setCompilePath($config['compilePath']);
        }
        if (isset($config['compileFragments'])) {
            $this->compileFragments($config['compileFragments']);
        }

        parent::__construct($config);
    }

    /**
     * Set the escaping callback.
     *
     * @param  mixed   $spec   The callback for escape() to use.
     * @return Revulo_View_Phtmlc
     */
    public function setEscape($spec)
    {
        parent::setEscape($spec);
        $this->_escape2 = $spec;
        return $this;
    }

    /**
     * Return the escaping callback.
     *
     * @return mixed
     */
    public function getEscape()
    {
        return $this->_escape2;
    }

    /**
     * Set the flag to compile template fragments into one .phtml file.
     *
     * @param  boolean $flag
     * @return Revulo_View_Phtmlc
     */
    public function compileFragments($flag = true)
    {
        $this->_compileFragments = ($flag) ? true : false;
        return $this;
    }

    /**
     * Set the directory path to compiled view scripts.
     *
     * @param  string  $path
     * @return Revulo_View_Phtmlc
     */
    public function setCompilePath($path)
    {
        $path = rtrim($path, '/\\');
        if (is_dir($path) && is_writable($path)) {
            $this->_compilePath = $path;
            return $this;
        }
        throw new Exception('Invalid path provided');
    }

    /**
     * Return the directory path to compiled view scripts.
     *
     * @param  string  $path   Directory path to the view script
     * @return string
     */
    protected function _getCompilePath($path)
    {
        if (isset($this->_compilePath)) {
            if (class_exists('Zend_Controller_Front', false)) {
                $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
                return $this->_compilePath . '/' . $module;
            } else {
                return $this->_compilePath;
            }
        } else {
            return $this->_getDefaultCompilePath($path);
        }
    }

    /**
     * Return default directory path to compiled view scripts.
     *
     * @param  string  $path   Directory path to the view script
     * @return string
     */
    protected function _getDefaultCompilePath($path)
    {
        return dirname($path) . '/compile';
    }

    /**
     * Return the path to the compiled view script.
     *
     * @param  string  $filename       Full path to the view script
     * @return string
     */
    protected function _getCompiledScriptPath($filename)
    {
        $dirs = $this->getScriptPaths();

        if (count($dirs) > 1) {
            foreach ($dirs as $dir) {
                if (strpos($filename, $dir) === 0) {
                    break;
                }
            }
        } else {
            $dir = $dirs[0];
        }

        $filename = substr($filename, strlen($dir));
        return $this->_getCompilePath($dir) . '/' . $filename;
    }

    /**
     * Read a view script.
     *
     * If some partial view scripts are specified by render() method,
     * their contents are recursively read and embedded.
     *
     * @param  string  $filename
     * @return string
     */
    protected function _read($filename)
    {
        $buffer = file_get_contents($filename);

        if ($this->_compileFragments === true) {
            $pattern = '/<\?(?:php\s)?\s*echo\s*\$this\s*->\s*render\s*\(\s*([\'"])([^\1]+?)\1\s*\)[;\s]*\?>/i';
            $offset  = 0;

            while (preg_match($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $start    = $matches[0][1];
                $length   = strlen($matches[0][0]);
                $filename = $this->getScriptPath($matches[2][0]);

                $compiled = $this->_compile($filename);
                $contents = $this->_read($compiled);
                $buffer   = substr_replace($buffer, $contents, $start, $length);
                $offset   = $start + strlen($contents);
            }
        }
        return $buffer;
    }

    /**
     * Convert '<?=...?>' to '<?php echo $this->escape(...) ?>'.
     *
     * @param  string  $buffer
     * @return string  The filtered buffer
     */
    protected function _prefilter($buffer)
    {
        $callback = $this->getEscape();

        if ($callback === 'htmlspecialchars' || $callback === 'htmlentities') {
            $escape = $callback . "($1, ENT_QUOTES, '" . $this->getEncoding() . "')";
        } else if (is_string($callback)) {
            $escape = $callback . '($1)';
        } else if (is_string($callback[0])) {
            $escape = $callback[0] . '::' . $callback[1] . '($1)';
        } else {
            $escape = '$this->escape($1)';
        }

        $pattern     = '/<\?=\s*(.*?)[;\s]*\?>/s';
        $replacement = '<?php echo ' . $escape . ' ?>';
        return preg_replace($pattern, $replacement, $buffer);
    }

    /**
     * Write buffer contents to a file.
     *
     * @param  string  $filename
     * @param  string  $buffer
     */
    protected function _write($filename, $buffer)
    {
        $dir = dirname($filename);
        if (file_exists($dir) === false) {
            $umask = umask(0);
            mkdir($dir, 0777, true);
            umask($umask);
        }
        file_put_contents($filename, $buffer, LOCK_EX);
    }

    /**
     * Compile a view script.
     *
     * @param  string  $filename
     * @return string  Path to the compiled view script
     */
    protected function _compile($filename)
    {
        $compiled = $this->_getCompiledScriptPath($filename);

        if (file_exists($compiled) === false || filemtime($compiled) < filemtime($filename)) {
            $buffer = $this->_read($filename);
            $buffer = $this->_prefilter($buffer);
            $this->_write($compiled, $buffer);
        }
        return $compiled;
    }

    /**
     * Include the view script in a scope with only public $this variables.
     *
     * @param  string  The view script to execute
     */
    protected function _run()
    {
        extract(get_object_vars($this));
        include $this->_compile(func_get_arg(0));
    }
}
