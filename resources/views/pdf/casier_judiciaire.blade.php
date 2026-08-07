<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Casier Judiciaire - {{ $militaire->matricule ?? 'Sans matricule' }}</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2d5a3d;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 18px;
            color: #2d5a3d;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header h2 {
            font-size: 14px;
            color: #555;
            margin: 0;
            font-weight: normal;
        }
        .header .subtitle {
            font-size: 11px;
            color: #888;
            margin-top: 5px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #2d5a3d;
            background-color: #f0f5f1;
            padding: 6px 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #2d5a3d;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-grid td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .info-grid .label {
            font-weight: bold;
            color: #555;
            width: 25%;
        }
        .info-grid .value {
            color: #1a1a1a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }
        .data-table th {
            background-color: #2d5a3d;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: right;
            font-size: 10px;
            color: #888;
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .mention {
            font-size: 10px;
            color: #888;
            font-style: italic;
            margin-top: 5px;
        }
        .neant {
            text-align: center;
            font-style: italic;
            color: #888;
            padding: 30px;
            font-size: 14px;
        }
        .badge-pluriel {
            display: inline-block;
            background-color: #e9d5ff;
            color: #6b21a8;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-principal {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-condamnation {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-acquittement {
            display: inline-block;
            background-color: #16a34a;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .co-accuse {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .infraction-item {
            display: inline-block;
            background-color: #f3f4f6;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            margin: 2px 2px 2px 0;
        }
        .badge-statut {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-statut-actif {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-statut-suspendu {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-statut-retraite {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-statut-radie {
            background-color: #e5e7eb;
            color: #374151;
        }
        .peine-detail {
            color: #dc2626;
            font-weight: bold;
        }
        .condamnation-tag {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .statut-normalized {
            font-weight: bold;
        }
        .statut-actif { color: #065f46; }
        .statut-non-activite { color: #92400e; }
        .statut-retraite { color: #1e40af; }
        .statut-radie { color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <h1>République du Mali</h1>
        <h2>Ministère de la Défense et des Anciens Combattants</h2>
        <div class="subtitle">Direction de la Justice Militaire</div>
        <h1 style="margin-top: 15px;">Casier Judiciaire Militaire</h1>
    </div>

    <!-- SECTION I : IDENTITÉ -->
    <div class="section-title">I - IDENTITÉ DU MILITAIRE</div>
    <table class="info-grid">
        <tr>
            <td class="label">Matricule</td>
            <td class="value"><strong>{{ $militaire->matricule ?? 'Non défini' }}</strong></td>
            <td class="label">Grade</td>
            <td class="value">{{ $militaire->grade->libelle ?? $militaire->grade ?? 'Non renseigné' }}</td>
        </tr>
        <tr>
            <td class="label">Nom</td>
            <td class="value">{{ strtoupper($militaire->nom ?? 'Inconnu') }}</td>
            <td class="label">Prénoms</td>
            <td class="value">{{ $militaire->prenoms ?? 'Inconnu' }}</td>
        </tr>
        <tr>
            <td class="label">Date de naissance</td>
            <td class="value">{{ $militaire->date_naissance ? \Carbon\Carbon::parse($militaire->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</td>
            <td class="label">Lieu de naissance</td>
            <td class="value">{{ $militaire->lieu_naissance ?? 'Non renseigné' }}</td>
        </tr>
        <tr>
            <td class="label">Genre</td>
            <td class="value">{{ $militaire->genre ?? 'Non renseigné' }}</td>
            <td class="label">Statut</td>
            <td class="value">
                @php
                    $statutRaw = $militaire->statut ?? 'Non renseigné';
                    $statutDisplay = $statutRaw;
                    $statutClass = 'badge-statut';
                    $statutColor = '';

                    // Normaliser les statuts pour l'affichage
                    if (in_array($statutRaw, ['Actif', 'En activite', 'En activité'])) {
                        $statutDisplay = 'En activité';
                        $statutClass .= ' badge-statut-actif';
                        $statutColor = 'statut-actif';
                    } elseif (in_array($statutRaw, ['Inactif', 'Non activite', 'Non activité', 'Suspendu'])) {
                        $statutDisplay = 'Non activité';
                        $statutClass .= ' badge-statut-suspendu';
                        $statutColor = 'statut-non-activite';
                    } elseif (in_array($statutRaw, ['Retraité', 'En retraite'])) {
                        $statutDisplay = 'En retraite';
                        $statutClass .= ' badge-statut-retraite';
                        $statutColor = 'statut-retraite';
                    } elseif ($statutRaw === 'Radié') {
                        $statutDisplay = 'Radié';
                        $statutClass .= ' badge-statut-radie';
                        $statutColor = 'statut-radie';
                    } else {
                        $statutClass .= ' badge-statut-radie';
                    }
                @endphp
                <span class="{{ $statutClass }}">{{ $statutDisplay }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">Filiation Père</td>
            <td class="value">
                @if($militaire->prenoms_pere && $militaire->nom_pere)
                    {{ $militaire->prenoms_pere }} {{ $militaire->nom_pere }}
                @elseif($militaire->nom_pere)
                    {{ $militaire->nom_pere }}
                @else
                    Non renseigné
                @endif
            </td>
            <td class="label">Filiation Mère</td>
            <td class="value">
                @if($militaire->prenoms_mere && $militaire->nom_mere)
                    {{ $militaire->prenoms_mere }} {{ $militaire->nom_mere }}
                @elseif($militaire->nom_mere)
                    {{ $militaire->nom_mere }}
                @else
                    Non renseigné
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Armée/Service</td>
            <td class="value">{{ $militaire->armee ?? ($militaire->armeeRelation->nom ?? 'Non renseigné') }}</td>
            <td class="label">Unité</td>
            <td class="value">{{ $militaire->unite ?? 'Non renseignée' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse</td>
            <td class="value" colspan="3">{{ $militaire->adresse ?? 'Non renseignée' }}</td>
        </tr>
        <tr>
            <td class="label">Téléphone</td>
            <td class="value" colspan="3">{{ $militaire->telephone ?? 'Non renseigné' }}</td>
        </tr>
    </table>

    <!-- SECTION II : PROCÉDURES EN COURS -->
    <div class="section-title">II - PROCÉDURES EN COURS</div>

    @php
        $enCours = collect();
        if (isset($procedures) && $procedures->count() > 0) {
            $enCours = $procedures->filter(function($p) {
                $estCloturee = $p->phase === 'Cloturee';
                $aJugement = $p->jugement !== null;
                return !$estCloturee && !$aJugement;
            });
        }
    @endphp

    @if($enCours->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Procédure</th>
                    <th>Date ouverture</th>
                    <th>Phase actuelle</th>
                    <th>Parquet</th>
                    <th>Infraction(s)</th>
                    <th>Autres accusés</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enCours as $procedure)
                <tr>
                    <td>{{ $procedure->numero_procedure ?? 'Non défini' }}</td>
                    <td>{{ $procedure->date_ouverture ? \Carbon\Carbon::parse($procedure->date_ouverture)->format('d/m/Y') : ($procedure->created_at ? $procedure->created_at->format('d/m/Y') : 'Non définie') }}</td>
                    <td>{{ str_replace('_', ' ', $procedure->phase ?? 'En cours') }}</td>
                    <td>
                        @if($procedure->parquet)
                            {{ $procedure->parquet->nom }}
                            <small style="color:#888;">({{ $procedure->parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun' }})</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $infractionsDuMilitaire = $procedure->infractions_pivot_models ?? collect();
                        @endphp
                        @if($infractionsDuMilitaire && $infractionsDuMilitaire->count() > 0)
                            @foreach($infractionsDuMilitaire as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @elseif($procedure->infractions && $procedure->infractions->count() > 0)
                            @foreach($procedure->infractions as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($procedure->est_plurielle && $procedure->procedureMilitaires && $procedure->procedureMilitaires->count() > 1)
                            <span class="badge-pluriel">Pluriel</span>
                            <div class="co-accuse">
                                @foreach($procedure->procedureMilitaires as $pm)
                                    @if($pm->militaire_id != $militaire->id)
                                        - {{ $pm->militaire->nom ?? $pm->nom_temp ?? 'Inconnu' }} 
                                        {{ $pm->militaire->prenoms ?? $pm->prenom_temp ?? '' }}
                                        @if($pm->est_principal && $pm->militaire_id == $procedure->militaire_id)
                                            <span class="badge-principal">Principal</span>
                                        @endif
                                        <br>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="neant">Aucune procédure en cours</div>
    @endif

    <!-- SECTION III : CONDAMNATIONS -->
    <div class="section-title">III - CONDAMNATIONS</div>

    @php
        $condamnations = collect();
        if (isset($procedures) && $procedures->count() > 0) {
            $condamnations = $procedures->filter(function($p) {
                if ($p->jugement && $p->jugement->verdict === 'Condamnation') {
                    return true;
                }
                if ($p->est_condamne === true) {
                    return true;
                }
                foreach ($p->procedurePhases as $phase) {
                    if ($phase->phaseType && $phase->phaseType->slug === 'ordre_de_poursuite') {
                        if ($phase->est_condamne || !empty($phase->peine_principale)) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }
    @endphp

    @if($condamnations->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Procédure</th>
                    <th>Date condamnation</th>
                    <th>Juridiction</th>
                    <th>Infraction(s)</th>
                    <th>Peine</th>
                    <th>Autres accusés</th>
                </tr>
            </thead>
            <tbody>
                @foreach($condamnations as $procedure)
                @php
                    $dateCondamnation = $procedure->date_condamnation ?? $procedure->jugement->date_jugement ?? $procedure->date_ouverture ?? null;
                    $peine = $procedure->peine_principale ?? $procedure->jugement->peine_principale ?? null;
                    $description = $procedure->peine_description ?? $procedure->jugement->peines_complementaires ?? null;
                @endphp
                <tr>
                    <td>{{ $procedure->numero_procedure ?? 'Non défini' }}</td>
                    <td>{{ $dateCondamnation ? \Carbon\Carbon::parse($dateCondamnation)->format('d/m/Y') : 'Non définie' }}</td>
                    <td>
                        @if($procedure->parquet)
                            {{ $procedure->parquet->nom }}
                            <small style="color:#888;">({{ $procedure->parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun' }})</small>
                        @elseif($procedure->jugement && $procedure->jugement->juridiction)
                            {{ $procedure->jugement->juridiction }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $infractionsDuMilitaire = $procedure->infractions_pivot_models ?? collect();
                        @endphp
                        @if($infractionsDuMilitaire && $infractionsDuMilitaire->count() > 0)
                            @foreach($infractionsDuMilitaire as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @elseif($procedure->infractions && $procedure->infractions->count() > 0)
                            @foreach($procedure->infractions as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($peine)
                            <span class="peine-detail">{{ $peine }}</span>
                            @if($description)
                                <br><small style="color:#666;">{{ $description }}</small>
                            @endif
                            <br><span class="condamnation-tag">Condamné</span>
                        @else
                            <span class="condamnation-tag">Condamné</span>
                        @endif
                    </td>
                    <td>
                        @if($procedure->est_plurielle && $procedure->procedureMilitaires && $procedure->procedureMilitaires->count() > 1)
                            <span class="badge-pluriel">Pluriel</span>
                            <div class="co-accuse">
                                @foreach($procedure->procedureMilitaires as $pm)
                                    @if($pm->militaire_id != $militaire->id)
                                        - {{ $pm->militaire->nom ?? $pm->nom_temp ?? 'Inconnu' }} 
                                        {{ $pm->militaire->prenoms ?? $pm->prenom_temp ?? '' }}
                                        @if($pm->est_principal && $pm->militaire_id == $procedure->militaire_id)
                                            <span class="badge-principal">Principal</span>
                                        @endif
                                        <br>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="neant">Aucune condamnation</div>
    @endif

    <!-- SECTION IV : ACQUITTEMENTS -->
    @php
        $acquittements = collect();
        if (isset($procedures) && $procedures->count() > 0) {
            $acquittements = $procedures->filter(function($p) {
                return $p->jugement && $p->jugement->verdict === 'Acquittement';
            });
        }
    @endphp

    @if($acquittements->count() > 0)
        <div class="section-title">IV - ACQUITTEMENTS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Jugement</th>
                    <th>Date jugement</th>
                    <th>Juridiction</th>
                    <th>Infraction(s)</th>
                    <th>Motif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($acquittements as $procedure)
                <tr>
                    <td>{{ $procedure->jugement->numero_jugement ?? 'Non défini' }}</td>
                    <td>{{ $procedure->jugement->date_jugement ? \Carbon\Carbon::parse($procedure->jugement->date_jugement)->format('d/m/Y') : 'Non définie' }}</td>
                    <td>{{ $procedure->jugement->juridiction ?? 'Non définie' }}</td>
                    <td>
                        @php
                            $infractionsDuMilitaire = $procedure->infractions_pivot_models ?? collect();
                        @endphp
                        @if($infractionsDuMilitaire && $infractionsDuMilitaire->count() > 0)
                            @foreach($infractionsDuMilitaire as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @elseif($procedure->infractions && $procedure->infractions->count() > 0)
                            @foreach($procedure->infractions as $inf)
                                <span class="infraction-item">{{ $inf->libelle }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $procedure->jugement->motif_acquittement ?? 'Non précisé' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- SECTION V : RÉCAPITULATIF -->
    <div class="section-title">V - RÉCAPITULATIF</div>
    <table class="info-grid">
        <tr>
            <td class="label">Total procédures</td>
            <td class="value">{{ isset($procedures) ? $procedures->count() : 0 }}</td>
            <td class="label">Procédures en cours</td>
            <td class="value">{{ $enCours->count() }}</td>
        </tr>
        <tr>
            <td class="label">Condamnations</td>
            <td class="value">{{ $condamnations->count() }}</td>
            <td class="label">Acquittements</td>
            <td class="value">{{ $acquittements->count() }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Document édité le {{ $date_edition ?? now()->format('d/m/Y à H:i') }}</p>
        <p class="mention">Ce document est un extrait du casier judiciaire militaire. Il est strictement confidentiel et réservé à un usage officiel.</p>
        <p class="mention">Ministère de la Défense - Direction de la Justice Militaire © {{ now()->year }}</p>
    </div>
</body>
</html>