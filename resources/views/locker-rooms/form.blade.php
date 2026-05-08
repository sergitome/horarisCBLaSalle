@extends('layouts.app', ['title' => $lockerRoom->exists ? 'Editar vestidor' : 'Nou vestidor', 'heading' => $lockerRoom->exists ? 'Editar vestidor' : 'Nou vestidor'])
@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $lockerRoom->exists ? route('locker-rooms.update', $lockerRoom) : route('locker-rooms.store') }}">
    @csrf @if($lockerRoom->exists) @method('PUT') @endif
    <div><label class="label">Nom</label><input class="field" name="name" value="{{ old('name', $lockerRoom->name) }}" required></div>
    <div><label class="label">Pavelló</label><input class="field" name="pavilion" value="{{ old('pavilion', $lockerRoom->pavilion) }}"></div>
    <div class="md:col-span-2"><label class="label">Descripció</label><textarea class="field" name="description">{{ old('description', $lockerRoom->description) }}</textarea></div>
    <label class="flex items-center gap-3 md:col-span-2"><input type="checkbox" name="active" value="1" @checked(old('active', $lockerRoom->active ?? true))> Actiu</label>
    <div class="md:col-span-2"><button class="btn-primary">Guardar</button></div>
</form>
@if($lockerRoom->exists)
    <form method="POST" action="{{ route('locker-rooms.destroy', $lockerRoom) }}" class="mt-4">@csrf @method('DELETE')<button class="btn-danger">Eliminar vestidor</button></form>
@endif
@endsection
