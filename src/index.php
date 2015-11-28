<?php

namespace Temperature_Monitor\Server;

define( 'APP_DIR', __DIR__ );

require_once( APP_DIR . '/inc/class.App.php' );
require_once( APP_DIR . '/inc/class.Database.php' );
require_once( APP_DIR . '/inc/class.GetRequest.php' );
require_once( APP_DIR . '/inc/class.PostRequest.php' );

new TemperatureMonitorApp();
