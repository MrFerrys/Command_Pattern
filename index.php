<?php
/**
 * Command Pattern Concept
 */
//EXECUTION TIME
ini_set('max_execution_time', '0');

//ERROR REPORTING
//error_reporting(0);
error_reporting(E_ALL);

//LIBS
require_once(dirname(__FILE__).'/lib/cmdlib.php');

/**
 *  MAIN
 */
try{
	echo "---------------- SIMPLE CMD \r\n";
	$invoker = new Invoker();
    $receiver = new SimpleCommand('Hello world');
    $invoker->setCommand($receiver);
    $invoker->run();
	
	echo "---------------- COMPLEX + SIMPLE CMD \r\n";
    $invoker = new Invoker();
    $receiver = new SimpleCommand('Hello world');
    $invoker->setCommand(new ComplexCommand($receiver,"Param_A","Param_B"));
    $invoker->run();

	echo "---------------- NESTED COMPLEX CMD \r\n";
	$invoker        =   new Invoker();
    $receiver       =   new SimpleCommand('Hello worlddd');
	$complexCommand =   new ComplexCommand($receiver,'PARAM_AA','PARAM_BB');
    $invoker->setCommand(new ComplexCommand($complexCommand,'PARAM_A','PARAM_B',false));
    $invoker->run();
}catch(\Exception $e){
    echo $e->getMessage();
}
?>

