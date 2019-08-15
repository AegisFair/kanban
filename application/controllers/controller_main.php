<?php
class Controller_Main extends Controller {
    function __construct()
	{
		$this->model = new Model_Main();
		$this->view = new View();
    }
    function action_index ($data=null){
        $this->view->generate('main_view.php','template_view.php', $data);
    }
    function action_check () {
        $data=$this->model->checkUser();
        if ($data===true) {
            header("Location: http://".$_SERVER['HTTP_HOST']."/desk/");
        }else if($data==="admin"){
            header("Location: http://".$_SERVER['HTTP_HOST']."/admin/");
        }
        else {
            $this->action_index($data);
        }
    }
    function action_reg () {
        $data=$this->model->Registration();
        $this->action_index($data);
    }
}