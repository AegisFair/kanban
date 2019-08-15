<aside class="myCanbanDesks">
    <h2>Доступные канбан доски:</h2>
    <ul class="accessDesk">
        <?php 
            if($data["desks"]!==null){
                // нужно удалить все вхождения после знака вопроса в URI
                    // а это пока костыль...
                $_SERVER['REQUEST_URI']="/desk/";
                // echo $_SERVER['REQUEST_URI'];
                for ($i=0; $i <count($data["desks"]) ; $i++) { 
                    $nameDesk=$data['desks'][$i]['name_desk'];
                    $idDesk=$data['desks'][$i]['id_desk'];
                    echo "<li><a href=".$_SERVER['REQUEST_URI']."index/?idDesk=".$idDesk.">".$nameDesk."</a></li>";
                }
            }
            else{
                echo "<li>Нет доступных досок!</li>";
            }
        ?>
    </ul>
    <!-- <form class="createDesk" action="index.php" method="GET">
        <input type="text" name="newDesk">
        <button type="submit">Создать</button>
    </form> -->
    <footer>
        <section id='preloader'>
            <div class="sk-wave">
                <div class="sk-rect sk-rect-1"></div>
                <div class="sk-rect sk-rect-2"></div>
                <div class="sk-rect sk-rect-3"></div>
                <div class="sk-rect sk-rect-4"></div>
                <div class="sk-rect sk-rect-5"></div>
            </div>
        </section>
    </footer>
</aside>
<table class="kanban_table" id="<?php echo(isset($data['deskANDallColumns']['id_desk'])) ? $data['deskANDallColumns']['id_desk'] : null; ?>">
    <colgroup>
        <col>
        <col>
        <col>
    </colgroup>
    <thead>
        <tr>
            <th class="todo">ToDo</th>
            <th class="doing">Doing</th>
            <th class="done">Done</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Управляющие элементы для textarea
    $controls="<span class='delete'>X</span>
        <span class='arrow-up'>&#9650;</span>
        <span class='arrow-down'>&#9660;</span>";
    // Узнаем сколько строк <tr> необходимо
        //для этого ищем колонку, в котором больше всех элементов
            // и берем данное число за основу
        $maxNumberOfElm=0;
        if(isset($data['deskANDallColumns']['columns'])){
            foreach ($data['deskANDallColumns']['columns'] as $value) {
                if ($maxNumberOfElm<count($value)) {
                    $maxNumberOfElm=count($value);
                }
            }
        }
        for ($i=0; $i < $maxNumberOfElm; $i++) {
            // значение поля field для колонки column_do
            @    $columnDoFieldDo=$data['deskANDallColumns']['columns']['column_do'][$i]['field'];
            @    $columnDoIdTextarea=$data['deskANDallColumns']['columns']['column_do'][$i]['id_textArea'];
            // значение поля field для колонки column_doing
            @    $columnDoingFieldDoing= ($data['deskANDallColumns']['columns']['column_doing'][$i]['field']);
            @   $columnDoingIdTextarea=$data['deskANDallColumns']['columns']['column_doing'][$i]['id_textArea'];
            // значение поля field для колонки column_done
            @    $columnDoneFieldDone=$data['deskANDallColumns']['columns']['column_done'][$i]['field'];
            @    $columnDoneIdTextarea=$data['deskANDallColumns']['columns']['column_done'][$i]['id_textArea'];
            //php (isset($data['deskANDallColumns']['column_do'][$i])) ? ("<textarea name=$columnDoIdTextarea".">".$columnDoFieldDo."</textarea>") : "null"; 
            ?>
            <tr>
                <td class="to_do">
                    <?php
                        echo (isset($data['deskANDallColumns']['columns']['column_do'][$i])) ? "<textarea class='to_do' name=$columnDoIdTextarea".">".$columnDoFieldDo."</textarea>".$controls : null ;
                    ?>
                </td>
                <td class="to_doing">
                    <?php echo (isset($data['deskANDallColumns']['columns']['column_doing'][$i])) ? "<textarea class='to_doing' name=$columnDoingIdTextarea".">".$columnDoingFieldDoing."</textarea>".$controls : null; ?>
                </td>
                <td class="to_done">
                    <?php echo (isset($data['deskANDallColumns']['columns']['column_done'][$i])) ? "<textarea class='to_done' name=$columnDoneIdTextarea".">".$columnDoneFieldDone."</textarea>".$controls : null; ?>
                </td>
            </tr>   
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td>
            <button class="to_do">+</button>
            </td>
            <td>
            <button class="to_doing">+</button>
            </td>
            <td>
            <button class="to_done">+</button>
            </td>
        </tr>
    </tfoot>
    <!-- <tr>
        <td>
            <textarea name="ToDo[]"></textarea>
        </td>
        <td>

        </td>
        <td>

        </td>
    </tr> -->
</table>