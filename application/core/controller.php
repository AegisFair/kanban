<?php
class Controller{
    public $model;
	public $view;
	
	function __construct()
	{
		$this->view = new View();
	}
	// Метод action_index — это действие, вызываемое по умолчанию, его мы перекроем при реализации классов потомков.
	function action_index()
	{
	}
}