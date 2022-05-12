@extends('settings::backend/layout')
<h1>{{\ucfirst($package->getId())}} settings</h1>
{!! $form->render() !!}

