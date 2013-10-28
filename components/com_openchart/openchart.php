<?php 
defined('_JEXEC') or die('Access Deny');
jimport('joomla.application.component.controller');
$controller=JController::getInstance('OpenChart');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
?>
