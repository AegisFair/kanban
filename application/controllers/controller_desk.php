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
        if($_GET['idDesk']){
             $data['deskANDallColumns']=$this->model->display_desk($data['desks'],$_GET['idDesk']);
        }else{
            $data['deskANDallColumns']=$this->model->display_desk($data['desks']);
        }

        $this->view->generate('desk_view.php','template_view.php',$data);
    }
    function action_xhr_save(){
        $this->model->saveChanges($_POST);
    }
    function action_xhr_create(){
        echo $this->model->createTextarea($_POST);
    }
    function action_xhr_delete(){
        $this->model->deleteTextarea($_POST);
    }
    function action_xhr_order(){
        $this->model->orderTextarea($_POST);
    }
}
?>