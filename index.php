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
require_once(dirname(__FILE__).'/src/cmd.php');
use CommandPattern\Invoker as Invoker;
use CommandPattern\ComplexCommand as ComplexCommand;
use CommandPattern\SimpleCommand  as SimpleCommand;


/**                                                                        
 *  MAIN
 */
try{
	echo "---------------- SIMPLE CMD \r\n";
	$invoker  = new Invoker();
    $invoker->setVerboseLevel(VERBOSE_QUIET);
    $receiver = new SimpleCommand('Hello world');
    $receiver->setId($receiver->calculateId());
    $invoker->setCommand($receiver);
    $receiver->setStartingTime(microtime(true));
    $invoker->run();
    $receiver->setEndingTime(microtime(true));
    $duration = $receiver->getExecutionTime();
    echo "Execution Time : $duration secs. \r\n";
	
	echo "---------------- COMPLEX + SIMPLE CMD \r\n";
    $invoker = new Invoker();
    $invoker->setVerboseLevel(VERBOSE_DEBUG);
    $receiver = new SimpleCommand('Hello world');
    $receiver->setId($receiver->calculateId());
    $invoker->setCommand(new ComplexCommand($receiver,"Param_A","Param_B"));
    $invoker->run();
    //echo "ID: ".$receiver->getId()."\r\n";

	echo "---------------- NESTED COMPLEX CMD \r\n";
	$invoker        =   new Invoker();
    $invoker->setVerboseLevel(VERBOSE_QUIET);
    $receiver       =   new SimpleCommand('Hello world');
    $receiver->setId($receiver->calculateId());
	$complexCommand =   new ComplexCommand($receiver,'PARAM_AA','PARAM_BB');
    $complexCommand->setId($complexCommand->calculateId());
    $invoker->setCommand(new ComplexCommand($complexCommand,'PARAM_A','PARAM_B'));
    $invoker->run();
}catch(\Exception $e){
    echo $e->getMessage();
}
?>

