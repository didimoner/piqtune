<?php

// -----------------------------------------------------------------------------
// Подключение phpconsole
// -----------------------------------------------------------------------------

define('ENVIRONMENT', 'dev'); // prod or dev
define('DIR_SEP', DIRECTORY_SEPARATOR);

require_once 'PhpConsole'.DIR_SEP.'__autoload.php';

$connector = PhpConsole\Connector::getInstance();
$connector->setPassword('nokia6303', true);

$handler = PhpConsole\Handler::getInstance();
if (ENVIRONMENT != 'dev')
{
  $handler->setHandleErrors(false);
  $handler->setHandleExceptions(false);
  $handler->setCallOldHandlers(false);
}
$handler->start();
$handler->getConnector()->setSourcesBasePath(dirname($_SERVER['DOCUMENT_ROOT']));

function debug($var, $tags = null)
{
  if (ENVIRONMENT == 'dev')
  {
    PhpConsole\Connector::getInstance()->getDebugDispatcher()->dispatchDebug($var, $tags, 1);
  }
}

function error($type = null, $text = null, $file = null, $line = null)
{
  if (ENVIRONMENT == 'dev')
  {
    PhpConsole\Connector::getInstance()->getErrorsDispatcher()->dispatchError($type, $text, $file, $line);
  }
}