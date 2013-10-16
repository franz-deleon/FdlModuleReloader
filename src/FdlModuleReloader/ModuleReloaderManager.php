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
     *     add(array(
     *         array(
     *             'name' => 'ModuleName',
     *             'callback' => function ($moduleInstance, $serviceManager) {
     *                 // return boolean;
     *             },
     *         )
     *     ));
     *     // or
     *     add('ModuleName');
     *
     * @param mixed $module
     */
    public function add($module)
    {
        if (!is_string($module) && !is_array($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string or arrays");
        }

        if (is_array($module)) {
            $index = $this->normalizedIndex($module['name']);
            $this->modules[$index]['name'] = $module['name'];
            if (isset($module['callback']) && is_callable($module['callback'])) {
                $this->modules[$index]['callback'] = $module['callback'];
            }
        } else {
            $index = $this->normalizedIndex($module);
            $this->modules[$index]['name'] = $module;
        }
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
    public function getModule($module)
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
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Normalizes an index
     * @param string $index
     */
    protected function normalizedIndex($index)
    {
        $filter = new UnderscoreToCamelCase();
        return $filter->filter($index);
    }
}
