<?php

$guess = md5(date("Y-m-d hh:mm:ss") . rand(-300,300) . time());
$guess = substr($guess, 0, 8);
//	if (ereg("[a-z]",$guess) && ereg("[A-Z]",$guess) && ereg("[0-9]",$guess))
//	{
		print substr($guess,0,8);
//	}
