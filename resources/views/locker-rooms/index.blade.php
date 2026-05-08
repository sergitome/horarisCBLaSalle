@extends('layouts.app', ['title' => 'Vestidors', 'heading' => 'Vestidors'])
@section('content')
<div class="panel p-6">
    <div class="flex items-end justify-between gap-4">
        <form class="grid gap-3 md:grid-cols-3">
            <input class="field" name="search" placeholder="Nom o pavelló" value="{{ request('search') }}">
            <select class="field" name="active"><option value="">Tots</option><option value="1" @selected(request('active')==='1')>Actius</option><option value="0" @selected(request('active')==='0')>Inactius</option></select>
            <button class="btn-secondary">Filtrar</button>
        </form>
        <a class="btn-primary" href="{{ route('locker-rooms.create') }}">Nou vestidor</a>
    </div>
    <div class="mt-6 space-y-3">@foreach($lockerRooms as $lockerRoom)<div class="rounded-2xl bg-stone-50 p-4 flex items-center justify-between"><div><p class="font-semibold">{{ $lockerRoom->name }}</p><p class="text-sm text-stone-500">{{ $lockerRoom->pavilion }}</p></div><a class="btn-secondary" href="{{ route('locker-rooms.edit', $lockerRoom) }}">Editar</a></div>@endforeach</div>
    <div class="mt-4">{{ $lockerRooms->links() }}</div>
</div>
@endsection
