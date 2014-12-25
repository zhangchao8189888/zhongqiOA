<!--Header-part-->
<?php
session_start ();
$user = $_SESSION ['admin'];
global $actionPath;
$placeholder = '请输入';
if ($actionPath == 'Customer') {
    $placeholder='请输入客户名称';
} elseif ($actionPath == 'Product'){
    $placeholder='请输入商品名称/编码/类别/关键字';
} elseif ($actionPath == 'Order') {
    $placeholder='请输入订单号或客户名称';
}
?>
<div id="header">
  <!--<img id="logo" width='160px' src="common/img/pinge_logo.png" />-->
</div>
<!--close-Header-part--> 
<!--top-Header-menu-->
<div id="user-nav" class="navbar navbar-inverse">
  <ul class="nav">
    <li class=""><a><i class="icon icon-user"></i><span class="text">&nbsp;<?php echo $user['real_name']; ?>，你好</span></a></li>
    <li class=""><a href=""><i class="icon icon-time"></i>&nbsp;<span class="text" id="Timer"></span></a>
    </li>
  </ul>
</div>
<!--close-top-Header-menu-->
<!--start-top-serch-->
<div id="search">
    <form action="" id="searchForm" method="post" >
    <select style="width: 100px" onchange="searchChange()" id="searchType"name="searchType">
        <option value="customer" <?php if($actionPath == 'Customer') echo 'selected';?>>搜客户</option>
        <option value="product" <?php if($actionPath == 'Product') echo 'selected';?>>搜商品</option>
        <option value="order" <?php if($actionPath == 'Order') echo 'selected';?>>搜订单</option>
    </select>
    <input type="text" style="width: 300px" id="searchKey" name="searchKey" placeholder="<?php echo $placeholder;?>">
    <button type="button" class="tip-bottom"  id="searchButton" data-original-title="查询"><i class="icon-search icon-white"></i></button>
        </form>
</div>
<div id="logout" class="navbar navbar-inverse">
	<ul class="nav">
     <a title="" href="index.php?action=Admin&mode=logoff" onClick="return confirm('确定注销退出吗？');"><i class="icon icon-share-alt"></i> <span class="text">[退出系统]</span></a>
    </ul>
</div>
<!--close-top-serch-->
<script type="text/javascript">
//$('#logo').addClass('animated fadeInRight ');
$(document).ready(function (){
    $('#search input[type=text]').typeahead({
        source: [],
        items: 4
    });
    $("#searchButton").on('click',function () {
        var url = '';
        var searchType = $("#searchType").val();
        if (searchType == 'customer') {
            url = 'index.php?action=Customer&mode=getCustomerList';
        } else if (searchType == 'product') {
            url = 'index.php?action=Product&mode=getProductList';
        } else if (searchType == 'order') {
            url = 'index.php?action=Order&mode=toOrderPage';
        }
        $("#searchForm").attr('action',url);
        $("#searchForm").submit();
    });
});

    function searchChange() {
        var searchType = $("#searchType").val();
        if (searchType == 'customer') {
            $("#searchKey").attr("placeholder",'请输入客户名称');
        } else if (searchType == 'product'){
            $("#searchKey").attr("placeholder",'请输入商品名称/编码/类别/关键字');
        } else if (searchType == 'order') {
            $("#searchKey").attr("placeholder",'请输入订单号或客户名称');
        }
    }

</script>


