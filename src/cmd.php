<?php
namespace CommandPattern;
/**
 * Command pattern concept based on refactoring.guru and designpatterns solutions.
 * 
 * src:
 * https://refactoring.guru/es/design-patterns/command/php/example
 * https://designpatternsphp.readthedocs.io/en/latest/Behavioral/Command/README.html
 * 
 * @version 1.1.0
 * @author  Ferrys
 * 
 */
/**
 * Verbosity level.
 * 
 */
define("VERBOSE_QUIET"			,-1);//QUIET
define("VERBOSE_NORMAL"			,0);//NORMAL
//define("VERBOSE_LVL_1"			,1);//ALL INFO MESSAGES INCLUDING ERR.
define("VERBOSE_LVL_2"			,2);//ERR MESSAGES
define("VERBOSE_DEBUG"			,3);//ALL MESSAGES

/**
 * DEFINEs; Command statuses
 */
define("CMD_STATUS_CREATED"		,1);
define("CMD_STATUS_READY"		,2);
define("CMD_STATUS_RUNNING"		,3);
define("CMD_STATUS_BLOCKED"		,4);
define("CMD_STATUS_TERMINATED"	,5);
define("CMD_STATUS_WAITING"		,6);
define("CMD_STATUS_STOPPED"		,7);
define("CMD_STATUS_PAUSED"		,8);
define("CMD_STATUS_ERROR"		,9);
/**
 * Command interface.
 */
interface Command
{
	/**
	 * Executes the command
	 */
    public function execute();
	/**
	 * Calculates the command id.
	 */
	public function calculateId();
	/**
	 * Returns the command id.
	 */
	public function getId();
	/**
	 * Sets the command id.
	 */
	public function setId($id);
	/**
	 * Returns the current command status.
	 */
	public function getStatus();
	/**
	 * Sets the command status.
	 */
	public function setStatus($status);
	/**
	 * Sets the verbose level.
	 */
	public function setVerboseLevel($level);
	/**
	 * Sets starting time.
	 * i.e. startingTime    =   microtime(true);
	 */
	public function setStartingTime($time);
	/**
	 * Sets ending time.
	 * i.e. endingTime    =   microtime(true);
	 */
	public function setEndingTime($time);
	/**
	 * Calculates exec time in secs
	 * 
	 */
	public function getExecutionTime();
	/**
	 * Sets the progression in %
	 */
	public function setProgress($amount);
	/**
	 * Gets the progression in %
	 */
	public function getProgress();
	/**
	 * Prints the output depending on verbosity level.
	 * message is the message to print.
	 * level is the verbosity's level of the message.
	 */
	public function printOutput($message,$msgLevel);
}

/**
 * The Receiver classes contain some important business logic.
 */
abstract class Receiver
{
	/**
	 * aWaiting call; It waits til the action is terminated.
	 */
	abstract public function doAction();
	/**
	 * aSync call; Delegates the action to the command line system.
	 */
	abstract public function doActionAsync();

	/**
	 * Executes command in command line.
	 */
	protected function execInCmdLine(Command $cmd)
	{
		$strCmd   = array();
		$strCmd[] = "ini_set('max_execution_time', '0');";
		$strCmd[] = "error_reporting(E_ALL);";
		$strCmd[] = "require_once('".dirname(__FILE__)."/cmdlib.php');";
		$strCmd[] = "unserialize(base64_decode('".base64_encode(serialize($cmd))."'))->execute();";
		
		if(PHP_OS==="WINNT")
        {
            $command  = 'START /B "" php -r "'.implode("",$strCmd).'" > nul 2>&1';
        }
        if(PHP_OS==="Linux")
        {
			$command  = 'php -r "'.implode("",$strCmd).'" > /dev/null 2>&1 &';
        }
		pclose(popen($command,'w'));
	}
	/**
	 * Gets the invoker id. 
	 * Invoker in that case is the command that is calling this receiver
	 */
	abstract public function getInvokerId();
	/**
	 * Sets the invoker id.
	 * Invoker in that case is the command that is calling this receiver
	 */
	abstract public function setInvokerId($id);
}

/**
 * Some commands can implement simple operations on their own.
 * It works as command and Receiver.
 */
class SimpleCommand extends Receiver implements Command 
{
	/**
	 * @var param
	 */
    private $param;
	/**
	 * @var id stores the command id
	 */
	private $id;
	/**
	 * @var status stores the command status (1=>created,2=>ready,3=>running,4=>blocked,5=>terminated,6=>waiting,7=>stopped,8=>paused,9=>error)
	 */
	private $status;
	/**
	 * @var invokerId stores the invoker id
	 */
	private $invokerId;
	/**
	 * @var verboseLevel stores the verbose level
	 */
	private $verboseLevel;
	/**
	 * @var startingTime stores the time when command has started
	 */
	private $startingTime;
	/**
	 * @var endingTime stores the time when command has finished
	 */
	private $endingTime;
	/**
	 * @var progression stores the command progression.
	 */
	private $progression;

 
	/**
     * Simple commands can accept one or several params via the constructor.
     */
    public function __construct($param)
    {
        $this->param 		= 	$param;
		$this->id			=	-1;
		$this->invokerId	=	-1;
		$this->progression  =   0;
		$this->status		=	CMD_STATUS_CREATED;
		$this->verboseLevel =	VERBOSE_QUIET;
    }
	/**
	 * executes the command
	 */
    public function execute()
    {
		$this->status		=	CMD_STATUS_RUNNING;
		$this->printOutput("SimpleCommand: See, I can do simple things like printing (" . $this->param . ")\r\n",VERBOSE_NORMAL);
		$this->status		=	CMD_STATUS_TERMINATED;
    }
	/**
	 * aWaiting call
	 */
	public function doAction()
	{
		list($paramA,$paramB) 	= 	(func_num_args()>1)?func_get_args():array("","");
		$this->param	= 	"| >>aWait ($paramA , $paramB) |";
		$this->status	=	CMD_STATUS_READY;
		$this->execute();
	}
	/**
	 * aSync call
	 */
	public function doActionAsync()
	{
		list($paramA,$paramB) 	=	(func_num_args()>1)?func_get_args():array("","");
		$this->param	=	"| >>aSync ($paramA , $paramB ) |";
		$this->status	=	CMD_STATUS_READY;
		$this->execInCmdLine($this);
	}
	/**
	 * Calculates the id.
	 */
	public function calculateId()
	{
		return md5(base64_encode(serialize($this)));
	}
	/**
	 * Returns the id.
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * Returns the id.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	/**
	 * Returns the current command status.
	 */
	public function getStatus(){
		return $this->status;
	}
	/**
	 * Sets the command status.
	 */
	public function setStatus($status){
		$this->status=$status;
	}
	/**
	 * Returns the invoker id.
	 */
	public function getInvokerId(){
		return $this->invokerId;
	}
	/**
	 * Sets the invoker id.
	 */
	public function setInvokerId($id){
		return $this->invokerId;
	}
	/**
	 * Sets the verbose level.
	 */
	public function setVerboseLevel($level)
	{
		$this->verboseLevel = $level;
	}
	/**
	 * Sets starting time.
	 * i.e. startingTime    =   microtime(true);
	 */
	public function setStartingTime($time)
	{
		$this->startingTime=$time;
	}
	/**
	 * Sets ending time.
	 * i.e. endingTime    =   microtime(true);
	 */
	public function setEndingTime($time)
	{
		$this->endingTime = $time;
	}
	/**
	 * Calculates exec time in secs
	 */
	public function getExecutionTime()
	{
        $diff           =   (float)$this->endingTime-(float)$this->startingTime;
        $executionTime  =   $diff;
        /*if($minutes ==  true && abs($executionTime) > 0)
        {
            $executionTime = $executionTime/60;
        }*/
        return round($executionTime,2);
	}
	/**
	 * Sets the progression in %
	 */
	public function getProgress()
	{
		return $this->progression;
	}
	/**
	 * Gets the progression in %
	 */
	public function setProgress($amount)
	{
		$this->progression=$amount;
	}
	/**
	 * Prints the output depending on verbosity level.
	 */
	public function printOutput($message,$msgLevel)
	{
		/*
		//BEHAVIOUR 1
		if($this->verboseLevel == VERBOSE_QUIET){return;}
		if(	$this->verboseLevel  == VERBOSE_DEBUG 	|| 
			$this->verboseLevel  == $level 			|| 
			($this->verboseLevel == VERBOSE_LVL_1 && ($level==VERBOSE_NORMAL || $level==VERBOSE_LVL_2)) 
		)
		{
			echo $message;
		}*/
		//BEHAVIOUR 2
		if($this->verboseLevel >= $msgLevel)
		{
			echo $message;
		}

	}
}

/**
 * Commands can delegate more complex operations to other objects(Receivers).
 * Any class could serve as a Receiver.
 * It works as commandHandler and Receiver
 */
class ComplexCommand extends Receiver implements Command
{
    /**
     * @var Receiver
     */
    private $receiver;
	/**
     * @var Await flag
     */
    private $await;
    /**
     * @var a param
     */
    private $a;
	/**
	 * @var b param
	 */
    private $b;
	/**
	 * @var id stores the command id
	 */
	private $id;
	/**
	 * @var status stores the command status (1=>created,2=>ready,3=>running,4=>blocked,5=>terminated,6=>waiting,7=>stopped,8=>paused,9=>error)
	 */
	private $status;
	/**
	 * @var invokerId stores the invoker id
	 */
	private $invokerId;
	/**
	 * @var verboseLevel stores the verbose level
	 */
	private $verboseLevel;
	/**
	 * @var startingTime stores the time when command has started
	 */
	private $startingTime;
	/**
	 * @var endingTime stores the time when command has finished
	 */
	private $endingTime;
	/**
	 * @var progression stores the command progression.
	 */
	private $progression;

    /**
     * Complex commands can accept one or several receiver objects along with
     * any context data via the constructor.
     */
    public function __construct(Receiver $receiver, $a='', $b='',$await=true)
    {
        $this->receiver 	= 	$receiver;
		$this->await    	= 	$await;
        $this->a 			= 	$a;
        $this->b 			=	$b;
		$this->id			=	-1;
		$this->invokerId	=	-1;
		$this->progression  =   0;
		$this->status		=	CMD_STATUS_CREATED;
		$this->verboseLevel =	VERBOSE_QUIET;
    }
    /**
     * Commands can delegate to any methods of a receiver.
     */
    public function execute()
    {
		$this->status		=	CMD_STATUS_RUNNING;
		$this->printOutput("ComplexCommand: Complex stuff should be done by a receiver object.\r\n",VERBOSE_NORMAL);
		if($this->await === true)
		{
			$this->printOutput( "aWait call \r\n",VERBOSE_DEBUG);
			$this->receiver->setInvokerId($this->id);
			$this->receiver->doAction($this->a,$this->b);
			$this->status	=	CMD_STATUS_TERMINATED;
		}else{
			$this->printOutput( "aSync call \r\n",VERBOSE_DEBUG);
			$this->receiver->setInvokerId($this->id);
			$this->receiver->doActionAsync($this->a,$this->b);
		}
    }
	/**
	 * aWaiting call
	 */
	public function doAction()
	{
		list($paramA,$paramB) = (func_num_args()>1)?func_get_args():array("","");
		$this->a		=	$paramA;
		$this->b		=	$paramB;
		$this->status	=	CMD_STATUS_READY;
		$this->execute();
	}
	/**
	 * aSync call
	 */
	public function doActionAsync()
	{
		list($paramA,$paramB) = (func_num_args()>1)?func_get_args():array("","");
		$this->a		=	$paramA;
		$this->b		=	$paramB;
		$this->status	=	CMD_STATUS_READY;
		$this->execInCmdLine($this);
	}
	/**
	 * Calculates the id.
	 */
	public function calculateId(){
		return md5(base64_encode(serialize($this)));
	}
	/**
	 * Returns the id.
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * Gets the id.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	/**
	 * Returns the current command status.
	 */
	public function getStatus()
	{
		return $this->status;
	}
	/**
	 * Sets the command status.
	 */
	public function setStatus($status){
		$this->status=$status;
	}
	/**
	 * Returns the invoker id.
	 */
	public function getInvokerId(){
		return $this->invokerId;
	}
	/**
	 * Sets the invoker id.
	 */
	public function setInvokerId($id)
	{
		$this->invokerId=$id;
	}
	/**
	 * Sets the verbose level.
	 */
	public function setVerboseLevel($level)
	{
		$this->verboseLevel=$level;
	}
	/**
	 * Sets starting time.
	 * i.e. startingTime    =   microtime(true);
	 */
	public function setStartingTime($time)
	{
		$this->startingTime=$time;
	}
	/**
	 * Sets ending time.
	 * i.e. endingTime    =   microtime(true);
	 */
	public function setEndingTime($time)
	{
		$this->endingTime = $time;
	}
	/**
	 * Calculates exec time in secs
	 */
	public function getExecutionTime()
	{
        $diff           =   (float)$this->endingTime-(float)$this->startingTime;
        $executionTime  =   $diff;
        /*if($minutes ==  true && abs($executionTime) > 0)
        {
            $executionTime = $executionTime/60;
        }*/
        return round($executionTime,2);
	}
	/**
	 * Sets the progression in %
	 */
	public function getProgress()
	{
		return $this->progression;
	}
	/**
	 * Gets the progression in %
	 */
	public function setProgress($amount)
	{
		$this->progression=$amount;
	}
	/**
	 * Prints the output depending on verbosity level.
	 */
	public function printOutput($message,$msgLevel)
	{
		if($this->verboseLevel >= $msgLevel)
		{
			echo $message;
		}
	}
}

/**
 * Invoker executes the given command.
 * commandInvoker
 */
class Invoker
{
	/**
	 * @var command
	 */
    private $command;
	/**
	 * @var verboseLevel stores the verbose level
	 */
	private $verboseLevel;

    /**
     * in the invoker we find this kind of method for subscribing the command
     * There can be also a stack, a list, a fixed set ...
     */
    public function setCommand(Command $cmd)
    {
        $this->command = $cmd;
		if(!empty($this->verboseLevel))
		{
			$this->command->setVerboseLevel($this->verboseLevel);
		}
		//$this->command->setVerboseLevel($this->verboseLevel);
    }
    /**
     * executes the command; the invoker is the same whatever is the command
     */
    public function run()
    {
        $this->command->execute();
    }
	/**
	 * Sets the verbose level.
	 */
	public function setVerboseLevel($level)
	{
		$this->verboseLevel = $level;
	}
	/**
	 * returns the verbose level.
	 */
	public function getVerboseLevel()
	{
		return $this->verboseLevel;
	}
}
?>