function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}
/***************************Return thousan separated number******************************/
function formatNumber(num) {
  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

$(document).ready(function() {

    $('.txt_box').keyup(function()
    {
       var yourInput = $(this).val();
       re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
       var isSplChar = re.test(yourInput);
       if(isSplChar)
       {
           var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
           $(this).val(no_spl_char);
       }
   });

    /*************************** MObile Number 8 DIGIT *************************/


    $('.mob_oman_8').on('keypress, keydown', function(event) {

      var $field = $(this);
      var readOnlyLength = 5;
      if ((event.which != 37 && (event.which != 39)) &&
       ((this.selectionStart < readOnlyLength) ||
        ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
       return false;
}

});
    $(".mob_validation_8").addClass("customRegMob_eight_digit");

    $.validator.addClassRules({
     customRegMob_eight_digit: {
        customRegMob_eight_digit: true
    }
	 
});
    jQuery.validator.addMethod("customRegMob_eight_digit", function(value, element) {
     return this.optional(element) || /^\d{8}$/i.test(value);
 }, "Please Enter 8 Digit Number ");

    /*************************** End  *************************/


    /*************************** MObile Number 13 DIGIT *************************/

    $(".mob_validation").addClass("customRegMob");

    $.validator.addClassRules({
        customRegMob: {
            customRegMob: true
        }
    });
    jQuery.validator.addMethod("customRegMob", function(value, element) {
        return this.optional(element) || /^\d{1,8}$/i.test(value);
    }, "Please Enter Less Than 8 Digit Number");



    $('.mob_oman_code').on('keypress, keydown', function(event) {

      var $field = $(this);
      var readOnlyLength = $('.mob_oman_code').val().length;
      if ((event.which != 37 && (event.which != 39)) &&
       ((this.selectionStart < readOnlyLength) ||
        ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
       return false;
}

});

    $('.mob_oman_13').on('keypress, keydown', function(event) {

      var $field = $(this);
      var readOnlyLength = 5;
      if ((event.which != 37 && (event.which != 39)) &&
       ((this.selectionStart < readOnlyLength) ||
        ((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
       return false;
}

});
    $(".mob_validation_13").addClass("customRegMob_thirteen_digit");

    $.validator.addClassRules({
     customRegMob_thirteen_digit: {
        customRegMob_thirteen_digit: true
    }
});
    jQuery.validator.addMethod("customRegMob_thirteen_digit", function(value, element) {
     return this.optional(element) || /^\d{8}$/i.test(value);
 }, "Please Enter 8 Digit");

    /*************************** End  *************************/


});
$(document).on("keypress",".allownumericwithdecimal", function(event) {

    var $this = $(this);
    if ((event.which != 46 || $this.val().indexOf('.') != -1) &&
     ((event.which < 48 || event.which > 57) &&
         (event.which != 0 && event.which != 8))) {
     event.preventDefault();
}

var text = $(this).val();
if ((event.which == 46) && (text.indexOf('.') == -1)) {
    setTimeout(function() {
        if ($this.val().substring($this.val().indexOf('.')).length > 3) {
            $this.val($this.val().substring(0, $this.val().indexOf('.') + 3));
        }
    }, 1);
}

if ((text.indexOf('.') != -1) &&
    (text.substring(text.indexOf('.')).length > 3) &&
    (event.which != 0 && event.which != 8) &&
    ($(this)[0].selectionStart >= text.length - 3)) {
    event.preventDefault();
}      
});
$(document).on("paste",".allownumericwithdecimal", function(event) {
	var text = e.originalEvent.clipboardData.getData('Text');
	if ($.isNumeric(text)) {
		if ((text.substring(text.indexOf('.')).length > 4) && (text.indexOf('.') > -1)) {
			e.preventDefault();
			$(this).val(text.substring(0, text.indexOf('.') + 4));
        }
    }
    else {
     e.preventDefault();
 }
});

/*************************** End  *************************/
/**********************Thousand Separating a number when user enter*****************/
function FormatCurrency(ctrl) {
    
            //Check if arrow keys are pressed - we want to allow navigation around textbox using arrow keys
            if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 50) {
                return;
            }
            
            var val = ctrl.value;

            val = val.replace(/,/g, "")
            ctrl.value = "";
            val += '';
            x = val.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';

            var rgx = /(\d+)(\d{3})/;

            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            } 

            
            if(x2.length > 4){
              return ctrl.value =  x1 + x2.substring(0, 4);  
          }


          ctrl.value = x1 + x2;
      }


      /********************* convert date into dd/mm/yyyy******************************************/
      function convertDate(dateString){
        var p = dateString.split(/\D/g)
        return [p[2],p[1],p[0] ].join("/")
    }
    /********************* convert date into dd/mm/yyyy**********************************/
    function replaceSpecial(str)
    {
        var str;
        return str.substring(0, str.indexOf('@'));
    }
	/********************* convert date into dd-mm-yyyy******************************************/
      function changeDate(dateString){
        var p = dateString.split(/\D/g)
        return [p[2],p[1],p[0] ].join("-")
    }