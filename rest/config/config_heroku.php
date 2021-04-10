<?php
/**
 * Sample of the configuration file. Replace with your own configuration constants.
 */

/**
 * Project constants and definitions.
 */

define('LOG_FILE', __DIR__.'/../logs/debug.log');

/**
 * Swagger configuration constants.
 * Change these constants to fit your project needs.
 */

define('DOCS_FOLDER', 'docs'); // relative path from the project root
/* Folders/files containing OpenAPI annotations. */
define('DOCS_ANNOTATION_LOCATIONS', [
    __DIR__.'/../app/models/',
    __DIR__.'/../app/routes',
    __DIR__.'/../docs/doc_setup.php'
]);

/* Project definitions */
if (explode(' ', php_uname('s'))[0] === 'Windows') {
    $slash = "\\";
} else {
    $slash = '/';
}

/* Define the API base path */
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    $base_path = 'https://'.$_SERVER['HTTP_HOST'].'/';
} else {
    $base_path = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$slash.explode($slash, $_SERVER['REQUEST_URI'])[1];
}
define('API_BASE_PATH', getenv('API_BASE_PATH'));
define('SERVER_ROOT', realpath(dirname(__FILE__)).'/..');

define('AUTH_HEADER_NAME', 'Authorization'); // name of the authorization header to be used
define('SERVER_DESCRIPTION', 'Flight/Swagger API skeleton.'); // description of the host server
define('PROJECT_TITLE', 'Flight/Swagger bundle'); // project title
define('PROJECT_DESCRIPTION', 'FlightPHP micro-framework bundled with Swagger and other useful utilities.'); // project description
define('PROJECT_VERSION', '0.1'); // project version
define('PROJECT_DOCS_TITLE', 'FlightPHP/Swagger bundle'); // title of the HTML page for the generated documentation 

/* Author/team definitions */
define('AUTHOR_NAME', 'Aldin Beriša');
define('AUTHOR_EMAIL', 'aldin.berisa@stu.ibu.edu.ba');
define('AUTHOR_URL', 'https://www.ibu.edu.ba');

//JWT secret parameter
define("JWT_SECRET", getenv('JWT_SECRET'));

// DB connection parameters
define("DB_HOST", getenv('DB_HOST'));
define("DB_SCHEME", getenv('DB_SCHEME'));
define("DB_USER", getenv('DB_USER'));
define("DB_PASSWORD", getenv('DB_PASSWORD'));

// NEXMO parameters
define("NEXMO_API_KEY", getenv('NEXMO_API_KEY'));
define("NEXMO_API_SECRET", getenv('NEXMO_API_SECRET'));

// hChapcha
define("CHAPCHA_API_SECRET", getenv('CHAPCHA_API_SECRET'));

//SendGrid
define("SENDGRID_USERNAME", getenv('SENDGRID_USERNAME'));
define("SENDGRID_SECRET", getenv('SENDGRID_SECRET'));
