window.onload = Init;
function Init() {
  // Создадим обьект Ajax
  var ajax = (function () {
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
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
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
  clickCreateDesk();

  //for admin panel
  clickDesks();
  clickUsersList();
  //deleteAccessUserForDesk(); - мы должны вызывать в функции clickDesks();

  function changeTextArea() {
    // Создать событие при (изменение и снятия фокуса) с textarea. 
    // за это отвечает тип события: onchange
    // отправлять ajax-запрос -> сохранить изменения
    // 1)Выберем все элементы textarea
    var textAreaElements = document.querySelectorAll(".kanban_table textarea");
    // 2)Добавим им обработчик события (callback-функция)
    for (let i = 0; i < textAreaElements.length; i++) {
      textAreaElements[i].onchange = function (event_obj) {
        // Идентификатор textarea (уникален для своей таблицы!)
        var id_textarea = event_obj.target.name;
        // К какой колонке принадлежит textarea
        var column = event_obj.target.className;
        // Переменная с содержимым в textarea
        var value = event_obj.target.value;
        // Формируем AJAX-запрос
        ajax.open("POST", '/desk/xhr_save', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param = "id_textarea=" + id_textarea + "&column=" + column + "&value=" + encodeURIComponent(value);
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            document.getElementById('preloader').style = "display:block";
          } else {
            document.getElementById('preloader').style = "display:none";
          }
        }
        ajax.send(param);
      };
    }
  }
  function clickControl(arrayControls) {
    if (arrayControls == undefined) {
      var controlsElm = document.querySelectorAll(".kanban_table td span");
    } else {
      var controlsElm = arrayControls;
    }
    // console.dir(controlsElm);
    for (let i = 0; i < controlsElm.length; i++) {
      switch (controlsElm[i].className) {
        case "delete":
          controlsElm[i].onclick = deleteTextarea;
          break;
        case "arrow-up":
          controlsElm[i].onclick = shiftUpTextarea;
          break;
        case "arrow-down":
          controlsElm[i].onclick = shiftDownTextarea;
          break;
      }
    }
  }
  function searchTextarea(parentTd) {
    // parentTd=controlElm.parentElement;
    // Поиск textarea по всем детям parent'a td
    for (let i = 0; i < parentTd.children.length; i++) {
      if (parentTd.children[i].nodeName == "TEXTAREA") {
        return parentTd.children[i];
      }
    }
    // Нет элемента textarea в <td>
    return null;
  }
  function deleteTextarea(e) {
    var currentTextarea = searchTextarea(e.target.parentNode);
    console.log(currentTextarea)
    // Внести изменения на сервер
    ajax.open("POST", '/desk/xhr_delete', true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var param = "id_textarea=" + currentTextarea.name + "&column=" + currentTextarea.className;
    ajax.onreadystatechange = function () {
      if (ajax.readyState !== 4) {
        document.getElementById('preloader').style = "display:block";
      } else {
        document.getElementById('preloader').style = "display:none";
        var currentTd = currentTextarea.parentNode;
        currentTd.innerHTML = "";
        shiftUpTextarea(currentTd, true);
      }
    }
    ajax.send(param);
    // Внести изменения в DOM
  }
  function shiftUpTextarea(emptyTd, recursiveShift) {
    var tbody = document.querySelector(".kanban_table tbody");

    // <table>.rows - коллекция строк TR таблицы
    // <tr>.rowIndex – номер строки в таблице
    // <tbody>.rows – коллекция строк TR секции.
    if (recursiveShift === true) {
      var row = emptyTd.parentNode;
      var nextRow = tbody.rows[row.sectionRowIndex + 1];
      if (nextRow !== undefined) {
        // console.log(nextRow);
        for (let i = 0; i < nextRow.children.length; i++) {
          if (nextRow.children[i].className == emptyTd.className) {
            var td = nextRow.children[i];
            for (let j = 0; j < td.children.length; j++) {
              if (td.children[j].tagName == "TEXTAREA") {
                // td.children[j].setAttribute('value',td.children[j].value);
                console.dir(td.innerHTML);
                emptyTd.innerHTML = td.innerHTML;
                td.innerHTML = "";
                // При копировании исчезают события onClick
                // поэтому, пока так
                clickControl();
                shiftUpTextarea(td, true);
              }
            }
          }
        }
      }
    } else {
      var row = emptyTd.target.parentNode.parentNode;
      var currentTd = emptyTd.target.parentNode,
        indexTd = currentTd.cellIndex,
        currentTextarea = searchTextarea(emptyTd.target.parentNode);
      // Это не первая строка, тогда ...
      if (row.sectionRowIndex !== 0) {
        let previousTd = tbody.rows[row.sectionRowIndex - 1].cells[indexTd],
          previousTextarea = searchTextarea(previousTd);
        // Динамичное изменение данных на сервере
        ajax.open("POST", '/desk/xhr_order', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param = "column=" + currentTextarea.className + "&id_textarea_current=" + currentTextarea.name + "&id_textarea_previous=" + previousTextarea.name;
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            document.getElementById('preloader').style = "display:block";
          } else {
            document.getElementById('preloader').style = "display:none";
            // Динамичное изменение DOM клиента
            // Присвоение значений: Предыдущей новое, а новой предыдущее
            previousTd.innerHTML = [currentTd.innerHTML, currentTd.innerHTML = previousTd.innerHTML][0];
            clickControl();
          }
        }
        ajax.send(param);
      }
    }
  }
  function shiftDownTextarea(e) {
    var tbody = document.querySelector(".kanban_table tbody");
    var row = e.target.parentNode.parentNode;
    var currentTd = e.target.parentNode,
      indexTd = currentTd.cellIndex,
      currentTextarea = searchTextarea(e.target.parentNode),
      nextTextarea;
    // Это не последняя строка и в ней есть содержимое для <td>, тогда ...
    if (row.sectionRowIndex < tbody.rows.length - 1) {
      nextRow = tbody.rows[row.sectionRowIndex + 1];
      for (let i = 0; i < nextRow.cells.length; i++) {
        if (nextRow.cells[i].className == currentTd.className) {
          var nextTd = nextRow.cells[i];
        }
      }
      // Содеримое не пустое, то ...
      if (nextTextarea = searchTextarea(nextTd)) {
        // Динамичное изменение данных на сервере
        ajax.open("POST", '/desk/xhr_order', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param = "column=" + currentTextarea.className + "&id_textarea_current=" + currentTextarea.name + "&id_textarea_next=" + nextTextarea.name;
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            document.getElementById('preloader').style = "display:block";
          } else {
            document.getElementById('preloader').style = "display:none";
            // Динамичное изменение DOM клиента
            // Присвоение значений: Предыдущей новое, а новой предыдущее
            nextTd.innerHTML = [currentTd.innerHTML, currentTd.innerHTML = nextTd.innerHTML][0];
            clickControl();
          }
        }
        ajax.send(param);
      }
    }
  }
  function clickButtonForNewTextarea() {
    var buttons = document.querySelectorAll(".kanban_table button");
    for (let i = 0; i < buttons.length; i++) {
      buttons[i].onclick = function (event_obj) {
        // Инициализация элементов управления textarea
        var control = {
          delete: document.createElement("span"),
          arrowUp: document.createElement("span"),
          arrowDown: document.createElement("span"),
          init: function () {
            this.delete.className = "delete";
            this.delete.innerHTML = "X";

            this.arrowUp.className = "arrow-up";
            this.arrowUp.innerHTML = "&#9650;";

            this.arrowDown.className = "arrow-down";
            this.arrowDown.innerHTML = "&#9660;";
            clickControl([this.delete, this.arrowUp, this.arrowDown]);
          }
        };
        // Завершение инициализации элементов управления textarea
        control.init();
        // to_do,to_doing,to_done
        var currentColumn = event_obj.target.className;

        var id_desk = document.querySelector(".kanban_table").id;

        ajax.open("POST", '/desk/xhr_create', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param = "id_desk=" + id_desk + "&column=" + event_obj.target.className;
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            document.getElementById('preloader').style = "display:block";
          } else {
            document.getElementById('preloader').style = "display:none";
            // Узнаем сколько всего textArea в данном столбце
            var textareaNumber = document.querySelectorAll(".kanban_table textarea." + currentColumn).length;
            // Есть ли в tbody, <tr> для нового элемента
            var tr = document.querySelector(".kanban_table tbody tr:nth-of-type(" + (textareaNumber + 1) + ")")
            if (tr !== null) {
              for (let i = 0; i < tr.childElementCount; i++) {
                if (tr.children[i].className == currentColumn) {
                  var td = tr.children[i];
                  var newTextarea = document.createElement('textarea');
                  newTextarea.className = currentColumn;
                  newTextarea.name = ajax.responseText;
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
            else {
              var tbody = document.querySelector(".kanban_table tbody");
              var newTr = document.createElement('tr');
              var newTd = [];
              var nameColumn = {
                0: "to_do",
                1: "to_doing",
                2: "to_done"
              }
              for (let i = 0; i < 3; i++) {
                newTd[i] = document.createElement('td');
                newTd[i].className = nameColumn[i];
                if (currentColumn == nameColumn[i]) {
                  var td = newTd[i];
                }
                newTr.appendChild(newTd[i]);
              }
              var newTextarea = document.createElement('textarea');
              newTextarea.className = currentColumn;
              newTextarea.name = ajax.responseText;
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
        setTimeout(changeTextArea, 2000);
      }
    }
  }
  function clickDesks() {
    var liElements = document.querySelectorAll(".all-group-desks ul li");
    for (let i = 0; i < liElements.length; i++) {
      liElements[i].onclick = function (elm) {
        //Очистка содержимого <section class="current-desk>Тут удаляем всё</section>" 
        document.querySelector(".current-desk").innerHTML = "";
        //
        ajax.open("POST", '/admin/xhr_click_desks', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var id_desk = elm.target.id;
        var param = "id_desk=" + id_desk;
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            //document.getElementById('preloader').style = "display:block";
          } else {
            //document.getElementById('preloader').style = "display:none";
            var str_response = ajax.responseText;
            //if(str_response=="[]"){str_response=""}
            //str_response = str_response.split(",");
            str_response=JSON.parse(str_response);
            console.log(str_response);
            // for (let k = 0; k < str_response.length; k++) {
            //   str_response[k] = str_response[k].replace(/.+login.+\"(\w+)\".+/, '$1');
            // }
            var ul = document.createElement('ul');

            for (let k = 0; k < str_response.length; k++) {
              var li = document.createElement('li');
              li.innerHTML = str_response[k].login;
              li.id = str_response[k].login;
              ul.append(li);
              // Код для удаления доступа к таблице
              li.onclick = function (e) {
                deleteAccessForUser(e);
              };
            }
            document.querySelector(".current-desk").append(ul);
            document.querySelector(".current-desk").id = id_desk;
            //console.log(str_response);
          }
        }
        ajax.send(param);

      }
    }
  }
  function deleteAccessForUser(event_obj) {
    ajax.open("POST", '/admin/xhr_delete_user', true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var login = event_obj.target.id;
    var id_desk = document.querySelector(".current-desk").id;
    var param = "login=" + login + "&id_desk=" + id_desk;
    ajax.onreadystatechange = function () {
      if (ajax.readyState !== 4) {
        //document.getElementById('preloader').style = "display:block";
      } else {
        //document.getElementById('preloader').style = "display:none";
        var currentLi = event_obj.target;
        currentLi.remove();
        ///////// delete desk
        //Если нет доступных юзеров к таблице
        // следовательно она никому не нужна
        // тогда удаляем
        if (document.querySelector(".current-desk ul li") == null) {
          deleteDesk();
        }
      }
    }
    ajax.send(param);
  }
  function clickUsersList() {
    var allUsers_li = document.querySelectorAll(".all-users ul li");
    for (let index = 0; index < allUsers_li.length; index++) {
      allUsers_li[index].onclick = function (event_obj) {
        console.log("clickUsersList");
        var currentDesk_li = document.querySelectorAll(".current-desk ul li");
        for (var i = 0, count = 0; i < currentDesk_li.length; i++) {
          // console.log(event_obj.target.id);
          // console.log(currentDesk_li[i].innerHTML);
          // Если CurrentDesk не выбран, сравниваться с пустым полем не будет т.к цикл не пустит
          if (event_obj.target.id != currentDesk_li[i].innerHTML) {
            count++;
          }
        }
        if (count == currentDesk_li.length /*|| currentDesk_li.length == 0*/) {
          // Такого юзера нет, мы открываем ему доступ к таблице
          //console.log('такого юзера нет')
          // передать логин и id доски
          // и нарисовать в current desk новый li
          ajax.open("POST", '/admin/xhr_add_user', true);
          ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          var login = event_obj.target.id;
          var id_desk = document.querySelector(".current-desk").id;
          var param = "login=" + login + "&id_desk=" + id_desk;
          ajax.onreadystatechange = function () {
            if (ajax.readyState !== 4) {
              //document.getElementById('preloader').style = "display:block";
            } else {
              //document.getElementById('preloader').style = "display:none";
              var ul = document.querySelector(".current-desk ul");
              var li = document.createElement('li');
              li.innerHTML = login;
              ul.append(li);
            }
          }
          ajax.send(param);
        }

      }

    };
  }
  function deleteDesk() {
    ajax.open("POST", '/admin/xhr_delete_desk', true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var id_desk = document.querySelector(".current-desk").id;
    var param = "id_desk=" + id_desk;
    ajax.onreadystatechange = function () {
      if (ajax.readyState !== 4) {
        //document.getElementById('preloader').style = "display:block";
      } else {
        //document.getElementById('preloader').style = "display:none";
        var currentUl = document.querySelector(".all-group-desks ul");
        // Переберем все li, внутри ul
        for (let i = 0; i < currentUl.children.length; i++) {
          var currentLi = currentUl.children[i];
          if (currentLi.id == id_desk) {
            currentLi.remove();
          }
        }
      }
    }
    ajax.send(param);
  }
  function clickCreateDesk() {
    document.querySelector(".createDeskButton").onclick = function () {
      var nameDesk = document.querySelector(".createDeskInput").value;
      //Если есть хоть какая-то запись в input, тогда ...
      if (nameDesk) {
        ajax.open("POST", '/admin/xhr_create_desk', true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var param = "nameDesk=" + nameDesk;
        ajax.onreadystatechange = function () {
          if (ajax.readyState !== 4) {
            //document.getElementById('preloader').style = "display:block";
          } else {
            //document.getElementById('preloader').style = "display:none";

            // получаем от серва id только что созданной таблицы
            var id_desk = ajax.responseText;
            var li = document.createElement('li');
            li.innerHTML = nameDesk;
            li.id = id_desk;
            document.querySelector(".all-group-desks ul").append(li);
            clickDesks();
          }
        }
        ajax.send(param);
      }
    }
  }
}