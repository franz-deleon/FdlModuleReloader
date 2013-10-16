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
        $moduleManager->getEventManager()->attach(new ModuleReloaderListener());
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
