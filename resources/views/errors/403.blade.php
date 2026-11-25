@extends('errors.layout')

{{-- A policy may refuse with a reason of its own (AdminPolicy does, in Russian): show it then. --}}
@php($reason = isset($exception) && preg_match('/\p{Cyrillic}/u', $exception->getMessage()) ? $exception->getMessage() : null)

@section('code', '403')
@section('title', 'Доступ запрещён')
@section('message', $reason ?? 'У вашей учётной записи нет прав на эту страницу или действие. Если они нужны для работы, обратитесь к администратору.')
