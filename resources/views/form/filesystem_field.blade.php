{{{$field_id = $field->getId()?? md5(uniqid())}}}
{{{$value = $field->getValue()}}}
<select name="{{$field->getName()}}">
    <option value="" @if(empty($value)) selected@endif></option>
    <!-- Тут проверка на существование приложения в пакете и тогда берем его контейнер для доступа к файловым системам --->
    @foreach($this->app('files')->getDisks() as $name => $disk_config)
        <option value="{{$name}}" @if($value===$name) selected@endif>
            {{$name}}
        </option>
    @endforeach
</select>



