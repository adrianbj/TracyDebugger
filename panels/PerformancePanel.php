<?php namespace ProcessWire;

use Tracy\Debugger;

Debugger::timer('performance');
require_once __DIR__ . '/PerformancePanel/Panel.php';
require_once __DIR__ . '/PerformancePanel/Register.php';
Debugger::getBar()->addPanel(new \Zarganwar\PerformancePanel\Panel);
TracyDebugger::$panelGenerationTime['performance']['time'] = Debugger::timer('performance');
