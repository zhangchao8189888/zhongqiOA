/**
 * Created by chaozhang204017 on 14-12-25.
 */
$(function(){
    $("#company_validate").validate({
        submitHandler:function(form){
            $(form).ajaxSubmit({
                type:"post",
                url:"test_save.php?time="+ (new Date()).getTime(),
                //beforeSubmit: showRequest,
                success: showResponse
            });
        },
        rules: {
            company_name: { required: true },
            contacts: { required: true },
            contacts_no: { required: true }
            /*contacts_no: { required: true },
            contacts: { required: true }*/
            /*company_name:
            {
                required: true,
                remote:{                                          //验证用户名是否存在
                    type:"POST",
                    url:"index.php?action=Customer&mode=verifyCustomCode",             //servlet
                    data:{
                        code:function(){return $("#customer_code").val();}
                    }
                }
            },
            txbNewPwd2: { required: true, rangelength: [8, 15], equalTo: "#txbNewPwd1" }*/
        },
        messages: {
            company_name:
            {
                required: '必填'
            },
            contacts:
            {
                required: '必填'
            },
            contacts:
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

});



jQuery.extend(jQuery.validator.messages, {
    required: "必选字段",
    remote: "请修正该字段",
    email: "请输入正确格式的电子邮件",
    url: "请输入合法的网址",
    date: "请输入合法的日期",
    dateISO: "请输入合法的日期 (ISO).",
    number: "请输入合法的数字",
    digits: "只能输入整数",
    creditcard: "请输入合法的信用卡号",
    equalTo: "请再次输入相同的值",
    accept: "请输入拥有合法后缀名的字符串",
    maxlength: jQuery.validator.format("请输入一个 长度最多是 {0} 的字符串"),
    minlength: jQuery.validator.format("请输入一个 长度最少是 {0} 的字符串"),
    rangelength: jQuery.validator.format("请输入 一个长度介于 {0} 和 {1} 之间的字符串"),
    range: jQuery.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
    max: jQuery.validator.format("请输入一个最大为{0} 的值"),
    min: jQuery.validator.format("请输入一个最小为{0} 的值")
});
