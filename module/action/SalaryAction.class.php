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
            case "salarySearchList" :
                $this->salarySearchList();
                break;
            case "getSalaryListByTimeIdJson" :
                $this->getSalaryListByTimeIdJson();
                break;
            case "toShoukuanList" :
                $this->toShoukuanList();
                break;
            default :
                $this->modelInput();
                break;
        }


    }

    function modelInput() {
        $this->mode = "toAdd";
    }
    function toShoukuanList () {

    }
    function getSalaryListByTimeIdJson() {
        $salaryTimeId = $_REQUEST['salTimeId'];

        $this->objDao = new SalaryDao ();
        //$salaryPO = $this->objDao->searchSalaryTimeBy_id ( $salaryTimeId );
        $salaryList = $this->objDao->searchSalaryListBy_SalaryTimeId ( $salaryTimeId );
        $tableHead = array();
        $tableHead[0] = '部门';
        $tableHead[1] = '姓名';
        $tableHead[2] = '身份证号';
        $salaryArray = array();
        $guding_num = 0;
        $i = 0;
        global $salaryTable;
        while($row = mysql_fetch_array($salaryList)) {
            $salary = array();
            $employ = $this->objDao->getEmByEno ($row ['employid']);
            $salary[] = $employ['e_company'];
            $salary[] = $employ['e_name'];
            $salary[] = $row ['employid'];

            if (!empty($row['sal_add_json'])){

                $addJson = json_decode($row['sal_add_json'],true);
                foreach($addJson as $val) {
                    $key = urldecode($val['key']);
                    if ($i == 0) $tableHead[] =$key;
                    $salary[] = $val['value'];
                }
            }
            if (!empty($row['sal_free_json'])){
                $addJson = json_decode($row['sal_free_json'],true);
                $key = urldecode($addJson['key']);
                if ($i == 0) $tableHead[] =$key;
                $salary[] = $val['value'];
            }
            if (!empty($row['sal_del_json'])){
                $addJson = json_decode($row['sal_del_json'],true);
                foreach($addJson as $val) {
                    $key = urldecode($val['key']);
                    if ($i == 0) $tableHead[] =$key;
                    $salary[] = $val['value'];
                }
            }
            if ($i == 0) {

                $guding_num = count($tableHead);
                foreach($salaryTable as $val){
                    $tableHead[] = $val;
                }


            }

            foreach($salaryTable as $key =>$val){
                $salary[] = $row[$key];
            }
            $salaryArray[] = $salary;
            $i ++;
        }

        $data['guding_num'] = $guding_num;
        $salarySum = array();
        for($j = 0; $j < $guding_num; $j++) {
            if ($j == 0) {
                $salarySum[$j] = '合计';
            }
            else {$salarySum[$j] = '';}
        }
        $result = $this->objDao->searchSumSalaryListBy_SalaryTimeId($salaryTimeId);
        foreach($salaryTable as $key =>$val){
            $salarySum[] = $result['sum_'.$key];
        }
        $salaryArray[] = $salarySum;
        $data['head'] = $tableHead;
        $data['salary'] = $salaryArray;
        $data['code'] = 100000;
        echo json_encode($data);
        exit;
    }
    function salarySearchList () {
        $this->mode = "salarySearchList";
        $this->objDao=new SalaryDao();
        $where=array();
        $where['companyId'] = $_REQUEST['companyId'];
        $salTime = $_REQUEST['salaryTime'];
        $opTime = $_REQUEST['op_salaryTime'];
        if($opTime) {
            $time=$this->AssignTabMonth($opTime,0);
            $where['op_salaryTime']=$time["next"];
            $where['op_time']   =   $time["data"];
        }
        $where['salaryTime']=$salTime;
        if($salTime) {
            $time=$this->AssignTabMonth($salTime,0);
            $where['salaryTime']=$time["month"];
        }
        $pageSize=PAGE_SIZE;
        $count = intval($_GET['c']);
        $page = intval($_GET['page']);
        if ($count == 0){
            $count = $pageSize;
        }
        if ($page == 0){
            $page = 1;
        }


        $startIndex = ($page-1)*$count;

        if (empty($sorts)){
            $sorts = "op_salaryTime" ;
        }
        if (empty($dir)) {
            $dir = "desc";
        }
        $sum =$this->objDao->searhSalaryTimeListCount($where);
        $result=$this->objDao->searhSalaryTimeListPage($startIndex,$pageSize,$sorts." ".$dir,$where);
        $total = $sum;
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $salaryTimeList = array();
        //company_code,company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type,company_status
        while ($row = mysql_fetch_array($result)) {
            $salary = array();
            $salary['id'] = $row['id'];
            $salary['salaryTime'] = $row['salaryTime'];
            $salary['companyId'] = $row['companyId'];
            $salary['company_name'] = $row['company_name'];
            $salary['op_salaryTime'] = $row['op_salaryTime'];
            $salaryTimeList[] = $salary;
        }
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("companyId",$where['companyId']);
        $this->objForm->setFormData("salaryTime",$salTime);
        $this->objForm->setFormData("op_salaryTime",$opTime);
        $this->objForm->setFormData ( "salaryTimeList", $salaryTimeList );
    }
    function sumSalary() {
        $dataExcel = $_REQUEST['data'];
        $shenfenzheng = ($_POST ['shenfenzheng'] - 1);
        $addArray = $_POST ['add'];
        $delArray = $_POST ['del'];
        if ($_POST ['freeTex']) {
            $freeTex = $_POST ['freeTex'] - 1;
        }
        $shifajian = $_POST ['shifajian'] - 1;
        $addArray = explode ( "+", $addArray );
        if (! empty ( $delArray )) {
            $delArray = explode ( "+", $delArray );
        } else {
            $delArray = "";
        }
        session_start ();
        $salaryList = $_SESSION ['salarylist'];
        $count_add = count ( $dataExcel [0] );
        $head = array();
        $head = $dataExcel [0];
        // 增加字段1·
        // 个人失业 个人医疗 个人养老 个人合计 单位失业 单位医疗 单位养老 单位工伤 单位生育 单位合计
        // 2011-10-14增加字段 姓名 身份证号 银行卡号 身份类别 社保基数 公积金基数
        $head [($count_add + 0)] = " 银行卡号";
        $head [($count_add + 1)] = "身份类别";$shenfenleibie = ($count_add + 1);
        $head [($count_add + 2)] = " 社保基数";
        $head [($count_add + 3)] = "公积金基数";
        // 再次算出字段总列数
        $count = count ( $head );
        $head [($count + 0)] = "个人应发合计";
        $head [($count + 1)] = "个人失业";
        $head [($count + 2)] = "个人医疗";
        $head [($count + 3)] = "个人养老";
        $head [($count + 4)] = "个人公积金";
        $head [($count + 5)] = "代扣税";
        $head [($count + 6)] = "个人扣款合计";
        $head [($count + 7)] = "实发合计";
        $head [($count + 8)] = "单位失业";
        $head [($count + 9)] = "单位医疗";
        $head [($count + 10)] = "单位养老";
        $head [($count + 11)] = "单位工伤";
        $head [($count + 12)] = "单位生育";
        $head [($count + 13)] = "单位公积金";
        $head [($count + 14)] = "单位合计";
        $head [($count + 15)] = "劳务费";
        $head [($count + 16)] = "残保金";
        $head [($count + 17)] = "档案费";
        $head [($count + 18)] = "交中企基业合计";
        if (! empty ( $freeTex )) {
            $head [($count + 19)] = "免税项";
        }
        if (! empty ( $_POST ['shifajian'] )) {
            $head [($count + 20)] = "实发合计减后项";
            $head [($count + 21)] = "交中企基业减后项";
        }
        $jisuan_var = array ();
        $error = array ();
        $this->objDao = new EmployDao ();
        // 根据身份证号查询出员工身份类别
        $errorRow = 0;
        for($i = 1; $i < count ( $dataExcel ); $i ++) {
            $employ = $this->objDao->getEmByEno ( $dataExcel [$i] [$shenfenzheng] );
            if ($employ) {
                $jisuan_var [$i] ['yinhangkahao'] = $employ ['bank_num'];
                $jisuan_var [$i] ['shenfenleibie'] = $employ ['e_type_name'];
                $jisuan_var [$i] ['shebaojishu'] = $employ ['shebaojishu'];
                $jisuan_var [$i] ['gongjijinjishu'] = $employ ['gongjijinjishu'];
                $jisuan_var [$i] ['laowufei'] = $employ ['laowufei'];
                $jisuan_var [$i] ['canbaojin'] = $employ ['canbaojin'];
                $jisuan_var [$i] ['danganfei'] = $employ ['danganfei'];
            } else {
                $error [$errorRow] ["error"] = "第$i 行:未查询到该员工身份类别！";
                $errorRow++;
                continue;
            }
            $addValue = 0;
            $delValue = 0;
            $f= 0;
            foreach ( $addArray as $row ) {
                if (is_numeric ( $dataExcel [$i] [($row - 1)] )) {

                    $move [$i]['add'][$f] ['key'] = urlencode($head [($row - 1)]);
                    $move [$i]['add'][$f] ['value'] =  $dataExcel [$i] [($row - 1)];
                    $f++;
                    $addValue += $dataExcel [$i] [($row - 1)];
                } else {
                    $error [$errorRow] ["error"] = "第$i 行 第$row 列所加项非数字类型";
                    $errorRow++;
                    continue;
                }
            }

            $f= 0;
            if (! empty ( $delArray )) {
                foreach ( $delArray as $row ) {
                    if (is_numeric ( $dataExcel [$i] [($row - 1)] )) {
                        $move [$i]['del'][$f] ['key'] = urlencode($head [($row - 1)]);
                        $move [$i]['del'][$f] ['value'] =  $dataExcel [$i] [($row - 1)];
                        $delValue += $dataExcel [$i] [($row - 1)];
                        $f++;
                    } else {
                        $error [$errorRow] ["error"] = "第$i 行 第$row 列所加项非数字类型";
                        $errorRow++;
                        continue;
                    }
                }
            }
            $jisuan_var [$i] ["addValue"] = $addValue;
            $jisuan_var [$i] ["delValue"] = $delValue;
            if (! empty ( $freeTex )) {
                $jisuan_var [$i] ['freeTex'] = $dataExcel [$i] [$freeTex];
                $move [$i]['freeTex'] ['key'] = urlencode($head [($freeTex)]);
                $move [$i]['freeTex'] ['value'] =  $dataExcel [$i] [($freeTex)];
            } else {
                $jisuan_var [$i] ['freeTex'] = 0;
            }
        }
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
        $sumLaowufeiheji = 0;
        $sumCanbaojinheji = 0;
        $sumDanganfeiheji = 0;
        $sumJiaozhongqiheji = 0;
        $data = array();
        for($i = 1; $i < count ( $dataExcel ); $i ++) {
            $canjiren = $this->objDao->getCanjiren ( $dataExcel [$i] [$shenfenzheng] );
            $salary = array();
            $salary = $dataExcel[$i];
            $salary [($count_add + 0)] = $jisuan_var [$i] ['yinhangkahao'];
            $salary [($count_add + 1)] = $jisuan_var [$i] ['shenfenleibie'];
            $salary [($count_add + 2)] = $jisuan_var [$i] ['shebaojishu'];
            $salary [($count_add + 3)] = $jisuan_var [$i] ['gongjijinjishu'];
            $salary [($count + 0)] = sprintf ( "%01.2f", $jisuan_var [$i] ['yingfaheji'] ) + 0;
            $salary [($count + 1)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenshiye'] ) + 0;
            $salary [($count + 2)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenyiliao'] ) + 0;
            $salary [($count + 3)] = sprintf ( "%01.2f", $jisuan_var [$i] ['gerenyanglao'] ) + 0;
            $salary [($count + 4)] = $jisuan_var [$i] ['gerengongjijin'] + 0;
            $salary [($count + 5)] = sprintf ( "%01.2f", $jisuan_var [$i] ['daikousui'] ) + 0;
            $salary [($count + 6)] = sprintf ( "%01.2f", $jisuan_var [$i] ['koukuanheji'] ) + 0;
            $salary [($count + 7)] = sprintf ( "%01.2f", $jisuan_var [$i] ['shifaheji'] ) + 0;
            if($canjiren[0]==1){
                $salary [($count + 5)] /= 2;
                $salary [($count + 6)] -=  $salary [($count + 5)];
                $salary [($count + 7)] += $salary [($count + 5)];
            }
            $salary [($count + 8)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweishiye'] ) + 0;
            $salary [($count + 9)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiyiliao'] ) + 0;
            $salary [($count + 10)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiyanglao'] ) + 0;
            $salary [($count + 11)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweigongshang'] ) + 0;
            $salary [($count + 12)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweishengyu'] ) + 0;
            $salary [($count + 13)] = $jisuan_var [$i] ['danweigongjijin'] + 0;
            $salary [($count + 14)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danweiheji'] ) + 0;
            $salary [($count + 15)] = sprintf ( "%01.2f", $jisuan_var [$i] ['laowufei'] ) + 0;
            $salary [($count + 16)] = sprintf ( "%01.2f", $jisuan_var [$i] ['canbaojin'] ) + 0;
            $salary [($count + 17)] = sprintf ( "%01.2f", $jisuan_var [$i] ['danganfei'] ) + 0;
            $salary [($count + 18)] = sprintf ( "%01.2f", $jisuan_var [$i] ['jiaozhongqiheji'] ) + 0;
            if (! empty ( $freeTex )) {
                $salary [($count + 19)] = sprintf ( "%01.2f", $jisuan_var [$i] ['freeTex'] ) + 0;
            }
            if (! empty ( $_POST ['shifajian'] )) {
                $salary [($count + 20)] = sprintf ( "%01.2f", ($jisuan_var [$i] ['shifaheji'] - $salary [$shifajian]) ) + 0;
                $salary [($count + 21)] = sprintf ( "%01.2f", ($jisuan_var [$i] ['jiaozhongqiheji'] - $salary [$shifajian]) ) + 0;
            }
            // 计算列的合计
            $sumYingfaheji += $salary [($count + 0)];
            $sumGerenshiye += $salary [($count + 1)];
            $sumGerenyiliao += $salary [($count + 2)];
            $sumGerenyanglao += $salary [($count + 3)];
            $sumGerengongjijin += $salary [($count + 4)];
            $sumDaikousui += $salary [($count + 5)];
            $sumKoukuanheji += $salary [($count + 6)];
            $sumShifaheji += $salary [($count + 7)];
            $sumDanweishiye += $salary [($count + 8)];
            $sumDanweiyiliao += $salary [($count + 9)];
            $sumDanweiyanglao += $salary [($count + 10)];
            $sumDanweigongshang += $salary [($count + 11)];
            $sumDanweishengyu += $salary [($count + 12)];
            $sumDanweigongjijin += $salary [($count + 13)];
            $sumDanweiheji += $salary [($count + 14)];
            $sumLaowufeiheji += $salary [($count + 15)];
            $sumCanbaojinheji += $salary [($count + 16)];
            $sumDanganfeiheji += $salary [($count + 17)];
            $sumJiaozhongqiheji += $salary [($count + 18)];
            // echo "<br>";
            $data [] = $salary;
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
        $result['shenfenleibie'] = $shenfenleibie;
        $result['move'] = $move;
        $result['data'] = $data;
        $result['head'] = $head;
        $result['error'] = $error;
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
