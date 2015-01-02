<?php
require_once("module/form/CompanyForm.class.php");
require_once("module/dao/SalaryDao.class.php");
require_once("tools/excel_class.php");
require_once("tools/Classes/PHPExcel.php");
require_once("tools/Util.php");

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

    }

}


$objModel = new CompanyAction($actionPath);
$objModel->dispatcher();



?>
