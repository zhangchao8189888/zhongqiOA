<?php
$companyList=$form_data['companyList'];
$total=$form_data['total'];
$searchType=$form_data['searchType'];
$search_name = $form_data['search_name'];
$com_status = $form_data['com_status'];
$admin=$_SESSION['admin'];
?>
<script src="common/hot-js/handsontable.full.js"></script>
<link rel="stylesheet" media="screen" href="common/hot-js/handsontable.full.css">
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        function createBigData() {
            var rows = []
                , i
                , j;

            for (i = 0; i < 1000; i++) {
                var row = [];
                for (j = 0; j < 22; j++) {
                    row.push(Handsontable.helper.spreadsheetColumnLabel(j) + (i + 1));
                }
                rows.push(row);
            }

            return rows;
        }
        var container = document.getElementById("exampleGrid");
        var hot5 = Handsontable(container, {
            data: createBigData(),
            startRows: 5,
            startCols: 4,
            colWidths: [55, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80], //can also be a number or a function
            rowHeaders: true,
            colHeaders: ['姓名', '社保基数', '公积金基数',
                '基本工资','考核工资','其他','应发合计','个人失业',
                '个人医疗','个人养老','个人公积金','个人代扣税',
                '个人扣款合计','个人实发合计','单位失业','单位医疗',
                '单位养老','单位工伤','单位生育','单位公积金','劳务费','合计付款'],
            stretchH: 'last',
            minSpareRows: 1,
            contextMenu: true
        });
        var selectFirst = document.getElementById('selectFirst'),
            rowHeaders = document.getElementById('rowHeaders'),
            colHeaders = document.getElementById('colHeaders');
        /*Handsontable.Dom.addEvent(rowHeaders, 'click', function () {
            hot5.updateSettings({
                rowHeaders: this.checked
            });
        });
*/
        Handsontable.Dom.addEvent(colHeaders, 'click', function () {
            if (this.checked) {
                hot5.updateSettings({
                    fixedColumnsLeft: 2
                });
            } else {
                hot5.updateSettings({
                    fixedColumnsLeft: 0
                });
            }

        });
        /*hot.loadData(data);

        var selectFirst = document.getElementById('selectFirst'),
            rowHeaders = document.getElementById('rowHeaders'),
            colHeaders = document.getElementById('colHeaders');

        Handsontable.Dom.addEvent(selectFirst, 'click', function () {
            hot.selectCell(0,0);
        });

        Handsontable.Dom.addEvent(rowHeaders, 'click', function () {
            hot.updateSettings({
                rowHeaders: this.checked
            });
        });

        Handsontable.Dom.addEvent(colHeaders, 'click', function () {
            hot.updateSettings({
                colHeaders: this.checked
            });
        });*/
    });
</script>
<div id="content">
<div id="content-header">
    <div id="breadcrumb">
        <a href="index.php" title="返回首页" class="tip-bottom"><i class="icon-home"></i>首页</a>
        <a href="#">企业管理</a>
        <a href="#">企业信息</a>
    </div>
</div>
<div class="container-fluid">
<div class="row-fluid">
<div class="span12"><div class="widget-box">
        <!--<div class="widget-title">
            <ul class="nav nav-pills">
                <li class="active"><a href="index.php?action=Order&mode=toOrderPage">订货单</a></li>
                <li class=""><a href="index.php?action=Order&mode=toOrderReturnList">退货单</a></li>
                <li class=""><a href="index.php?action=Order&mode=toOrderStatistics">订单商品统计</a></li>
            </ul>

        </div>-->

        <div class="widget-content tab-content ">
            <div class="tab-pane active" id="tab1">

                <div class="controls">
                    <form id="iForm" action="index.php?action=Company&mode=toCompanyList" method="post">
                        <select id="searchType" name="searchType"   onchange="searchByType()" >
                            <option value="name" <?php if ($searchType == 'name') echo 'selected'; ?>>企业名称</option>
                            <option value="status" <?php if ($searchType == 'status') echo 'selected'; ?>>企业状态</option>
                        </select>
                        <input type="text" value="<?php echo $search_name;?>" name="search_name" id="search_name" placeholder="请输入企业名称"/>
                        <select id="com_status" name="com_status"   onchange="searchByStatus()" style="display: none">
                            <option value="1" <?php if ($com_status == '1') echo 'selected'; ?>>启用</option>
                            <option value="0" <?php if ($com_status == '0') echo 'selected'; ?>>停用</option>
                        </select>
                        <input type="submit" value="查询"/>
                        <input type="hidden" value="" id="pro_id"/>
                        <input type="hidden" value="" id="pro_code"/>
                        <div style="float: right;margin-right: 20px"><a href="#" id="com_add" class="btn btn-success" >新增订单</a></div>
                    </form>
                </div>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <!-- 编号 企业名称 联系人 联系方式 地址 银行账户 客户等级 -->
                        <th class="tl"><div></div></th>
                        <th class="tl"><div>编号</div></th>
                        <th class="tl"><div>企业名称</div></th>
                        <!--<th class="tl"><div>金额</div></th>-->
                        <th class="tl"><div>联系人</div></th>
                        <th class="tl"><div>联系方式</div></th>
                        <th class="tl"><div>地址</div></th>
                        <th class="tl"><div>银行账户</div></th>
                        <th class="tl"><div>客户等级</div></th>
                        <th class="tl"><div>操作</div></th>
                    </tr>
                    </thead>
                    <tbody  class="tbodays">

                    </tbody>
                </table>
                <?php require_once("tpl/page.php"); ?>
                <div class="total_page">共 <span class="redtitle"><?php echo $total ;?></span> 条记录</div>
            </div>
        </div>
    </div>
</div>
<div class="span12" style="margin-left:0;">
    <div class="widget-box">
        <ul class="nav nav-tabs" id="myTab">
            <li class="active"><a href="#home">劳动派遣合同</a></li>
            <li><a href="#profile">企业账户</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="home">
                <div class="controls">
                    <!-- checked="checked"-->
                    <input type="checkbox" id="colHeaders" autocomplete="off"> <span>锁定前两列</span>
                </div>
                <div id="exampleGrid" class="dataTable" style="width: 1400px; height: 400px; overflow: auto"></div>
            </div>
            <div class="tab-pane" id="profile">

                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <!-- 资金来往方 资金类型 收入 支出 支付方式 填报人 填报时间 备注
                        清华大学 工资汇入 520210.15 支票支付 王子诚 2014-12-04 14:20:30 2015年1月份工资汇入
                        -->
                        <th class="tl"><div>资金来往方</div></th>
                        <th class="tl"><div>资金类型</div></th>
                        <th class="tl"><div>收入</div></th>
                        <th class="tl"><div>支出</div></th>
                        <th class="tl"><div>支付方式</div></th>
                        <th class="tl"><div>填报人</div></th>
                        <th class="tl"><div>填报时间</div></th>
                        <th class="tl"><div>备注</div></th>
                    </tr>
                    </thead>
                    <tbody  class="tbodays">
                    <tr class="">
                        <td>清华大学</td>
                        <td>工资汇入</td>
                        <td>￥<em style="color: red">52000.15</em></td>
                        <td>￥<em style="color: #008000">0.00</em></td>
                        <td>支票支付</td>
                        <td>王子诚</td>
                        <td>2014-12-04 14:20:30</td>
                        <td class="tl pl10">
                            2015年1月份工资汇入
                        </td>
                    </tr>
                    <tr class="">
                        <td>海淀公安局</td>
                        <td>垫付冲回</td>
                        <td>￥<em style="color: red">0.00</em></td>
                        <td>￥<em style="color: #008000">-600</em></td>
                        <td>银行转账</td>
                        <td>王子诚</td>
                        <td>2014-12-04 14:20:30</td>
                        <td class="tl pl10">
                            1月张超公积金垫付冲回
                        </td>
                    </tr>
                    <tr class="">
                        <td>合计</td>
                        <td></td>
                        <td>￥<em style="color: red">52000.15</em></td>
                        <td>￥<em style="color: #008000">-600</em></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="tl pl10">

                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
<script language="javascript" type="text/javascript">
    $(function(){
        $("#pro_add").click(function(){
            $("#pro_date").val($("#shaijia_date").val());
            $('#modal-event1').modal({show:true});
        });
    });
    function searchByType () {
        var type = $("#searchType").val();
        if (type == 'name') {
            $("#search_name").show();
            $("#com_status").hide();
        } else if (type == 'status') {
            $("#search_name").hide();
            $("#com_status").show();
        }
    }
    function searchByStatus() {

    }
</script>
<script language="javascript" type="text/javascript" src="common/common-js/company.js" charset="utf-8"></script>
<div class="modal hide" id="modal-event1">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>企业信息新增</h3>
    </div>
    <form action="" id="company_validate" method="post" class="form-horizontal"  novalidate="novalidate">
        <div class="modal-body">
            <div class="designer_win">
                <div class="tips">客户编号：<span class="codeNo"></span><input type="hidden" value="" id="company_id" name="company_id"/></div>
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>企业名称：<input type="text" maxlength="20" id="company_name"name="company_name"  /></div>
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>联系人：<input type="text" maxlength="20" id="contacts"name="contacts"  /></div>
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>联系方式：<input type="text" maxlength="20" id="contacts_no"name="contacts_no"  /></div>
                <div class="tips">公司地址：<input type="text" maxlength="20" id="com_address"  /></div>
                <div class="tips">开户行：<input type="text" maxlength="20" id="com_bank"  /></div>
                <div class="tips">银行帐号：<input type="text" maxlength="20" id="bank_no"  /></div>
                <div class="tips">企业类型：<select name="company_type" id="company_type"/>
                    <option value="1">国有企业</option>
                    <option value="2">事业单位</option>
                    <option value="3">名营企业</option>
                    <option value="4">外资企业</option>
                    <option value="5">政府部门</option>
                    </select></div>
                <div class="tips">客户级别：<select name="company_level" id="company_level">
                        <option value="1">普通客户</option>
                        <option value="2">中级客户</option>
                        <option value="3">高级客户</option>
                    </select></div>
                <div class="tips">状态：启用</div>
            </div>
        </div>

        <div class="modal-footer modal_operate">
            <button type="submit" class="btn btn-primary">添加</button>
            <a href="#" class="btn" data-dismiss="modal">取消</a>
        </div>
    </form>
</div>

