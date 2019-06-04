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
  // OnChange
  changeTextArea();
  // Onclick
  clickButtonForNewTextarea();



  function changeTextArea(){
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
              ajax.open("POST",'/desk/xhr_save',true);
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
          };
      }
    }
  function clickButtonForNewTextarea(){
    var buttons=document.querySelectorAll(".kanban_table button");
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].onclick=function(event_obj){
        // to_do,to_doing,to_done
        var currentColumn=event_obj.target.className;

        var id_desk=document.querySelector(".kanban_table").id;

        ajax.open("POST",'/desk/xhr_create',true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param="id_desk="+id_desk+"&column="+event_obj.target.className;
        ajax.onreadystatechange=function(){
          if(ajax.readyState!==4){
              document.getElementById('preloader').style="display:block";
          }else{
              document.getElementById('preloader').style="display:none";
              // Узнаем сколько всего textArea в данном столбце
              var textareaNumber=document.querySelectorAll(".kanban_table textarea."+currentColumn).length;
              // Есть ли в tbody, <tr> для нового элемента
              var tr=document.querySelector(".kanban_table tbody tr:nth-of-type("+(textareaNumber+1)+")")
              if (tr!==null) {
                for(let i=0;i<tr.childElementCount;i++){
                  if(tr.children[i].className==currentColumn){
                    var td=tr.children[i];
                    var newTextarea=document.createElement('textarea');
                    newTextarea.className=currentColumn;
                    newTextarea.name=ajax.responseText;
                    td.appendChild(newTextarea);
                  }
                  // else{
                  //   console.log("Нет <td> с указанным классом!")
                  // }
                }
              }// иначе создать <tr> вместе с пустыми <td class="to_do">, <td class"to_done"> ....">>
              else{
                var tbody=document.querySelector(".kanban_table tbody");
                var newTr=document.createElement('tr');
                var newTd=[];
                var nameColumn={
                  0:"to_do",
                  1:"to_doing",
                  2:"to_done"
                }
                for(let i=0;i<3;i++){
                  newTd[i]=document.createElement('td');
                  newTd[i].className=nameColumn[i];
                  if(currentColumn==nameColumn[i]){
                    var td=newTd[i];
                  }
                  newTr.appendChild(newTd[i]);
                }
                var newTextarea=document.createElement('textarea');
                newTextarea.className=currentColumn;
                newTextarea.name=ajax.responseText;
                td.appendChild(newTextarea);
                tbody.appendChild(newTr);
              }
          }
        }
        ajax.send(param);
        // Врменное решение, необходимо, т.к перестройка модели DOM требует время
        setTimeout(changeTextArea,2000);
      }
    }
  }
}