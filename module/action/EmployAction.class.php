<?php
require_once("module/form/EmployForm.class.php");
require_once("module/dao/EmployDao.class.php");
require_once("module/dao/SalaryDao.class.php");
require_once("tools/excel_class.php");
require_once("tools/Classes/PHPExcel.php");
require_once("tools/Util.php");
require_once("tools/JPagination.php");

class EmployAction extends BaseAction {
    /*
        *
        * @param $actionPath
        * @return TestAction
        */
    function EmployAction($actionPath) {
        parent::BaseAction();
        $this->objForm = new EmployForm();
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
            case "toEmployList" :
                $this->toEmployList();
                break;
            case "saveOrUpdateEmploy" :
                $this->saveOrUpdateEmploy();
                break;
            default :
                $this->modelInput();
                break;
        }


    }

    function modelInput() {
        $this->mode = "toAdd";
    }
    function toEmployList () {
        $this->mode = "toEmployList";
        $searchType = $_REQUEST['searchType'];
        $search_name = $_REQUEST['search_name'];
        $this->objDao = new EmployDao();
        $where = '';
        if ($searchType =='name') {
            $where.= ' and e_name = "'.$search_name.'"';
        } elseif ($searchType =='e_num') {
            $where.= ' and e_num ='.$search_name;
        }
        $sum =$this->objDao->g_db_count("OA_employ","*","1=1 $where");
        $pageSize=PAGE_SIZE_EMPLOY;
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
        $searchResult=$this->objDao->getEmployList($where,$startIndex,$pageSize);
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $employList = array();
        //company_code,company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type,company_status
        while ($row = mysql_fetch_array($searchResult)) {
            $employ['id'] = $row['id'];
            $employ['e_company_id'] = $row['e_company_id'];
            $employ['e_name'] = $row['e_name'];
            $employ['e_company'] = $row['e_company'];
            $employ['e_num'] = $row['e_num'];
            $employ['e_type_name'] = $row['e_type_name'];
            $employ['shebaojishu'] = $row['shebaojishu'];
            $employ['gongjijinjishu'] = $row['gongjijinjishu'];
            $employList[] = $employ;
        }
        $this->objForm->setFormData("employList",$employList);
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("searchType",$searchType);
        $this->objForm->setFormData("search_name",$search_name);
    }
    function saveOrUpdateEmploy () {
        //company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type
        $employ = array();
        $employ['e_name'] = $_POST['e_name'];
        $employ['e_num'] = $_POST['e_num'];
        $employ['bank_name'] = $_POST['e_bank'];
        $employ['bank_num'] = $_POST['bank_no'];
        $employ['e_type'] = $_POST['e_type'];
        $employ['e_company'] = $_POST['e_company'];
        $employ['shebaojishu'] = $_POST['shebaojishu'];
        $employ['gongjijinjishu'] = $_POST['gongjijinjishu'];
        $employ['laowufei'] = $_POST['laowufei'];
        $employ['canbaojin'] = $_POST['canbaofei'];
        $employ['danganfei'] = $_POST['danganfei'];
        $employ['memo'] = $_POST['memo'];
        $this->objDao = new EmployDao();
        $data = array();
        if (empty($company['id'])) {
            $emper = $this->objDao->getEmByEno($employ['e_num']);
            if (!empty($emper)) {
                $mess = "此员工身份证号已存在，请重新确认";
                $data['code'] = 100001;
                $data['mess'] = $mess;
                echo json_encode($data);
                exit;
            }
            $result = $this->objDao->addEm($employ);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '员工添加成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '员工添加失败，请重试';
            }
        } else {
            $result = $this->objDao->updateEm($employ);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '员工修改成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '员工修改失败，请重试';
            }
        }
        echo json_encode($data);
        exit;

    }

}


$objModel = new EmployAction($actionPath);
$objModel->dispatcher();



?>
