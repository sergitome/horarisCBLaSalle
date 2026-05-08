@extends('layouts.app', ['title' => 'Logs', 'heading' => 'Logs d\'auditoria'])
@section('content')
<div class="panel p-6">
    <form class="grid gap-3 md:grid-cols-3">
        <input class="field" name="search" placeholder="Usuari / acció / entitat" value="{{ request('search') }}">
        <select class="field" name="entity"><option value="">Totes les entitats</option>@foreach($entities as $entity)<option value="{{ $entity }}" @selected(request('entity')===$entity)>{{ $entity }}</option>@endforeach</select>
        <button class="btn-secondary">Filtrar</button>
    </form>
    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead><tr class="text-stone-500"><th class="pb-3">Moment</th><th class="pb-3">Usuari</th><th class="pb-3">Acció</th><th class="pb-3">Entitat</th><th class="pb-3">Resultat</th></tr></thead>
            <tbody class="divide-y divide-stone-200">@foreach($logs as $log)<tr><td class="py-3">{{ $log->created_at?->format('d/m/Y H:i') }}</td><td class="py-3">{{ $log->username ?: 'Sistema' }}</td><td class="py-3">{{ $log->action }}</td><td class="py-3">{{ $log->entity }} #{{ $log->entity_id }}</td><td class="py-3">{{ $log->result }}</td></tr>@endforeach</tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
