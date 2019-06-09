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
//// Onload
  // Onchange
  changeTextArea();
  // Onclick
  clickButtonForNewTextarea();
  clickControl();

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
  function clickControl(arrayControls){
    if(arrayControls==undefined){
      var controlsElm=document.querySelectorAll(".kanban_table td span");
    }else{
      var controlsElm=arrayControls;
    }
    // console.dir(controlsElm);
    for (let i = 0; i < controlsElm.length; i++) {
      switch(controlsElm[i].className){
        case "delete":
          controlsElm[i].onclick=deleteTextarea;
          break;
        case "arrow-up":
          controlsElm[i].onclick=shiftUpTextarea;
          break;
        case "arrow-down":
          controlsElm[i].onclick=shiftDownTextarea;
          break;
      }
    }
  }
  function searchTextarea(parentTd){
    // parentTd=controlElm.parentElement;
    // Поиск textarea по всем детям parent'a td
    for (let i = 0; i < parentTd.children.length; i++) {
      if (parentTd.children[i].nodeName=="TEXTAREA"){
        return parentTd.children[i];
      }
    }
    // Нет элемента textarea в <td>
    return null;
  }
  function deleteTextarea(e){
    var currentTextarea=searchTextarea(e.target.parentNode);
    console.log(currentTextarea)
    // Внести изменения на сервер
    ajax.open("POST",'/desk/xhr_delete',true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var param="id_textarea="+currentTextarea.name+"&column="+currentTextarea.className;
    ajax.onreadystatechange=function(){
        if(ajax.readyState!==4){
            document.getElementById('preloader').style="display:block";
        }else{
            document.getElementById('preloader').style="display:none";
            var currentTd=currentTextarea.parentNode;
            currentTd.innerHTML="";
            shiftUpTextarea(currentTd,true);
        }
    }
    ajax.send(param);
    // Внести изменения в DOM
  }
  function shiftUpTextarea(emptyTd,recursiveShift){
    var tbody=document.querySelector(".kanban_table tbody");
    
    // <table>.rows - коллекция строк TR таблицы
      // <tr>.rowIndex – номер строки в таблице
      // <tbody>.rows – коллекция строк TR секции.
    if(recursiveShift===true){
      var row=emptyTd.parentNode;
      var nextRow=tbody.rows[row.sectionRowIndex+1];
      if(nextRow!==undefined){
        // console.log(nextRow);
        for(let i=0;i<nextRow.children.length;i++){
          if(nextRow.children[i].className==emptyTd.className){
            var td=nextRow.children[i];
            for (let j=0;j<td.children.length;j++){
              if(td.children[j].tagName=="TEXTAREA"){
                // td.children[j].setAttribute('value',td.children[j].value);
                console.dir(td.innerHTML);
                emptyTd.innerHTML=td.innerHTML;
                td.innerHTML="";
                // При копировании исчезают события onClick
                  // поэтому, пока так
                  clickControl();
                shiftUpTextarea(td,true);
              }
            }
          }
        }
      }
    }else{
      var row=emptyTd.target.parentNode.parentNode;
      var currentTd=emptyTd.target.parentNode,
          indexTd=currentTd.cellIndex,
          currentTextarea=searchTextarea(emptyTd.target.parentNode);
      // Это не первая строка, тогда ...
      if(row.sectionRowIndex!==0){
        let previousTd=tbody.rows[row.sectionRowIndex-1].cells[indexTd],
            previousTextarea=searchTextarea(previousTd);
        // Динамичное изменение данных на сервере
        ajax.open("POST",'/desk/xhr_order',true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param="column="+currentTextarea.className+"&id_textarea_current="+currentTextarea.name+"&id_textarea_previous="+previousTextarea.name;
        ajax.onreadystatechange=function(){
          if(ajax.readyState!==4){
            document.getElementById('preloader').style="display:block";
          }else{
            document.getElementById('preloader').style="display:none";
            // Динамичное изменение DOM клиента
            // Присвоение значений: Предыдущей новое, а новой предыдущее
            previousTd.innerHTML=[currentTd.innerHTML,currentTd.innerHTML=previousTd.innerHTML][0];
            clickControl();
          }
        }
        ajax.send(param);
      }
    }
  }
  function shiftDownTextarea(e){
    var tbody=document.querySelector(".kanban_table tbody");
    var row=e.target.parentNode.parentNode;
    var currentTd=e.target.parentNode,
        indexTd=currentTd.cellIndex,
        currentTextarea=searchTextarea(e.target.parentNode),
        nextTextarea;
    // Это не последняя строка и в ней есть содержимое для <td>, тогда ...
    if(row.sectionRowIndex < tbody.rows.length-1){
      nextRow=tbody.rows[row.sectionRowIndex+1];
      for(let i=0; i<nextRow.cells.length; i++){
        if(nextRow.cells[i].className==currentTd.className){
          var nextTd=nextRow.cells[i];
        }
      }
      // Содеримое не пустое, то ...
      if(nextTextarea=searchTextarea(nextTd)){
        // Динамичное изменение данных на сервере
        ajax.open("POST",'/desk/xhr_order',true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param="column="+currentTextarea.className+"&id_textarea_current="+currentTextarea.name+"&id_textarea_next="+nextTextarea.name;
        ajax.onreadystatechange=function(){
          if(ajax.readyState!==4){
            document.getElementById('preloader').style="display:block";
          }else{
            document.getElementById('preloader').style="display:none";
            // Динамичное изменение DOM клиента
            // Присвоение значений: Предыдущей новое, а новой предыдущее
            nextTd.innerHTML=[currentTd.innerHTML,currentTd.innerHTML=nextTd.innerHTML][0];
            clickControl();
          }
        }
        ajax.send(param);
      }
    }
  }
  function clickButtonForNewTextarea(){
    var buttons=document.querySelectorAll(".kanban_table button");
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].onclick=function(event_obj){
        // Инициализация элементов управления textarea
        var control={
          delete: document.createElement("span"),
          arrowUp: document.createElement("span"),
          arrowDown: document.createElement("span"),
          init: function(){
            this.delete.className="delete";
            this.delete.innerHTML="X";

            this.arrowUp.className="arrow-up";
            this.arrowUp.innerHTML="&#9650;";

            this.arrowDown.className="arrow-down";
            this.arrowDown.innerHTML="&#9660;";
            clickControl([this.delete,this.arrowUp,this.arrowDown]);
          }
        };
        // Завершение инициализации элементов управления textarea
        control.init();
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
                    td.appendChild(control.delete);
                    td.appendChild(control.arrowUp);
                    td.appendChild(control.arrowDown);
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
                td.appendChild(control.delete);
                td.appendChild(control.arrowUp);
                td.appendChild(control.arrowDown);
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