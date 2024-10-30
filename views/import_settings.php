<?php

if(!isset($_REQUEST["step"]) || $_REQUEST["step"] == 1)
{
    include_once __DIR__ . '/import_steps/step1.php';
}
elseif($_REQUEST["step"] == 2)
{
    include_once __DIR__ . '/import_steps/step2.php';
}