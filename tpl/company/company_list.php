<?php
$orderList=$form_data['orderList'];
$total=$form_data['total'];
$searchType=$form_data['searchType'];
$by=$form_data['by'];
$up=$form_data['up'];
$admin=$_SESSION['admin'];
?>
<script language="javascript" type="text/javascript">
    $(function(){
        $("#com_add").click(function(){
            /*$("#pro_date").val($("#shaijia_date").val());*/
            $('#modal-event1').modal({show:true});
        });
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
                                <form id="iForm" action="index.php?action=Order&mode=toOrderPage" method="post">
                                    <select id="searchType" name="searchType"   onchange="searchByType()" >
                                        <option value="name" <?php if ($searchType == 'name') echo 'selected'; ?>>企业名称</option>
                                        <option value="status" <?php if ($searchType == 'status') echo 'selected'; ?>>企业状态</option>
                                    </select>
                                    <input type="text" name="company_name" id="company_name" placeholder="请输入企业名称"/>
                                    <select id="com_status" name="com_status"   onchange="searchByStatus()" style="display: none">
                                        <option value="qiyong" <?php if ($searchType == 'qiyong') echo 'selected'; ?>>启用</option>
                                        <option value="tingyong" <?php if ($searchType == 'tingyong') echo 'selected'; ?>>停用</option>
                                    </select>
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

                                <?php
                                foreach ($orderList as $row){
                                    ?>
                                    <tr class="">
                                        <td>    </td>
                                        <td class="tl pl10">
                                            <div><a href="index.php?action=Order&mode=getOrderById&orderId=<?php echo $row['order_no'];?>" class="serial"><?php echo $row['order_no'];?>
                                                    <?php if ($row['isOff']) echo '<span class="label label-success">特价</span>';?></a>
                                                <input type="hidden" value="<?php echo $row['order_no'];?>" class="order-num" autocomplete="off">
                                            </div>
                                            <span class="lite-gray"><?php echo $row['ding_date'];?></span>
                                        </td>
                                        <td class="tl pl10"><a href="/customer/customer?action=load&amp;id=25762928" target="_blank" class="company"><?php echo $row['custer_name'];?></a></td>
                                        <!--<td class="tr pr10">￥<?php /*echo $row['chengjiaoer'];*/?></td>-->
                                        <td class="tr pr10">￥<?php echo $row['realChengjiaoer'];?></td>
                                        <td class="tc order-logistics-status">
                                            <span><?php echo $row['pay_type'];?></span><br>
                                        </td>
                                        <td class="tc">
                                            <div class="orange"><?php echo $row['pay_status'];?></div>
                                        </td>
                                        <td class="tr">
                                            <a title="订单查详情" class="theme-color" href="index.php?action=Order&mode=getOrderById&orderId=<?php echo $row['order_no'];?>">订单详情</a>



                                            <div class="cb"></div>

                                            <!--<a title="添加收款记录" class="theme-color order-pay" href="javascript:void(0)">添加收款记录</a>-->

                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <?php require_once("tpl/page.php"); ?>
                            <div class="total_page">共 <span class="redtitle"><?php echo $total ;?></span> 条记录</div>
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
            $("#company_name").show();
            $("#com_status").hide();
        } else if (type == 'status') {
            $("#company_name").hide();
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
    <form action="index.php?action=Customer&mode=addCustomer" id="company_validate" method="post" class="form-horizontal"  novalidate="novalidate">
    <div class="modal-body">
        <div class="designer_win">
             <div class="tips">客户编号：QY2014120400001</div>
            <div class="tips"><em style="color: red;padding-right: 10px;">*</em>企业名称：<input type="text" maxlength="20" id="company_name"name="company_name"  /></div>
            <div class="tips"><em style="color: red;padding-right: 10px;">*</em>联系人：<input type="text" maxlength="20" id="contacts"name="contacts"  /></div>
            <div class="tips"><em style="color: red;padding-right: 10px;">*</em>联系方式：<input type="text" maxlength="20" id="contacts_no"name="contacts_no"  /></div>
            <div class="tips">公司地址：<input type="text" maxlength="20" id="com_address"  /></div>
            <div class="tips">开户行：<input type="text" maxlength="20" id="com_bank"  /></div>
            <div class="tips">银行帐号：<input type="text" maxlength="20" id="bank_no"  /></div>
            <div class="tips">企业类型：<select name="company_type" id="company_type"/>
                    <?php foreach($levelList as $val){
                        $select = '';
                        if ($val['id'] == $customerPo['custo_level']) {
                            $select = 'selected';
                        }
                        echo "<option value='{$val["id"]}' {$select}>{$val["level_name"]}</option>";
                    }?>
                </select></div>
            <div class="tips">客户级别：<select name="company_level" id="company_level">
                    <?php foreach($levelList as $val){
                        $select = '';
                        if ($val['id'] == $customerPo['custo_level']) {
                            $select = 'selected';
                        }
                        echo "<option value='{$val["id"]}' {$select}>{$val["level_name"]}</option>";
                    }?>
                </select></div>
        </div>
    </div>

    <div class="modal-footer modal_operate">
        <button type="submit" class="btn btn-primary">添加</button>
        <a href="#" class="btn" data-dismiss="modal">取消</a>
    </div>
    </form>
</div>

