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
    function display_desk($massDesks,$id_desk=null){
        global $host,$user,$password_db,$database;
        if($massDesks==null){
            return [];
        }
        else{
            if($id_desk==null){
                // Если id_desk не выбран, по умолчанию выбираем первую попавшуюся
                $id_desk=$massDesks[0]['id_desk'];
            }
            $allColumnsForDesk=[];
            // Запрос к таблице column_do
                // узнаем сколько textarea в данном столбце
            $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
            $query="SELECT field,id_textArea, field_order FROM column_do WHERE id_desk=".$id_desk;
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            for ($to_do=[];$row=mysqli_fetch_assoc($result);$to_do[]=$row){}
            $allColumnsForDesk['columns']['column_do']=$to_do;    
            // Запрос к таблице column_doing
                // узнаем сколько textarea в данном столбце
            $query="SELECT field,id_textArea, field_order FROM column_doing WHERE id_desk=".$id_desk;
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            for ($to_doing=[];$row=mysqli_fetch_assoc($result);$to_doing[]=$row){}
            $allColumnsForDesk['columns']['column_doing']=$to_doing;    
            // Запрос к таблице column_done
                // узнаем сколько textarea в данном столбце
                $query="SELECT field,id_textArea, field_order FROM column_done WHERE id_desk=".$id_desk;
                $result=mysqli_query($link,$query) or die (mysqli_error($link));
                for ($to_done=[];$row=mysqli_fetch_assoc($result);$to_done[]=$row){}
                $allColumnsForDesk['columns']['column_done']=$to_done;
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
                    [columns] => Array
                        (
                            [column_do] => Array
                                (
                                    [0] => Array
                                        (
                                            [field] => test_запись в texxtArweaкуfds
                                            [id_textArea] => 1
                                            [field_order] => 0
                                        )

                                    [1] => Array
                                        (
                                            [field] => еще одна запqись! Воиwстинуddfds
                                            [id_textArea] => 2
                                            [field_order] => 1
                                        )
                                )
                            [column_doing] => Array
                                (
                                    [0] => Array
                                        (
                                            [field] => test_dwoing_1
                                            [id_textArea] => 2
                                            [field_order] => 1
                                        )

                                    [1] => Array
                                        (
                                            [field] => hello3wwdvfdfsd
                                            [id_textArea] => 1
                                            [field_order] => 5
                                        )

                                )
                            [column_done] => Array
                                (
                                    [0] => Array
                                        (
                                            [field] => test_done_1x
                                            [id_textArea] => 2
                                            [field_order] => 1
                                        )

                                    [1] => Array
                                        (
                                            [field] => test_donvcdscxezxffdsd
                                            [id_textArea] => 1
                                            [field_order] => 5
                                        )
                                )
                        )
                    [id_desk] => 1
                )
            */
            //////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////
            // Необходимо сориторовать элемент по field_order'y (по возрастанию)
            // запись вида &$currentColumn - создает ссылку на значения массива
            foreach ($allColumnsForDesk['columns'] as &$currentColumn) {
                usort($currentColumn, function($a, $b){
                    return $a['field_order']-$b['field_order'];
                });
            }
            
            // Добавляем id стола у которого взяты содержимое всех колонок
            $allColumnsForDesk['id_desk']=$id_desk;
            // Возвращаем сортированный массив по field_order'у
            // echo "<pre style='font-size:10px'>";
            // print_r($allColumnsForDesk);
            // echo "</pre>";
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
    function createTextarea($arrayValues){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
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
        // Сперва узнаем максимальный field_order в таблице $table_column
            // чтобы новая textarea имела на +1 больше порядок
        $query="SELECT MAX(field_order) FROM $table_column WHERE id_desk='".$arrayValues['id_desk']."'";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $oldFieldOrder=mysqli_fetch_row($result)[0];
        $newfieldOrder=$oldFieldOrder+1;
        // Создаем соответствующую textare'йку
        $query="INSERT INTO $table_column (id_desk,field_order) VALUES ('".$arrayValues['id_desk']."','".$newfieldOrder."')";
       
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $id_textArea=mysqli_insert_id($link);
        return $id_textArea;
    }
    function deleteTextarea($arrayValues){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));
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
        $query="DELETE FROM $table_column WHERE id_textArea='".$arrayValues['id_textarea']."'";
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
    }
    function orderTextarea($arrayValues){
        global $host,$user,$password_db,$database;
        $link = mysqli_connect($host, $user, $password_db, $database);
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
        // Определим тип изменения порядка arrow-up или arrow-down
            // Для arrow-up
        if(isset($arrayValues['id_textarea_previous'])){
            // Узнаем field-order у textare's
        $query="SELECT field_order FROM $table_column WHERE id_textarea=".$arrayValues["id_textarea_current"];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $currentID = mysqli_fetch_assoc($result)['field_order'];
        
        $query="SELECT field_order FROM $table_column WHERE id_textarea=".$arrayValues["id_textarea_previous"];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $previousID=mysqli_fetch_assoc($result)['field_order'];
        
            //Поменяем местами field_order textarea's 
        $query="UPDATE $table_column SET field_order='".$currentID."' WHERE id_textArea=".$arrayValues['id_textarea_previous'];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="UPDATE $table_column SET field_order='".$previousID."' WHERE id_textArea=".$arrayValues['id_textarea_current'];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        }
        // Для arrow_down
        else if(isset($arrayValues['id_textarea_next'])){
                // Узнаем field-order у textare's
        $query="SELECT field_order FROM $table_column WHERE id_textarea=".$arrayValues["id_textarea_current"];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $currentID = mysqli_fetch_assoc($result)['field_order'];
        
        $query="SELECT field_order FROM $table_column WHERE id_textarea=".$arrayValues["id_textarea_next"];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        $nextID=mysqli_fetch_assoc($result)['field_order'];
        
            //Поменяем местами field_order textarea's 
        $query="UPDATE $table_column SET field_order='".$currentID."' WHERE id_textArea=".$arrayValues['id_textarea_next'];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));

        $query="UPDATE $table_column SET field_order='".$nextID."' WHERE id_textArea=".$arrayValues['id_textarea_current'];
        $result=mysqli_query($link,$query) or die (mysqli_error($link));
        }
    }
        
}
?>