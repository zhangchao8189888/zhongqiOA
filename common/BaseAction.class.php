<?php
require_once("common/BaseDao.class.php");
require_once("common/BaseForm.class.php");
require_once("tools/fileTools.php");
/*require_once("tools/excel_class.php");*/
require_once ("tools/Classes/PHPExcel.php");
class BaseAction
{
	//action路径
	var $actionPath;
	//Form对象
	var $objForm;
	//操作DB对象
	var $objDao;
	//页面模式
	var $mode;
	//页面
	var $pageId;
	//设置管理员属性
	var $admin;
    function BaseAction()
    {
    }
    function initBase()
    {
    	//开始SESSION
    	//startSession();
    	//页面ID设定
    	//setPageID($this->actionPath);
    	//页面ID取得
    	//$this->pageId = getPageID(); 
    }
    function view()
    {
        // 取得画面表示文件
        $nextPageFile = $this->objForm->getTpl($this->mode);
        // 取得画面表示数据
        $form_data = $this->objForm->getFormData();
        // 画面表示
        require_once("tpl/commom/main.php");
        // 画面表示完了、清空SESSION
        //unsetNamespace(getPageID());
        // 对象释放
        unset($this->objForm);
        unset($this->objDao);
    }
   /**
    *关闭数据库
    */ 
   function closeDB()
    {
        if (isset($this->objDao)) {
            $this->objDao->closeConnect();
        }
    }
    function AssignTabMonth($date,$step){
        $date= date("Y-m-d",strtotime($step." months",strtotime($date)));//得到处理后的日期（得到前后月份的日期）
        $u_date = strtotime($date);
        $days=date("t",$u_date);// 得到结果月份的天数

        //月份第一天的日期
        $first_date=date("Y-m",$u_date).'-01';
        for($i=0;$i<$days;$i++){
            $for_day=date("Y-m-d",strtotime($first_date)+($i*3600*24));
        }
        $time = array ();
        $time["data"]   =  $date ;
        $time["next"]   =   (date("Y-m-d",strtotime("+1 day",strtotime($date))));
        $time["month"]  =   (date("Y-m",strtotime($date)));
        $time["first"]  =    $first_date;
        $time["last"]   =      $for_day;
        $time["days"]   =      $days;
        return $time;
    }
    function newExcelToHtml() {
        $fname = $_REQUEST ['fname'];
        $checkType = $_REQUEST ['checkType'];
        $path = "upload/" . $fname;
        $_ReadExcel = new PHPExcel_Reader_Excel2007 ();
        if (!$_ReadExcel->canRead($path))
            $_ReadExcel = new PHPExcel_Reader_Excel5 ();
        $_phpExcel = $_ReadExcel->load($path);
        $_newExcel = array();
        for ($_s = 0; $_s < 1; $_s++) {
            $_currentSheet = $_phpExcel->getSheet($_s);
            $_allColumn = $_currentSheet->getHighestColumn();
            $_allRow = $_currentSheet->getHighestRow();
            $temp = 0;
            for ($_r = 1; $_r <= $_allRow; $_r++) {
                for ($_currentColumn = 'A'; $_currentColumn <= $_allColumn; $_currentColumn++) {
                    $address = $_currentColumn . $_r;
                    $val = $_currentSheet->getCell($address)->getValue();
                    $_newExcel ['moban'] [$temp] [] = $val;
                }
                $temp++;
            }
        }
        $this->objForm->setFormData("salarylist", $_newExcel);
        $this->mode = "excelList";
    }
    function addOpLog($opLog) {

        $exmsg = new EC(); //设置错误信息类

        //{$OpLog['who']},{$OpLog['what']},{$OpLog['Subject']},{$OpLog['time']},{$OpLog['memo']}
        $rasult = $this->objDao->addOplog($opLog);
        if (!$rasult) {
            $exmsg->setError(__FUNCTION__, "uploadfile  add oplog  faild ");
            $this->objForm->setFormData("warn", "失败");
            //事务回滚
            //$this->objDao->rollback();
            throw new Exception ($exmsg->error());
        }
    }
    function fileUpload() {
        //设置上传目录
        $path = "/var/www/orderOA/picUpload/";

        if (!empty($_FILES)) {

            //得到上传的临时文件流
            $tempFile = $_FILES['Filedata']['tmp_name'];

            //得到文件原名
            $fileName = $_FILES["Filedata"]["name"];

            //最后保存服务器地址
            if(!is_dir($path))
                mkdir($path);
            $data = array();
            if (move_uploaded_file($tempFile, $path.$fileName)){
                $image_size = getimagesize($path.$fileName);
                $data['code'] = 100000;
                $data['imageUrl'] = 'picUpload/'.$fileName;
                $data['width'] = $image_size[0];
                $data['height'] = $image_size[1];
                echo json_encode($data);
                exit;
            }else{
                echo $path.$fileName.'------------'.$tempFile;
                echo $fileName."上传失败！";
            }

        }
        exit;
    }
    function picCut () {
        $pic = '/var/www/orderOA/'.$_POST['name'];
        $scale = $_POST['scale'];
        $cutPosition = json_decode($_POST['position']);  //取得上传的数据
        $x1 = $cutPosition->x1*$scale;
        $y1 = $cutPosition->y1*$scale;
        $width = $cutPosition->width*$scale;
        $height = $cutPosition->height*$scale;

        $type=exif_imagetype($pic);  //判断文件类型
        $support_type=array(IMAGETYPE_JPEG , IMAGETYPE_PNG , IMAGETYPE_GIF);
        if(!in_array($type, $support_type,true)) {
            echo "this type of image does not support! only support jpg , gif or png";
            exit();
        }
        switch($type) {
            case IMAGETYPE_JPEG :
                $image = imagecreatefromjpeg($pic);
                break;
            case IMAGETYPE_PNG :
                $image = imagecreatefrompng($pic);
                break;
            case IMAGETYPE_GIF :
                $image = imagecreatefromgif($pic);
                break;
            default:
                echo "Load image error!";
                exit();
        }
        $picNameArray = explode('.',$_POST['name']);
        $picPath = $picNameArray[0].'60X60.'.$picNameArray[1];
        $copy = $this->PIPHP_ImageCrop($image, $x1, $y1, $width, $height);//裁剪

        $targetPic = '/var/www/orderOA/'.$picPath;

        imagejpeg($copy, $targetPic);  //输出新图

        @unlink($pic);//删除原图节省空间

        //echo $targetPic.'?'.time(); //返回新图地址
        echo $picPath; //返回新图地址
        exit;
    }
    function PIPHP_ImageCrop($image, $x, $y, $w, $h){
        $tw = imagesx($image);
        $th = imagesy($image);

        if ($x > $tw || $y > $th || $w > $tw || $h > $th)
            return FALSE;

        $temp = imagecreatetruecolor($w, $h);
        imagecopyresampled($temp, $image, 0, 0, $x, $y,
            $w, $h, $w, $h);
        return $temp;
    }
}
?>
