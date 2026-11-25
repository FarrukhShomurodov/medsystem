@extends('admin.layouts.app')

@section('title', $documentResponse->document->title)

@section('header')
    <x-page-header :title="$documentResponse->document->title" pretitle="Ответы на документ">
        {{-- The stay forms open this page in a new tab: there is no page to go back to, only the patient. --}}
        @can('view_patients')
            <x-slot:actions>
                <a href="{{ route('patients.show', $documentResponse->patient_id) }}" class="btn"><i class="bx bx-user" aria-hidden="true"></i>К карте пациента</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    @include('admin.patients.documents.answer-list', ['documentResponse' => $documentResponse])
@endsection
