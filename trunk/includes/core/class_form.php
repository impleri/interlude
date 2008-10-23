<?php
/* file: system/class_form
** begin: 01/01/2006
** $Revision$
** $Date$
**
** description: form classes
**/

if (!defined('PLAY_MUSIC'))
{
	die('Start from the beginning.');
}

abstract class field
{
	function __construct()
	{

	}

	function debug()
	{
		return $this->__construct();
	}
}

class input
{
	function __construct()
	{
		$this->type = 'input';
		$this->name = '';
	}
}

class form
{
	private var $name;
	private var $action;
	private var $method;
	private var $fields = array();

	public function __construct($name, array &$fields, $action='', $method='POST')
	{
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;

		foreach ($fields as $field => $data)
		{
			$this->add_field($field, $data);
		}
		return true;
	}

	public function add_field($field, $data)
	{
		switch ($data['type'])
		{
			case 'blah':
				$this->fields[$field] = new input_blah($field, $data['blah1'], $data['blah2']);
				break;
		}
		return true;
	}

	private function check()
	{
		if (is_empty($fields))
		{
			$this->error = true;
			$this->error_msg[] = 'FIELDS_NOT_SET';
		}

		foreach ($this->fields as $field)
		{
			if (!$field->check())
			{
				$this->error = true;
				$this->error_msg[] = $field->get_error();
			}
		}

		return !$this->error;
	}

	private function validate()
	{
		foreach ($this->fields as $field)
		{
			if (!$field->validate())
			{
				$this->error = true;
				$this->error_msg[] = $field->get_error();
			}
		}

		return !$this->error;
	}

	private function display_errors()
	{

	}

	private function display()
	{
		if ($this->error)
		{
			$this->display_errors();
		}

		// else
	}

	public function reset()
	{
		foreach ($this->fields as $field)
		{
			$field->reset();
		}
	}

	public function process()
	{
		if ($this->check())
		{
			$this->validate();
		}
		$this->display();
		$this->reset();
		return true;
	}
}

?>