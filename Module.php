<?php
namespace FdlModuleReloader;

use Zend\ModuleManager\ModuleEvent;

class Module
{

    /**
     * @var \Zend\ModuleManager\ModuleManager
     */
    protected $moduleManager;

    /**
     * @var array
     */
    protected $loadedModules;

    /**
     * initialize
     * @param object $moduleManager
     */
    public function init($moduleManager)
    {
        $moduleManager->getEventManager()->attach(
            ModuleEvent::EVENT_LOAD_MODULES_POST,
            array($this, 'reloadModules'),
            1009
        );
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

    /**
     * Reload specific modules
     * @param ModuleEvent $e
     */
    public function reloadModules(ModuleEvent $e)
    {
        $moduleManager = $e->getTarget();
        $loadedModules = $moduleManager->getLoadedModules();
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');
        var_dump($serviceManager->get('ServiceManager')->getCanonicalNames());die;

        $moduleReloaderManager = ModuleReloaderManager::getInstance();
        foreach ($moduleReloaderManager->getModules() as $key => $moduleName) {
            if (isset($loadedModules[$moduleName])) {
                $moduleManager->getEventManager()->trigger(
                    ModuleEvent::EVENT_LOAD_MODULE,
                    $moduleManager,
                    $e->setModuleName($moduleName)
                      ->setModule($loadedModules[$moduleName])
                );

                // delete the module
                $moduleReloaderManager->delete($key);
            }
        }
    }
}
