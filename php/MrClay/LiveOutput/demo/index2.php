<?php

require_once '../../LiveOutput.php';
require_once '../Renderer.php';
require_once '../Processor.php';

MrClay_LiveOutput::processFile(dirname(__FILE__) . '/stuff.php', 'StringDebug');
