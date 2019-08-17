<?php
class Controller_Admin extends Controller {
    function __construct()
	{
		$this->model = new Model_Admin();
		$this->view = new View();
    }
    function action_index () //($data=null)
	{
        $allUsers=$this->model->loginUsersList();
        //выводим абсолютно все доски доступные в системе
        $allGroupDesks=$this->model->groupsDesks();
        $data['allUsers']=$allUsers;
        $data['allGroupDesks']=$allGroupDesks;
        // echo "<pre>";
        // print_r($data);
        $this->view->generate('admin_view.php','template_view.php', $data);
    }
    function action_xhr_click_desks(){
        //header('Content-Type: text/xml');
        //echo json_encode($this->model->allUsersForCurrentDesk());
        echo json_encode($this->model->allUsersForCurrentDesk());
    }
    function action_xhr_add_user(){
        $this->model->addUsersForCurrentDesk();
    }
    function action_xhr_delete_user(){
        $this->model->deleteUsersForCurrentDesk();
    }
    function action_xhr_delete_desk(){
        $this->model->deleteDesk();
    }
    function action_xhr_create_desk(){
        echo $this->model->createDesk();
    }
}
?>