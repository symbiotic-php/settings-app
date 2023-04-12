@extends('settings::backend/layout')
<h1>{{$this->lang('Databases')}}</h1>

@if($databases)
    <table class="">
        <tr>
            <th>id</th>
            <th>Name</th>
            <th>Type</th>
            <th></th>
        </tr>
        @foreach($databases->all() as $id => $v)
            <tr>
                <td>{{$id}}</td>
                <td>{{$v['title']}}</td>
                <td>{{$v['driver']}}</td>
                <td><a href="{{$this->adminRoute('databases.edit', ['id'=> $id])}}" target="_blank">{{$this->lang('Edit')}}</a> </td>
            </tr>
        @endforeach
    </table>
@endif
<a href="{{$this->adminRoute('databases.selectType')}}" class="button primary">{{$this->lang('Add')}}</a>