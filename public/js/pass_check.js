$(function(){$(".re-password").change(function(){var s=$(".password").val(),a=$(this).val();s!=a?($(this).removeClass("valid"),$(this).addClass("invalid"),document.getElementById("re_password").setCustomValidity("Введите данные в указанном формате.")):($(this).removeClass("invalid"),$(this).addClass("valid"),document.getElementById("re_password").setCustomValidity(""))})});