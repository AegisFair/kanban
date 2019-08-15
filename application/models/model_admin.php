<?php
class Model_Admin extends Model {
    function loginUsersList(){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        // Узнаем сколько всего юзеров и выводим их
        $query="SELECT login FROM users";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        //$data[]=mysqli_fetch_assoc($result);
        for ($data=[];$row=mysqli_fetch_assoc($result);$data[]=$row);
        return $data;
    }
// Цель этой функции взять id_desk и name_desk
    function groupsDesks(){
        //Доски доступны более чем одному юзеру
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        // Формируем запрос к бд
        $query="SELECT id_desk FROM (SELECT id_desk,COUNT(*) as num FROM accessdesk GROUP BY id_desk) as t1 WHERE num > 1";
        // отправка запроса
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        // в $list_id_gr_desks храниться список id групповых досок
        for ($list_id_gr_desks=[];$row=mysqli_fetch_assoc($result);$list_id_gr_desks[]=$row);
        
        $query="";//Первая стадия формирования запроса
        for ($index=0; $index < count($list_id_gr_desks); $index++) { 
            $query.=" id_desk=".$list_id_gr_desks[$index]["id_desk"]. " OR";    
        }
        $query=preg_replace("/OR$/", '', $query);
        // Дополняем второй стадией
        $query="SELECT id_desk,name_desk FROM kanban_desk WHERE ".$query;
        // 
        // отправка запроса
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        
        for ($data=[];$row=mysqli_fetch_assoc($result);$data[]=$row);

        return $data;
    }
// Обработка Ajax'сов
    //клика на desks
    function allUsersForCurrentDesk(){
        $id_desk=$_POST['id_desk'];
        // Connect к бд
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        // в accessdesk по id_desk узнаем кол-во доступных ей юзеров
        $query="SELECT login FROM (SELECT id_user FROM accessdesk WHERE id_desk=$id_desk) as t1 LEFT JOIN users USING(id_user)";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        for ($data=[];$row=mysqli_fetch_assoc($result);$data[]=$row);
        return $data;
        //print_r($data);
    } 
}
?>