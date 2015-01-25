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
    var excelHead = '';
    $('.rowCheck').click(function () {
        var salTimeId = $(this).attr('data-id');
        $("#salaryId").val(salTimeId);
        $.ajax({
            url: "index.php?action=Salary&mode=getSalaryListByTimeIdJson",
            data: {
                salTimeId : salTimeId
                }, //returns all cells' data
            dataType: 'json',
            type: 'POST',
            success: function (res) {
                if (res.code == 100000) {
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
                    console.log('get error');
                }
            },
            error: function () {
                console.log('ajax error');
            }
        });

    });
    $("#import").click(function(){
        $("#excelForm").attr("action","index.php?action=Salary&mode=salaryImport");
        $("#excelForm").submit();
    });
    $(".rowAdd").click(function(){
        var salTimeId = $(this).attr("data-id");
        $("#salTimeId").val(salTimeId);
        $("#iForm").attr("action","index.php?action=Salary&mode=toAddNewSal");
        $("#iForm").submit();

    });
});/**
 * Created by zhangchao8189888 on 15-1-3.
 */