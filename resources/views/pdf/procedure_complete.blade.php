<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procédure {{ $procedure->numero_procedure }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
        .header { text-align: center; border-bottom: 3px solid #2d5a3d; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; color: #2d5a3d; margin: 0 0 3px 0; text-transform: uppercase; }
        .header h2 { font-size: 12px; color: #555; margin: 0; font-weight: normal; }
        .header .subtitle { font-size: 10px; color: #888; margin-top: 3px; }
        .section-title { font-size: 12px; font-weight: bold; color: #2d5a3d; background-color: #f0f5f1; padding: 5px 8px; margin: 15px 0 8px 0; border-left: 3px solid #2d5a3d; page-break-after: avoid; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px; }
        .info-table td { padding: 3px 8px; vertical-align: top; border-bottom: 1px dotted #ddd; }
        .info-table .label { font-weight: bold; color: #555; width: 30%; }
        .info-table .value { color: #1a1a1a; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 10px; }
        .data-table th { background-color: #2d5a3d; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .data-table td { padding: 5px 8px; border-bottom: 1px solid #ddd; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-pluriel { background: #e9d5ff; color: #6b21a8; }
        .badge-nouveau { background: #fef3c7; color: #92400e; }
        .badge-principal { background: #d1fae5; color: #065f46; }
        .badge-individuel { background: #dbeafe; color: #1e40af; }
        .phase-block { border: 1px solid #ddd; padding: 10px; margin-bottom: 12px; border-radius: 4px; page-break-inside: avoid; }
        .phase-header { font-weight: bold; font-size: 12px; color: #2d5a3d; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .field-list { margin: 3px 0; padding-left: 15px; }
        .field-list li { font-size: 10px; margin: 1px 0; }
        .footer { text-align: right; font-size: 9px; color: #888; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 8px; }
        .neant { text-align: center; font-style: italic; color: #888; padding: 15px; font-size: 11px; }
        .militaire-card { border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; margin-bottom: 8px; background-color: #f9fafb; }
        .militaire-card-header { font-weight: bold; color: #2d5a3d; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 6px; }
        .infraction-tag { display: inline-block; background: #f3f4f6; padding: 1px 6px; border-radius: 3px; font-size: 9px; margin: 2px; }
        .faute-item { font-size: 9px; color: #555; display: block; }
        .pc-item { font-size: 9px; color: #555; display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>République du Mali</h1>
        <h2>Ministère de la Défense et des Anciens Combattants</h2>
        <div class="subtitle">Direction de la Justice Militaire</div>
        <h1 style="margin-top: 10px;">Dossier de Procédure Judiciaire</h1>
    </div>

    <!-- I - INFORMATIONS GÉNÉRALES -->
    <div class="section-title">I - INFORMATIONS GÉNÉRALES</div>
    <table class="info-table">
        <tr>
            <td class="label">N° Procédure</td>
            <td class="value"><strong>{{ $procedure->numero_procedure ?? 'Non défini' }}</strong></td>
            <td class="label">Date d'ouverture</td>
            <td class="value">{{ $procedure->date_ouverture ? \Carbon\Carbon::parse($procedure->date_ouverture)->format('d/m/Y') : 'Non définie' }}</td>
        </tr>
        <tr>
            <td class="label">Phase actuelle</td>
            <td class="value">{{ $procedure->phase ?? 'Non définie' }}</td>
            <td class="label">Parquet compétent</td>
            <td class="value">{{ $procedure->parquet_competent ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Type</td>
            <td class="value">
                @if($procedure->est_plurielle)
                    <span class="badge badge-pluriel">Pluriel ({{ $procedure->procedureMilitaires->count() ?? 0 }} militaires)</span>
                @else
                    <span class="badge badge-individuel">Individuel</span>
                @endif
            </td>
            <td class="label">Validé par</td>
            <td class="value">{{ $procedure->validateur->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Créé par</td>
            <td class="value">{{ $procedure->createur->name ?? '-' }}</td>
            <td class="label">Date création</td>
            <td class="value">{{ $procedure->created_at ? $procedure->created_at->format('d/m/Y à H:i') : 'Non définie' }}</td>
        </tr>
    </table>

    <!-- II - MILITAIRES CONCERNÉS -->
    <div class="section-title">
        @if($procedure->est_plurielle)
            II - MILITAIRES CONCERNÉS ({{ $procedure->procedureMilitaires->count() ?? 0 }})
        @else
            II - MILITAIRE CONCERNÉ
        @endif
    </div>

    @if($procedure->procedureMilitaires && $procedure->procedureMilitaires->count() > 0)
        @foreach($procedure->procedureMilitaires as $pm)
            <div class="militaire-card">
                <div class="militaire-card-header">
                    @if($procedure->est_plurielle)
                        Militaire #{{ $loop->iteration }}
                    @endif
                    @if($pm->militaire_id == $procedure->militaire_id)
                        <span class="badge badge-principal">Principal</span>
                    @endif
                    @if($pm->est_nouveau)
                        <span class="badge badge-nouveau">Nouveau</span>
                    @endif
                </div>
                <table class="info-table" style="margin-bottom:0;">
                    <tr>
                        <td class="label">Matricule</td>
                        <td class="value">{{ ($pm->militaire ? $pm->militaire->matricule : null) ?? $pm->matricule_temp ?? 'Non défini' }}</td>
                        <td class="label">Grade</td>
                        <td class="value">
                            @if($pm->militaire && $pm->militaire->grade)
                                {{ $pm->militaire->grade->libelle }}
                            @elseif($pm->grade_temp)
                                {{ $pm->grade_temp }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Nom</td>
                        <td class="value">{{ strtoupper(($pm->militaire ? $pm->militaire->nom : null) ?? $pm->nom_temp ?? 'Inconnu') }}</td>
                        <td class="label">Prénoms</td>
                        <td class="value">{{ ($pm->militaire ? $pm->militaire->prenoms : null) ?? $pm->prenom_temp ?? 'Inconnu' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Unité</td>
                        <td class="value">{{ $pm->militaire ? $pm->militaire->unite : '-' }}</td>
                        <td class="label">Statut</td>
                        <td class="value">{{ $pm->militaire ? $pm->militaire->statut : 'Non renseigné' }}</td>
                    </tr>
                    @if($pm->militaire)
                    <tr>
                        <td class="label">Genre</td>
                        <td class="value">{{ $pm->militaire->genre ?? '-' }}</td>
                        <td class="label">Armée</td>
                        <td class="value">{{ $pm->militaire->armee ?? '-' }}</td>
                    </tr>
                    @endif
                </table>

                <!-- Infractions du militaire -->
                @if($pm->infractions && count($pm->infractions) > 0)
                <div style="margin-top:6px; padding-top:6px; border-top:1px solid #e5e7eb;">
                    <strong style="font-size:10px;">Infractions :</strong>
                    @php
                        $infractions = \App\Models\InfractionBase::whereIn('id', $pm->infractions)->get();
                    @endphp
                    @foreach($infractions as $inf)
                        <span class="infraction-tag">{{ $inf->libelle }}</span>
                    @endforeach
                </div>
                @endif

                <!-- Fautes du militaire -->
                @if($pm->fautes_militaires && count($pm->fautes_militaires) > 0)
                <div style="margin-top:4px;">
                    <strong style="font-size:10px;">Fautes :</strong>
                    @foreach($pm->fautes_militaires as $faute)
                        <span class="faute-item">- {{ $faute['libelle'] ?? 'Non définie' }}</span>
                    @endforeach
                </div>
                @endif

                <!-- Parties civiles du militaire -->
                @if($pm->parties_civiles && count($pm->parties_civiles) > 0)
                <div style="margin-top:4px;">
                    <strong style="font-size:10px;">Parties civiles :</strong>
                    @foreach($pm->parties_civiles as $pc)
                        <span class="pc-item">
                            @if($pc['type'] === 'Structure')
                                Structure : {{ $pc['nom'] ?? 'Inconnu' }}
                            @else
                                Personne : {{ $pc['nom'] ?? 'Inconnu' }} {{ $pc['prenom'] ?? '' }}
                            @endif
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="neant">Aucun militaire associé</div>
    @endif

    <!-- III - PARTIES CIVILES -->
    @if($procedure->partiesCiviles && $procedure->partiesCiviles->count() > 0)
    <div class="section-title">III - PARTIES CIVILES</div>
    @foreach($procedure->partiesCiviles as $pc)
    <div style="margin-bottom:5px; padding:5px; background:#f9f9f9; border:1px solid #eee;">
        @if($pc->type === 'Structure')
            <strong>Structure :</strong> {{ $pc->nom ?? 'Inconnu' }}
        @else
            <strong>Personne :</strong> {{ $pc->nom ?? 'Inconnu' }} {{ $pc->prenom ?? '' }}@if($pc->profession), {{ $pc->profession }}@endif
        @endif
        @if($pc->adresse)<br><small>Adresse : {{ $pc->adresse }}</small>@endif
    </div>
    @endforeach
    @endif

    <!-- IV - FAUTES MILITAIRES -->
    @if($procedure->fautesMilitaires && $procedure->fautesMilitaires->count() > 0)
    <div class="section-title">IV - FAUTES MILITAIRES</div>
    @foreach($procedure->fautesMilitaires as $faute)
    <div style="margin-bottom:3px;">
        <strong>{{ $faute->libelle ?? 'Non définie' }}</strong>
        @if($faute->description) - {{ $faute->description }} @endif
    </div>
    @endforeach
    @endif

    <!-- V - HISTORIQUE DES PHASES -->
    <div class="section-title">V - HISTORIQUE DES PHASES</div>
    @if($procedure->procedurePhases && $procedure->procedurePhases->count() > 0)
        @foreach($procedure->procedurePhases as $phase)
        <div class="phase-block">
            <div class="phase-header">
                Phase : {{ $phase->libelle ?? $phase->phaseType->libelle ?? 'Phase sans nom' }}
                <span style="font-weight:normal; font-size:10px; color:#888;">
                    - Date : {{ $phase->date_phase ? \Carbon\Carbon::parse($phase->date_phase)->format('d/m/Y') : 'Non définie' }}
                    - Par : {{ $phase->createur->name ?? 'N/A' }}
                </span>
                @if($phase->est_retour)
                    <span class="badge badge-warning">Retour</span>
                @endif
            </div>

            @if($phase->description)
                <p style="font-size:10px; color:#555; margin:3px 0;">{{ $phase->description }}</p>
            @endif

            <!-- Champs dynamiques -->
            @if($phase->champs && $phase->champs->count() > 0)
                <strong style="font-size:10px;">Champs :</strong>
                <ul class="field-list">
                    @foreach($phase->champs as $champ)
                    <li>
                        <em>{{ str_replace('_', ' ', ucfirst($champ->cle)) }} :</em>
                        {{ $champ->valeur ?: 'Non renseigné' }}
                    </li>
                    @endforeach
                </ul>
            @endif

            <!-- Personnes -->
            @if($phase->personnes && $phase->personnes->count() > 0)
                <strong style="font-size:10px;">Personnes concernées :</strong>
                <ul class="field-list">
                    @foreach($phase->personnes as $p)
                    <li>
                        {{ $p->nom }} {{ $p->prenom }}
                        @if($p->profession) - {{ $p->profession }} @endif
                    </li>
                    @endforeach
                </ul>
            @endif

            <!-- Événements -->
            @if($phase->evenements && $phase->evenements->count() > 0)
                <strong style="font-size:10px;">Événements :</strong>
                <ul class="field-list">
                    @foreach($phase->evenements as $e)
                    <li>
                        {{ $e->nom }}
                        @if($e->date_evenement)
                            ({{ \Carbon\Carbon::parse($e->date_evenement)->format('d/m/Y') }})
                        @endif
                        @if($e->description)
                            - {{ $e->description }}
                        @endif
                    </li>
                    @endforeach
                </ul>
            @endif

            <!-- Références -->
            @if($phase->references && $phase->references->count() > 0)
                <strong style="font-size:10px;">Références :</strong>
                <ul class="field-list">
                    @foreach($phase->references as $r)
                    <li>
                        {{ $r->libelle }}
                        @if($r->description) - {{ $r->description }} @endif
                    </li>
                    @endforeach
                </ul>
            @endif

            <!-- Options cochées -->
            @php 
                $optionsCochees = $phase->optionsCocher ? $phase->optionsCocher->filter(fn($o) => $o->est_coche) : collect();
            @endphp
            @if($optionsCochees->count() > 0)
                <strong style="font-size:10px;">Options retenues :</strong>
                <span style="font-size:10px;">{{ $optionsCochees->pluck('libelle')->join(', ') }}</span>
            @endif

            <!-- Pièces jointes -->
            @if($phase->piecesJointes && $phase->piecesJointes->count() > 0)
                <strong style="font-size:10px;">Pièces jointes :</strong>
                <ul class="field-list">
                    @foreach($phase->piecesJointes as $pj)
                    <li>
                        {{ $pj->nom }}
                        @if($pj->description) - {{ $pj->description }} @endif
                        @if($pj->chemin_fichier) <span style="color:#2d5a3d;">[Fichier joint]</span> @endif
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
        @endforeach
    @else
        <div class="neant">Aucune phase enregistrée</div>
    @endif

    <!-- VI - JUGEMENT -->
    @if($procedure->jugement)
    <div class="section-title">VI - JUGEMENT</div>
    <table class="info-table">
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ $procedure->jugement->date_jugement ? \Carbon\Carbon::parse($procedure->jugement->date_jugement)->format('d/m/Y') : 'Non définie' }}</td>
            <td class="label">N° Jugement</td>
            <td class="value">{{ $procedure->jugement->numero_jugement ?? 'Non défini' }}</td>
        </tr>
        <tr>
            <td class="label">Juridiction</td>
            <td class="value">{{ $procedure->jugement->juridiction ?? 'Non définie' }}</td>
            <td class="label">Verdict</td>
            <td class="value"><strong>{{ $procedure->jugement->verdict ?? 'Non défini' }}</strong></td>
        </tr>
        @if($procedure->jugement->peine_principale)
        <tr>
            <td class="label">Peine principale</td>
            <td class="value" colspan="3">{{ $procedure->jugement->peine_principale }}</td>
        </tr>
        @endif
        @if($procedure->jugement->peines_complementaires)
        <tr>
            <td class="label">Peines complémentaires</td>
            <td class="value" colspan="3">{{ $procedure->jugement->peines_complementaires }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="footer">
        <p>Document édité le {{ $date_edition ?? now()->format('d/m/Y à H:i') }}</p>
        <p style="font-style:italic; font-size:9px;">Ce document est un extrait officiel du dossier de procédure judiciaire militaire. Strictement confidentiel.</p>
        <p style="font-style:italic; font-size:9px;">Ministère de la Défense - Direction de la Justice Militaire © {{ now()->year }}</p>
    </div>
</body>
</html>