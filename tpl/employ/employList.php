<?php
$employList=$form_data['employList'];
$total=$form_data['total'];
$searchType=$form_data['searchType'];
$search_name = $form_data['search_name'];
$admin=$_SESSION['admin'];
?>
<script language="javascript" type="text/javascript">
    $(function(){
        //$('#myTab a:last').tab('show');//初始化显示哪个tab

        $('#myTab a').click(function (e) {
            e.preventDefault();//阻止a链接的跳转行为
            $(this).tab('show');//显示当前选中的链接及关联的content
        })
        $("#com_add").click(function(){
            $("#company_validate")[0].reset();
            $('#modal-event1').modal({show:true});
        });
        $(".rowUpdate").click(function(){
            /*$("#pro_date").val($("#shaijia_date").val());*/
            $("#company_validate")[0].reset();
            var id = $(this).attr("data-id");
            $.ajax(
                {
                    type: "post",
                    url: "index.php?action=Employ&mode=getEmployInfo",
                    data: {employId:id},
                    dataType: "json",
                    success: function(data){
                        $("#employ_id").val(data.id);
                        $("#e_num").val(data.e_num);
                        $("#e_company").val(data.company_name);
                        $("#company_id").val(data.e_company_id);
                        $("#e_name").val(data.e_name);
                        $("#bank_no").val(data.bank_no);
                        $("#e_bank").val(data.e_bank);
                        $("#e_type").val(data.e_type);
                        $("#shebaojishu").val(data.shebaojishu);
                        $("#gongjijinjishu").val(data.gongjijinjishu);
                        $("#canbaojin").val(data.canbaojin);
                        $("#laowufei").val(data.laowufei);
                        $("#danganfei").val(data.danganfei);
                        $("#e_hetongnian").val(data.e_hetongnian);
                        $("#e_hetong_date").val(data.e_hetong_date);
                        $("#memo").val(data.memo);
                    }
                }
            );
            $('#modal-event1').modal({show:true});
        });
    });
</script>
<div id="content">
<div id="content-header">
    <div id="breadcrumb">
        <a href="index.php" title="返回首页" class="tip-bottom"><i class="icon-home"></i>首页</a>
        <a href="#">员工管理</a>
        <a href="#">员工信息</a>
    </div>
</div>
<div class="container-fluid">
<div class="row-fluid">
<div class="span12"><div class="widget-box">
        <div class="widget-content tab-content ">
            <div class="tab-pane active" id="tab1">

                <div class="controls">
                    <form id="iForm" action="index.php?action=Employ&mode=toEmployList" method="post">
                        <select id="searchType" name="searchType"   onchange="searchByType()" >
                            <option value="e_company" <?php if ($searchType == 'e_company') echo 'selected'; ?>>企业名称</option>
                            <option value="e_num" <?php if ($searchType == 'e_num') echo 'selected'; ?>>身份证号</option>
                        </select>
                        <input type="text" value="<?php echo $search_name;?>" name="search_name" id="search_name" placeholder="请输入企业名称"/>

                        <input type="submit" value="查询"/>
                        <input type="hidden" value="" id="pro_id"/>
                        <input type="hidden" value="" id="pro_code"/>
                        <div style="float: right;margin-right: 20px"><a href="#" id="com_add" class="btn btn-success" >新增员工</a></div>
                    </form>
                </div>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th class="tl"><div></div></th>
                        <th class="tl"><div>姓名</div></th>
                        <th class="tl"><div>所属公司</div></th>
                        <th class="tl"><div>身份证号</div></th>
                        <th class="tl"><div>身份类别</div></th>
                        <th class="tl"><div>社保基数</div></th>
                        <th class="tl"><div>公积金基数</div></th>
                        <th class="tl"><div>操作</div></th>
                    </tr>
                    </thead>
                    <tbody  class="tbodays">

                    <?php
                    foreach ($employList as $row){
                        ?>
                        <tr class="" data-id="<?php echo $row['e_company_id'];?>">
                            <td>    </td>

                            <td class="tl pl10">
                                <div><?php echo $row['e_name'];?></div>
                            </td>
                            <td class="tl pl10">
                                <div><?php echo $row['e_company'];?></div>
                            </td>
                            <td class="tl pl10">
                                <div><?php echo $row['e_num'];?></div>
                            </td>
                            <td class="tl pl10">
                                <div><?php echo $row['e_type_name'];?></div>
                            </td>
                            <td class="tl pl10">
                                <div><?php echo $row['shebaojishu'];?></div>
                            </td>
                            <td class="tl pl10">
                                <div><?php echo $row['gongjijinjishu'];?></div>
                            </td>
                            <td class="tr">
                                <a title="修改" data-id="<?php echo $row['id'];?>"  class="rowUpdate pointer theme-color">修改</a>
                                <div class="cb"></div>
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
        $("#search_name").val('');
    }
    function searchByStatus() {

    }
</script>
<script language="javascript" type="text/javascript" src="common/common-js/employ.js" charset="utf-8"></script>
<div class="modal hide" id="modal-event1">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>员工信息新增</h3>
    </div>
    <form action="" id="company_validate" method="post" class="form-horizontal"  novalidate="novalidate">
        <div class="modal-body">
            <div class="designer_win">
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>姓名：<input type="text" maxlength="20" id="e_name"name="e_name"  /><input type="hidden" value="" id="employ_id" name="employ_id"/></div>
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>所属公司：<input type="text" maxlength="20" id="e_company"name="e_company"  /><input type="hidden" value="" id="company_id" name="company_id"/></div>
                <div class="tips"><em style="color: red;padding-right: 10px;">*</em>身份证号：<input type="text" maxlength="20" id="e_num"name="e_num"  /></div>
                <div class="tips">银行卡号：<input type="text" maxlength="20" id="bank_no"  /></div>
                <div class="tips">开户行：<input type="text" maxlength="20" id="e_bank"  /></div>
                <div class="tips">社保基数：<input type="text" maxlength="20" id="shebaojishu"  /></div>
                <div class="tips">公积金基数：<input type="text" maxlength="20" id="gongjijinjishu"  /></div>
                <div class="tips">身份类别：<select name="company_type" id="e_type"/>
                    <option value="0">未缴纳保险</option>
                    <option value="1">本市城镇职工</option>
                    <option value="2">外埠城镇职工</option>
                    <option value="3">本市农村劳动力</option>
                    <option value="4">外地农村劳动力</option>
                    <option value="5">本市农民工</option>
                    <option value="6">外地农民工</option>
                    </select></div>
                <div class="tips">劳务费：<input type="text" maxlength="20" id="laowufei" value="0.00" /></div>
                <div class="tips">档案费：<input type="text" maxlength="20" id="danganfei"  value="0.00" /></div>
                <div class="tips">残保金：<input type="text" maxlength="20" id="canbaojin" value="0.00"   /></div>
                <div class="tips">合同年份：<input type="text" maxlength="20" id="e_hetongnian"/>年</div>
                <div class="tips">合同介绍日期：<input type="text" maxlength="20" id="e_hetong_date"  onFocus="WdatePicker({isShowClear:false,readOnly:true,dateFmt:'yyyy-MM-dd',realDateFmt:'yyyy-MM-dd'})" /></div>
                <div class="tips">备注：<textarea id="memo">

                    </textarea></div>
            </div>
        </div>

        <div class="modal-footer modal_operate">
            <button type="submit" class="btn btn-primary">添加</button>
            <a href="#" class="btn" data-dismiss="modal">取消</a>
        </div>
    </form>
    <div class="search_suggest" id="custor_search_suggest">
        <ul class="search_ul">

        </ul>
        <div class="extra-list-ctn"><a href="javascript:void(0);" id="quickChooseProduct" class="quick-add-link"><i class="ui-icon-choose"></i>选择客户</a></div>
    </div>
</div>


