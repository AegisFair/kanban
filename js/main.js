window.onload=Init;
function Init(){
    // Создадим обьект Ajax
    var ajax=(function (){
        var xmlhttp;
        try {
          xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (E) {
            xmlhttp = false;
          }
        }
        if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
          xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
      })();
      console.log(ajax);

    // Создать событие при (изменение и снятия фокуса) с textarea. 
    // за это отвечает тип события: onchange
        // отправлять ajax-запрос -> сохранить изменения
        // 1)Выберем все элементы textarea
    var textAreaElements=document.querySelectorAll(".kanban_table textarea");
        // 2)Добавим им обработчик события (callback-функция)
    for (let i = 0; i < textAreaElements.length; i++) {
        textAreaElements[i].onchange=function (event_obj){
            // Идентификатор textarea (уникален для своей таблицы!)
            var id_textarea=event_obj.target.name;
            // К какой колонке принадлежит textarea
            var column=event_obj.target.className;
            // Переменная с содержимым в textarea
            var value=event_obj.target.value;
            // Формируем AJAX-запрос
            ajax.open("POST",'/desk/xhr',true);
            ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            var param="id_textarea="+id_textarea+"&column="+column+"&value="+encodeURIComponent(value);
            ajax.onreadystatechange=function(){
                if(ajax.readyState!==4){
                    document.getElementById('preloader').style="display:block";
                }else{
                    document.getElementById('preloader').style="display:none";
                }
            }
            ajax.send(param);
            // console.log(event_obj);
        };
    }

}