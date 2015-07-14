<?php
include_once('spider/SintegraEs.php');

$cnpj = '31804115000243';

$sintegra = new SintegraEs();
$sintegra->searchByCnpj( $cnpj );

