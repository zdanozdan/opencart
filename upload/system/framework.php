<?php

// Registry
$registry = new Registry();

// Config
$config = new Config();
$config->load('default');
$config->load($application_config);
$registry->set('config', $config);

// Log
$log = new Log($config->get('error_filename'));
$registry->set('log', $log);

date_default_timezone_set($config->get('date_timezone'));

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register($key, new Action($action), $priority);
		}
	}
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Request
$registry->set('request', new Request());

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$response->setCompression($config->get('config_compression'));
$registry->set('response', $response);


// Database
if ($config->get('db_autostart')) {
	$registry->set('db', new DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
}

// Session
$session = new Session($config->get('session_engine'), $registry);
$registry->set('session', $session);

if ($config->get('session_autostart')) {
	/*
      We are adding the session cookie outside of the session class as I believe
      PHP messed up in a big way handling sessions. Why in the hell is it so hard to
      have more than one concurrent session using cookies!

      Is it not better to have multiple cookies when accessing parts of the system
      that requires different cookie sessions for security reasons.

      Also cookies can be accessed via the URL parameters. So why force only one cookie
      for all sessions!
	*/

	if (isset($_COOKIE[$config->get('session_name')])) {
		$session_id = $_COOKIE[$config->get('session_name')];
	} else {
		$session_id = '';
	}

	$session->start($session_id);

	setcookie($config->get('session_name'), $session->getId(), ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
}

// Cache
$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));

// Url
if ($config->get('url_autostart')) {
	$registry->set('url', new Url($config->get('site_url'), $config->get('site_ssl')));
}

// Language
$language = new Language($config->get('language_directory'));
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Config Autoload
if ($config->has('config_autoload')) {
	foreach ($config->get('config_autoload') as $value) {
		$loader->config($value);
	}
}

// Language Autoload
if ($config->has('language_autoload')) {
	foreach ($config->get('language_autoload') as $value) {
		$loader->language($value);
	}
}

// Library Autoload
if ($config->has('library_autoload')) {
	foreach ($config->get('library_autoload') as $value) {
		$loader->library($value);
	}
}

// Model Autoload
if ($config->has('model_autoload')) {
	foreach ($config->get('model_autoload') as $value) {
		$loader->model($value);
	}
}

// Route
$route = new Router($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
	foreach ($config->get('action_pre_action') as $value) {
		$route->addPreAction(new Action($value));
	}
}

set_error_handler(function($code, $message, $file, $line) use($log, $config, $route, $response) {
    //var_dump('error');
	// error suppressed with @
	//if (error_reporting() === 0) {
	//	return false;
	//}

	switch ($code) {
    case E_NOTICE:
    case E_USER_NOTICE:
        $error = 'Notice';
        break;
    case E_WARNING:
    case E_USER_WARNING:
        $error = 'Warning';
        break;
    case E_ERROR:
    case E_USER_ERROR:
        $error = 'Fatal Error';
        break;
    default:
        $error = 'Unknown';
        break;
	}

	if ($config->get('error_log')) {
		$log->write('PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line);
	}

    //if ($config->get('error_display')) {
    //    echo '<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>';
    // }

    $backtrace = debug_backtrace();
    $traces = "";
    foreach($backtrace as $trace) {
        $traces .= "<li>File: ".$trace['file'].'Line: '.$trace['line'].' Function: [ '.$trace['function'].' ] </li>';
    }

    // Dispatch
    $route->dispatch(new Action('error/fatal'), new Action($config->get('action_error')));
    $response->output();

    // In production enviroment we want to send an email when error happen
    $transport = (new Swift_SmtpTransport($config->get('error_mail_smtp_hostname'),$config->get('error_mail_smtp_port'), $config->get('error_mail_smtp_protocol')))
        ->setUsername($config->get('error_mail_smtp_username'))
        ->setPassword($config->get('error_mail_smtp_password'));
 
    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);
    
    $server = "";
    foreach($_SERVER as $key=>$value) {
        $server .= "<li>".$key.":".$value."</li>";
    }

    $gets = "";
    foreach($_GET as $key=>$value) {
        $gets .= "<li>".$key.":".$value."</li>";
    }
    $posts = "";
    foreach($_POST as $key=>$value) {
        $posts .= "<li>".$key.":".$value."</li>";
    }

    $message = (new Swift_Message('OpenCart error mailer:'.$error))
        ->setFrom(['errors@mikran.com' => 'OpenCart Errors'])
        ->setTo(['tomasz.zdanowski@mikran.com' => 'Tomasz Zdanowski'])
        ->setBody('<html><body><ul><li>'.$error.'</li><li> Message:'.$message.'</li><li>File: '.$file.', Line: '.$line.'</li><hr/><ul>'.$traces.'</ul><hr/><ul>'.$server.'</ul><hr/><ul>'.$gets.'</ul><hr/><ul>'.$posts.'</ul></body></html>')
        ->setContentType('text/html');
 
    // Send the message
    $mailer->send($message);
    die();
});

set_exception_handler(function ($e) use($config, $route, $response) {
    //Call error handler
    $route->dispatch(new Action('error/fatal'), new Action($config->get('action_error')));
    $response->output();

    // In production enviroment we want to send an email when error happen
    $transport = (new Swift_SmtpTransport($config->get('error_mail_smtp_hostname'),$config->get('error_mail_smtp_port'), $config->get('error_mail_smtp_protocol')))
        ->setUsername($config->get('error_mail_smtp_username'))
        ->setPassword($config->get('error_mail_smtp_password'));
        
    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);
        
    $server = "";
    foreach($_SERVER as $key=>$value) {
        $server .= "<li>".$key.":".$value."</li>";
    }
        
    $gets = "";
    foreach($_GET as $key=>$value) {
        $gets .= "<li>".$key.":".$value."</li>";
    }
    $posts = "";
    foreach($_POST as $key=>$value) {
        $posts .= "<li>".$key.":".$value."</li>";
    }
        
    $message = (new Swift_Message('OpenCart error mailer:'.$e->getMessage()))
        ->setFrom(['errors@mikran.com' => 'OpenCart Errors'])
        ->setTo(['tomasz.zdanowski@mikran.com' => 'Tomasz Zdanowski'])
        ->setBody('<html><body><ul><li>Exception class name: '.get_class($e).'</li><li> Code:'.$e->getCode().'</li><li>File: '.$e->getFile().', Line: '.$e->getLine().', Message:'.$e->getMessage().'</li><hr/><li>Trace: '.implode('</br>',explode("#",$e->getTraceAsString())).'</li><hr/><ul>'.$server.'</ul><hr/><ul>'.$gets.'</ul><hr/><ul>'.$posts.'</ul></body></html>')
        ->setContentType('text/html');
        
    // Send the message
    $mailer->send($message);
    die();
});

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\Debug;

if(defined('OC_ENV') && OC_ENV=="debug") {
    ErrorHandler::register();
    ExceptionHandler::register();
    Debug::enable();

    //XDebug
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);

    ini_set('xdebug.var_display_max_depth', '10');
    ini_set('xdebug.var_display_max_children', '256');
    ini_set('xdebug.var_display_max_data', '1024');
}

$route->dispatch(new Action($config->get('action_router')), new Action($config->get('action_error')));
$response->output();