# Structure des fichiers audio CIE

Placez vos fichiers MP3 dans les dossiers correspondants à la langue.

## Langues disponibles
- `fr/`      — Français
- `dioula/`  — Dioula / Malinké
- `baoule/`  — Baoulé
- `bete/`    — Bété

## Types d'alerte (noms de fichiers attendus)
| Fichier               | Déclenchement         |
|-----------------------|-----------------------|
| `coupure_urgente.mp3` | Probabilité ≥ 80%     |
| `coupure_prevue.mp3`  | Probabilité 60–79%    |
| `coupure_possible.mp3`| Probabilité < 60%     |

## Exemple
```
public/audio/fr/coupure_urgente.mp3
public/audio/dioula/coupure_prevue.mp3
public/audio/baoule/coupure_urgente.mp3
```

## Fallback
Si le fichier MP3 n'existe pas, le système utilise
automatiquement la synthèse vocale du navigateur (Web Speech API).
