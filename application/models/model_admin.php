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
        //Доски доступны более чем одному юзеру! Но это было раньше, сейчас список всех досок выводим!
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        // Формируем запрос к бд
        // Раньше
        //$query="SELECT id_desk FROM (SELECT id_desk,COUNT(*) as num FROM accessdesk GROUP BY id_desk) as t1 WHERE num > 1";
        // сейчас
        $query="SELECT id_desk,name_desk FROM kanban_desk";
        // отправка запроса
        //$result=mysqli_query($link,$query) or die (mysqli_error($link));
        // в $list_id_gr_desks храниться список id групповых досок
        // for ($list_id_gr_desks=[];$row=mysqli_fetch_assoc($result);$list_id_gr_desks[]=$row);
        /*
        $query="";//Первая стадия формирования запроса
        for ($index=0; $index < count($list_id_gr_desks); $index++) { 
            $query.=" id_desk=".$list_id_gr_desks[$index]["id_desk"]. " OR";    
        }
        $query=preg_replace("/OR$/", '', $query);
        // Дополняем второй стадией
        $query="SELECT id_desk,name_desk FROM kanban_desk WHERE ".$query;
        // 
        */
        // отправка запроса
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        
        for ($data=[];$row=mysqli_fetch_assoc($result);$data[]=$row);
        mysqli_close($link); //Закрыли соединение с mySQL
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
        mysqli_close($link); //Закрыли соединение с mySQL
        //if(empty($data)){$data="";}
        return $data;
        //print_r($data);
    }
    function addUsersForCurrentDesk(){
        $login=$_POST['login'];
        $id_desk=$_POST['id_desk'];
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        $query="SELECT id_user FROM users WHERE login=\"$login\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $data=mysqli_fetch_assoc($result);
        //$id_user = Array ( [id_user] => 11 )
        $id_user=$data['id_user'];
        // Открываем доступ к таблице
        $query="INSERT INTO accessdesk (id_user,id_desk) VALUES (\"$id_user\",\"$id_desk\")";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        mysqli_close($link); //Закрыли соединение с mySQL
    }
    function deleteUsersForCurrentDesk(){
        $login=$_POST['login'];
        $id_desk=$_POST['id_desk'];
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        $query="SELECT id_user FROM users WHERE login=\"$login\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $data=mysqli_fetch_assoc($result);
        print_r ($data);
        //$id_user = Array ( [id_user] => 11 )
        $id_user=$data['id_user'];
        // Закрываем доступ к таблице
        $query="DELETE FROM accessdesk WHERE id_user=\"$id_user\" AND id_desk=\"$id_desk\"";
        //print_r ($query);
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        mysqli_close($link); //Закрыли соединение с mySQL
    }
    function deleteDesk(){
        // Удаляем доску из таблицы kanban_desk
            // На всякий случай, удалим еще любое упоминание об этой доске
                // в таблицах accessdesk, column_do_doing_done
        $id_desk=$_POST['id_desk'];

        global $host,$user,$password_db,$database;
        
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        
        $query="DELETE FROM accessdesk WHERE id_desk=\"$id_desk\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="DELETE FROM column_do WHERE id_desk=\"$id_desk\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="DELETE FROM column_doing WHERE id_desk=\"$id_desk\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="DELETE FROM column_done WHERE id_desk=\"$id_desk\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="DELETE FROM kanban_desk WHERE id_desk=\"$id_desk\"";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        mysqli_close($link); //Закрыли соединение с mySQL
    }
    function createDesk(){
        $nameDesk=$_POST['nameDesk'];
        global $host,$user,$password_db,$database;
        
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        
        // обезвреживаем строку
        $nameDesk=mysqli_real_escape_string($link,$nameDesk);

        $query="INSERT INTO kanban_desk (name_desk) VALUES (\"$nameDesk\")";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        // id_desk только что созданной таблицы
        $currentId=mysqli_insert_id($link);

        mysqli_close($link); //Закрыли соединение с mySQL
        return $currentId;
    }
}
?>