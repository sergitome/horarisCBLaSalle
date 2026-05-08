@extends('layouts.app', ['title' => 'Temporades', 'heading' => 'Temporades'])
@section('content')
<div class="panel p-6">
    <div class="flex items-end justify-between gap-4">
        <form class="grid gap-3 md:grid-cols-3">
            <input class="field" name="search" placeholder="Nom temporada" value="{{ request('search') }}">
            <select class="field" name="active"><option value="">Totes</option><option value="1" @selected(request('active')==='1')>Actives</option><option value="0" @selected(request('active')==='0')>Inactives</option></select>
            <button class="btn-secondary" type="submit">Filtrar</button>
        </form>
        <a class="btn-primary" href="{{ route('seasons.create') }}">Nova temporada</a>
    </div>
    <div class="mt-6 space-y-3">
        @foreach($seasons as $season)
            <div class="rounded-2xl bg-stone-50 p-4 flex items-center justify-between">
                <div><p class="font-semibold">{{ $season->name }}</p><p class="text-sm text-stone-500">{{ $season->start_date->format('d/m/Y') }} - {{ $season->end_date->format('d/m/Y') }}</p></div>
                <a class="btn-secondary" href="{{ route('seasons.edit', $season) }}">Editar</a>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $seasons->links() }}</div>
</div>
@endsection
