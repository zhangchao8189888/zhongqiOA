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
            case "toFukuanList" :
                $this->toFukuanList();
                break;
            case "salaryImport" :
                $this->salaryImport ();
                break;
            case "getSalaryTimeListJson" :
                $this->getSalaryTimeListJson ();
                break;
            case "saveFukuanTongzhi" :
                $this->saveFukuanTongzhi ();
                break;
            case "saveShoukuan" :
                $this->saveShoukuan ();
                break;
            case "saveFukuandan" :
                $this->saveFukuandan ();
                break;
            case "getSalaryInfoJson" :
                $this->getSalaryInfoJson ();
                break;
            case "getSalTimeInfo" :
                $this->getSalTimeInfo ();
                break;
            case "toAddNewSal" :
                $this->toAddNewSal ();
                break;
            case "getSalHeadJson" :
                $this->getSalHeadJson ();
                break;
            case "toFukuandanList" :
                $this->toFukuandanList ();
                break;
            case "SalaryComeIn" :
                $this->SalaryComeIn ();
                break;
            default :
                $this->modelInput();
                break;
        }


    }
    function modelInput() {
        $this->mode = "toAdd";
    }
    function SalaryComeIn () {
        $bukouData = array();//补扣、垫付：企业单位未缴纳个人金额，中企垫付
        $daikouData = array();//代扣、延付：企业单位已缴纳个人金额，中企暂存延付
        $fukuanData = $_REQUEST['data'];
        $salaryTimeId = $_REQUEST['salaryTimeId'];
        $this->objDao = new SalaryDao();
        $salListResult = $this->objDao->searchSalaryListBy_SalaryTimeId($salaryTimeId);
        $fukuanList = array();
        $errorList =array();
        foreach($fukuanData as $key =>$fukuan){
            if ($key == 0) continue;
            if (empty($fukuan[2])) {
                $errorList[] = "第$key 行,身份证为空";
            }
            $fukuanList[$fukuan[2].'']['name'] = $fukuan[1];
            $fukuanList[$fukuan[2].'']['per_shiye'] = $fukuan[3];
            $fukuanList[$fukuan[2].'']['per_yiliao'] = $fukuan[4];
            $fukuanList[$fukuan[2].'']['per_yanglao'] = $fukuan[5];
            $fukuanList[$fukuan[2].'']['per_gongjijin'] = $fukuan[6];
            $fukuanList[$fukuan[2].'']['com_shiye'] = $fukuan[7];
            $fukuanList[$fukuan[2].'']['com_yiliao'] = $fukuan[8];
            $fukuanList[$fukuan[2].'']['com_yanglao'] = $fukuan[9];
            $fukuanList[$fukuan[2].'']['com_gongshang'] = $fukuan[10];
            $fukuanList[$fukuan[2].'']['com_shengyu'] = $fukuan[11];
            $fukuanList[$fukuan[2].'']['com_gongjijin'] = $fukuan[12];
        }
        if (count($errorList) > 0) {
            $jsonData['code'] = 100001;
            $jsonData['data'] = $errorList;
            echo json_encode($jsonData);
            exit;
        }
        $noEqual = array();
        while ($row = mysql_fetch_array($salListResult)) {
            $key = $row['employid'];
            $results = $this->_commonEqual($fukuanList[$key],$row);
            if (count($results['error']) >2)$noEqual[] = $results['error'];
            if (count($results['dianfu']) >2) $bukouData[] = $results['dianfu'];
            if (count($results['yanfu']) >2)$daikouData[] = $results['yanfu'];
        }
        $jsonData = array();
        $jsonData['code'] = 100000;
        $jsonData['data']['error'] = $noEqual;
        $jsonData['data']['dianfu'] = $bukouData;
        $jsonData['data']['yanfu'] = $daikouData;
        echo json_encode($jsonData);
        exit;
    }

    /**
     * @param $arr1 付款单
     * @param $arr2 工资
     */
    function _commonEqual($arr1,$arr2){
        $noEqual['yanfu'] =array();
        $noEqual['dianfu'] =array();
        $noEqual['error'] =array();
        $noEqual['yanfu']['e_num'] = $arr2['employid'];
        $noEqual['dianfu']['e_num'] = $arr2['employid'];
        $noEqual['error']['e_num'] = $arr2['employid'];
        $noEqual['yanfu']['e_name'] = $arr2['e_name'];
        $noEqual['dianfu']['e_name'] = $arr2['e_name'];
        $noEqual['error']['e_name'] = $arr2['e_name'];
        foreach($arr1 as $key => $val){
            if (floatval($arr2[$key]) != floatval($val)) {
                if ($val == 0) {
                    $noEqual['yanfu'][$key] =$arr2[$key];
                } else if ($arr2[$key] == 0) {
                    $noEqual['dianfu'][$key] =$val;
                } else {
                    $noEqual['error'][$key] = '工资：'.$arr2[$key].'/付款单：'.$val;
                }
            }
        }
        return $noEqual;
    }
    function toFukuandanList () {
        $this->mode = "toFukuandanList";
        $searchType = $_REQUEST['searchType'];
        $com_status = $_REQUEST['com_status'];
        $search_name = $_REQUEST['search_name'];
        $this->objDao = new SalaryDao();
        $where = '';
        if ($searchType =='name') {
            $where['company_name'] = $search_name;
        }
        $sum =$this->objDao->getFukuandanListCount($where);
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
        $total = $sum;
        $searchResult=$this->objDao->getFukuandanList($where,$startIndex,$pageSize);
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $shoukuanList = array();

        while ($row = mysql_fetch_array($searchResult)) {
            $shoukuanList[] = $row;
        }
        $this->objForm->setFormData("shoukuanList",$shoukuanList);
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("searchType",$searchType);
        $this->objForm->setFormData("search_name",$search_name);
        $this->objForm->setFormData("com_status",$com_status);
    }
    function getSalHeadJson () {
        $salaryTimeId = $_REQUEST['salTimeId'];

        $this->objDao = new SalaryDao ();
        $result = $this->objDao->searchSalaryListBy_SalaryTimeId($salaryTimeId,1);
        $salaryPo = mysql_fetch_array($result);
        $salaryAddJson = $salaryPo['sal_add_json'];
        $sal_del_json = $salaryPo['sal_del_json'];
        $sal_free_json = $salaryPo['sal_free_json'];
        $salHead = array();
        $salHead[] = '姓名';
        $salHead[] = '身份证号';
        $addJson = json_decode($salaryAddJson,true);
        $delJson = json_decode($sal_del_json,true);
        $freeJson = json_decode($sal_free_json,true);
        foreach($addJson as $val) {
            $key = urldecode($val['key']);
            $salHead[] = $key;
        }
        foreach($delJson as $val) {
            $key = urldecode($val['key']);
            $salHead[] = $key;
        }
        foreach($freeJson as $val) {
            $key = urldecode($val['key']);
            $salHead[] = $key;
        }
        $headData =array();
        $headData[] = $salHead;
        echo json_encode($headData);
        exit;
    }
    function toAddNewSal () {
        $this->mode = "toAddNewSal";
        $salaryTimeId = $_REQUEST['salTimeId'];
        $this->objDao = new SalaryDao ();
        $salTime = $this->objDao->getSalaryTimeBySalId($salaryTimeId);

        $this->objForm->setFormData("salTime",$salTime);
    }
    function getSalaryInfoJson () {
        $salaryTimeId = $_REQUEST['salTimeId'];
        $this->objDao = new SalaryDao ();
        $result = $this->objDao->searchSumSalaryListBy_SalaryTimeId($salaryTimeId);
        echo json_encode($result);
        exit;

    }
    function getSalTimeInfo () {
        $salaryTimeId = $_REQUEST['salTimeId'];
        $this->objDao = new SalaryDao ();
        $result = $this->objDao->getSalaryTimeBySalId($salaryTimeId);
        echo json_encode($result);
        exit;
    }
    function getSalaryTimeListJson () {
        $companyId = $_REQUEST['companyId'];
        $this->objDao = new SalaryDao ();
        $result = $this->objDao->getSalaryListByComId($companyId);
        $salTimeList = array();
        while ( $row = mysql_fetch_array ( $result ) ) {
            $salTimeList[] = $row;
        }
        echo json_encode($salTimeList);
        exit;
    }
    function salaryImport() {
        require 'tools/php-excel.class.php';
        $salaryTimeId = $_REQUEST['salaryId'];

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
                $salaryArray[] = $tableHead;

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
        $time = date('Y-m-d');
        ob_end_clean();

        $xls = new Excel_XML('UTF-8', false, 'My Test Sheet');
        $xls->addArray($salaryArray);
        $xls->generateXML($time);
    }
    function toFukuanList () {
        $this->mode = "toFukuanList";
        $searchType = $_REQUEST['searchType'];
        $com_status = $_REQUEST['com_status'];
        $search_name = $_REQUEST['search_name'];
        $this->objDao = new SalaryDao();
        $where = '';
        if ($searchType =='name') {
            $where.= ' and company_name = "'.$search_name.'"';
        } elseif ($searchType =='status') {
            $where.= ' and company_status ='.$com_status;
        }
        $sum =$this->objDao->g_db_count("OA_fukuantongzhi","*","1=1 $where");
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
        $total = $sum;
        $searchResult=$this->objDao->getFukuantongzhiList($where,$startIndex,$pageSize);
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $fukuanList = array();
        //company_code,company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type,company_status
        while ($row = mysql_fetch_array($searchResult)) {
            $fukuanList[] = $row;
        }
        $this->objForm->setFormData("fukuanList",$fukuanList);
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("searchType",$searchType);
        $this->objForm->setFormData("search_name",$search_name);
        $this->objForm->setFormData("com_status",$com_status);
    }
    function toShoukuanList () {
        $this->mode = "toShoukuanList";
        $searchType = $_REQUEST['searchType'];
        $com_status = $_REQUEST['com_status'];
        $search_name = $_REQUEST['search_name'];
        $this->objDao = new SalaryDao();
        $where = '';
        if ($searchType =='name') {
            $where.= ' and company_name = "'.$search_name.'"';
        } elseif ($searchType =='status') {
            $where.= ' and company_status ='.$com_status;
        }
        $sum =$this->objDao->g_db_count("OA_shoukuan","*","1=1 $where");
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
        $total = $sum;
        $searchResult=$this->objDao->getShoukuanList($where,$startIndex,$pageSize);
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $shoukuanList = array();
        //company_code,company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type,company_status
        global $payType;
        while ($row = mysql_fetch_array($searchResult)) {
            $row['pay_type_name'] =$payType[$row['pay_type']];
            $shoukuanList[] = $row;
        }
        $this->objForm->setFormData("shoukuanList",$shoukuanList);
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("searchType",$searchType);
        $this->objForm->setFormData("search_name",$search_name);
        $this->objForm->setFormData("com_status",$com_status);
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
        global $userType;
        for($i = 1; $i < count ( $dataExcel ); $i ++) {
            if($dataExcel [$i] [$shenfenzheng] =='null') {
                continue;
            }
            $employ = $this->objDao->getEmByEno ( $dataExcel [$i] [$shenfenzheng] );
            if ($employ) {
                $jisuan_var [$i] ['yinhangkahao'] = $employ ['bank_num'];
                $jisuan_var [$i] ['shenfenleibie'] = $userType[$employ ['e_type']];
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
                    $dataExcel [$i] [($row - 1)] = '无数值';
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
                        $dataExcel [$i] [($row - 1)] = '无数值';
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
            if($dataExcel [$i] [$shenfenzheng] =='null') {
                continue;
            }
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
    function filesUpCommon ($uploadPath,$fileName = null) {
        if ($fileName != null) {
            $fullfilepath = $uploadPath . $fileName;
        } else {
            $fullfilepath = $uploadPath . $_FILES['file']['name'];
        }

        $errorMsg = "";
        //var_dump($_FILES);
        $fileArray = split("\.", $_FILES['file']['name']);
        //print_r($fileArray);
        if (count($fileArray) != 2) {
            $errorMsg = '文件名格式 不正确';
            $this->objForm->setFormData("error", $errorMsg);
            return $errorMsg;
        } else if ($fileArray[1] != 'xls' && $fileArray[1] != "xlsx") {
            $errorMsg = '文件类型不正确，必须是xls或xlsx类型';
            $this->objForm->setFormData("error", $errorMsg);
            return $errorMsg;
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
            return $errorMsg;
        }
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $fullfilepath)) { //上传文件
            $this->objForm->setFormData("error", "文件导入失败");
            throw new Exception($fullfilepath . " is a disable dir");

        } else {
            $this->mode = "toSalaryUpload";
            $succMsg = '文件导入成功';
            $this->objForm->setFormData("succ", $succMsg);

        }
    }
    function getFileContentJson(){
        $fname = $_REQUEST ['fileName'];
        $checkType = $_REQUEST ['checkType'];
        if ($checkType == 'fukuan') {
            $path = "fukuandanfile/" . $fname;
        } else {
            $path = "upload/" . $fname;
        }

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
    function saveFukuandan () {
        $adminPO = $_SESSION ['admin'];
        $fukuandan = array();
        $fukuandan['id'] = $_REQUEST['fid'];
        $fukuandan['company_id'] = $_REQUEST['company_id'];
        $fukuandan['salTime_id'] = $_REQUEST['salTimeId'];
        $fukuandan['salSumValue'] = $_REQUEST['salSumValue'];
        $fukuandan['fukuan_status'] = 0;
        $fukuandan['memo'] = $_REQUEST['more'];
        $fukuandan['op_id'] = $adminPO['id'];

        $this->objDao = new SalaryDao();
        if (empty($fukuandan['id'])) {
            $fukuandanPO = $this->objDao->getFukuandanBySalTimeId($fukuandan['salTime_id']);
            if (!empty($fukuandanPO)) {
                $errorMsg = '该工资月份已经添加过了';
                $this->objForm->setFormData("error", $errorMsg);
            } else{
                $path = 'fukuandanfile/';
                $fileArray = explode(".",$_FILES['file']['name']);
                $fileName = 'fukuandan-'.$fukuandan['salTime_id'].".{$fileArray[1]}";
                $fukuandan['file_path'] = $fileName;
                $mess = $this->filesUpCommon($path,$fileName);

                if(empty($mess)) {
                    $result = $this->objDao->saveFukuandan($fukuandan);
                    if ($result) {
                        $this->objForm->setFormData("succ", '添加成功');
                    } else {
                        $this->objForm->setFormData("error", '添加失败请重试');
                    }
                }

            }

        } else {
            $result = $this->objDao->updateFukuandan($fukuandan);
            if ($result) {
                $this->objForm->setFormData("succ", '修改成功');
            } else {
                $this->objForm->setFormData("error", '修改失败请重试');
            }
        }
        $this->toFukuandanList();
    }
    function saveShoukuan () {

        $shoukuan['id'] = $_REQUEST['sid'];
        $shoukuan['shou_code'] = $_REQUEST['shouNo'];
        $shoukuan['company_id'] = $_REQUEST['company_id'];;
        $shoukuan['company_name'] = $_REQUEST['company_name'];
        $shoukuan['salaryTime_id'] = $_REQUEST['salTimeId'];
        $shoukuan['salary_time'] = $_REQUEST['salaryDate'];
        $shoukuan['shoukuanjin'] = $_REQUEST['shoukuanjin'];
        $shoukuan['laowufei'] = $_REQUEST['laowufei'];
        $shoukuan['pay_type'] = $_REQUEST['payType'];
        $fapiaoJson = array();
        $fapiaoJson['zhipiaojin'] = $_REQUEST['zhipiaojin'];
        $fapiaoJson['piao_no'] = $_REQUEST['piao_no'];
        $path = 'shoukuanfile/';
        $shoukuan['file_path'] = $_FILES['file']['name'];
        $mess = $this->filesUpCommon($path);

        $shoukuan['piao_json'] = json_encode($fapiaoJson);
        $shoukuan['jieshou_person_id'] = 0;
        $shoukuan['shoukuan_person_name'] = $_REQUEST['jieshouren'];
        $shoukuan['shou_status'] = 0;
        $shoukuan['more'] = $_REQUEST['more'];
        $adminPO = $_SESSION ['admin'];
        $shoukuan['op_id'] = $adminPO['id'];
        if(!empty($mess)) {
            $this->toShoukuanList();
        } else {
            $this->objDao = new SalaryDao();
            $data = array();
            if (empty($shoukuan['id'])) {
                $result = $this->objDao->saveShoukuan($shoukuan);
                if ($result) {
                    $data['code'] = 100000;
                    $data['mess'] = '添加成功';
                } else {
                    $data['code'] = 100001;
                    $data['mess'] = '添加失败，请重试';
                }
            } else {
                $result = $this->objDao->updateShoukuan($shoukuan);
                if ($result) {
                    $data['code'] = 100000;
                    $data['mess'] = '修改成功';
                } else {
                    $data['code'] = 100001;
                    $data['mess'] = '修改失败，请重试';
                }
            }
            $this->toShoukuanList();
        }


    }
    function saveFukuanTongzhi () {

        $fukuan['id'] = $_REQUEST['fid'];
        $fukuan['fu_code'] = $_REQUEST['fuNo'];
        $fukuan['company_id'] = $_REQUEST['company_id'];;
        $fukuan['company_name'] = $_REQUEST['company_name'];
        $fukuan['salary_time_id'] = $_REQUEST['salTimeId'];
        $fukuan['salary_time'] = $_REQUEST['salaryDate'];
        $fukuan['yingfu_money'] = $_REQUEST['yingfujine'];
        $fukuan['laowufei_money'] = $_REQUEST['laowufei'];
        $fapiaoJson = array();
        $fapiaoJson['fapiaojin'] = $_REQUEST['fapiaojin'];
        $fapiaoJson['piao_no'] = $_REQUEST['piao_no'];

        $fukuan['fapiao_id_json'] = json_encode($fapiaoJson);
        $fukuan['jieshou_person_id'] = 0;
        $fukuan['jieshou_person_name'] = $_REQUEST['jieshouren'];
        $fukuan['zhifu_status'] = 0;
        $fukuan['more'] = $_REQUEST['more'];
        $adminPO = $_SESSION ['admin'];
        $fukuan['op_id'] = $adminPO['id'];
        $this->objDao = new SalaryDao();
        $data = array();
        if (empty($fukuan['id'])) {
            $result = $this->objDao->saveFukuanTongzhi($fukuan);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '添加成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '添加失败，请重试';
            }
        } else {
            $result = $this->objDao->updateFukuanTongzhi($fukuan);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '修改成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '修改失败，请重试';
            }
        }
        echo json_encode($data);
        exit;

    }

}


$objModel = new SalaryAction($actionPath);
$objModel->dispatcher();



?>
