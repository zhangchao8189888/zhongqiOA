$(document).ready(function () {
    $("#company_validate").validate({
        onsubmit:true,
        submitHandler:function(form){
            form.submit();
        },
        rules: {
            company_name: { required: true },
            salaryDate: { required: true },
            file: { required: true }
        },
        messages: {
            company_name:
            {
                required: '必填'
            },
            salaryDate:
            {
                required: '必填'
            },
            file:
            {
                required: '必填'
            }
        },
        errorClass: "help-inline",
        errorElement: "span",
        highlight:function(element, errorClass, validClass) {
            $(element).parents('.control-group').removeClass('success');
            $(element).parents('.control-group').addClass('error');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }


    });
    $("#com_add").click(function(){
        /*$("#pro_date").val($("#shaijia_date").val());*/
        $.ajax(
            {
                type: "get",
                url: "index.php?action=Company&mode=getCode",
                data: {type:'fukuantongzhi'},
                dataType: "json",
                success: function(data){
                    $(".codeNo").text(data.codeNo);
                    $("#shouNo").val(data.codeNo);
                }
            }
        );
        $('#modal-event1').modal({show:true});
    });
    var dianfuGrid = document.getElementById("dianfuGrid");
    var dianfu = new Handsontable(dianfuGrid,{
        data: [],
        startRows: 5,
        startCols: 4,
        colHeaders: true,
        dataSchema: {e_num: '', per_shiye: {first: null, last: null}, address: null},
        colHeaders: ['姓名','身份证号', '个人失业', '个人医疗', '个人养老',
            '个人公积金', '单位失业', '单位医疗', '单位养老', '单位工伤',
            '单位生育','单位公积金'
        ],
        colWidths: [100,160, 100, 100, 100, 100, 100, 100, 100, 100, 100],
        columns: [
            {data: "e_name"},
            {data: "e_num"},
            {data: "per_shiye"},
            {data: "per_yiliao"},
            {data: "per_yanglao"},
            {data: "per_gongjijin"},
            {data: "com_shiye"},
            {data: "com_yiliao"},
            {data: "com_yanglao"},
            {data: "com_gongshang"},
            {data: "com_shengyu"},
            {data: "com_gongjijin"}
        ],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        readOnly:true,
        minSpareRows: 0,
        contextMenu: true
    });
    var yanfuGrid = document.getElementById("yanfuGrid");
    var yanfu = new Handsontable(yanfuGrid,{
        data: [],
        startRows: 5,
        startCols: 4,
        colHeaders: true,
        dataSchema: {e_num: '', per_shiye: {first: null, last: null}, address: null},
        colHeaders: ['姓名','身份证号', '个人失业', '个人医疗', '个人养老',
            '个人公积金', '单位失业', '单位医疗', '单位养老', '单位工伤',
            '单位生育','单位公积金'
        ],
        colWidths: [100,160, 100, 100, 100, 100, 100, 100, 100, 100, 100],
        columns: [
            {data: "e_name"},
            {data: "e_num"},
            {data: "per_shiye"},
            {data: "per_yiliao"},
            {data: "per_yanglao"},
            {data: "per_gongjijin"},
            {data: "com_shiye"},
            {data: "com_yiliao"},
            {data: "com_yanglao"},
            {data: "com_gongshang"},
            {data: "com_shengyu"},
            {data: "com_gongjijin"}
        ],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        readOnly:true,
        minSpareRows: 0,
        contextMenu: true
    });
    var errorGrid = document.getElementById("errorGrid");
    var error = new Handsontable(errorGrid,{
        data: [],
        startRows: 5,
        startCols: 4,
        colHeaders: true,
        dataSchema: {e_num: '', per_shiye: {first: null, last: null}, address: null},
        colHeaders: ['姓名','身份证号','个人失业', '个人医疗', '个人养老',
            '个人公积金', '单位失业', '单位医疗', '单位养老', '单位工伤',
            '单位生育','单位公积金'
        ],
        colWidths: [100,160, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100],
        columns: [
            {data: "e_name"},
            {data: "e_num"},
            {data: "per_shiye"},
            {data: "per_yiliao"},
            {data: "per_yanglao"},
            {data: "per_gongjijin"},
            {data: "com_shiye"},
            {data: "com_yiliao"},
            {data: "com_yanglao"},
            {data: "com_gongshang"},
            {data: "com_shengyu"},
            {data: "com_gongjijin"}
        ],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        readOnly:true,
        minSpareRows: 0,
        contextMenu: true
    });
    var container = document.getElementById("exampleGrid");
    var salaryGride = Handsontable(container, {
        data: [],
        startRows: 5,
        startCols: 4,
        colWidths: [], //can also be a number or a function
        rowHeaders: true,
        colHeaders: [],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        readOnly:true,
        minSpareRows: 0,
        contextMenu: true
    });
    var selectFirst = document.getElementById('selectFirst'),
        rowHeaders = document.getElementById('rowHeaders'),
        colHeaders = document.getElementById('colHeaders');

    Handsontable.Dom.addEvent(colHeaders, 'click', function () {
        if (this.checked) {
            salaryGride.updateSettings({
                fixedColumnsLeft: 2
            });
        } else {
            salaryGride.updateSettings({
                fixedColumnsLeft: 0
            });
        }

    });
    Handsontable.Dom.addEvent(rowHeaders, 'click', function () {
        if (this.checked) {
            salaryGride.updateSettings({
                fixedRowsTop: 1
            });
        } else {
            salaryGride.updateSettings({
                fixedRowsTop: 0
            });
        }

    });
    $('.rowCheck').click(function () {
        var salTimeId = $(this).attr('data-id');
        var fileName = $(this).attr('data-file');
        if (!fileName) {
            return;
        }
        //salTimeId
        $("#salaryTimeId").val(salTimeId);
        $.ajax(
            {
                type: "get",
                url: "index.php?action=Salary&mode=getFileContentJson",
                data: {
                    fileName : fileName,
                    checkType : 'fukuan'
                },
                dataType: "json",
                success: function(data){
                    var header = [];
                    for(var i = 1;i <= data[0].length; i++){
                        header.push(i);
                    }
                    salaryGride.updateSettings({
                        colHeaders: header
                    });
                    salaryGride.loadData(data);

                }
            }
        );

    });

    $("#comeIn").click(function(){
        var data = salaryGride.getData();
        if (data.length < 0) {
            return;
        }
        var url = 'index.php?action=Salary&mode=SalaryComeIn';
        var formData = {
            "data": data,
            salaryTimeId: $("#salaryTimeId").val()
        }
        $.ajax({
            url: url,
            data: formData, //returns all cells' data
            dataType: 'json',
            type: 'POST',
            success: function (res) {
                if (res.code == '100000') {
                    console.log(res.data);
                    dianfu.loadData(res.data.dianfu);
                    yanfu.loadData(res.data.yanfu);
                    error.loadData(res.data.error);
                    alert('工作保存成功');
                }
                else {
                    console.log('Save error');
                }
            },
            error: function () {
                console.text('Save error');
            }
        });
    });
    $("#e_company").on("click",function(){
        var input;
        var inputVal;
        var suggestWrap = $('#custor_search_suggest');
        var oSearchSelect = BaseWidget.UI.SearchSelect;
        oSearchSelect.fnInt();
        oSearchSelect.leftPlus = -200;
        oSearchSelect.topPlus = 76;
        oSearchSelect.inputWith = 314;
        oSearchSelect.url = 'index.php?action=Company&mode=getCompanyListJson';
        var fnHideSuggest = function(){
            var that = BaseWidget.UI.SearchSelect;
            that.inputVal = '';
            that.targetSuggestWrap.hide();
        }
        oSearchSelect.targetSuggestWrap = suggestWrap;
        oSearchSelect.fnHideSuggest = fnHideSuggest;
        oSearchSelect.fnMousedown = function (that,obj) {
            if (that.inputVal == obj.name) {
                that.fnHideSuggest();
            } else {
                //Customer.oCustomer.fnGetCustomerInfo(obj);
                //得到用户信息
                $("#e_company").val(obj.name);
                $("#company_id").val(obj.id);
                $.ajax(
                    {
                        type: "get",
                        url: "index.php?action=Salary&mode=getSalaryTimeListJson",
                        data: {companyId : obj.id},
                        dataType: "json",
                        success: function(data){
                            $("#salTimeId").html('<option value="-1">选择工资月份</option>');
                            for(var i = 0; i < data.length; i++) {
                                $("#salTimeId").append('<option value="'+data[i].id+'">'+data[i].salaryTime+'</option>');
                            }

                        }
                    }
                );
            }
        }
        input = $(this);
        oSearchSelect.targetInput = input;
        input.click(function(e){
            oSearchSelect.fnSendKeyWord(e);
        });
        input.keyup(
            function (e) {
                oSearchSelect.fnSendKeyWord(e);
            }
        );
        input.blur(oSearchSelect.fnHideSuggest);
        if (input.val() == '') {
            oSearchSelect.fnSendKeyWord({});
        } else {
            inputVal = input.val();
        }
        oSearchSelect.inputVal = inputVal;
    });

});
function getSalarySumInfo() {
    var salTime = $("#salTimeId").val();
    if (salTime < 0) {
        return;
    }
    var obj = {
        salTimeId : salTime
    };
    $.ajax(
        {
            type: "post",
            url: "index.php?action=Salary&mode=getSalaryInfoJson",
            data: obj,
            dataType: "json",
            success: function(data){
                $("#salSum").text(data.sum_paysum_zhongqi);
                $("#salSumValue").val(data.sum_paysum_zhongqi);
                //$("#laowufei").val(data.sum_laowufei);

            }
        }
    );
}/**
 * Created by zhangchao8189888 on 15-1-3.
 */
/**
 * Created by chaozhang204017 on 15-1-17.
 */
