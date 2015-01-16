/**
 * Created by chaozhang204017 on 15-1-15.
 */
$(document).ready(function () {
    var container = document.getElementById("exampleGrid");
    var salaryGride = Handsontable(container, {
        data: [],
        startRows: 5,
        startCols: 4,
        colWidths: [], //can also be a number or a function
        rowHeaders: true,
        colHeaders: ['姓名', '社保基数', '公积金基数',
            '基本工资','考核工资','其他','应发合计','个人失业',
            '个人医疗','个人养老','个人公积金','个人代扣税',
            '个人扣款合计','个人实发合计','单位失业','单位医疗',
            '单位养老','单位工伤','单位生育','单位公积金','劳务费','合计付款'],
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
    /*Handsontable.Dom.addEvent(rowHeaders, 'click', function () {
     hot5.updateSettings({
     rowHeaders: this.checked
     });
     });
     */
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
    var excelHead = '';
    $('.rowCheck').click(function () {
        var salTimeId = $(this).attr('data-id');
        $.ajax({
            url: "index.php?action=Salary&mode=getSalaryListByTimeIdJson",
            data: {
                salTimeId : salTimeId
                }, //returns all cells' data
            dataType: 'json',
            type: 'POST',
            success: function (res) {
                if (res.result === 'ok') {
                    var  salary = res.salary;
                    excelHead =  res.head;
                    var shenfenleibie = res['shenfenleibie'];
                    var colWidths = [];
                    for(var i = 0;i < excelHead.length; i++){
                        if (i == shenfenleibie) colWidths.push(160);
                        else if (i == excelHead.length-1) {colWidths.push(160);}
                        else {
                            colWidths.push(80);
                        }
                    }

                    salaryGride.updateSettings({
                        colHeaders: excelHead
                    });
                    salaryGride.updateSettings({
                        colWidths: colWidths
                    });
                    salaryGride.loadData(salary);
                }
                else {
                    console.log('Save error');
                }
            },
            error: function () {
                console.log('Save error');
            }
        });

    });
    $("#import").click(function(){
        $.ajax({
            url: "tpl/salary/import.php",
            data: {
                salaryData: salaryGride.getData(),
                excelHead :excelHead
            }, //returns all cells' data
            dataType: 'json',
            type: 'POST',
            success: function (res) {
                if (res.result === 'ok') {
                }
                else {
                    console.log('Save error');
                }
            },
            error: function () {
                console.log('Save error');
            }
        });
    });
});/**
 * Created by zhangchao8189888 on 15-1-3.
 */