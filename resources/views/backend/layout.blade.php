@extends('ui_backend::layout')

@section('sidebar')
    <li>
      <a href="{{adminRoute('settings::system.index')}}" >
          <i class="ti-panel"></i>
          Системные настройки
      </a>
    </li>
    <li>
      <a href="{{adminRoute('settings::filesystems.index')}}" >
          <i class="ti-harddrives"></i>
          Файловые системы</a>
    </li>
    <li>
    <a href="{{adminRoute('settings::packages.index')}}" >
               <i class="ti-package"></i>
               Настройки приложений
   </a>
    </li>
@stop
<div class="section center" style="max-width: 950px">
    @yield('content')
</div>

