# Command Pattern Concept

Command pattern concept based on refactoring.guru and designpatterns solutions.
## Description

Command pattern concept.

## Getting Started

### Dependencies

* PHP >= 5
* ex. Command line

### Executing program

* How to run a basic command.
```
    $invoker = new Invoker();
    $receiver = new SimpleCommand('Hello world');
    $invoker->setCommand($receiver);
    $invoker->run();
```
* How to run a complex command.
```
    $invoker = new Invoker();
    $receiver = new SimpleCommand('Hello world');
    $invoker->setCommand(new ComplexCommand($receiver,"Param_A","Param_B"));
    $invoker->run();
```
* How to run a nested complex command .
```
    $invoker        =   new Invoker();
    $receiver       =   new SimpleCommand('Hello worlddd');
	$complexCommand =   new ComplexCommand($receiver,'PARAM_AA','PARAM_BB');
    $invoker->setCommand(new ComplexCommand($complexCommand,'PARAM_A','PARAM_B'));
    $invoker->run();
```
* How to run a nested complex command aSync.
```
    $invoker        =   new Invoker();
    $receiver       =   new SimpleCommand('Hello worlddd');
	$complexCommand =   new ComplexCommand($receiver,'PARAM_AA','PARAM_BB');
    $invoker->setCommand(new ComplexCommand($complexCommand,'PARAM_A','PARAM_B',false));
    $invoker->run();
```

## Authors

Ferrys  

## Version History

* 1.0.0
    * Initial Release (X.Y.Z MAJOR.MINOR.PATCH)

## License

This project is licensed under the MiT License - see the LICENSE.txt file for details

## Acknowledgments

Based on/Inspired By:
* [refactoring.guru](https://refactoring.guru/es/design-patterns/command/php/example)
* [designpatternsphp](https://designpatternsphp.readthedocs.io/en/latest/Behavioral/Command/README.html)