<?php 
defined('_JEXEC') or die('Direct Access Not Possible');
jimport('joomla.application.component.controller');
class OpenChartController extends JController {
	private $perPage;
	private $limitstart;
	private $pagination;
	function __construct() {
			parent::__construct();
			$this->perPage=15;
			$this->limitstart=JRequest::getInt('limitstart',0);
	}
	function display()
	{
		echo "Welcome To Open Chart";
	}
	/**
	* Chart History
	*/
	function chat_history()
	{
		$doc=JFactory::getDocument();
		$doc->addStyleSheet(JURI::root().'media/com_openchart/css/openchart.css');
		JToolBarHelper::Title('chat History','chathistory.png');
		$chats=$this->getChatHistory();
		//echo '<pre>';
//		print_r($chats);
//		echo '</pre>';
		JHtml::_('behavior.formvalidation');
		echo '<form action="index.php?option=com_openchart&task=chat_history" method="post" name="adminForm" id="adminForm">';
		echo '<table class=adminlist><thead><tr><th>Id</th><th>Full Name</th><th>User Name</th><th>Chat Message</th><th>Date Time</th><th>Action</th></tr></thead><tbody>';
	
		for($i=0;$i<count($chats);$i++)
		{
			$chat=$chats[$i];
			echo "<tr>";
			echo "<td>".$chat->id."</td>";
			echo "<td>".$chat->name."</td>";
			echo "<td>".$chat->username."</td>";
			echo "<td>".$chat->msg."</td>";
			echo "<td>".$chat->datetime."</td>";
			if($chat->blocked_user>0)
			{
				echo "<td><a href='index.php?option=com_openchart&task=unblockuser&user_id=".$chat->user_id."'>UnBlock User</a></td>";
			}
			else
			{
				echo "<td><a href='index.php?option=com_openchart&task=blockuser&user_id=".$chat->user_id."'>Block User</a></td>";
			}
			
		
		}
		echo "</tbody>";
		echo "<tfoot>";
			echo "<tr>";
				echo "<td colspan='5'>".$this->pagination->getListFooter()."</td>";
		echo "</tr></tfoot>";
		echo "</table></form>";
		
	}
	
	/**
	* Blocked User
	*/
	function blocked_users()
	{
		$doc=JFactory::getDocument();
		$doc->addStyleSheet(JURI::root().'media/com_openchart/css/openchart.css');
		JToolBarHelper::Title('Blocked Users','blockedusers.png');
		$blockedUsers=$this->getBlockedUsers();
		//echo "<pre>";
//		print_r($this->getBlockedUsers());
//		echo "</pre>";
		JHtml::_('behavior.formvalidation');
		echo '<form action="index.php?option=com_openchart&task=blocked_users" method="post" name="adminForm" id="adminForm">';
		echo '<table class=adminlist><thead><tr><th>Id</th><th>Full Name</th><th>User Name</th><th>Date Time</th><th>Action</th></tr></thead><tbody>';
	
		for($i=0;$i<count($blockedUsers);$i++)
		{
			$blockedUser=$blockedUsers[$i];
			echo "<tr>";
			echo "<td>".$blockedUser->id."</td>";
			echo "<td>".$blockedUser->name."</td>";
			echo "<td>".$blockedUser->username."</td>";
			echo "<td>".$blockedUser->datetime."</td>";
			echo "<td><a href='index.php?option=com_openchart&task=unblockuser&user_id=".$blockedUser->user_id."'>UnBlock User</a></td>";
				
		}
		echo "</tbody>";
		echo "<tfoot>";
			echo "<tr>";
				echo "<td colspan='5'>".$this->pagination->getListFooter()."</td>";
		echo "</tr></tfoot>";
		echo "</table></form>";
		
	}
	/**
	*  Get Total Chats
	*/
	private function getTotal() {
		$db=JFactory::getDBO();
		$query1="select c.*,u.username,u.name,ub.user_id as blocked_user from #__openchart_msg as c LEFT JOIN #__users as u on c.user_id=u.id
		LEFT JOIN #__openchart_blocked_users as ub on c.user_id=ub.user_id order by c.id desc";
		$db->setQuery($query1);
		$db->query();
		return $db->getNumRows();	
	}
	/**
	* Get Chat History MSG
	*/
	private function getChatHistory() {
		$db=JFactory::getDBO();
		$query="select c.*,u.username,u.name,ub.user_id as blocked_user from #__openchart_msg as c
		 LEFT JOIN #__users as u on c.user_id=u.id 
		 LEFT JOIN #__openchart_blocked_users as ub on c.user_id=ub.user_id
		 order by c.id desc LIMIT ".$this->limitstart.", ".$this->perPage;
		$db->setQuery($query);
		$rows=$db->loadObjectList();
		$total=$this->getTotal();
		jimport('joomla.html.pagination');
		$this->pagination=new JPagination($total,$this->limitstart,$this->perPage);
		return $rows;	
	}
	/**
	* Block User
	*/
	function blockuser()
	{
		$app=JFactory::getApplication();
		$userId=JRequest::getInt('user_id');
		$db=JFactory::getDBO();
		$db->setQuery("INSERT INTO #__openchart_blocked_users (user_id) VALUES ($userId)");
		if($db->query())
		{
			$app->redirect('index.php?option=com_openchart&task=chat_history&user_id='.$userId,'User Blocked Successfully');
		} else {
			$app->redirect('index.php?option=com_openchart&task=chat_history','Error Occured','error');	
		}
	}
	/**
	* UnBlock User
	*/
	function unblockuser()
	{
		$app=JFactory::getApplication();
		$userId=JRequest::getInt('user_id');
		$db=JFactory::getDBO();
		$db->setQuery("DELETE FROM #__openchart_blocked_users where user_id=$userId");
		if($db->query())
		{
			$app->redirect('index.php?option=com_openchart&task=blocked_users&user_id='.$userId,'User UnBlocked Successfully');
		} else {
			$app->redirect('index.php?option=com_openchart&task=blocked_users','Error Occured','error');	
		}
	}
	/**
	* Get Blocked Users
	*/
	private function getBlockedUsers()
	{
		$db=JFactory::getDBO();
		$query="select ub.*,u.username,u.name from #__openchart_blocked_users as ub
		 LEFT JOIN #__users as u on ub.user_id=u.id 
		 order by ub.id desc LIMIT ".$this->limitstart.", ".$this->perPage;
		$db->setQuery($query);
		$rows=$db->loadObjectList();
		$total=$this->getTotalBlockedUsers();
		jimport('joomla.html.pagination');
		$this->pagination=new JPagination($total,$this->limitstart,$this->perPage);
		return $rows;	
	}
	/**
	*  Get Total Blocked Users
	*/
	private function getTotalBlockedUsers() {
		$db=JFactory::getDBO();
		$query1="select ub.*,u.username,u.name from #__openchart_blocked_users as ub LEFT JOIN #__users as u on ub.user_id=u.id order by ub.id desc";
		$db->setQuery($query1);
		$db->query();
		return $db->getNumRows();	
	}
}
?>