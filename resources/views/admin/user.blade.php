@extends('layout.admin')
<table class="table">
@foreach($users as $user)
<tr>
<td>{{ $user['id'] }}</td>
<td>{{ $user['name'] }}</td>
</tr>
@endforeach
</table>

