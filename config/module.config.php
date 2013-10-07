<?php

return array(
    'service_manager' => array(
		'services' => array(
             'moduleReloaderManager' => FdlModuleReloader\ModuleReloaderManager::getInstance(),
        ),
    ),
);