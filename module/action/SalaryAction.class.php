<?php
require_once("module/form/SalaryForm.class.php");
require_once("module/dao/CompanyDao.class.php");
require_once("module/dao/SalaryDao.class.php");
require_once("tools/excel_class.php");
require_once("tools/Classes/PHPExcel.php");
require_once("tools/Util.php");
require_once("tools/JPagination.php");

class CompanyAction extends BaseAction {
    /*
        *
        * @param $actionPath
        * @return TestAction
        */
    function CompanyAction($actionPath) {
        parent::BaseAction();
        $this->objForm = new CompanyForm();
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
            case "toCompanyList" :
                $this->toCompanyList();
                break;
            case "saveOrUpdateCompany" :
                $this->saveOrUpdateCompany();
                break;
            case "getCode" :
                $this->getCode();
                break;
            case "getCompany" :
                $this->getCompany();
                break;
            default :
                $this->modelInput();
                break;
        }


    }

    function modelInput() {
        $this->mode = "toAdd";
    }
    function toCompanyList () {
        $this->mode = "toCompanyList";
        $searchType = $_REQUEST['searchType'];
        $com_status = $_REQUEST['com_status'];
        $search_name = $_REQUEST['search_name'];
        $this->objDao = new CompanyDao();
        $where = '';
        if ($searchType =='name') {
            $where.= ' and company_name = "'.$search_name.'"';
        } elseif ($searchType =='status') {
            $where.= ' and company_status ='.$com_status;
        }
        $sum =$this->objDao->g_db_count("OA_company","*","1=1 $where");
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
        $searchResult=$this->objDao->getCompanyList($where,$startIndex,$pageSize);
        $pages = new JPagination($total);
        $pages->setPageSize($pageSize);
        $pages->setCurrent($page);
        $pages->makePages();
        $companyList = array();
        //company_code,company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type,company_status
        while ($row = mysql_fetch_array($searchResult)) {
            $company['id'] = $row['id'];
            $company['company_code'] = $row['company_code'];
            $company['company_name'] = $row['company_name'];
            $company['com_contact'] = $row['com_contact'];
            $company['contact_no'] = $row['contact_no'];
            $company['company_address'] = $row['company_address'];
            $company['com_bank'] = $row['com_bank'];
            $company['bank_no'] = $row['bank_no'];
            $company['company_level'] = $row['company_level'];
            $company['company_type'] = $row['company_type'];
            $company['company_status'] = $row['company_status'];
            $companyList[] = $company;
        }
        $this->objForm->setFormData("companyList",$companyList);
        $this->objForm->setFormData("total",$total);
        $this->objForm->setFormData("page",$pages);
        $this->objForm->setFormData("searchType",$searchType);
        $this->objForm->setFormData("search_name",$search_name);
        $this->objForm->setFormData("com_status",$com_status);

    }
    function getCode() {
        $type = $_GET['type'];
        $firstCode = '';
        if ($type =='qiye') {
            $firstCode = 'QY';
        }
        $date = date("Ymd",time());
        $this->objDao = new CompanyDao();
        $maxId = $this->objDao->getMaxId('OA_company');
        if(empty($maxId['max'])){
            $maxId = 0;
        } else {
            $maxId = $maxId['max'];
        }
        $maxId+=1;
        $maxId =  str_pad($maxId,4,'0',STR_PAD_LEFT);
        $codeNo = $firstCode.$date.$maxId;
        $data['codeNo'] = $codeNo;
        echo json_encode($data);
        exit;
    }
    function getCompany() {
        $id = $_REQUEST['id'];
        $this->objDao = new CompanyDao();
        $result = $this->objDao->getCompanyById($id);
        echo json_encode($result);
        exit;
    }
    function saveOrUpdateCompany () {
        //company_name,com_contact,contact_no,company_address,com_bank,bank_no,company_level,company_type
        $company['company_code'] = $_REQUEST['company_code'];
        $company['company_name'] = $_REQUEST['company_name'];
        $company['com_contact'] = $_REQUEST['contacts'];
        $company['contact_no'] = $_REQUEST['contacts_no'];
        $company['company_address'] = $_REQUEST['com_address'];
        $company['com_bank'] = $_REQUEST['com_bank'];
        $company['bank_no'] = $_REQUEST['bank_no'];
        $company['company_level'] = $_REQUEST['company_level'];
        $company['company_type'] = $_REQUEST['company_type'];
        $company['id'] = $_REQUEST['company_id'];
        $this->objDao = new CompanyDao();
        $data = array();
        if (empty($company['id'])) {
            $result = $this->objDao->addCompany($company);
            if ($result) {
                $data['code'] = 100000;
                $data['mess'] = '公司添加成功';
            } else {
                $data['code'] = 100001;
                $data['mess'] = '公司添加失败，请重试';
            }
        } else {
            $result = $this->objDao->updateCompany($company);
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


$objModel = new CompanyAction($actionPath);
$objModel->dispatcher();



?>
