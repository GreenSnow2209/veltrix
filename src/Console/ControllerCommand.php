<?php

namespace Veltrix\Console;

class ControllerCommand extends MakeCommand
{
    protected $signature = 'veltrix:controller
                            {name : Name of the Admin Controller}
                            {--model= : Fully qualified model class}
                            {--title= : Custom page title}';

    protected $aliases = ['admin:controller'];

    protected $description = 'Alias of veltrix:make';
}
