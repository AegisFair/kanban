<?php
class Model_Main extends Model {
    public function noEmptyField(){
        if (($_GET['login'] !=='') && ($_GET['password'] !=='')){
            return true;
        }else{
            return false;
        }
    }
    public function checkUser(){
        if($this->noEmptyField()){
                // подключаемся к серверу
            global $host,$user,$password_db,$database;
            $link = mysqli_connect($host, $user, $password_db, $database) 
                or die("Ошибка " . mysqli_error($link));

            $login=$_GET['login'];
            $password=$_GET['password'];

            $query="SELECT*FROM users WHERE login = '$login' and password='$password' ";
            $result=mysqli_query($link,$query) or die (mysqli_error($link));
            // если запрос выполнился успешно и есть такой пользователь, то создаем куку
            if (mysqli_num_rows($result)==1) {
                // проверка на статус - админ/юзер
                $info_about_user=mysqli_fetch_assoc($result)['status'];
                $status=$info_about_user['status'];
                if ($status==1) {
                    // Это админ
                    mysqli_close($link); //Закрыли соединение с mySQL
                    setcookie('username',$login);
                    return "admin";
                }else{
                    setcookie('username',$login);
                    mysqli_close($link); //Закрыли соединение с mySQL
                    return true;
                }
            }
            else {
                return "Неверный логин/пароль!";
                mysqli_close($link); //Закрыли соединение с mySQL
            }
        }else{
            return "Пропущено поле логин/пароль!";
        }
    }
    function Registration(){
        if($this->noEmptyField()){
            global $host,$user,$password_db,$database;
            $link = mysqli_connect($host, $user, $password_db, $database) 
            or die("Ошибка " . mysqli_error($link));

            $login=$_GET['login'];
            $password=$_GET['password'];
            
            $query="SELECT*FROM users WHERE login = '$login'";
            $result=mysqli_query($link,$query) or die (mysqli_error($link));

            if (mysqli_num_rows($result)==1){
                // Такой login уже занят!
                mysqli_close($link); //Закрыли соединение с mySQL
                return "Данный логин уже занят!";
            }
            else{
                // Добавляем запись в таблицу users
                $query="INSERT INTO users (login, password, status) VALUES ('$login','$password','2')";
                $result=mysqli_query($link,$query) or die (mysqli_error($link));
                    // И узнаем ее id
                    $idUser=mysqli_insert_id($link);
                //Создаем таблицу для нового юзера
                $nameDesk=$login."_desk";
                $query="INSERT INTO kanban_desk (name_desk) VALUES ('$nameDesk')";
                $result=mysqli_query($link,$query) or die (mysqli_error($link));
                    // И узнаем ее id
                    $idDesk=mysqli_insert_id($link);
                // Связываем таблицу с юзером
                $query="INSERT INTO accessdesk (id_user, id_desk) VALUES ('$idUser','$idDesk')";
                $result=mysqli_query($link,$query) or die (mysqli_error($link));
                mysqli_close($link); //Закрыли соединение с mySQL
                return "Вы зарегистрированы!";
            }
        }
        else{
            return "Пропущено поле логин/пароль!";
        }
    }
}
?>