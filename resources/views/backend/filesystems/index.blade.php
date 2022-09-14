@extends('settings::backend/layout')
<h1>{{$this->lang('Filesystems')}}</h1>

@if($filesystems)
    <table>
        <tr>
            <th>id</th>
            <th>Name</th>
            <th>Type</th>
            <th></th>
        </tr>
        @foreach($filesystems->all() as $id => $v)
            <tr>
                <td>{{$id}}</td>
                <td>{{$v['name']}}</td>
                <td>{{$v['driver']}}</td>
                <td><a href="{{$this->adminRoute('filesystems.edit', ['id'=> $id])}}" target="_blank">{{$this->lang('Edit')}}</a> </td>
            </tr>
        @endforeach
    </table>
@endif
<a href="{{$this->adminRoute('filesystems.selectType')}}" class="button primary">{{$this->lang('Add')}}</a>