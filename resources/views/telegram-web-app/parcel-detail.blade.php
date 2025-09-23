@extends('layouts.telegram-web-app')

@section('content')
    <livewire:telegram-web-app.parcel-detail :parcel-id="$parcelId" />
@endsection

