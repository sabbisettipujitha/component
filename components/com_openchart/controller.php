<?php 
defined('_JEXEC') or die('Access Denied');
jimport('joomla.application.component.controller');
class OpenChartController  extends JController {
	/**
	* Display chat form
	*/
	function chat()
	{ 
		$doc=JFactory::getDocument();
		$doc->addStyleSheet(JURI::root().'media/com_openchart/css/frontend.css');
		$doc->addScript(JURI::root().'media/com_openchart/js/jquery.js');
		$doc->addScript(JURI::root().'media/com_openchart/js/frontend.js');
	?>
		<div id="openchat">
        	<div id="chat_msg_area">
            	<ul id="chat_msg">
                 	
                </ul>
            </div>
            <div id="chat_tool_bar">
            	<input type="text" name="msg" id="msg" value=""/>
                <input type="button" name="chat_btn"  id="chat_btn" value="Send" class="btn"/>
            </div>
        </div>
	<?php }
	/**
	* Save a Chat Option
	
	*/
	function saveChatViaAjax()
	{
		$app=JFactory::getApplication();
		$msg=JRequest::getString('msg');
		$userId=JFactory::getUser()->id;
		if($msg=="")
		{
			$res['status']=false;
			$res['msg']="Please Enter Message";
			echo json_encode($res);
			exit();
		}
		if($userId==0)
		{
			$res['status']=false;
			$res['msg']="Please Login Befor Chat";
			echo json_encode($res);
			exit();
		}
		if($this->isUserBlocked($userId))
		{
			$res['status']=false;
			$res['msg']="Hello you have been blocked by the admin";
			echo json_encode($res);
			exit();
		}
		$res=array();
		if($chatid=$this->saveChat($msg,$userId)) {
			$chatDetails=$this->getChatDetailsById($chatid);
			$res['chatDetails']=$chatDetails;
			$res['status']=true;
		} else {
			$res['status']=false;
		}
	    echo json_encode($res);
		$app->close();
	}
	/**
	* Save Chat
	*/
	private function saveChat($msg,$userId)
	{
		$userId=(INT)$userId;
		$db=JFactory::getDBO();
		$sql="INSERT INTO #__openchart_msg (msg,user_id) values ('$msg',$userId)";
		$db->setQuery($sql);
		if($db->query())
		{
		 	return $db->insertid();
		}
		else
		{
			return false;
		}
	}
	/**
	* Chat Details from Id
	*/
	function getChatDetailsById($id)
	{
		$id=(INT)$id;
		$db=JFactory::getDBO();
	    $sql="select c.id,c.msg,u.name from #__openchart_msg as c LEFT JOIN #__users as u on c.user_id=u.id where c.id=$id order by c.id desc LIMIT 1";
		$db->setQuery($sql);
		return $db->loadObject();
	}
	/**
	*	 Get Recent Charts
	*/
	function getRecentChats()
	{
		 $res=array();
		 $app=JFactory::getApplication();
		 $db=JFactory::getDBO();
		 $max_chat_id=JRequest::getInt('max_chat_id',0);
		 if($max_chat_id>0)
		 {
			  $sql="select c.id,c.msg,u.name from #__openchart_msg as c LEFT JOIN #__users as u on c.user_id=u.id where c.id>$max_chat_id order by c.id desc LIMIT 0,50";
		 }
		 else
		 {
			 $sql="select c.id,c.msg,u.name from #__openchart_msg as c LEFT JOIN #__users as u on c.user_id=u.id order by c.id desc LIMIT 0,50";	 
		 }
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		if($rows) {
			$res['chats']=$rows;
			$res['status']=true;
		} else
		{
			
			$res['status']=false;
		}
		echo json_encode($res);
		$app->close();
	}
	/**
	* User Is Blocked
	*/
	private function isUserBlocked($user_id){
		$db=JFactory::getDBO();
		$db->setQuery("select * from #__openchart_blocked_users where user_id=$user_id");
		$db->query();
		$total=$db->getNumRows();
		if($total>0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>