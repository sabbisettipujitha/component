// JavaScript Document
var JQ=jQuery.noConflict();
var maxChatId=0;
JQ(document).ready(function() {
	loadRecentChats();
	function chatScrollDown()
	{
		JQ('ul#chat_msg').scrollTop(JQ('ul#chat_msg')[0].scrollHeight);
	}
	/**
	* On Press Enter Post Chat
	*/
	JQ(document).on('keyup','#msg',function(e){ 
		if(e.keyCode==13)
		{
			JQ('#chat_btn').click();
		}
	});
	/**
	*  Check Recent Chat
	*/
	var timer=setInterval(function(){
		loadRecentChats();
		},3000);
	/**
	* Load Recent Chats
	*/
	function loadRecentChats()
	{
		var param={};
		param.option='com_openchart';
		param.task='getRecentChats';
		param.max_chat_id=maxChatId;
		JQ.post('index.php',param,function(res) {
			var obj=JSON.parse(res);
			if(obj.status)
			{
				var chats=obj.chats;
				var i=0;
				var total=chats.length - 1;
				for(i=total;i>=0;i--)
				{
					var chatDetails=chats[i];
					appendChatMessage(chatDetails);
					
				}
			}	
		})
	}
	/**
	* Append Chat Message
	*/
	function appendChatMessage(chatDetails)
	{
		var chatId=parseInt(chatDetails.id);
		if(chatId>maxChatId)
		{
			maxChatId=chatId;
		}
		var chatHTML='';
		chatHTML+='<li id="'+chatDetails.id+'">';
		chatHTML+='<span class="user">'+chatDetails.name+'</span><span class="chatmsgseperator">:</span>';
		chatHTML+='<span class="msg">'+chatDetails.msg+'</span>';
		chatHTML+='</li>';
		JQ('ul#chat_msg').append(chatHTML);
		chatScrollDown();
	}
	/**
	* On click of chat button
	*/
	JQ(document).on('click',"#chat_btn",function(){
		var msg=JQ('#msg').val().trim();
		if(msg=="")
		{
			alert("Please Enter Message");
			return false;
		}
		var param={};
		param.option='com_openchart';
		param.task='saveChatViaAjax';
		param.msg=msg;
		
		JQ.post('index.php',param,function(res) {
				var obj=JSON.parse(res);
				if(obj.status) {
					JQ('#msg').val('');
					var chatDetails = obj.chatDetails;
					appendChatMessage(chatDetails);
				}
					else
					{
						alert(obj.msg);
					}
			})
		})
});