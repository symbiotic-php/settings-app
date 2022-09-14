@extends('settings::backend/layout')
<h1>{{\ucfirst($package->getId())}} settings</h1>
@if(is_object($form) && method_exists($form,'render'))
{!! $form->render() !!}
    @else
    <h2>{{$this->lang('The module (:module) has no settings!',['module'=>$package->getId()])}}</h2>
@endif

