<?php
class Model_Desk extends Model {
    // Все доступные доски для юзера
    function allDesks(){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        ////////////////////
        ////////////////////
        
        // 1) Узнаем id_user'a через его логин
        $query="SELECT id_user FROM users WHERE login = '$_COOKIE[username]'";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $data[]=mysqli_fetch_assoc($result);
        
        // Нумерация в массиве начинается с нуля
            // функция выше вернула одну строку=> ее индекс [0]
                // в ней получаем по индексу 'id_user' число
        
        //Цикл не нужен т.к у нас должна быть
            // только ОДНА строка, соответствующая логину юзера
                //  for ($data=[];$row=mysqli_fetch_assoc($result);$data[]=$row);
        
        $idUser=$data[0]['id_user'];
        // Пункт 1 - готов!
        ////////////////////
        ////////////////////
        // 2) По id_user'a в таблице ACCESSDESK мы смотрим, сколько ему доступна для работы Канбан-досок
            // нам нужен их id_desk!
        $query="SELECT id_desk FROM accessdesk WHERE id_user = '$idUser'";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        //Если ли вообще доски для нашего юзера 
        if((mysqli_num_rows($result)!==0)){
            // если есть
            // Т.к Канбан-досок может быть несколько, нам нужен массив, куда все это дело поместим
            for ($massIdDeskForUser=[];$row=mysqli_fetch_assoc($result);$massIdDeskForUser[]=$row);
            
            // !!!!! в $massIdDeskForUser структура такая : [номер_строки][id_desk]
            // Пункт 2 - готов!
            ////////////////////
            ////////////////////
            // 3) Получить имя этих Канбан-досок, опять же их м.б несколько
            // создадим под имена Канбан-досок свой массив
            $query=""; 
            for ($i=0; $i < count($massIdDeskForUser) ;$i++) { 
                $query .=" id_desk = ". $massIdDeskForUser[$i]["id_desk"].' OR';
                // $nameDesks[]=;
            }
            // убираем крайнию OR
            $query=preg_replace("/OR$/", '', $query);
            $query="SELECT id_desk,name_desk FROM kanban_desk WHERE".$query;
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            for ($nameDesks=[];$row=mysqli_fetch_assoc($result);$nameDesks[]=$row){}
            
            
            // echo "<pre style='font-size:16px'>";
            // var_dump ($nameDesks);
            mysqli_close($link); //Закрыли соединение с mySQL
            // $nameDesks - массив вида
                /*array(2) {
                    [0]=>
                    array(2) {
                        ["id_desk"]=>
                        string(1) "1"
                        ["name_desk"]=>
                        string(8) "alexDesk"
                    }
                    [1]=>
                    array(2) {
                        ["id_desk"]=>
                        string(1) "2"
                        ["name_desk"]=>
                        string(9) "googleDev"
                    }
                }*/
            return $nameDesks;
        }
        else{
            mysqli_close($link); //Закрыли соединение с mySQL
            return null;
        }
    }
    function display_desk($massDesks){
        global $host,$user,$password_db,$database;
        // Из массива со столами, выбираем id_desk первого стола
        if($massDesks==null){
            return [];
        }
        else{
            $id_desk=$massDesks[0]['id_desk'];
            $allColumnsForDesk=[];
            // Запрос к таблице column_do
                // узнаем сколько textarea в данном столбце
            $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
            $query="SELECT field,id_textArea, field_order FROM column_do WHERE id_desk=".$id_desk;
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            for ($to_do=[];$row=mysqli_fetch_assoc($result);$to_do[]=$row){}
            $allColumnsForDesk['column_do']=$to_do;    
            // Запрос к таблице column_doing
                // узнаем сколько textarea в данном столбце
            $query="SELECT field,id_textArea, field_order FROM column_doing WHERE id_desk=".$id_desk;
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            for ($to_doing=[];$row=mysqli_fetch_assoc($result);$to_doing[]=$row){}
            $allColumnsForDesk['column_doing']=$to_doing;    
            // Запрос к таблице column_done
                // узнаем сколько textarea в данном столбце
                $query="SELECT field,id_textArea, field_order FROM column_done WHERE id_desk=".$id_desk;
                $result=mysqli_query($link,$query) or die (mysqli_error($link));
                for ($to_done=[];$row=mysqli_fetch_assoc($result);$to_done[]=$row){}
                $allColumnsForDesk['column_done']=$to_done;
            mysqli_close($link); //Закрыли соединение с mySQL            
            //////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////
            // $to_do массив вида
            /*Array
            (
                [0] => Array
                    (
                        [field] => test_запись в textAreaку
                        [id_textArea] => 1
                        [field_order] => 0
                    )
            
                [1] => Array
                    (
                        [field] => еще одна запись!
                        [id_textArea] => 2
                        [field_order] => 1
                    )
            
            )*/
            //////////////////////////////////////////////////////////////////////////
            // $allColumnsForDesk массив вида
            /*
                 Array
                (
                    [column_do] => Array
                    (
                        [0] => Array
                        (
                            [field] => test_запись в textAreaку
                            [id_textArea] => 1
                            [field_order] => 0
                        )
                    )
                    [column_doing] => Array
                    (
                        [0] => Array
                        (
                            [field] => test_запись в textAreaку
                            [id_textArea] => 1
                            [field_order] => 0
                        )
                    )
                    [column_done] => Array
                    (
                        [0] => Array
                        (
                            [field] => test_запись в textAreaку
                            [id_textArea] => 1
                            [field_order] => 0
                        )
                    )
                )
            */
            //////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////
            // Необходимо сориторовать элемент по field_order'y (по возрастанию)
            // запись вида &$currentColumn - создает ссылку на значения массива
            foreach ($allColumnsForDesk as &$currentColumn) {
                usort($currentColumn, function($a, $b){
                    return $a['field_order']-$b['field_order'];
                });
            }
            // echo "<pre style='font-size:16px'>";
            // print_r($allColumnsForDesk);
            // echo "</pre>";
            // Возвращаем сортированный массив по field_order'у
            return $allColumnsForDesk;
        }
    }
    function saveChanges($arrayValues){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
        // Сохранение внесенных изменений textarea'йки в соответствующим столбце (соответствующей таблице) 
            // Узнаем сначала таблицу в которую вносим изменения
        switch ($arrayValues['column']) {
            case 'to_do':
                $table_column="column_do";
                break;
            case 'to_doing':
                $table_column="column_doing";
                break;
            case 'to_done':
                $table_column="column_done";
                break;
        }
            // Обновим соответствующую textare'йку
            $query="UPDATE $table_column SET field='".$arrayValues['value']."' WHERE id_textArea=".$arrayValues['id_textarea'];
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
        // print_r($query);
            // Отдельно будем передавать значения для порядка order
    }
}
?>