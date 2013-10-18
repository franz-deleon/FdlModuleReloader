<?php
namespace FdlModuleReloader;

use Zend\EventManager;
use Zend\ModuleManager\ModuleEvent;

class ModuleReloaderListener implements EventManager\ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var ModuleReloaderManager
     */
    protected $moduleReloaderManager;

    /**
     * Constructor to inject manager
     */
    public function __construct(ModuleReloaderManager $manager, array $config)
    {
        $this->moduleReloaderManager = $manager;
        $this->config = $config;
    }

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManager\EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'loadConfig'));
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'reloadModules'));
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

            $config = $serviceManager->get('config');//var_dump($serviceManager->getCanonicalNames());
        foreach ($config['module_reloader'] as $module) {
            //$this->moduleReloaderManager->add($module);
            $moduleReloaderManager->add($module);
        }
    }

    /**
     * Reload specific modules
     * @param ModuleEvent $e
     */
    public function reloadModules(ModuleEvent $e)
    {print 2;
        $moduleManager  = $e->getTarget();
        $loadedModules  = $moduleManager->getLoadedModules();
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');
        $moduleReloaderManager = $serviceManager->get('moduleReloaderManager');

        foreach ($loadedModules as $loadedModuleName => $loadedModule) {
            foreach ($moduleReloaderManager->getAll() as $managerModule) {
                if ($managerModule['name'] === $loadedModuleName || $managerModule['name'] === '*') {
                    if (isset($managerModule['callback']) && is_callable($managerModule['callback'])) {
                        // call the callback
                        if ($managerModule['callback']($loadedModule, $serviceManager) != true) {
                            continue;
                        }
                    }

                    // reload the module
                   // $e = clone $e;
                    $moduleManager->getEventManager()->trigger(
                        ModuleEvent::EVENT_LOAD_MODULE,
                        $moduleManager,
                        $e->setModuleName($loadedModuleName)->setModule($loadedModule)
                    );

                    continue;
                }
            }
        }
    }
}
