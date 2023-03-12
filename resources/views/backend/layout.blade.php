@extends('ui_backend::layout')

@section('sidebar')
    <li>
      <a href="{{$this->adminRoute('settings::system.index')}}" >
          <i class="ti-panel"></i>
          Системные настройки
      </a>
    </li>
    <li>
      <a href="{{$this->adminRoute('settings::filesystems.index')}}" >
          <i class="ti-harddrives"></i>
          Файловые системы</a>
    </li>
    <li>
        <a href="{{$this->adminRoute('settings::databases.index')}}" >
            <i class="ti-server"></i>
            Базы данных</a>
    </li>
    <li>
    <a href="{{$this->adminRoute('settings::packages.index')}}">
               <i class="ti-package"></i>
               Настройки приложений
   </a>
    </li>
@stop
<div class="section center" style="max-width: 950px">
    @yield('content')
</div>

