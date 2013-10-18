<?php
namespace FdlModuleReloader;

class Module
{
    /**
     * initialize
     * @param object $moduleManager
     */
    public function init($moduleManager)
    {
        $moduleReloaderListener = new ModuleReloaderListener(
            new ModuleReloaderManager(),
            $this->getConfig()
        );
        $moduleManager->getEventManager()->attach($moduleReloaderListener);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
