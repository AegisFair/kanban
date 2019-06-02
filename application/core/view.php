<?php
// $OBJ_view=new View;
class View {
    //public $template_view; // здесь можно указать общий вид по умолчанию.
    function generate($content_view, $template_view, $data = null)
	{
		/*
		if(is_array($data)) {
			// преобразуем элементы массива в переменные
			extract($data);
		}
		*/
        
        // Функцией include динамически подключается общий шаблон (вид), внутри которого будет встраиваться вид
            // для отображения контента конкретной страницы
		include 'application/views/'.$template_view;
    }
}
?>