Array.prototype.remove=function(obj){
    for(var i =0;i <this.length;i++){
        var temp = this[i];
        if(!isNaN(obj)){
            temp=i;
        }
        if(temp == obj){
            for(var j = i;j <this.length;j++){
                this[j]=this[j+1];
            }
            this.length = this.length-1;
        }
    }
}
$(document).ready(function () {

    function createBigData() {
        var fileName = $('#fileName').val(),rows;
        $.ajax(
            {
                type: "get",
                url: "index.php?action=Salary&mode=getFileContentJson",
                data: {fileName : fileName},
                dataType: "json",
                success: function(data){
                    var header = [];
                    for(var i = 1;i <= data[0].length; i++){
                        header.push(i);
                    }
                    hot5.updateSettings({
                        colHeaders: header
                    });
                    hot5.loadData(data);

                }
            }
        );
    }
    var container = document.getElementById("exampleGrid");
    var hot5 = Handsontable(container, {
        data: [],
        startRows: 5,
        startCols: 4,
        colWidths: [55, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80], //can also be a number or a function
        rowHeaders: true,
        colHeaders: [],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        minSpareRows: 1,
        contextMenu: true
    });
    var selectFirst = document.getElementById('selectFirst'),
        rowHeaders = document.getElementById('rowHeaders'),
        colHeaders = document.getElementById('colHeaders'),
        reload = document.getElementById('reload');
    Handsontable.Dom.addEvent(reload,'click', function (){
        createBigData();
    });

    var sumGrid = document.getElementById("sumGrid");
    var hot6 = Handsontable(sumGrid, {
        data: [],
        startRows: 5,
        startCols: 4,
        colWidths: [55, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80], //can also be a number or a function
        rowHeaders: true,
        colHeaders: [],
        stretchH: 'last',
        manualColumnResize: true,
        manualRowResize: true,
        minSpareRows: 1,
        contextMenu: true
    });
    Handsontable.Dom.addEvent(colHeaders, 'click', function () {
        if (this.checked) {
            hot6.updateSettings({
                fixedColumnsLeft: 2
            });
        } else {
            hot5.updateSettings({
                fixedColumnsLeft: 0
            });
        }

    });
    $('#sumFirst').click(function () {
        //console.log(hot5.getData());
        var salaryData = hot5.getData();
        var endIndex = salaryData.length-1;
        salaryData.remove(endIndex);
        $.ajax({
            url: "index.php?action=Salary&mode=sumSalary",
            data: {
                shenfenzheng : $("#shenfenzheng").val(),
                add : $("#add").val(),
                del : $("#del").val(),
                freeTex : $("#freeTex").val(),
                data: hot5.getData()}, //returns all cells' data
            dataType: 'json',
            type: 'POST',
            success: function (res) {
                if (res.result === 'ok') {
                    var  salary = res.data;
                    hot6.updateSettings({
                        colHeaders: salary[0]
                    });
                    salary.remove(0);
                    hot6.loadData(salary);
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
    createBigData();
});/**
 * Created by zhangchao8189888 on 15-1-3.
 */
