<?php
/**
 * generic autoform field object
 *
 * @package interlude
 * @subpackage framework
 * @copyright Christopher Roussel <christopher@impleri.net>
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

class ilParentAutoform {
	private $name;
	private $action;
	private $method;
	private $fields = array();

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
