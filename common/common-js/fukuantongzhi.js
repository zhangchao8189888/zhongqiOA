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
                }
            }
        );
        $('#modal-event1').modal({show:true});
    });
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
});/**
 * Created by zhangchao8189888 on 15-1-3.
 */
