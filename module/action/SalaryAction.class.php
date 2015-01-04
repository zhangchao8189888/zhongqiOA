<?php
require_once("module/form/SalaryForm.class.php");
require_once("module/dao/SalaryDao.class.php");
require_once("tools/excel_class.php");
require_once("tools/Classes/PHPExcel.php");
require_once("tools/Util.php");
require_once("tools/JPagination.php");
require_once("tools/fileTools.php");

class SalaryAction extends BaseAction {
    /*
        *
        * @param $actionPath
        * @return TestAction
        */
    function SalaryAction($actionPath) {
        parent::BaseAction();
        $this->objForm = new SalaryForm();
        $this->actionPath = $actionPath;
    }

    function dispatcher() {
        // (1) mode set
        $this->setMode();
        // (2) COM initialize
        $this->initBase($this->actionPath);
        // (3)验证SESSION是否过期
        //$this->checkSession();
        // (4) controll -> Model
        $this->controller();
        // (5) view
        $this->view();
        // (6) closeConnect
        $this->closeDB();
    }

    function setMode() {
        // 模式设定
        $this->mode = $_REQUEST['mode'];
    }

    function controller() {
        // Controller -> Model
        switch ($this->mode) {
            case "saveFukuanTongzhi" :
                $this->saveFukuanTongzhi();
                break;
            case "toSalaryUpload" :
                $this->toSalaryUpload();
                break;
            case "fileProDownload" :
                $this->fileProDownload();
                break;
            case "filesUp" :
                $this->filesUp();
                break;
            case "excelToHtml" :
                $this->newExcelToHtml();
                break;
            default :
                $this->modelInput();
                break;
        }


    }

    function modelInput() {
        $this->mode = "toAdd";
    }
    function toSalaryUpload () {
        $this->mode = "toSalaryUpload";
        $op = new fileoperate();
        $files = $op->list_filename("upload/", 1);
        $this->objForm->setFormData("files", $files);
    }
    function fileProDownload()
    {
        $file = DOWNLOAD_SALARY_PATH;
        //$this->mode="toUpload";
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);

        }
        $this->filesUpload();
    }
    function filesUp()
    {
        $exmsg = new EC();
        $fullfilepath = UPLOADPATH . $_FILES['file']['name'];
        $errorMsg = "";
        //var_dump($_FILES);
        $fileArray = split("\.", $_FILES['file']['name']);

        if (count($fileArray) != 2) {
            $this->mode = "toSalaryUpload";
            $errorMsg = '文件名格式 不正确';
            $this->objForm->setFormData("error", $errorMsg);
            return;
        } else if ($fileArray[1] != 'xls') {
            $this->mode = "toSalaryUpload";
            $errorMsg = '文件类型不正确，必须是xls类型';
            $this->objForm->setFormData("error", $errorMsg);
            return;
        }
        if ($_FILES['file']['error'] != 0) {
            $error = $_FILES['file']['error'];
            switch ($error) {
                case 1:
                    $errorMsg = '1,上传的文件超过了php.ini中  upload_max_filesize选项限制的值.';
                    break;
                case 2:
                    $errorMsg = '2,上传文件的大小超过了HTML表单中MAX_FILE_SIZE  选项指定的大小';
                    break;
                case 3:
                    $errorMsg = '3,文件只有部分被上传';
                    break;
                case 4:
                    $errorMsg = '4,文件没有被上传';
                    break;
                case 6:
                    $errorMsg = '找不到临文件夹';
                    break;
                case 7:
                    $errorMsg = '文件写入失败';
                    break;
            }
        }
        if ($errorMsg != "") {
            $this->mode = "toSalaryUpload";
            $this->objForm->setFormData("error", $errorMsg);
            return;
        }
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $fullfilepath)) { //上传文件
            $this->objForm->setFormData("error", "文件导入失败");
            throw new Exception(UPLOADPATH . " is a disable dir");

        } else {
            $this->mode = "toSalaryUpload";
            $succMsg = '文件导入成功';
            $this->objForm->setFormData("succ", $succMsg);

        }
        $op = new fileoperate();
        $files = $op->list_filename("upload/", 1);
        $this->objForm->setFormData("files", $files);
    }
    function newExcelToHtml() {
        $this->mode = "excelList";
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

    }
    function saveFukuanTongzhi () {

        $fileName = $_FILES ['file'] ['name'];

        $errorMsg = "";
        var_dump($_FILES);

        $fileArray = split ( "\.", $_FILES ['file'] ['name'] );
        if (count ( $fileArray ) != 2) {
            $this->mode = "toUpload";
            $errorMsg = '文件名格式 不正确';
            $this->objForm->setFormData ( "error", $errorMsg );
            return;
        } else if ($fileArray [1] != 'xls' && $fileArray [1] != 'xlsx') {
            $this->mode = "toUpload";
            $errorMsg = '文件类型不正确，必须是xls，xlsx类型';
            $this->objForm->setFormData ( "error", $errorMsg );
            return;
        }
        if ($_FILES ['file'] ['error'] != 0) {
            $error = $_FILES ['file'] ['error'];
            switch ($error) {
                case 1 :
                    $errorMsg = '1,上传的文件超过了php.ini中  upload_max_filesize选项限制的值.';
                    break;
                case 2 :
                    $errorMsg = '2,上传文件的大小超过了HTML表单中MAX_FILE_SIZE  选项指定的大小';
                    break;
                case 3 :
                    $errorMsg = '3,文件只有部分被上传';
                    break;
                case 4 :
                    $errorMsg = '4,文件没有被上传';
                    break;
                case 6 :
                    $errorMsg = '找不到临文件夹';
                    break;
                case 7 :
                    $errorMsg = '文件写入失败';
                    break;
            }
        }
        if ($errorMsg != "") {
            $this->mode = "toUpload";
            $this->objForm->setFormData ( "error", $errorMsg );
            return ;
        }
        $this->readExcelContent();

        $fukuan['fu_code'] = $_REQUEST['fuNo'];
        $fukuan['company_id'] = 0;
        $fukuan['company_name'] = $_REQUEST['company_name'];
        $fukuan['salary_time_id'] = 0;
        $fukuan['salary_time'] = $_REQUEST['salaryDate'];
        $fukuan['yingfu_money'] = $_REQUEST['yingfujine'];
        $fukuan['laowufei_money'] = $_REQUEST['laowufei'];
        $fukuan['fapiao_id_json'] = '';
        $fukuan['jieshou_person_id'] = 0;
        $fukuan['jieshou_person_name'] = $_REQUEST['jieshouren'];
        $fukuan['zhifu_status'] = 0;
        $fukuan['more'] = $_REQUEST['more'];
        $this->objDao = new SalaryDao();
        $data = array();
        if (empty($fukuan['id'])) {
            $result = $this->objDao->saveFukuanTongzhi($fukuan);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '公司添加成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '公司添加失败，请重试';
            }
        } else {
            $result = $this->objDao->updateCompany($fukuan);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '公司修改成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '公司修改失败，请重试';
            }
        }
        echo json_encode($data);
        exit;

    }

}


$objModel = new SalaryAction($actionPath);
$objModel->dispatcher();



?>
