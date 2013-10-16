<?php
namespace FdlModuleReloader;

use Zend\EventManager;
use Zend\ModuleManager\ModuleEvent;

class ModuleReloaderListener implements EventManager\ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManager\EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'loadConfig'), -999);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'reloadModules'), -1000);
    }

    /**
     * @param EventManagerInterface $events
     * @return void
    */
    public function detach(EventManager\EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Load the module reloader config
     * @param ModuleEvent $e
     */
    public function loadConfig(ModuleEvent $e)
    {
        $moduleManager  = $e->getTarget();
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');
        $moduleReloaderManager = $serviceManager->get('moduleReloaderManager');
        $config = $serviceManager->get('config');

        foreach ($config['module_reloader'] as $module) {
            $moduleReloaderManager->add($module);
        }
    }

    /**
     * Reload specific modules
     * @param ModuleEvent $e
     */
    public function reloadModules(ModuleEvent $e)
    {
        $moduleManager  = $e->getTarget();
        $loadedModules  = $moduleManager->getLoadedModules();
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');
        $moduleReloaderManager = $serviceManager->get('moduleReloaderManager');

        foreach ($moduleReloaderManager->getModules() as $key => $module) {
            if (isset($loadedModules[$module['name']])) {
                if (isset($module['callable']) && is_callable($module['callable'])) {
                    // call the callback
                    if (false == $module['callable']($loadedModules[$module['name']], $serviceManager)) {
                        continue;
                    }
                }

                $moduleManager->getEventManager()->trigger(
                    ModuleEvent::EVENT_LOAD_MODULE,
                    $moduleManager,
                    $e->setModuleName($module['name'])
                      ->setModule($loadedModules[$module['name']])
                );

                // delete the module
                $moduleReloaderManager->remove($key);
            }
        }
    }
}
