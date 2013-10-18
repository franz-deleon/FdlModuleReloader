<?php
namespace FdlModuleReloader;

use Zend\Filter\Word\UnderscoreToCamelCase;

class ModuleReloaderManager
{
    /**
     * Reload module list
     * @var array
     */
    protected $modules = array();

    /**
     * Add a module to the reload list
     *
     * Usage:
     * <code>
     *     add(array(
     *         array(
     *             'name' => 'ModuleName|*|ModuleName1,ModuleName2',
     *             'callback' => function ($moduleInstance, $serviceManager) {
     *                 // return boolean;
     *             },
     *         )
     *     ));
     *     // or
     *     add('ModuleName');
     * </code>
     *
     * @param mixed $module
     */
    public function add($module)
    {
        if (!is_string($module) && !is_array($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string or arrays");
        }

        if (is_array($module)) {
            $moduleNames = explode(',', $module['name']);
            foreach ($moduleNames as $moduleName) {
                $moduleName = trim($moduleName);
                $index = $this->normalizedIndex($moduleName);
                $this->modules[$index]['name'] = $moduleName;
                if (isset($module['callback']) && is_callable($module['callback'])) {
                    $this->modules[$index]['callback'] = $module['callback'];
                }
            }
        } else {
            $index = $this->normalizedIndex($module);
            $this->modules[$index]['name'] = $module;
        }
    }

    /**
     * Does the module exists in the list?
     * @param string $module
     */
    public function exists($module)
    {
        $index = $this->normalizedIndex($module);
        if (isset($this->modules[$index])) {
            return true;
        } else {
            while (list($index) = each($this->modules)) {
                if (substr($index, 0, 3) === 'all') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Remove a module from the reload list
     * @param string $module
     */
    public function remove($module)
    {
        if (!is_string($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string");
        }

        $index = $this->normalizedIndex($module);
        if (isset($this->modules[$index])) {
            unset($this->modules[$index]);
        }
    }

    /**
     * Return a module from the list
     * @param string $module
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function get($module)
    {
        if (!is_string($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string");
        }

        $index = $this->normalizedIndex($module);
        if (isset($this->modules[$index])) {
            return $this->modules[$index];
        }
    }

    /**
     * Return the reload modules list
     * @param void
     * @return array
     */
    public function getAll()
    {
        return $this->modules;
    }

    /**
     * Normalizes an index
     * @param string $index
     */
    protected function normalizedIndex($index)
    {
        if ($index === '*') {
            return uniqid('all-');
        } else {
            $filter = new UnderscoreToCamelCase();
            return $filter->filter($index);
        }
    }
}
