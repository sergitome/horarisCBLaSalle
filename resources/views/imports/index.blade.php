@extends('layouts.app', ['title' => 'Importacions', 'heading' => 'Importacions FBIB'])

@section('content')
<section class="space-y-6">
    <section class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
        <article class="panel p-6">
            <h3 class="text-xl font-bold">Clubs configurats</h3>
            <p class="mt-4 text-sm text-stone-500">
                La importacio s'executa en el moment i consulta directament els endpoints de la FBIB.
            </p>
            <div class="mt-4 space-y-4">
                @foreach($clubs as $club)
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-semibold">{{ $club->name }}</p>
                                <p class="text-sm text-stone-500">FBIB {{ $club->fbib_club_id }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('imports.teams', $club) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary">Importar equips</button>
                                </form>

                                <form method="POST" action="{{ route('imports.matches', $club) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary">Importar partits</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="panel p-6">
            <h3 class="text-xl font-bold">Historial d'importacions</h3>
            <div class="mt-4 space-y-3">
                @foreach($executions as $execution)
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <p class="font-semibold">{{ strtoupper($execution->type) }} - {{ $execution->importClub?->name }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ optional($execution->started_at)->format('d/m/Y H:i') }} - {{ $execution->result }}</p>
                        <p class="mt-2 text-xs text-stone-500">
                            Creats {{ $execution->created_count }} -
                            Actualitzats {{ $execution->updated_count }} -
                            Omesos {{ $execution->skipped_count }} -
                            Errors {{ $execution->error_count }}
                        </p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $executions->links() }}</div>
        </article>
    </section>
</section>
@endsection
