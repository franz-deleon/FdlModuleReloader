<?php
namespace FdlModuleReloader;

use Zend\Filter\Word\UnderscoreToCamelCase;

class ModuleReloaderManager
{
    /**
     * @var FdlModuleReloader\ModuleReloaderManager
     */
    protected static $instance;

    /**
     * Reload module list
     * @var array
     */
    protected static $modules = array();

    /**
     * Implementation of singleton pattern.
     * We are implementing singleton since this
     * class cannot be called from the service locator.
     *
     * @param void
     */
    protected function __construct()
    {
    }

    /**
     * Retrieve the instance
     * @param void
     * @return FdlModuleReloader\ModuleReloaderManager
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * Add a module to the reload list
     * @param string $module
     */
    public function add($module)
    {
        if (!is_string($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string");
        }

        static::$modules[$this->normalizedIndex($module)] = $module;
    }

    /**
     * Delete a module from the reload list
     * @param string $module
     */
    public function delete($module)
    {
        if (!is_string($module)) {
            throw new Exception\InvalidArgumentException("Invalid type, only accepts string");
        }

        $index  = $this->normalizedIndex($module);
        if (isset(static::$modules[$index])) {
            unset(static::$modules[$index]);
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

        $index  = $this->normalizedIndex($module);
        if (isset(static::$modules[$index])) {
            return static::$modules[$index];
        }
    }

    /**
     * Return the reload modules list
     * @param void
     * @return array
     */
    public function getModules()
    {
        return static::$modules;
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
