<?php
date_default_timezone_set("Asia/Shanghai");
//���ݿ���������	
	class dbClass{ //��ʼ�������ݿ���
		var $username;//�����½���ݿ������
		var $password;//��½���ݿ������
		var $database;//��Ҫѡ������ݿ�
		var $hostname;//mysql���ݿ������
		var $link;//�������ݿ�ı���
		var $result;//���������$result
		function dbClass($username,$password,$database,$hostname){//���ù��췽����������ֵ
			$this->username=$username;
			$this->password=$password;
			$this->database=$database;
			$this->hostname=$hostname;
		}
		function connect(){ //������������������ݿ�
			$this->link=@mysql_connect($this->hostname,$this->username,$this->password) or die("�Բ����������ݿ�ʧ��");
			return $this->link;
		}
		function select(){ //�����������ѡ�����ݿ�
			@mysql_select_db($this->database,$this->link);
		}
		function query($sql){ //������������ͳ���ѯ��䲢���ؽ�������á�
			@mysql_query("SET NAMES 'utf8'"); 
			if($this->result=@mysql_query($sql,$this->link)){ 
				
				return $this->result;
			}
			//else {				//��������ʾSQL���Ĵ�����Ϣ����Ҫ����ƽ׶�������ʾ����ʽ���н׶οɽ��������ע�͵���
				//echo "<br>SQL������ <font color=red>$sql</font> <BR><BR>������Ϣ�� ".mysql_error();
				//return false;
			//}
		}
/*
���º������ڴӽ��ȡ�����飬һ���� while()ѭ����$db->query($sql) ���ʹ�ã����磺
$result=query("select * from mytable");
while($row=$db->getarray($result)){
echo "$row[id] ";
}
*/
	function getarray($result){
		return @mysql_fetch_array($result);
	}
/*
���º�������ȡ��SQL��ѯ�ĵ�һ�У�һ�����ڲ�ѯ�������������Ƿ���ڣ����磺
�û��ӱ��ύ���û���$username������$password�Ƿ����û���user���У�����������Ӧ�����飺
if($user=$db->getfirst("select * from user where username='$username' and password='$password' "))
echo "��ӭ $username �����ID�� $user[id] ��";
else
echo "�û������������";
*/
	function getfirst($sql){
	return @mysql_fetch_array($this->query($sql));
	}
/*
�������º������ط��ϲ�ѯ���������������������ڷ�ҳ�ļ����Ҫ�õ������磺
$totlerows=$db->getcount("select * from mytable");
echo "���� $totlerows ����Ϣ��";
*/
	function getcount($sql){
		return @mysql_num_rows($this->query($sql)); 
	}

/*
�������º������ڸ������ݿ⣬�����û��������룺
$db->update("update user set password='$new_password' where userid='$userid' ");
*/
	function update($sql){
		return $this->query($sql);
	}

/*
�������º������������ݿ����һ�У��������һ���û���
$db->insert("insert into user (userid,username,password) values (null,'$username','$password')");
*/
	function insert($sql){
		return $this->query($sql);
	}
	function getid(){ //�����������ȡ�øղ����е�id
		return @mysql_insert_id();
	}
	//��ȡ�ļ���׺��
	function getFileExt($file_name){
        while($dot = strpos($file_name, "."))
        {
                $file_name = substr($file_name, $dot+1);
        }
        return $file_name;
	} 
}
//��ʼ����ʵ�������ù��췽��
$db=new dbClass("root","123456","xifei2011","localhost");
$db->connect();//�������ݿ�
$db->select();//ѡ�����ݿ�
//��ֵ��ѯ
function get_vclassname($yx_name,$yx_biao,$yx_idname,$yx_id)
{
	$sqlyxc = "SELECT ".$yx_name." FROM ".$yx_biao." where ".$yx_idname."='".$yx_id."'";
	@mysql_query("SET NAMES 'utf8'"); 
	$resyxd = mysql_query($sqlyxc);
	$rowyxd = mysql_fetch_array($resyxd);
	return $rowyxd[$yx_name];
}
?>