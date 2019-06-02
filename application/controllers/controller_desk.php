<?php
class Controller_Desk extends Controller {
    function __construct()
	{
		$this->model = new Model_Desk();
		$this->view = new View();
    }
    function action_index()
	{
        $data=[];
        // Массива для вывода списка доступных досок
        $data['desks']=$this->model->allDesks();
        
        $data['allColumns']=$this->model->display_desk($data['desks']);
        //здесь же проверяем нажатие кнопки newDesk... 

        // 
        $this->view->generate('desk_view.php','template_view.php',$data);
    }
    function action_xhr(){
        $this->model->saveChanges($_POST);
    }
}
?>