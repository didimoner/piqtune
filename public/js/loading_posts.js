function getParameterByName(e,t){t||(t=window.location.href),e=e.replace(/[\[\]]/g,"\\$&");var a=new RegExp("[?&]"+e+"(=([^&#]*)|&|#|$)"),o=a.exec(t);return o?o[2]?decodeURIComponent(o[2].replace(/\+/g," ")):"":null}$(document).ready(function(){$(".load-more a").click(function(){var e=$(this).parent(),t=e.attr("type"),a=parseInt(e.attr("offset")),o=parseInt(e.attr("limit")),r=$("._token").val(),n=$("._username").val(),s={target:getParameterByName("target"),tags:getParameterByName("tags"),emotion:getParameterByName("emotion"),period:getParameterByName("period"),login:n};$.ajax({url:"/posts/ajax_load_posts",method:"POST",data:{type:t,additional_data:s,offset:a,limit:o,_token:r},beforeSend:function(){$(".load-more a").hide(),e.addClass("loading")},success:function(t){e.removeClass("loading"),obj=JSON.parse(t),void 0!==obj.content?($(".section").append(obj.content),obj.count>o?($(".load-more a").show(),e.attr("offset",a+o)):e.html('<p class="sub-text grey-text">Больше ничего нет...</p>')):e.html('<p class="sub-text grey-text">Больше ничего нет...</p>'),$("._token").val(obj.token)},error:function(t){e.removeClass("loading"),$(".load-more a").show()}})})});