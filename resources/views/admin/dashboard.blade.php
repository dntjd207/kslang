@extends('layouts.admin')

@section('title', 'Dashboard - kslang Admin')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-common.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-indigo-600">-</p>
                <p class="text-sm text-gray-500 mt-1">Total Slangs</p>
            </div>
        </x-common.card>

        <x-common.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600">-</p>
                <p class="text-sm text-gray-500 mt-1">Categories</p>
            </div>
        </x-common.card>

        <x-common.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600">-</p>
                <p class="text-sm text-gray-500 mt-1">Active Slangs</p>
            </div>
        </x-common.card>

        <x-common.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-purple-600">-</p>
                <p class="text-sm text-gray-500 mt-1">Total Examples</p>
            </div>
        </x-common.card>
    </div>
@endsection
