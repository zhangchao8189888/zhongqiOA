<?php
require_once("module/form/SalaryForm.class.php");
require_once("module/dao/SalaryDao.class.php");
require_once("module/dao/EmployDao.class.php");
require_once("tools/excel_class.php");
require_once("tools/Classes/PHPExcel.php");
require_once("tools/Util.php");
require_once("tools/JPagination.php");
require_once("tools/fileTools.php");
require_once ("tools/sumSalary.class.php");

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
            case "getFileContentJson" :
                $this->getFileContentJson();
                break;
            case "sumSalary" :
                $this->sumSalary();
                break;
            default :
                $this->modelInput();
                break;
        }


    }

    function modelInput() {
        $this->mode = "toAdd";
    }
    function sumSalary() {
        $data = $_REQUEST['data'];
        $shenfenzheng = ($_POST ['shenfenzheng'] - 1);
        $addArray = $_POST ['add'];
        $delArray = $_POST ['del'];
        $freeTex = $_POST ['freeTex'] - 1;
        $shifajian = $_POST ['shifajian'] - 1;
        $checkType = $_REQUEST ['salType']; // 客服做工资选项
        $addArray = explode ( "+", $addArray );
        if (! empty ( $delArray )) {
            $delArray = explode ( "+", $delArray );
        } else {
            $delArray = "";
        }
        // print_r($addArray);
        // print_r($delArray);
        session_start ();
        $salaryList = $_SESSION ['salarylist'];
        $count_add = count ( $data [0] );
        // 增加字段1·
        // 个人失业 个人医疗 个人养老 个人合计 单位失业 单位医疗 单位养老 单位工伤 单位生育 单位合计
        // 2011-10-14增加字段 姓名 身份证号 银行卡号 身份类别 社保基数 公积金基数
        $data [0] [($count_add + 0)] = " 银行卡号";
        $data [0] [($count_add + 1)] = "身份类别";
        $data [0] [($count_add + 2)] = " 社保基数";
        $data [0] [($count_add + 3)] = "公积金基数";
        // 再次算出字段总列数
        $count = count ( $data [0] );
        $data [0] [($count + 0)] = "个人应发合计";
        $data [0] [($count + 1)] = "个人失业";
        $data [0] [($count + 2)] = "个人医疗";
        $data [0] [($count + 3)] = "个人养老";
        $data [0] [($count + 4)] = "个人公积金";
        $data [0] [($count + 5)] = "代扣税";
        $data [0] [($count + 6)] = "个人扣款合计";
        $data [0] [($count + 7)] = "实发合计";
        $data [0] [($count + 8)] = "单位失业";
        $data [0] [($count + 9)] = "单位医疗";
        $data [0] [($count + 10)] = "单位养老";
        $data [0] [($count + 11)] = "单位工伤";
        $data [0] [($count + 12)] = "单位生育";
        $data [0] [($count + 13)] = "单位公积金";
        $data [0] [($count + 14)] = "单位合计";
        $data [0] [($count + 15)] = "劳务费";
        $data [0] [($count + 16)] = "残保金";
        $data [0] [($count + 17)] = "档案费";
        $data [0] [($count + 18)] = "交中企基业合计";
        if (! empty ( $freeTex )) {
            $data [0] [($count + 19)] = "免税项";
        }
        if (! empty ( $_POST ['shifajian'] )) {
            $data [0] [($count + 20)] = "实发合计减后项";
            $data [0] [($count + 21)] = "交中企基业减后项";
        }
        $jisuan_var = array ();
        $error = array ();
        $this->objDao = new EmployDao ();
        // 根据身份证号查询出员工身份类别
        for($i = 1; $i < count ( $data ); $i ++) {
            // $error[$i]["error"]="";
            // $jisuan_var[$i]['error']="";
            /*
             * if(!is_numeric($salaryList[Sheet1][$i][$shenfenzheng])){ $error[$i]["error"]="身份证非数字类型！"; continue; }
             */
            $employ = $this->objDao->getEmByEno ( $data [$i] [$shenfenzheng] );
            if ($employ) {
                $jisuan_var [$i] ['yinhangkahao'] = $employ ['bank_num'];
                $jisuan_var [$i] ['shenfenleibie'] = $employ ['e_type'];
                $jisuan_var [$i] ['shebaojishu'] = $employ ['shebaojishu'];
                $jisuan_var [$i] ['gongjijinjishu'] = $employ ['gongjijinjishu'];
                $jisuan_var [$i] ['laowufei'] = $employ ['laowufei'];
                $jisuan_var [$i] ['canbaojin'] = $employ ['canbaojin'];
                $jisuan_var [$i] ['danganfei'] = $employ ['danganfei'];
            } else {
                $error [$i] ["error"] = "{$salaryList[Sheet1][$i][$shenfenzheng]}:未查询到该员工身份类别！";
                continue;
            }
            $addValue = 0;
            $delValue = 0;
            $f= 0;
            foreach ( $addArray as $row ) {
                if (is_numeric ( $data [$i] [($row - 1)] )) {

                    $salaryList[Sheet1] [$i]['add'][$f] ['key'] = urlencode($data [0] [($row - 1)]);
                    $salaryList[Sheet1] [$i]['add'][$f] ['value'] =  $data [$i] [($row - 1)];
                    $f++;
                    $addValue += $data [$i] [($row - 1)];
                } else {
                    $error [$i] ["error"] = "第1$row列所加项非数字类型";
                    continue;
                }
            }

            $f= 0;
            if (! empty ( $delArray )) {
                foreach ( $delArray as $row ) {
                    if (is_numeric ( $data [$i] [($row - 1)] )) {
                        $salaryList[Sheet1] [$i]['del'][$f] ['key'] = urlencode($data [0] [($row - 1)]);
                        $salaryList[Sheet1] [$i]['del'][$f] ['value'] =  $data [$i] [($row - 1)];
                        $delValue += $data [$i] [($row - 1)];
                        $f++;
                    } else {
                        $error [$i] ["error"] = "第2$row列所减项非数字类型";
                        continue;
                    }
                }
            }
            $jisuan_var [$i] ["addValue"] = $addValue;
            $jisuan_var [$i] ["delValue"] = $delValue;
            if (! empty ( $freeTex )) {
                $jisuan_var [$i] ['freeTex'] = $data [$i] [$freeTex];
                $salaryList[Sheet1] [$i]['freeTex'] ['key'] = urlencode($data [0] [($freeTex)]);
                $salaryList[Sheet1] [$i]['freeTex'] ['value'] =  $data [$i] [($freeTex)];
            } else {
                $jisuan_var [$i] ['freeTex'] = 0;
            }
        }
         //var_dump($error);
         //exit;
        $sumclass = new sumSalary ();
        $sumclass->getSumSalary ( $jisuan_var );
        $sumYingfaheji = 0;
        $sumGerenshiye = 0;
        $sumGerenyiliao = 0;
        $sumGerenyanglao = 0;
        $sumGerengongjijin = 0;
        $sumDaikousui = 0;
        $sumKoukuanheji = 0;
        $sumShifaheji = 0;
        $sumDanweishiye = 0;
        $sumDanweiyiliao = 0;
        $sumDanweiyanglao = 0;
        $sumDanweigongshang = 0;
        $sumDanweishengyu = 0;
        $sumDanweigongjijin = 0;
        $sumDanweiheji = 0;
        $sumJiaozhongqiheji = 0;
        for($i = 1; $i < count ( $data ); $i ++) {
            /**
             * $jisuan_var[$i]['yingfaheji']=0;
             * $jisuan_var[$i]['gerenshiye']="错误";
             * $jisuan_var[$i]['gerenyiliao']="错误";
             * $jisuan_var[$i]['gerenyanglao']="错误";
             * $jisuan_var[$i]['gerengongjijin']=0;
             * $jisuan_var[$i]['daikousui']=0;
             * $jisuan_var[$i]['koukuanheji']=0;
             * $jisuan_var[$i]['shifaheji']=0;
             * $jisuan_var[$i]['danweishiye']="错误";
             * $jisuan_var[$i]['danweigongshang']="错误";
             * $jisuan_var[$i]['danweishengyu']="错误";
             * $jisuan_var[$i]['danweiyanglao']="错误";
             * $jisuan_var[$i]['danweiyiliao']="错误";
             * $jisuan_var[$i]['danweigongjijin']=0;
             * $jisuan_var[$i]['danweiheji']="错误";
             * $jisuan_var[$i]['jiaozhongqiheji']=0;
             */
            // 增加的字段赋值
            /*
             * $salaryList[Sheet1][0][($count_add+0)]=" 银行卡号"; $salaryList[Sheet1][0][($count_add+1)]="身份类别"; $salaryList[Sheet1][0][($count_add+2)]=" 社保基数"; $salaryList[Sheet1][0][($count_add+3)]="公积金基数";
             */
            $canjiren = $this->objDao->getCanjiren ( $data [$i] [$shenfenzheng] );

            $data [$i] [($count_add + 0)] = $jisuan_var [$i] ['yinhangkahao'];
            $data [$i] [($count_add + 1)] = $jisuan_var [$i] ['shenfenleibie'];
            $data [$i] [($count_add + 2)] = $jisuan_var [$i] ['shebaojishu'];
            $data [$i] [($count_add + 3)] = $jisuan_var [$i] ['gongjijinjishu'];
            $data [$i] [($count + 0)] = sprintf ( "%01.2f", $jisuan_var [$i] ['yingfaheji'] ) + 0;
            $data [$i] [($count + 1)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenshiye'] ) + 0;
            $data [$i] [($count + 2)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenyiliao'] ) + 0;
            $data [$i] [($count + 3)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenyanglao'] ) + 0;
            $data [$i] [($count + 4)] = $jisuan_var [$i] ['gerengongjijin'] + 0;
            $data [$i] [($count + 5)] = sprintf ( "%01.2f", $jisuan_var [$i] ['daikousui'] ) + 0;
            $data [$i] [($count + 6)] = sprintf ( "%01.2f", $jisuan_var [$i] ['koukuanheji'] ) + 0;
            $data [$i] [($count + 7)] = sprintf ( "%01.2f", $jisuan_var [$i] ['shifaheji'] ) + 0;
            if($canjiren[0]==1){
                $data [$i] [($count + 5)] /= 2;
                $data [$i] [($count + 6)] -=  $data [$i] [($count + 5)];
                $data [$i] [($count + 7)] += $data [$i] [($count + 5)];
            }
            $data [$i] [($count + 8)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweishiye'] ) + 0;
            $data [$i] [($count + 9)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiyiliao'] ) + 0;
            $data [$i] [($count + 10)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiyanglao'] ) + 0;
            $data [$i] [($count + 11)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweigongshang'] ) + 0;
            $data [$i] [($count + 12)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweishengyu'] ) + 0;
            $data [$i] [($count + 13)] = $jisuan_var [$i] ['danweigongjijin'] + 0;
            $data [$i] [($count + 14)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiheji'] ) + 0;
            $data [$i] [($count + 15)] = sprintf ( "%01.2f", $jisuan_var [$i] ['laowufei'] ) + 0;
            $data [$i] [($count + 16)] = sprintf ( "%01.2f", $jisuan_var [$i] ['canbaojin'] ) + 0;
            $data [$i] [($count + 17)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danganfei'] ) + 0;
            // $jisuan_var[$i]['laowufei']+$jisuan_var[$i]['canbaojin']+$jisuan_var[$i]['danganfei']
            $data [$i] [($count + 18)] = sprintf ( "%01.2f", $jisuan_var [$i] ['jiaozhongqiheji'] ) + 0;
            if (! empty ( $freeTex )) {
                $data [$i] [($count + 19)] = sprintf ( "%01.2f", $jisuan_var [$i] ['freeTex'] ) + 0;
            }
            if (! empty ( $_POST ['shifajian'] )) {
                $data [$i] [($count + 20)] = sprintf ( "%01.2f", ($jisuan_var [$i] ['shifaheji'] - $data [$i] [$shifajian]) ) + 0;
                $data [$i] [($count + 21)] = sprintf ( "%01.2f", ($jisuan_var [$i] ['jiaozhongqiheji'] - $data [$i] [$shifajian]) ) + 0;
            }
            // echo $salaryList[Sheet1][$i][$j]."|";
            // 计算列的合计
            $sumYingfaheji += $data [$i] [($count + 0)];
            $sumGerenshiye += $data [$i] [($count + 1)];
            $sumGerenyiliao += $data [$i] [($count + 2)];
            $sumGerenyanglao += $data [$i] [($count + 3)];
            $sumGerengongjijin += $data [$i] [($count + 4)];
            $sumDaikousui += $data [$i] [($count + 5)];
            $sumKoukuanheji += $data [$i] [($count + 6)];
            $sumShifaheji += $data [$i] [($count + 7)];
            $sumDanweishiye += $data [$i] [($count + 8)];
            $sumDanweiyiliao += $data [$i] [($count + 9)];
            $sumDanweiyanglao += $data [$i] [($count + 10)];
            $sumDanweigongshang += $data [$i] [($count + 11)];
            $sumDanweishengyu += $data [$i] [($count + 12)];
            $sumDanweigongjijin += $data [$i] [($count + 13)];
            $sumDanweiheji += $data [$i] [($count + 14)];
            $sumLaowufeiheji += $data [$i] [($count + 15)];
            $sumCanbaojinheji += $data [$i] [($count + 16)];
            $sumDanganfeiheji += $data [$i] [($count + 17)];
            $sumJiaozhongqiheji += $data [$i] [($count + 18)];
            // echo "<br>";
        }
        // 计算合计行

        $countLie = count ( $data ); // 代表一共多少行
        for($j = 0; $j < $count; $j ++) {
            if ($j == 0) {
                $data [$countLie] [$j] = "合计";
            } else {
                $data [$countLie] [$j] = " ";
            }
        }
        $data [$countLie] [($count + 0)] = $sumYingfaheji;
        $data [$countLie] [($count + 1)] = $sumGerenshiye;
        $data [$countLie] [($count + 2)] = $sumGerenyiliao;
        $data [$countLie] [($count + 3)] = $sumGerenyanglao;
        $data [$countLie] [($count + 4)] = $sumGerengongjijin;
        $data [$countLie] [($count + 5)] = $sumDaikousui;
        $data [$countLie] [($count + 6)] = $sumKoukuanheji;
        $data [$countLie] [($count + 7)] = $sumShifaheji;
        $data [$countLie] [($count + 8)] = $sumDanweishiye;
        $data [$countLie] [($count + 9)] = $sumDanweiyiliao;
        $data [$countLie] [($count + 10)] = $sumDanweiyanglao;
        $data [$countLie] [($count + 11)] = $sumDanweigongshang;
        $data [$countLie] [($count + 12)] = $sumDanweishengyu;
        $data [$countLie] [($count + 13)] = $sumDanweigongjijin;
        $data [$countLie] [($count + 14)] = $sumDanweiheji;
        $data [$countLie] [($count + 15)] = $sumLaowufeiheji;
        $data [$countLie] [($count + 16)] = $sumCanbaojinheji;
        $data [$countLie] [($count + 17)] = $sumDanganfeiheji;
        $data [$countLie] [($count + 18)] = $sumJiaozhongqiheji;
        $result['result'] = 'ok';
        $result['data'] = $data;
        echo json_encode($result);
        exit;
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
    function getFileContentJson(){
        $fname = $_REQUEST ['fileName'];
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
                    if (ucwords($val) === 'NULL' || is_object($val) || $val === null) {
                        $val = "";
                    }
                    $_newExcel ['moban'] [$temp] [] = $val;
                }
                $temp++;
            }
        }
        echo json_encode($_newExcel['moban']);
        exit;
    }
    function newExcelToHtml() {
        $this->mode = "excelList";
        $fname = $_REQUEST ['fname'];
        $this->objForm->setFormData("fname", $fname);
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
