<?php

return array(
    'service_manager' => array(
        'services' => array(
             'moduleReloaderManager' => new FdlModuleReloader\ModuleReloaderManager(),
        ),
    ),
);