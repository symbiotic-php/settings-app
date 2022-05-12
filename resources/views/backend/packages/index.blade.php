@extends('settings::backend/layout')

<?php
/**
 * @var \Symbiotic\Packages\PackagesRepositoryInterface $packages
 * @uses \Symbiotic\Core\View\adminRoute
 */
?>

<h1>{{lang('Packages settings')}}</h1>
<table>
    @foreach($packages->all() as $package_data)
        {{var_dump($package_data)}}
        @if(isset($package_data['settings_controller']) || isset($package_data['settings_fields']) || isset($package_data['settings_form']))
            {{{$package = $packages->getPackageConfig($package_data['id'])}}}
            <tr>
                <td><a href="{{adminRoute('package.edit', ['package_id' => $package_data['id']])}}">{{$package->getId()}}</a></td>
            </tr>
        @endif
    @endforeach
</table>




