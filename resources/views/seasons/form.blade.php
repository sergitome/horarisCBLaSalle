@extends('layouts.app', ['title' => $season->exists ? 'Editar temporada' : 'Nova temporada', 'heading' => $season->exists ? 'Editar temporada' : 'Nova temporada'])
@section('content')
<form class="panel grid gap-5 p-6 md:grid-cols-2" method="POST" action="{{ $season->exists ? route('seasons.update', $season) : route('seasons.store') }}">
    @csrf @if($season->exists) @method('PUT') @endif
    <div class="md:col-span-2"><label class="label">Nom</label><input class="field" name="name" value="{{ old('name', $season->name) }}" required></div>
    <div><label class="label">Data inici</label><input class="field" type="date" name="start_date" value="{{ old('start_date', optional($season->start_date)->toDateString()) }}" required></div>
    <div><label class="label">Data final</label><input class="field" type="date" name="end_date" value="{{ old('end_date', optional($season->end_date)->toDateString()) }}" required></div>
    <div class="md:col-span-2"><label class="label">Notes</label><textarea class="field" name="notes">{{ old('notes', $season->notes) }}</textarea></div>
    <label class="flex items-center gap-3 md:col-span-2"><input type="checkbox" name="active" value="1" @checked(old('active', $season->active ?? true))> Activa</label>
    <div class="md:col-span-2 flex gap-3"><button class="btn-primary">Guardar</button></div>
</form>
@if($season->exists)
    <form method="POST" action="{{ route('seasons.destroy', $season) }}" class="mt-4">@csrf @method('DELETE')<button class="btn-danger">Eliminar temporada</button></form>
@endif
@endsection
