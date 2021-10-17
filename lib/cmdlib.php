<?php
/**
 * Command pattern concept based on refactoring.guru and designpatterns solutions.
 * 
 * src:
 * https://refactoring.guru/es/design-patterns/command/php/example
 * https://designpatternsphp.readthedocs.io/en/latest/Behavioral/Command/README.html
 * 
 * @version 1.0.0
 * @author  Ferrys
 * 
 */
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
	 * Returns the command id.
	 */
	public function getId();
	/**
	 * Returns the current command status.
	 */
	public function getStatus();
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
	 *  
	 */
	private $status;
	/**
	 * @var invokerId stores the invoker id
	 */
	private $invokerId;
 
	/**
     * Simple commands can accept one or several params via the constructor.
     */
    public function __construct($param)
    {
        $this->param 		= 	$param;
		$this->id			=	-1;
		$this->status		=	CMD_STATUS_CREATED;
		$this->invokerId	=	-1;
    }
 
	/**
	 * executes the command
	 */
    public function execute()
    {
		$this->status		=	CMD_STATUS_RUNNING;
        echo "SimpleCommand: See, I can do simple things like printing (" . $this->param . ")\n";
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
	 * Returns the id.
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * Returns the current command status.
	 */
	public function getStatus(){
		return $this->status;
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
}

/**
 * Commands can delegate more complex operations to other objects(Receivers).
 * Any class could serve as a Receiver.
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
		$this->status		=	CMD_STATUS_CREATED;
		$this->invokerId	=	-1;
    }

    /**
     * Commands can delegate to any methods of a receiver.
     */
    public function execute()
    {
		$this->status		=	CMD_STATUS_RUNNING;
		echo "ComplexCommand: Complex stuff should be done by a receiver object.\r\n";
		if($this->await === true)
		{
			echo "aWait call \r\n";
			$this->receiver->doAction($this->a,$this->b);
			$this->status	=	CMD_STATUS_TERMINATED;
		}else{
			echo "aSync call \r\n";
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
	 * Returns the id value
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the current command status.
	 */
	public function getStatus()
	{
		return $this->status;
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
		$this->invokerId=$id;
	}
}

/**
 * Invoker executes the given command.
 */
class Invoker
{
	/**
	 * @var command
	 */
    private $command;

    /**
     * in the invoker we find this kind of method for subscribing the command
     * There can be also a stack, a list, a fixed set ...
     */
    public function setCommand(Command $cmd)
    {
        $this->command = $cmd;
		echo "##########STATUS-> ".$this->command->getStatus()."\r\n";
    }

    /**
     * executes the command; the invoker is the same whatever is the command
     */
    public function run()
    {
        $this->command->execute();
		echo "##########STATUS-> ".$this->command->getStatus()."\r\n";
    }
}
?>